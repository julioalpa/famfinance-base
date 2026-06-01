<?php

namespace App\Http\Controllers;

use App\Models\ExchangeRate;
use App\Models\FamilyGroup;
use App\Models\Installment;
use App\Models\MonthlyPayment;
use App\Models\PaymentItem;
use App\Models\Tag;
use App\Models\TagGroup;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $groupId = session('active_family_group_id');
        $group   = auth()->user()->familyGroups()->find($groupId);

        $months = (int) $request->get('months', 6);
        $months = in_array($months, [3, 6, 12]) ? $months : 6;

        $startDate    = now()->startOfMonth()->subMonths($months - 1);
        $exchangeRate = $group->latestExchangeRate();

        $driver    = DB::connection()->getDriverName();
        $yearExpr  = $driver === 'sqlite' ? "CAST(strftime('%Y', date) AS INTEGER)" : 'EXTRACT(YEAR FROM date)';
        $monthExpr = $driver === 'sqlite' ? "CAST(strftime('%m', date) AS INTEGER)" : 'EXTRACT(MONTH FROM date)';
        $dayExpr   = $driver === 'sqlite' ? "CAST(strftime('%d', date) AS INTEGER)" : 'EXTRACT(DAY FROM date)';

        // ── Monthly income/expense (split por moneda para convertir USD→ARS) ──
        $monthlyRaw = Transaction::where('family_group_id', $groupId)
            ->where('date', '>=', $startDate)
            ->whereIn('type', ['income', 'expense'])
            ->selectRaw("{$yearExpr} as year, {$monthExpr} as month, type, currency, SUM(amount) as total")
            ->groupBy('year', 'month', 'type', 'currency')
            ->get();

        $monthlyData = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $date  = now()->startOfMonth()->subMonths($i);
            $key   = $date->format('Y-n');
            $monthlyData[$key] = [
                'label'   => ucfirst($date->locale('es')->isoFormat('MMM YY')),
                'income'  => 0.0,
                'expense' => 0.0,
            ];
        }

        foreach ($monthlyRaw as $row) {
            $key = "{$row->year}-{$row->month}";
            if (! isset($monthlyData[$key])) continue;

            $amount = (float) $row->total;
            if ($row->currency === 'USD' && $exchangeRate) {
                $amount = $exchangeRate->convert($amount, 'USD');
            }
            $monthlyData[$key][$row->type] += $amount;
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
            ->map(fn($items) => round($items->sum(fn($t) => $t->amountInArs($exchangeRate)), 2))
            ->sortDesc()
            ->take(10);

        // ── Daily spending (current month, split por moneda) ─────────────────
        $dailyRaw = Transaction::where('family_group_id', $groupId)
            ->where('type', 'expense')
            ->whereYear('date', now()->year)
            ->whereMonth('date', now()->month)
            ->selectRaw("{$dayExpr} as day, currency, SUM(amount) as total")
            ->groupBy('day', 'currency')
            ->orderBy('day')
            ->get();

        $dailyMap = [];
        foreach ($dailyRaw as $row) {
            $amount = (float) $row->total;
            if ($row->currency === 'USD' && $exchangeRate) {
                $amount = $exchangeRate->convert($amount, 'USD');
            }
            $dailyMap[(int) $row->day] = ($dailyMap[(int) $row->day] ?? 0.0) + $amount;
        }

        $dailySpending = [];
        for ($day = 1; $day <= now()->daysInMonth; $day++) {
            $dailySpending[] = [
                'day'   => $day,
                'total' => $dailyMap[$day] ?? 0.0,
            ];
        }

        // ── Expense by member ─────────────────────────────────────────────────
        $byMember = Transaction::where('family_group_id', $groupId)
            ->where('date', '>=', $startDate)
            ->where('type', 'expense')
            ->with('user')
            ->get()
            ->groupBy(fn($t) => $t->user->name)
            ->map(fn($items) => round($items->sum(fn($t) => $t->amountInArs($exchangeRate)), 2))
            ->sortDesc();

        // ── Patrimonio neto (convertido a ARS) ───────────────────────────────
        $allAccounts      = $group->accounts()->where('is_active', true)->get();
        $totalAssets      = $allAccounts->filter(fn($a) => ! $a->isLiability())
                                        ->sum(fn($a) => $a->balanceInArs($exchangeRate));
        $totalLiabilities = $allAccounts->filter(fn($a) => $a->isLiability())
                                        ->sum(fn($a) => $a->balanceInArs($exchangeRate));
        $netWorth         = $totalAssets - $totalLiabilities;

        // ── Historial de ítems de pago (pendientes) ───────────────────────────
        $monthKeys = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $d = now()->startOfMonth()->subMonths($i);
            $monthKeys[] = [
                'key'   => "{$d->year}-{$d->month}",
                'label' => ucfirst($d->locale('es')->isoFormat('MMM YY')),
                'month' => $d->month,
                'year'  => $d->year,
            ];
        }

        $activePaymentItems = PaymentItem::with('account')
            ->where('family_group_id', $groupId)
            ->where('is_active', true)
            ->orderBy('description')
            ->get();

        $historyRaw = MonthlyPayment::where('family_group_id', $groupId)
            ->where('is_paid', true)
            ->whereIn('payment_item_id', $activePaymentItems->pluck('id'))
            ->get()
            ->groupBy('payment_item_id');

        $paymentItemHistory = $activePaymentItems->map(function ($item) use ($historyRaw, $monthKeys) {
            $byKey = $historyRaw->get($item->id, collect())
                ->keyBy(fn($mp) => "{$mp->year}-{$mp->month}");
            $rows     = [];
            $prevAmount = null;
            foreach ($monthKeys as $mk) {
                $mp     = $byKey->get($mk['key']);
                $amount = $mp ? (float) $mp->amount : null;
                $change = null;
                if ($amount !== null && $prevAmount !== null && $prevAmount > 0) {
                    $change = round((($amount - $prevAmount) / $prevAmount) * 100, 1);
                }
                if ($amount !== null) $prevAmount = $amount;
                $rows[] = ['label' => $mk['label'], 'amount' => $amount, 'change' => $change];
            }
            return ['item' => $item, 'months' => $rows];
        })->filter(fn($row) => collect($row['months'])->contains(fn($m) => $m['amount'] !== null));

        // ── Previsión de cuotas ───────────────────────────────────────────────
        $forecastHorizon = 12;
        $forecastStart   = now()->startOfMonth();

        $upcomingInstallments = Installment::with(['transaction', 'account'])
            ->whereHas('account', fn($q) => $q->where('family_group_id', $groupId))
            ->where('is_paid', false)
            ->where('due_date', '>=', $forecastStart)
            ->where('due_date', '<', $forecastStart->copy()->addMonths($forecastHorizon))
            ->orderBy('due_date')
            ->get();

        $installmentForecast = collect();
        for ($i = 0; $i < $forecastHorizon; $i++) {
            $d    = $forecastStart->copy()->addMonths($i);
            $slot = $upcomingInstallments->filter(
                fn($inst) => $inst->due_date->year === $d->year && $inst->due_date->month === $d->month
            );
            if ($slot->isEmpty()) continue;
            $installmentForecast->push([
                'label' => ucfirst($d->locale('es')->isoFormat('MMMM YYYY')),
                'is_current' => $i === 0,
                'total' => round($slot->sum(function ($inst) use ($exchangeRate) {
                    $amt = (float) $inst->amount;
                    if ($inst->transaction?->currency === 'USD' && $exchangeRate) {
                        return $exchangeRate->convert($amt, 'USD');
                    }
                    return $amt;
                }), 2),
                'count' => $slot->count(),
                'items' => $slot->map(fn($inst) => [
                    'description' => $inst->transaction?->description ?? 'Sin descripción',
                    'account'     => $inst->account?->name ?? '—',
                    'amount'      => (float) $inst->amount,
                    'number'      => $inst->installment_number,
                    'of'          => $inst->transaction?->installments_count ?? '?',
                ])->sortBy('description')->values(),
            ]);
        }

        // ── Gastos por grupo de etiquetas ────────────────────────────────────────
        // Cargar todas las transacciones etiquetadas del período para evitar N+1
        $taggedExpenses = Transaction::where('family_group_id', $groupId)
            ->where('date', '>=', $startDate)
            ->whereIn('type', ['expense', 'income'])
            ->whereHas('tags')
            ->with('tags')
            ->get();

        // Total etiquetado base (transacciones únicas con al menos una tag)
        $totalTaggedExpenseBase = $taggedExpenses
            ->where('type', 'expense')
            ->sum(fn ($t) => $t->amountInArs($exchangeRate));

        // Tag IDs de grupos existentes
        $groupsRaw = TagGroup::where('family_group_id', $groupId)
            ->with('tags')
            ->orderBy('name')
            ->get();

        $tagGroupStats = $groupsRaw->map(function ($tg) use ($taggedExpenses, $totalTaggedExpenseBase, $exchangeRate) {
            $tagIds  = $tg->tags->pluck('id')->all();
            // Transacciones que tienen al menos una tag del grupo
            $matched = $taggedExpenses->filter(
                fn ($tx) => $tx->tags->pluck('id')->intersect($tagIds)->isNotEmpty()
            );
            $expense = round($matched->where('type', 'expense')->sum(fn ($t) => $t->amountInArs($exchangeRate)), 2);
            $income  = round($matched->where('type', 'income')->sum(fn ($t) => $t->amountInArs($exchangeRate)), 2);
            $pct     = $totalTaggedExpenseBase > 0
                ? round($expense / $totalTaggedExpenseBase * 100, 1)
                : 0;
            return [
                'id'      => $tg->id,
                'name'    => $tg->name,
                'color'   => $tg->color,
                'tags'    => $tg->tags->map(fn ($t) => ['id' => $t->id, 'name' => $t->name, 'color' => $t->color]),
                'expense' => $expense,
                'income'  => $income,
                'count'   => $matched->count(),
                'pct'     => $pct,
            ];
        })->sortByDesc('expense')->values();

        // "Sin grupo": transacciones cuyas tags no pertenecen a ningún grupo del grupo familiar
        $allGroupedTagIds = $groupsRaw->flatMap(fn ($tg) => $tg->tags->pluck('id'))->unique()->all();
        $noGroupExpenses  = $taggedExpenses->filter(function ($tx) use ($allGroupedTagIds) {
            return $tx->tags->pluck('id')->intersect($allGroupedTagIds)->isEmpty();
        });
        $noGroupTotal = round($noGroupExpenses->where('type', 'expense')->sum(fn ($t) => $t->amountInArs($exchangeRate)), 2);
        $noGroupPct   = $totalTaggedExpenseBase > 0 ? round($noGroupTotal / $totalTaggedExpenseBase * 100, 1) : 0;

        // ── Gastos por etiqueta ──────────────────────────────────────────────────
        $tagStats = Tag::where('family_group_id', $groupId)
            ->with(['transactions' => function ($q) use ($startDate, $groupId) {
                $q->where('date', '>=', $startDate)
                  ->where('family_group_id', $groupId)
                  ->whereIn('type', ['expense', 'income']);
            }])
            ->get()
            ->map(function ($tag) use ($exchangeRate) {
                $txs     = $tag->transactions;
                $expense = round($txs->where('type', 'expense')->sum(fn ($t) => $t->amountInArs($exchangeRate)), 2);
                $income  = round($txs->where('type', 'income')->sum(fn ($t) => $t->amountInArs($exchangeRate)), 2);
                return [
                    'name'    => $tag->name,
                    'color'   => $tag->color,
                    'expense' => $expense,
                    'income'  => $income,
                    'count'   => $txs->count(),
                ];
            })
            ->filter(fn ($t) => $t['expense'] > 0 || $t['income'] > 0)
            ->sortByDesc('expense')
            ->values();

        return view('reports.index', compact(
            'tagGroupStats',
            'noGroupTotal',
            'noGroupPct',
            'totalTaggedExpenseBase',
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
            'monthKeys',
            'paymentItemHistory',
            'installmentForecast',
            'allAccounts',
            'totalAssets',
            'totalLiabilities',
            'netWorth',
            'exchangeRate',
            'tagStats',
        ));
    }
}
