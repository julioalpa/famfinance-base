<?php

namespace App\Http\Controllers;

use App\Http\Requests\PayMonthlyPaymentRequest;
use App\Models\MonthlyPayment;
use App\Models\PaymentItem;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

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
                $mp->is_dismissed ? 2 : ($mp->is_paid ? 1 : 0),
                $mp->paymentItem?->day_of_month ?? 99,
                $mp->paymentItem?->description,
            ])
            ->values();

        // Agregar el último monto pagado y % de diferencia a cada registro
        foreach ($monthlyPayments as $mp) {
            $mp->last_amount = $mp->paymentItem?->lastPaidAmount($mon, $year);
            $mp->pct_change  = null;
            if ($mp->is_paid && $mp->amount && $mp->last_amount > 0) {
                $mp->pct_change = round(((float) $mp->amount - (float) $mp->last_amount) / (float) $mp->last_amount * 100, 1);
            }
        }

        $group = Auth::user()->familyGroups()->find($groupId);
        $rate  = $group->latestExchangeRate();

        // Dismissed items se excluyen del conteo de progreso
        $activeMps  = $monthlyPayments->where('is_dismissed', false);
        $paidCount  = $activeMps->where('is_paid', true)->count();
        $totalCount = $activeMps->count();

        $totalPaid = $activeMps->where('is_paid', true)->sum(function ($mp) use ($rate) {
            $amt = (float) $mp->amount;
            if ($mp->paymentItem?->currency === 'USD' && $rate) {
                return $rate->convert($amt, 'USD');
            }
            return $amt;
        });

        // Oportunidad de ahorro: suma de ítems prescindibles (monto real si pagado, fijo o último si no)
        $dispensableTotal = $activeMps
            ->filter(fn($mp) => $mp->paymentItem?->is_dispensable)
            ->sum(function ($mp) use ($rate) {
                if ($mp->is_paid) {
                    $amt = (float) $mp->amount;
                } elseif ($mp->paymentItem?->is_direct_debit) {
                    $amt = (float) $mp->paymentItem->amount;
                } else {
                    $amt = (float) ($mp->last_amount ?? 0);
                }
                if ($mp->paymentItem?->currency === 'USD' && $rate) {
                    return $rate->convert($amt, 'USD');
                }
                return $amt;
            });

        return view('monthly-payments.index', compact(
            'monthlyPayments',
            'month',
            'mon',
            'year',
            'paidCount',
            'totalCount',
            'totalPaid',
            'dispensableTotal',
        ));
    }

    /** Confirmar un débito directo (un click, sin modal). */
    public function confirm(MonthlyPayment $monthlyPayment)
    {
        $this->authorizePayment($monthlyPayment);

        $item = $monthlyPayment->paymentItem;

        abort_if(! $item, 404, 'Ítem de pago no encontrado.');
        abort_unless($item->is_direct_debit, 400, 'Este ítem no es débito directo.');
        abort_if($monthlyPayment->is_paid, 400, 'Este pago ya fue registrado.');
        abort_if($monthlyPayment->is_dismissed, 400, 'Este pago está descartado.');

        DB::transaction(function () use ($monthlyPayment, $item) {
            $transaction = $this->transactionService->create(
                [
                    'account_id'  => $item->account_id,
                    'category_id' => $item->category_id,
                    'type'        => 'expense',
                    'amount'      => $item->amount,
                    'currency'    => $item->currency,
                    'date'        => now()->format('Y-m-d'),
                    'description' => $item->description,
                    'notes'       => $item->notes,
                ],
                $monthlyPayment->family_group_id,
                auth()->id(),
            );

            $monthlyPayment->update([
                'amount'         => $item->amount,
                'is_paid'        => true,
                'paid_at'        => now(),
                'transaction_id' => $transaction->id,
            ]);
        });

        $year  = $monthlyPayment->year;
        $month = str_pad($monthlyPayment->month, 2, '0', STR_PAD_LEFT);

        return redirect()
            ->route('monthly-payments.index', ['month' => "{$year}-{$month}"])
            ->with('success', "Débito de «{$item->description}» registrado correctamente.");
    }

    public function markPaid(PayMonthlyPaymentRequest $request, MonthlyPayment $monthlyPayment)
    {
        $this->authorizePayment($monthlyPayment);

        abort_if($monthlyPayment->is_paid, 400, 'Este pago ya fue registrado.');
        abort_if($monthlyPayment->is_dismissed, 400, 'Este pago está descartado.');

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

    /** Descartar el pago de este mes (no aplica, se pagó por otro lado, etc.). */
    public function dismiss(MonthlyPayment $monthlyPayment)
    {
        $this->authorizePayment($monthlyPayment);

        abort_if($monthlyPayment->is_paid, 400, 'No podés descartar un pago ya registrado.');

        $monthlyPayment->update(['is_dismissed' => true]);

        $item  = $monthlyPayment->paymentItem;
        $year  = $monthlyPayment->year;
        $month = str_pad($monthlyPayment->month, 2, '0', STR_PAD_LEFT);

        return redirect()
            ->route('monthly-payments.index', ['month' => "{$year}-{$month}"])
            ->with('success', "«{$item->description}» descartado para este mes.");
    }

    /** Restaurar un pago descartado. */
    public function undismiss(MonthlyPayment $monthlyPayment)
    {
        $this->authorizePayment($monthlyPayment);

        $monthlyPayment->update(['is_dismissed' => false]);

        $item  = $monthlyPayment->paymentItem;
        $year  = $monthlyPayment->year;
        $month = str_pad($monthlyPayment->month, 2, '0', STR_PAD_LEFT);

        return redirect()
            ->route('monthly-payments.index', ['month' => "{$year}-{$month}"])
            ->with('success', "«{$item->description}» restaurado.");
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
