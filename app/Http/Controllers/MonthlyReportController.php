<?php

namespace App\Http\Controllers;

use App\Models\ExchangeRate;
use App\Models\Installment;
use App\Models\PaymentItem;
use App\Models\RecurringExpense;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MonthlyReportController extends Controller
{
    // Umbral para gastos hormiga en ARS
    private const ANT_THRESHOLD_ARS = 2000;

    public function index(Request $request)
    {
        $data = $this->buildReportData($request);
        return view('reports.monthly', $data);
    }

    public function pdf(Request $request)
    {
        $data = $this->buildReportData($request);
        $data['chartImages'] = $request->input('charts', []);

        $pdf = Pdf::loadView('reports.monthly-pdf', $data)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont'  => 'DejaVu Sans',
                'isRemoteEnabled' => false,
                'isHtml5ParserEnabled' => true,
                'dpi' => 150,
            ]);

        $filename = 'balance-' . $data['date']->format('Y-m') . '.pdf';
        return $pdf->download($filename);
    }

    private function buildReportData(Request $request): array
    {
        $groupId      = session('active_family_group_id');
        $group        = auth()->user()->familyGroups()->find($groupId);
        $exchangeRate = $group->latestExchangeRate();

        // ── Mes seleccionado ─────────────────────────────────────────────────
        $monthParam = $request->get('mes', now()->format('Y-m'));
        try {
            $date = Carbon::createFromFormat('Y-m', $monthParam)->startOfMonth();
        } catch (\Exception) {
            $date = now()->startOfMonth();
        }
        $endDate        = $date->copy()->endOfMonth();
        $isCurrentMonth = $date->isSameMonth(now());
        $monthLabel     = ucfirst($date->locale('es')->isoFormat('MMMM [de] YYYY'));

        // ── Transacciones del mes ─────────────────────────────────────────────
        $transactions = Transaction::where('family_group_id', $groupId)
            ->whereBetween('date', [$date->toDateString(), $endDate->toDateString()])
            ->whereIn('type', ['income', 'expense'])
            ->with(['category', 'user'])
            ->get();

        $expenseTransactions = $transactions->where('type', 'expense');
        $incomeTransactions  = $transactions->where('type', 'income');

        $totalIncome  = round($incomeTransactions->sum(fn ($t) => $t->amountInArs($exchangeRate)), 2);
        $totalExpense = round($expenseTransactions->sum(fn ($t) => $t->amountInArs($exchangeRate)), 2);
        $balance      = $totalIncome - $totalExpense;
        $savingsRate  = $totalIncome > 0 ? round((($totalIncome - $totalExpense) / $totalIncome) * 100, 1) : 0;

        // ── Comparación mes anterior ──────────────────────────────────────────
        $prevDate  = $date->copy()->subMonth();
        $prevTx    = Transaction::where('family_group_id', $groupId)
            ->whereBetween('date', [$prevDate->startOfMonth()->toDateString(), $prevDate->endOfMonth()->toDateString()])
            ->whereIn('type', ['income', 'expense'])
            ->get();
        $prevIncome  = round($prevTx->where('type', 'income')->sum(fn ($t) => $t->amountInArs($exchangeRate)), 2);
        $prevExpense = round($prevTx->where('type', 'expense')->sum(fn ($t) => $t->amountInArs($exchangeRate)), 2);
        $prevBalance = $prevIncome - $prevExpense;

        $expenseVsPrev = $prevExpense > 0
            ? round((($totalExpense - $prevExpense) / $prevExpense) * 100, 1)
            : null;
        $incomeVsPrev = $prevIncome > 0
            ? round((($totalIncome - $prevIncome) / $prevIncome) * 100, 1)
            : null;

        // ── Promedio últimos 3 meses (sin el mes actual) ─────────────────────
        $avgMonths = 3;
        $avgStart  = $date->copy()->subMonths($avgMonths)->startOfMonth();
        $avgEnd    = $date->copy()->subMonth()->endOfMonth();
        $avgTx     = Transaction::where('family_group_id', $groupId)
            ->whereBetween('date', [$avgStart->toDateString(), $avgEnd->toDateString()])
            ->whereIn('type', ['income', 'expense'])
            ->get();
        $avgIncome  = $avgMonths > 0 ? round($avgTx->where('type', 'income')->sum(fn ($t) => $t->amountInArs($exchangeRate)) / $avgMonths, 2) : 0;
        $avgExpense = $avgMonths > 0 ? round($avgTx->where('type', 'expense')->sum(fn ($t) => $t->amountInArs($exchangeRate)) / $avgMonths, 2) : 0;
        $expenseVsAvg = $avgExpense > 0 ? round((($totalExpense - $avgExpense) / $avgExpense) * 100, 1) : null;

        // ── Por categoría (top 10 + Otros) ───────────────────────────────────
        $allCategories = $expenseTransactions
            ->groupBy(fn ($t) => $t->category?->name ?? 'Sin categoría')
            ->map(function ($items, $name) use ($exchangeRate, $totalExpense) {
                $total = round($items->sum(fn ($t) => $t->amountInArs($exchangeRate)), 2);
                return [
                    'name'    => $name,
                    'total'   => $total,
                    'count'   => $items->count(),
                    'percent' => $totalExpense > 0 ? round(($total / $totalExpense) * 100, 1) : 0,
                    'color'   => $items->first()->category?->color ?? '#6a6676',
                    'icon'    => $items->first()->category?->icon ?? '📦',
                ];
            })
            ->sortByDesc('total')
            ->values();

        $top10      = $allCategories->take(10);
        $others     = $allCategories->slice(10);
        $othersTotal = round($others->sum('total'), 2);
        $othersPct   = $totalExpense > 0 ? round(($othersTotal / $totalExpense) * 100, 1) : 0;

        if ($others->isNotEmpty()) {
            $top10->push([
                'name'    => 'Otros',
                'total'   => $othersTotal,
                'count'   => $others->sum('count'),
                'percent' => $othersPct,
                'color'   => '#6a6676',
                'icon'    => '📦',
            ]);
        }

        $byCategory = $top10->values();

        // ── Gasto diario ──────────────────────────────────────────────────────
        $dailyMap = $expenseTransactions
            ->groupBy(fn ($t) => (int) $t->date->format('j'))
            ->map(fn ($items) => round($items->sum(fn ($t) => $t->amountInArs($exchangeRate)), 2));

        $daysInMonth   = $date->daysInMonth;
        $dailySpending = [];
        $cumulative    = 0;
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $amount      = $dailyMap[$day] ?? 0;
            $cumulative += $amount;
            $dailySpending[] = [
                'day'        => $day,
                'amount'     => $amount,
                'cumulative' => round($cumulative, 2),
            ];
        }

        // ── Gastos hormiga ────────────────────────────────────────────────────
        $antThreshold = self::ANT_THRESHOLD_ARS;
        $antTx        = $expenseTransactions->filter(fn ($t) => $t->amountInArs($exchangeRate) < $antThreshold);
        $antTotal     = round($antTx->sum(fn ($t) => $t->amountInArs($exchangeRate)), 2);
        $antCount     = $antTx->count();
        $antScore     = $totalExpense > 0 ? round(($antTotal / $totalExpense) * 100, 1) : 0;
        $antByCategory = $antTx
            ->groupBy(fn ($t) => $t->category?->name ?? 'Sin categoría')
            ->map(fn ($items, $name) => [
                'name'  => $name,
                'total' => round($items->sum(fn ($t) => $t->amountInArs($exchangeRate)), 2),
                'count' => $items->count(),
                'icon'  => $items->first()->category?->icon ?? '📦',
            ])
            ->sortByDesc('total')
            ->values();

        // ── Gastos recurrentes del mes ────────────────────────────────────────
        $allRecurring = RecurringExpense::where('family_group_id', $groupId)
            ->where('is_active', true)
            ->with(['logs' => fn ($q) => $q->where('month', $date->month)->where('year', $date->year), 'category'])
            ->get();

        $confirmedRecurring = $allRecurring->filter(
            fn ($r) => $r->logForMonth($date->month, $date->year)?->isConfirmed()
        );
        $skippedRecurring = $allRecurring->filter(
            fn ($r) => $r->logForMonth($date->month, $date->year)?->isSkipped()
        );
        $pendingRecurring = $allRecurring->filter(
            fn ($r) => $r->logForMonth($date->month, $date->year) === null
        );

        $confirmedRecurringTotal = round($confirmedRecurring->sum(fn ($r) => $this->recurringAmountInArs($r, $exchangeRate)), 2);
        $pendingRecurringTotal   = round($pendingRecurring->sum(fn ($r) => $this->recurringAmountInArs($r, $exchangeRate)), 2);

        // ── Cuotas del mes ────────────────────────────────────────────────────
        $installments = Installment::with(['transaction', 'account'])
            ->whereHas('account', fn ($q) => $q->where('family_group_id', $groupId))
            ->whereYear('due_date', $date->year)
            ->whereMonth('due_date', $date->month)
            ->orderBy('due_date')
            ->get();

        $installmentTotal = round($installments->sum(function ($inst) use ($exchangeRate) {
            $amt = (float) $inst->amount;
            if ($inst->transaction?->currency === 'USD' && $exchangeRate) {
                return $exchangeRate->convert($amt, 'USD');
            }
            return $amt;
        }), 2);

        $paidInstallmentTotal = round($installments->where('is_paid', true)->sum(function ($inst) use ($exchangeRate) {
            $amt = (float) $inst->amount;
            if ($inst->transaction?->currency === 'USD' && $exchangeRate) {
                return $exchangeRate->convert($amt, 'USD');
            }
            return $amt;
        }), 2);

        // ── Pagos del mes (gastos fijos / checklist) ──────────────────────────
        $paymentItems = PaymentItem::where('family_group_id', $groupId)
            ->where('is_active', true)
            ->with([
                'account',
                'category',
                'monthlyPayments' => fn ($q) => $q
                    ->where('month', $date->month)
                    ->where('year', $date->year),
            ])
            ->orderBy('day_of_month')
            ->orderBy('description')
            ->get();

        $paymentItemsWithStatus = $paymentItems->map(function ($item) use ($exchangeRate) {
            $mp = $item->monthlyPayments->first();
            $amount = $mp?->amount ?? null;
            $amountArs = $amount !== null
                ? ($item->currency === 'USD' && $exchangeRate
                    ? $exchangeRate->convert((float) $amount, 'USD')
                    : (float) $amount)
                : null;
            return [
                'item'       => $item,
                'is_paid'    => $mp?->is_paid ?? false,
                'amount'     => $amount,
                'amount_ars' => $amountArs,
                'paid_at'    => $mp?->paid_at,
            ];
        });

        $paidPaymentsItems    = $paymentItemsWithStatus->where('is_paid', true)->values();
        $pendingPaymentsItems = $paymentItemsWithStatus->where('is_paid', false)->values();
        $paidPaymentsTotal    = round($paidPaymentsItems->sum('amount_ars'), 2);

        // ── Cuentas ───────────────────────────────────────────────────────────
        $allAccounts      = $group->accounts()->where('is_active', true)->get();
        $totalAssets      = $allAccounts->filter(fn ($a) => ! $a->isLiability())->sum(fn ($a) => $a->balanceInArs($exchangeRate));
        $totalLiabilities = $allAccounts->filter(fn ($a) => $a->isLiability())->sum(fn ($a) => $a->balanceInArs($exchangeRate));
        $netWorth         = $totalAssets - $totalLiabilities;

        // ── Gráfico: últimos 6 meses (income vs expense para comparativa) ─────
        $chartMonths = 6;
        $chartStart  = $date->copy()->subMonths($chartMonths - 1)->startOfMonth();
        $chartRaw    = Transaction::where('family_group_id', $groupId)
            ->where('date', '>=', $chartStart->toDateString())
            ->where('date', '<=', $endDate->toDateString())
            ->whereIn('type', ['income', 'expense'])
            ->selectRaw('EXTRACT(YEAR FROM date) as year, EXTRACT(MONTH FROM date) as month, type, currency, SUM(amount) as total')
            ->groupBy('year', 'month', 'type', 'currency')
            ->get();

        $chartData = [];
        for ($i = $chartMonths - 1; $i >= 0; $i--) {
            $d   = $date->copy()->subMonths($i)->startOfMonth();
            $key = $d->format('Y-n');
            $chartData[$key] = [
                'label'   => ucfirst($d->locale('es')->isoFormat('MMM YY')),
                'income'  => 0.0,
                'expense' => 0.0,
                'isCurrent' => $i === 0,
            ];
        }
        foreach ($chartRaw as $row) {
            $key = "{$row->year}-{$row->month}";
            if (! isset($chartData[$key])) continue;
            $amount = (float) $row->total;
            if ($row->currency === 'USD' && $exchangeRate) {
                $amount = $exchangeRate->convert($amount, 'USD');
            }
            $chartData[$key][$row->type] += $amount;
        }
        $chartData = collect(array_values($chartData));

        // ── Previsión (solo mes en curso) ─────────────────────────────────────
        $forecast = null;
        if ($isCurrentMonth) {
            $daysPassed      = now()->day;
            $daysRemaining   = $daysInMonth - $daysPassed;
            $dailyAvgSpend   = $daysPassed > 0 ? $totalExpense / $daysPassed : 0;
            $projectedExpense = round($totalExpense + ($dailyAvgSpend * $daysRemaining), 2);

            // Cuotas pendientes de pago este mes
            $pendingInstTotal = round($installments->where('is_paid', false)->sum(function ($inst) use ($exchangeRate) {
                $amt = (float) $inst->amount;
                if ($inst->transaction?->currency === 'USD' && $exchangeRate) {
                    return $exchangeRate->convert($amt, 'USD');
                }
                return $amt;
            }), 2);

            $projectedTotal   = $projectedExpense + $pendingRecurringTotal + $pendingInstTotal;
            $projectedBalance = $avgIncome - $projectedTotal;

            $forecast = [
                'days_passed'          => $daysPassed,
                'days_remaining'       => $daysRemaining,
                'days_in_month'        => $daysInMonth,
                'daily_avg'            => round($dailyAvgSpend, 2),
                'projected_expense'    => $projectedExpense,
                'pending_recurring'    => $pendingRecurringTotal,
                'pending_installments' => $pendingInstTotal,
                'projected_total'      => $projectedTotal,
                'avg_income'           => $avgIncome,
                'projected_balance'    => $projectedBalance,
                'progress_pct'         => round(($daysPassed / $daysInMonth) * 100),
                'expense_pct_of_avg'   => $avgExpense > 0 ? round(($totalExpense / $avgExpense) * 100) : 0,
            ];
        }

        return compact(
            'group',
            'date',
            'endDate',
            'monthLabel',
            'isCurrentMonth',
            'exchangeRate',
            'antThreshold',
            // Resumen
            'totalIncome',
            'totalExpense',
            'balance',
            'savingsRate',
            // Comparativas
            'prevIncome',
            'prevExpense',
            'prevBalance',
            'expenseVsPrev',
            'incomeVsPrev',
            'avgIncome',
            'avgExpense',
            'expenseVsAvg',
            // Por categoría
            'byCategory',
            // Gasto diario
            'dailySpending',
            'daysInMonth',
            // Gastos hormiga
            'antTotal',
            'antCount',
            'antScore',
            'antByCategory',
            // Recurrentes
            'allRecurring',
            'confirmedRecurring',
            'skippedRecurring',
            'pendingRecurring',
            'confirmedRecurringTotal',
            'pendingRecurringTotal',
            // Cuotas
            'installments',
            'installmentTotal',
            'paidInstallmentTotal',
            // Pagos del mes
            'paymentItemsWithStatus',
            'paidPaymentsItems',
            'pendingPaymentsItems',
            'paidPaymentsTotal',
            // Cuentas
            'allAccounts',
            'totalAssets',
            'totalLiabilities',
            'netWorth',
            // Chart
            'chartData',
            // Previsión
            'forecast',
        );
    }

    private function recurringAmountInArs(RecurringExpense $r, ?ExchangeRate $rate): float
    {
        $amt = (float) $r->amount;
        if ($r->currency === 'USD' && $rate) {
            return $rate->convert($amt, 'USD');
        }
        return $amt;
    }
}
