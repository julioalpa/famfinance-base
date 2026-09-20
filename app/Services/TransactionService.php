<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Installment;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    /**
     * Crea una transacción y, si tiene cuotas, genera los registros de installments.
     *
     * @param  array  $data  Datos validados del request
     * @param  int  $userId  Quien la registra
     */
    public function create(array $data, int $familyGroupId, int $userId): Transaction
    {
        return DB::transaction(function () use ($data, $familyGroupId, $userId) {

            // Calcular monto por cuota si aplica
            if (! empty($data['has_installments']) && ! empty($data['installments_count'])) {
                $data['installment_amount'] = round($data['amount'] / $data['installments_count'], 2);
            }

            $transaction = Transaction::create([
                ...$data,
                'family_group_id' => $familyGroupId,
                'user_id' => $userId,
            ]);

            if ($transaction->has_installments && $transaction->installments_count > 1) {
                $this->generateInstallments($transaction);
            }

            return $transaction;
        });
    }

    /**
     * Actualiza una transacción. Si cambia el monto o las cuotas,
     * borra y regenera los installments.
     */
    public function update(Transaction $transaction, array $data): Transaction
    {
        return DB::transaction(function () use ($transaction, $data) {

            $reinstall = isset($data['amount']) || isset($data['installments_count']);

            if (! empty($data['has_installments']) && ! empty($data['installments_count'])) {
                $data['installment_amount'] = round($data['amount'] / $data['installments_count'], 2);
            }

            $transaction->update($data);

            if ($reinstall && $transaction->fresh()->has_installments) {
                $transaction->installments()->delete();
                $this->generateInstallments($transaction->fresh());
            }

            return $transaction->fresh();
        });
    }

    /**
     * Genera los registros de cuotas para una transacción en cuotas.
     * La cuota 1 cae en el mes del resumen al que pertenece la compra:
     * si se compró antes del cierre, en el mes de la compra; si fue después,
     * en el mes siguiente. El día de vencimiento de cada cuota es el `due_day`
     * de la tarjeta (acotado al último día del mes cuando corresponda); si la
     * tarjeta no tiene `due_day` configurado, se usa el día 1 como fallback.
     */
    private function generateInstallments(Transaction $transaction): void
    {
        $account = Account::find($transaction->account_id);

        // Día de cierre de la tarjeta (default 1 si no está configurado)
        $closingDay = $account->closing_day ?? 1;

        // Día de vencimiento del resumen (fecha de débito). Fallback: día 1.
        $dueDay = $account->due_day ?: 1;

        $purchaseDay = $transaction->date->day;
        $startMonth = $transaction->date->copy()->startOfMonth();

        if ($purchaseDay > $closingDay) {
            $startMonth->addMonth();
        }

        $installments = [];

        for ($i = 1; $i <= $transaction->installments_count; $i++) {
            $monthBase = $startMonth->copy()->addMonths($i - 1);
            // Acotar el día de vencimiento al último día del mes (ej: due_day 31
            // en febrero → 28/29) para no desbordar al mes siguiente.
            $dueOnMonth = min($dueDay, $monthBase->daysInMonth);
            $dueDate = $monthBase->copy()->day($dueOnMonth);

            $installments[] = [
                'transaction_id' => $transaction->id,
                'account_id' => $transaction->account_id,
                'installment_number' => $i,
                'amount' => $transaction->installment_amount,
                'due_date' => $dueDate->format('Y-m-d'),
                'is_paid' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        Installment::insert($installments);
    }
}
