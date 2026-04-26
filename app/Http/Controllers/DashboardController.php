<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Category;
use App\Models\ExchangeRate;
use App\Models\Installment;
use App\Models\MonthlyPayment;
use App\Models\PaymentItem;
use App\Models\RecurringExpense;
use App\Models\RecurringExpenseLog;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $groupId = session('active_family_group_id');
        $group   = auth()->user()->familyGroups()->with('members')->find($groupId);

        // Mes a visualizar (default: mes actual)
        $month   = $request->get('month', now()->format('Y-m'));
        [$year, $mon] = array_map('intval', explode('-', $month));

        // ── Cuentas activas del grupo ────────────────────────────────────────
        $accounts = $group->accounts()->where('is_active', true)->get();

        // ── Tipo de cambio vigente (necesario antes de cualquier cálculo) ────
        $exchangeRate = $group->latestExchangeRate();

        // ── Patrimonio neto (convertido a ARS) ───────────────────────────────
        $totalAssets      = $accounts->filter(fn($a) => ! $a->isLiability())
                                     ->sum(fn($a) => $a->balanceInArs($exchangeRate));
        $totalLiabilities = $accounts->filter(fn($a) => $a->isLiability())
                                     ->sum(fn($a) => $a->balanceInArs($exchangeRate));
        $netWorth         = $totalAssets - $totalLiabilities;

        // ── Totales del mes (split por moneda, luego convertir) ──────────────
        $baseQuery = Transaction::where('family_group_id', $groupId)
            ->whereYear('date', $year)
            ->whereMonth('date', $mon);

        $totalIncome  = $this->sumConverted(clone $baseQuery, 'income',  $exchangeRate);
        $totalExpense = $this->sumConverted(clone $baseQuery, 'expense', $exchangeRate);
        $balance      = $totalIncome - $totalExpense;

        // ── Gastos por categoría (para gráfico) ─────────────────────────────
        $expensesByCategoryRaw = (clone $baseQuery)
            ->where('type', 'expense')
            ->with('category')
            ->get()
            ->groupBy(fn($t) => $t->category?->id ?? 0)
            ->map(fn($items) => [
                'category' => $items->first()->category,
                'amount'   => $items->sum(fn($t) => $t->amountInArs($exchangeRate)),
            ])
            ->sortByDesc('amount')
            ->take(8);

        // Legacy: keyed by name for the view bar chart
        $expensesByCategory = $expensesByCategoryRaw
            ->mapWithKeys(fn($row) => [($row['category']?->name ?? 'Sin categoría') => $row['amount']]);

        // ── Cuotas pendientes por cuenta de crédito en el mes ───────────────
        $creditAccounts = $accounts->where('type', 'credit');
        $installmentSummary = $creditAccounts->map(function (Account $account) use ($mon, $year, $exchangeRate) {
            $installments = $account->getUpcomingInstallments($mon, $year);
            $total = $installments->sum(function ($inst) use ($exchangeRate) {
                $amt = (float) $inst->amount;
                if ($inst->transaction?->currency === 'USD' && $exchangeRate) {
                    return $exchangeRate->convert($amt, 'USD');
                }
                return $amt;
            });
            return [
                'account'      => $account,
                'installments' => $installments,
                'total'        => $total,
            ];
        })->filter(fn($item) => $item['installments']->isNotEmpty());

        // ── Últimas 10 transacciones ─────────────────────────────────────────
        $recentTransactions = Transaction::with(['account', 'category', 'user'])
            ->where('family_group_id', $groupId)
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        // ── Débitos fijos activos del grupo + logs del mes actual ────────────
        $recurringExpenses = RecurringExpense::with(['account', 'category'])
            ->where('family_group_id', $groupId)
            ->where('is_active', true)
            ->orderBy('day_of_month')
            ->get();

        $recurringLogs = RecurringExpenseLog::where('family_group_id', $groupId)
            ->where('month', now()->month)
            ->where('year', now()->year)
            ->get()
            ->keyBy('recurring_expense_id');

        // ── Pendientes del mes actual (para widget) ───────────────────────────
        $currentMon  = now()->month;
        $currentYear = now()->year;

        $activeItems = PaymentItem::where('family_group_id', $groupId)
            ->where('is_active', true)
            ->get();

        foreach ($activeItems as $item) {
            MonthlyPayment::firstOrCreate(
                [
                    'payment_item_id' => $item->id,
                    'month'           => $currentMon,
                    'year'            => $currentYear,
                ],
                ['family_group_id' => $groupId]
            );
        }

        $pendingPayments = MonthlyPayment::with(['paymentItem.account'])
            ->where('family_group_id', $groupId)
            ->where('month', $currentMon)
            ->where('year', $currentYear)
            ->get()
            ->sortBy(fn ($mp) => [
                $mp->is_paid ? 1 : 0,
                $mp->paymentItem?->day_of_month ?? 99,
            ])
            ->values();

        $pendingPaidCount  = $pendingPayments->where('is_paid', true)->count();
        $pendingTotalCount = $pendingPayments->count();

        return view('dashboard', compact(
            'group',
            'accounts',
            'month',
            'totalIncome',
            'totalExpense',
            'balance',
            'totalAssets',
            'totalLiabilities',
            'netWorth',
            'expensesByCategory',
            'expensesByCategoryRaw',
            'installmentSummary',
            'recentTransactions',
            'exchangeRate',
            'recurringExpenses',
            'recurringLogs',
            'pendingPayments',
            'pendingPaidCount',
            'pendingTotalCount',
        ));
    }

    /**
     * Suma montos de un tipo de transacción convirtiendo USD→ARS cuando hay cotización.
     * Hace dos SUM() en DB (por moneda) para no cargar todos los registros en memoria.
     */
    private function sumConverted(\Illuminate\Database\Eloquent\Builder $query, string $type, ?\App\Models\ExchangeRate $rate): float
    {
        $q = (clone $query)->where('type', $type);

        $ars = (float) (clone $q)->where('currency', 'ARS')->sum('amount');
        $usd = (float) (clone $q)->where('currency', 'USD')->sum('amount');

        if ($usd === 0.0) return $ars;
        if ($rate === null) return $ars + $usd; // sin cotización: suma cruda (con banner de aviso)

        return $ars + $rate->convert($usd, 'USD');
    }
}
