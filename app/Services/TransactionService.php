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
     * @param  int    $familyGroupId
     * @param  int    $userId  Quien la registra
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
                'user_id'         => $userId,
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
     * La fecha de la primera cuota es el mes siguiente a la fecha de compra.
     */
    private function generateInstallments(Transaction $transaction): void
    {
        $account = Account::find($transaction->account_id);

        // Día de cierre de la tarjeta (default 1 si no está configurado)
        $closingDay = $account->closing_day ?? 1;

        // Si la compra se hizo después del cierre, la primera cuota es en 2 meses
        $purchaseDay  = $transaction->date->day;
        $startDate    = $transaction->date->copy()->startOfMonth()->addMonth();

        if ($purchaseDay > $closingDay) {
            $startDate->addMonth();
        }

        $installments = [];

        for ($i = 1; $i <= $transaction->installments_count; $i++) {
            $installments[] = [
                'transaction_id'     => $transaction->id,
                'account_id'         => $transaction->account_id,
                'installment_number' => $i,
                'amount'             => $transaction->installment_amount,
                'due_date'           => $startDate->copy()->addMonths($i - 1)->format('Y-m-d'),
                'is_paid'            => false,
                'created_at'         => now(),
                'updated_at'         => now(),
            ];
        }

        Installment::insert($installments);
    }
}
