<?php

namespace App\Http\Controllers;

use App\Http\Requests\PayMonthlyPaymentRequest;
use App\Models\MonthlyPayment;
use App\Models\PaymentItem;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MonthlyPaymentController extends Controller
{
    public function __construct(private TransactionService $transactionService) {}

    public function index(Request $request)
    {
        $groupId = session('active_family_group_id');

        $month = $request->get('month', now()->format('Y-m'));
        [$year, $mon] = array_map('intval', explode('-', $month));

        // Auto-crear registros del mes para ítems activos
        $activeItems = PaymentItem::where('family_group_id', $groupId)
            ->where('is_active', true)
            ->get();

        foreach ($activeItems as $item) {
            MonthlyPayment::firstOrCreate(
                [
                    'payment_item_id' => $item->id,
                    'month'           => $mon,
                    'year'            => $year,
                ],
                ['family_group_id' => $groupId]
            );
        }

        // Cargar todos los pagos del mes con relaciones
        $monthlyPayments = MonthlyPayment::with(['paymentItem.account', 'paymentItem.category', 'transaction'])
            ->where('family_group_id', $groupId)
            ->where('month', $mon)
            ->where('year', $year)
            ->get()
            ->sortBy(fn ($mp) => [
                $mp->is_paid ? 1 : 0,
                $mp->paymentItem?->day_of_month ?? 99,
                $mp->paymentItem?->description,
            ])
            ->values();

        // Agregar el último monto pagado a cada registro para pre-cargar el form
        foreach ($monthlyPayments as $mp) {
            $mp->last_amount = $mp->paymentItem?->lastPaidAmount($mon, $year);
        }

        $paidCount   = $monthlyPayments->where('is_paid', true)->count();
        $totalCount  = $monthlyPayments->count();
        $totalPaid   = $monthlyPayments->where('is_paid', true)->sum(fn ($mp) => (float) $mp->amount);

        return view('monthly-payments.index', compact(
            'monthlyPayments',
            'month',
            'mon',
            'year',
            'paidCount',
            'totalCount',
            'totalPaid',
        ));
    }

    public function markPaid(PayMonthlyPaymentRequest $request, MonthlyPayment $monthlyPayment)
    {
        $this->authorizePayment($monthlyPayment);

        abort_if($monthlyPayment->is_paid, 400, 'Este pago ya fue registrado.');

        $item = $monthlyPayment->paymentItem;

        DB::transaction(function () use ($request, $monthlyPayment, $item) {
            $transaction = $this->transactionService->create(
                [
                    'account_id'  => $item->account_id,
                    'category_id' => $item->category_id,
                    'type'        => 'expense',
                    'amount'      => $request->amount,
                    'currency'    => $item->currency,
                    'date'        => $request->date,
                    'description' => $item->description,
                    'notes'       => $request->notes,
                ],
                $monthlyPayment->family_group_id,
                auth()->id(),
            );

            $monthlyPayment->update([
                'amount'         => $request->amount,
                'is_paid'        => true,
                'paid_at'        => now(),
                'transaction_id' => $transaction->id,
            ]);
        });

        $year  = $monthlyPayment->year;
        $month = str_pad($monthlyPayment->month, 2, '0', STR_PAD_LEFT);

        return redirect()
            ->route('monthly-payments.index', ['month' => "{$year}-{$month}"])
            ->with('success', "Pago de «{$item->description}» registrado correctamente.");
    }

    public function markUnpaid(MonthlyPayment $monthlyPayment)
    {
        $this->authorizePayment($monthlyPayment);

        abort_if(! $monthlyPayment->is_paid, 400, 'Este pago no está marcado como pagado.');

        DB::transaction(function () use ($monthlyPayment) {
            // Eliminar la transacción vinculada
            if ($monthlyPayment->transaction) {
                $monthlyPayment->transaction->delete();
            }

            $monthlyPayment->update([
                'is_paid'        => false,
                'paid_at'        => null,
                'amount'         => null,
                'transaction_id' => null,
            ]);
        });

        $item  = $monthlyPayment->paymentItem;
        $year  = $monthlyPayment->year;
        $month = str_pad($monthlyPayment->month, 2, '0', STR_PAD_LEFT);

        return redirect()
            ->route('monthly-payments.index', ['month' => "{$year}-{$month}"])
            ->with('success', "Pago de «{$item->description}» desmarcado.");
    }

    private function authorizePayment(MonthlyPayment $monthlyPayment): void
    {
        abort_if(
            $monthlyPayment->family_group_id !== session('active_family_group_id'),
            403,
            'No tenés permiso para acceder a este pago.'
        );
    }
}
