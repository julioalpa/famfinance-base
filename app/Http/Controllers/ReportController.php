<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $groupId = session('active_family_group_id');

        $months = (int) $request->get('months', 6);
        $months = in_array($months, [3, 6, 12]) ? $months : 6;

        $startDate = now()->subMonths($months - 1)->startOfMonth();

        // ── Monthly income/expense (single grouped query) ─────────────────────
        $monthlyRaw = Transaction::where('family_group_id', $groupId)
            ->where('date', '>=', $startDate)
            ->whereIn('type', ['income', 'expense'])
            ->selectRaw('YEAR(date) as year, MONTH(date) as month, type, SUM(amount) as total')
            ->groupBy('year', 'month', 'type')
            ->get();

        $monthlyData = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $date  = now()->subMonths($i)->startOfMonth();
            $key   = $date->format('Y-n');
            $monthlyData[$key] = [
                'label'   => ucfirst($date->locale('es')->isoFormat('MMM YY')),
                'income'  => 0.0,
                'expense' => 0.0,
            ];
        }

        foreach ($monthlyRaw as $row) {
            $key = "{$row->year}-{$row->month}";
            if (isset($monthlyData[$key])) {
                $monthlyData[$key][$row->type] = (float) $row->total;
            }
        }

        $monthlyData = collect(array_values($monthlyData));

        // ── Summary stats ─────────────────────────────────────────────────────
        $avgIncome  = round($monthlyData->avg('income'), 2);
        $avgExpense = round($monthlyData->avg('expense'), 2);
        $savingsRate = $avgIncome > 0
            ? round((($avgIncome - $avgExpense) / $avgIncome) * 100, 1)
            : 0;

        $bestMonth  = $monthlyData->sortByDesc(fn($m) => $m['income'] - $m['expense'])->first();
        $totalPeriodExpense = $monthlyData->sum('expense');
        $totalPeriodIncome  = $monthlyData->sum('income');

        // ── Expenses by category (period) ─────────────────────────────────────
        $expensesByCategory = Transaction::where('family_group_id', $groupId)
            ->where('date', '>=', $startDate)
            ->where('type', 'expense')
            ->with('category')
            ->get()
            ->groupBy(fn($t) => $t->category?->name ?? 'Sin categoría')
            ->map(fn($items) => round($items->sum('amount'), 2))
            ->sortDesc()
            ->take(10);

        // ── Daily spending (current month) ────────────────────────────────────
        $dailyRaw = Transaction::where('family_group_id', $groupId)
            ->where('type', 'expense')
            ->whereYear('date', now()->year)
            ->whereMonth('date', now()->month)
            ->selectRaw('DAY(date) as day, SUM(amount) as total')
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->keyBy('day');

        $dailySpending = [];
        for ($day = 1; $day <= now()->daysInMonth; $day++) {
            $dailySpending[] = [
                'day'   => $day,
                'total' => (float) ($dailyRaw->get($day)?->total ?? 0),
            ];
        }

        // ── Expense by member ─────────────────────────────────────────────────
        $byMember = Transaction::where('family_group_id', $groupId)
            ->where('date', '>=', $startDate)
            ->where('type', 'expense')
            ->with('user')
            ->get()
            ->groupBy(fn($t) => $t->user->name)
            ->map(fn($items) => round($items->sum('amount'), 2))
            ->sortDesc();

        return view('reports.index', compact(
            'monthlyData',
            'months',
            'avgIncome',
            'avgExpense',
            'savingsRate',
            'bestMonth',
            'totalPeriodExpense',
            'totalPeriodIncome',
            'expensesByCategory',
            'dailySpending',
            'byMember',
            'startDate',
        ));
    }
}
