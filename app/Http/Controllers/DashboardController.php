<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Budget;
use App\Models\MonthlyPayment;
use App\Models\PaymentItem;
use App\Models\Promotion;
use App\Models\Transaction;
use App\Services\ExchangeRateResolver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $groupId = session('active_family_group_id');
        $group = auth()->user()->familyGroups()->with('members')->find($groupId);

        // Mes a visualizar (default: mes actual)
        $month = $request->get('month', now()->format('Y-m'));
        [$year, $mon] = array_map('intval', explode('-', $month));

        // ── Cuentas activas del grupo ────────────────────────────────────────
        $accounts = $group->accounts()->where('is_active', true)->get();

        // ── Tipo de cambio vigente (necesario antes de cualquier cálculo) ────
        $exchangeRate = $group->latestExchangeRate();
        $rateResolver = ExchangeRateResolver::forGroup($groupId);
        // Tasa vigente al mes visualizado (fin de mes) para presupuestos.
        $monthEnd = Carbon::create($year, $mon, 1)->endOfMonth();

        // ── Patrimonio neto (convertido a ARS) ───────────────────────────────
        $totalAssets = $accounts->filter(fn ($a) => ! $a->isLiability())
            ->sum(fn ($a) => $a->balanceInArs($exchangeRate));
        $totalLiabilities = $accounts->filter(fn ($a) => $a->isLiability())
            ->sum(fn ($a) => $a->balanceInArs($exchangeRate));
        $netWorth = $totalAssets - $totalLiabilities;

        // ── Totales del mes (split por moneda, luego convertir) ──────────────
        $baseQuery = Transaction::where('family_group_id', $groupId)
            ->whereYear('date', $year)
            ->whereMonth('date', $mon);

        $totalIncome = $this->sumConverted(clone $baseQuery, 'income', $rateResolver);
        $totalExpense = $this->sumConverted(clone $baseQuery, 'expense', $rateResolver);
        $balance = $totalIncome - $totalExpense;

        // ── Comparativas: mes anterior y promedio 3 meses previos ────────────
        $viewedDate = Carbon::create($year, $mon, 1)->startOfMonth();

        // Mes anterior
        $prevDate = $viewedDate->copy()->subMonth();
        $prevTx = Transaction::where('family_group_id', $groupId)
            ->whereBetween('date', [$prevDate->copy()->startOfMonth()->toDateString(), $prevDate->copy()->endOfMonth()->toDateString()])
            ->whereIn('type', ['income', 'expense'])
            ->get();
        $prevIncome = round($prevTx->where('type', 'income')->sum(fn ($t) => $t->amountInArsOn($rateResolver)), 2);
        $prevExpense = round($prevTx->where('type', 'expense')->sum(fn ($t) => $t->amountInArsOn($rateResolver)), 2);
        $prevBalance = $prevIncome - $prevExpense;

        $incomeVsPrev = $prevIncome > 0 ? round((($totalIncome - $prevIncome) / $prevIncome) * 100, 1) : null;
        $expenseVsPrev = $prevExpense > 0 ? round((($totalExpense - $prevExpense) / $prevExpense) * 100, 1) : null;

        // Promedio de los 3 meses previos (sin incluir el mes visualizado).
        // Se divide por la cantidad de meses con datos reales para no subestimar
        // en grupos con poca historia.
        $avgMonths = 3;
        $avgStart = $viewedDate->copy()->subMonths($avgMonths)->startOfMonth();
        $avgEnd = $viewedDate->copy()->subMonth()->endOfMonth();
        $avgTx = Transaction::where('family_group_id', $groupId)
            ->whereBetween('date', [$avgStart->toDateString(), $avgEnd->toDateString()])
            ->whereIn('type', ['income', 'expense'])
            ->get();

        $monthsWithData = $avgTx
            ->map(fn ($t) => $t->date->format('Y-m'))
            ->unique()
            ->count();
        $divisor = max($monthsWithData, 1);

        $avgIncome = $monthsWithData > 0
            ? round($avgTx->where('type', 'income')->sum(fn ($t) => $t->amountInArsOn($rateResolver)) / $divisor, 2)
            : 0;
        $avgExpense = $monthsWithData > 0
            ? round($avgTx->where('type', 'expense')->sum(fn ($t) => $t->amountInArsOn($rateResolver)) / $divisor, 2)
            : 0;

        $incomeVsAvg = $avgIncome > 0 ? round((($totalIncome - $avgIncome) / $avgIncome) * 100, 1) : null;
        $expenseVsAvg = $avgExpense > 0 ? round((($totalExpense - $avgExpense) / $avgExpense) * 100, 1) : null;

        // ── Gastos por categoría (para gráfico) ─────────────────────────────
        $expensesByCategoryRaw = (clone $baseQuery)
            ->where('type', 'expense')
            ->with('category')
            ->get()
            ->groupBy(fn ($t) => $t->category?->id ?? 0)
            ->map(fn ($items) => [
                'category' => $items->first()->category,
                'amount' => $items->sum(fn ($t) => $t->amountInArsOn($rateResolver)),
            ])
            ->sortByDesc('amount')
            ->take(8);

        // Legacy: keyed by name for the view bar chart
        $expensesByCategory = $expensesByCategoryRaw
            ->mapWithKeys(fn ($row) => [($row['category']?->name ?? 'Sin categoría') => $row['amount']]);

        // ── Presupuestos (widget compacto) ───────────────────────────────────
        $budgets = Budget::where('family_group_id', $groupId)
            ->with('category')
            ->get();

        // Gasto del mes por categoría (para presupuestos mensuales).
        $spentByCategoryAll = (clone $baseQuery)
            ->where('type', 'expense')
            ->get()
            ->groupBy(fn ($t) => $t->category_id ?? 0)
            ->map(fn ($items) => round($items->sum(fn ($t) => $t->amountInArsOn($rateResolver)), 2));

        // Gasto del año calendario por categoría (para presupuestos anuales).
        $spentByCategoryYear = Transaction::where('family_group_id', $groupId)
            ->where('type', 'expense')
            ->whereBetween('date', [
                $monthEnd->copy()->startOfYear()->toDateString(),
                $monthEnd->copy()->endOfYear()->toDateString(),
            ])
            ->get()
            ->groupBy(fn ($t) => $t->category_id ?? 0)
            ->map(fn ($items) => round($items->sum(fn ($t) => $t->amountInArsOn($rateResolver)), 2));

        $allBudgetProgress = $budgets->map(function ($budget) use ($rateResolver, $monthEnd, $spentByCategoryAll, $spentByCategoryYear) {
            $isAnnual = $budget->isAnnual();
            $limit = $budget->amountInArs($rateResolver, $monthEnd);
            $spent = (float) (($isAnnual ? $spentByCategoryYear : $spentByCategoryAll)->get($budget->category_id) ?? 0.0);
            $pct = $limit > 0 ? round(($spent / $limit) * 100, 1) : null;

            return [
                'category' => $budget->category,
                'is_annual' => $isAnnual,
                'limit' => $limit,
                'spent' => $spent,
                'pct' => $pct,
                'status' => $pct === null ? 'ok' : ($pct >= 100 ? 'over' : ($pct >= 80 ? 'warn' : 'ok')),
            ];
        })
            ->filter(fn ($r) => $r['category'] !== null)
            ->sortByDesc(fn ($r) => $r['pct'] ?? -1)
            ->values();

        // Widget compacto: top 5 por avance.
        $budgetProgress = $allBudgetProgress->take(5)->values();

        // Conteos y alertas sobre TODOS los presupuestos (no sólo el top 5).
        $budgetOverCount = $allBudgetProgress->where('status', 'over')->count();
        $budgetWarnCount = $allBudgetProgress->where('status', 'warn')->count();

        // Alertas: presupuestos excedidos o cerca del límite (para el banner).
        $budgetAlerts = $allBudgetProgress
            ->whereIn('status', ['over', 'warn'])
            ->values();

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
                'account' => $account,
                'installments' => $installments,
                'total' => $total,
            ];
        })->filter(fn ($item) => $item['installments']->isNotEmpty());

        // ── Últimas 10 transacciones ─────────────────────────────────────────
        $recentTransactions = Transaction::with(['account', 'category', 'user'])
            ->where('family_group_id', $groupId)
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        // ── Pendientes del mes actual (para widget) ───────────────────────────
        $currentMon = now()->month;
        $currentYear = now()->year;

        $activeItems = PaymentItem::where('family_group_id', $groupId)
            ->where('is_active', true)
            ->get();

        foreach ($activeItems as $item) {
            MonthlyPayment::firstOrCreate(
                [
                    'payment_item_id' => $item->id,
                    'month' => $currentMon,
                    'year' => $currentYear,
                ],
                ['family_group_id' => $groupId]
            );
        }

        $pendingPayments = MonthlyPayment::with(['paymentItem.account'])
            ->where('family_group_id', $groupId)
            ->where('month', $currentMon)
            ->where('year', $currentYear)
            ->where('is_dismissed', false)
            ->get()
            ->sortBy(fn ($mp) => [
                $mp->is_paid ? 1 : 0,
                $mp->paymentItem?->day_of_month ?? 99,
            ])
            ->values();

        $pendingPaidCount = $pendingPayments->where('is_paid', true)->count();
        $pendingTotalCount = $pendingPayments->count();

        // ── Alertas de promociones próximas a vencer ─────────────────────────
        $promotionAlerts = Promotion::where('family_group_id', $groupId)
            ->where('is_active', true)
            ->whereNull('alerted_at')
            ->whereNull('deleted_at')
            ->get()
            ->filter(fn ($p) => $p->needsAlert());

        return view('dashboard', compact(
            'group',
            'accounts',
            'month',
            'totalIncome',
            'totalExpense',
            'balance',
            'prevIncome',
            'prevExpense',
            'prevBalance',
            'incomeVsPrev',
            'expenseVsPrev',
            'avgIncome',
            'avgExpense',
            'incomeVsAvg',
            'expenseVsAvg',
            'totalAssets',
            'totalLiabilities',
            'netWorth',
            'expensesByCategory',
            'expensesByCategoryRaw',
            'budgetProgress',
            'budgetOverCount',
            'budgetWarnCount',
            'budgetAlerts',
            'installmentSummary',
            'recentTransactions',
            'exchangeRate',
            'pendingPayments',
            'pendingPaidCount',
            'pendingTotalCount',
            'promotionAlerts',
        ));
    }

    private function sumConverted(Builder $query, string $type, ExchangeRateResolver $resolver): float
    {
        return round(
            (clone $query)->where('type', $type)
                ->get(['amount', 'currency', 'date'])
                ->sum(fn ($t) => $t->amountInArsOn($resolver)),
            2
        );
    }
}
