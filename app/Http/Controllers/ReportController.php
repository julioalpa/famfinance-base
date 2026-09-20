<?php

namespace App\Http\Controllers;

use App\Models\Installment;
use App\Models\MonthlyPayment;
use App\Models\PaymentItem;
use App\Models\Tag;
use App\Models\TagGroup;
use App\Models\Transaction;
use App\Services\ExchangeRateResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $groupId = session('active_family_group_id');
        $group = auth()->user()->familyGroups()->find($groupId);

        $months = (int) $request->get('months', 6);
        $months = in_array($months, [3, 6, 12]) ? $months : 6;

        $startDate = now()->startOfMonth()->subMonths($months - 1);
        $exchangeRate = $group->latestExchangeRate();
        $rateResolver = ExchangeRateResolver::forGroup($groupId);

        $driver = DB::connection()->getDriverName();
        $yearExpr = $driver === 'sqlite' ? "CAST(strftime('%Y', date) AS INTEGER)" : 'EXTRACT(YEAR FROM date)';
        $monthExpr = $driver === 'sqlite' ? "CAST(strftime('%m', date) AS INTEGER)" : 'EXTRACT(MONTH FROM date)';
        $dayExpr = $driver === 'sqlite' ? "CAST(strftime('%d', date) AS INTEGER)" : 'EXTRACT(DAY FROM date)';

        // ── Monthly income/expense (split por moneda para convertir USD→ARS) ──
        $monthlyRaw = Transaction::where('family_group_id', $groupId)
            ->where('date', '>=', $startDate)
            ->whereIn('type', ['income', 'expense'])
            ->selectRaw("{$yearExpr} as year, {$monthExpr} as month, type, currency, SUM(amount) as total")
            ->groupBy('year', 'month', 'type', 'currency')
            ->get();

        $monthlyData = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->startOfMonth()->subMonths($i);
            $key = $date->format('Y-n');
            $monthlyData[$key] = [
                'label' => ucfirst($date->locale('es')->isoFormat('MMM YY')),
                'income' => 0.0,
                'expense' => 0.0,
            ];
        }

        foreach ($monthlyRaw as $row) {
            $key = "{$row->year}-{$row->month}";
            if (! isset($monthlyData[$key])) {
                continue;
            }

            $amount = (float) $row->total;
            if ($row->currency === 'USD') {
                // Tasa vigente al mes de la operación (fin de mes como referencia).
                $monthDate = Carbon::create((int) $row->year, (int) $row->month, 1)->endOfMonth();
                $amount = $rateResolver->convert($amount, 'USD', $monthDate);
            }
            $monthlyData[$key][$row->type] += $amount;
        }

        $monthlyData = collect(array_values($monthlyData));

        // ── Summary stats ─────────────────────────────────────────────────────
        $avgIncome = round($monthlyData->avg('income'), 2);
        $avgExpense = round($monthlyData->avg('expense'), 2);
        $savingsRate = $avgIncome > 0
            ? round((($avgIncome - $avgExpense) / $avgIncome) * 100, 1)
            : 0;

        $bestMonth = $monthlyData->sortByDesc(fn ($m) => $m['income'] - $m['expense'])->first();
        $totalPeriodExpense = $monthlyData->sum('expense');
        $totalPeriodIncome = $monthlyData->sum('income');

        // Meses en positivo (income > expense) y tasa de ahorro por mes (sparkline)
        $positiveMonths = $monthlyData->filter(fn ($m) => $m['income'] > $m['expense'] && $m['income'] > 0)->count();
        $monthlyData = $monthlyData->map(function ($m) {
            $m['rate'] = $m['income'] > 0
                ? round((($m['income'] - $m['expense']) / $m['income']) * 100, 1)
                : null;

            return $m;
        });

        // ── Ingresos y egresos por categoría (Ing/Egr/Neto sobre el período) ──
        $periodTx = Transaction::where('family_group_id', $groupId)
            ->where('date', '>=', $startDate)
            ->whereIn('type', ['income', 'expense'])
            ->with(['category', 'user'])
            ->get();

        $categoryStats = $periodTx
            ->groupBy(fn ($t) => $t->category?->name ?? 'Sin categoría')
            ->map(function ($items, $name) use ($rateResolver, $totalPeriodExpense, $totalPeriodIncome) {
                $expense = round($items->where('type', 'expense')->sum(fn ($t) => $t->amountInArsOn($rateResolver)), 2);
                $income = round($items->where('type', 'income')->sum(fn ($t) => $t->amountInArsOn($rateResolver)), 2);
                $first = $items->first();

                return [
                    'name' => $name,
                    'income' => $income,
                    'expense' => $expense,
                    'net' => round($income - $expense, 2),
                    'count' => $items->count(),
                    'percent' => $totalPeriodExpense > 0 ? round(($expense / $totalPeriodExpense) * 100, 1) : 0,
                    'income_pct' => $totalPeriodIncome > 0 ? round(($income / $totalPeriodIncome) * 100, 1) : 0,
                    'color' => $first->category?->color ?? '#6a6676',
                    'icon' => $first->category?->icon ?? 'other',
                    'type' => $first->category?->type ?? 'expense',
                ];
            })
            ->sortByDesc(fn ($c) => $c['expense'] + $c['income'])
            ->values();

        // Legacy para donut y JS: mapa "nombre => total expense"
        $expensesByCategory = $categoryStats
            ->filter(fn ($c) => $c['expense'] > 0)
            ->sortByDesc('expense')
            ->take(10)
            ->mapWithKeys(fn ($c) => [$c['name'] => $c['expense']]);

        // ── Gasto e ingreso diario (mes en curso, con acumulados) ────────────
        $dailyRaw = Transaction::where('family_group_id', $groupId)
            ->whereIn('type', ['expense', 'income'])
            ->whereYear('date', now()->year)
            ->whereMonth('date', now()->month)
            ->selectRaw("{$dayExpr} as day, type, currency, SUM(amount) as total")
            ->groupBy('day', 'type', 'currency')
            ->orderBy('day')
            ->get();

        $expenseByDay = [];
        $incomeByDay = [];
        foreach ($dailyRaw as $row) {
            $amount = (float) $row->total;
            if ($row->currency === 'USD') {
                // Tasa vigente al día del mes en curso.
                $dayDate = Carbon::create(now()->year, now()->month, (int) $row->day);
                $amount = $rateResolver->convert($amount, 'USD', $dayDate);
            }
            if ($row->type === 'expense') {
                $expenseByDay[(int) $row->day] = ($expenseByDay[(int) $row->day] ?? 0.0) + $amount;
            } else {
                $incomeByDay[(int) $row->day] = ($incomeByDay[(int) $row->day] ?? 0.0) + $amount;
            }
        }

        $dailySpending = [];
        $cumulative = 0.0;
        $incomeCumulative = 0.0;
        for ($day = 1; $day <= now()->daysInMonth; $day++) {
            $expenseAmount = $expenseByDay[$day] ?? 0.0;
            $incomeAmount = $incomeByDay[$day] ?? 0.0;
            $cumulative += $expenseAmount;
            $incomeCumulative += $incomeAmount;
            $dailySpending[] = [
                'day' => $day,
                'total' => $expenseAmount,               // legacy: gasto del día
                'amount' => $expenseAmount,
                'cumulative' => round($cumulative, 2),
                'income_amount' => $incomeAmount,
                'income_cumulative' => round($incomeCumulative, 2),
            ];
        }

        // ── Movimientos por integrante (Ing/Egr/Neto) ────────────────────────
        $byMember = $periodTx
            ->groupBy(fn ($t) => $t->user?->name ?? '—')
            ->map(function ($items) use ($rateResolver) {
                $expense = round($items->where('type', 'expense')->sum(fn ($t) => $t->amountInArsOn($rateResolver)), 2);
                $income = round($items->where('type', 'income')->sum(fn ($t) => $t->amountInArsOn($rateResolver)), 2);

                return [
                    'income' => $income,
                    'expense' => $expense,
                    'net' => round($income - $expense, 2),
                    'count' => $items->count(),
                ];
            })
            ->sortByDesc(fn ($m) => $m['expense'] + $m['income']);

        // ── Patrimonio neto (convertido a ARS) ───────────────────────────────
        $allAccounts = $group->accounts()->where('is_active', true)->get();
        $totalAssets = $allAccounts->filter(fn ($a) => ! $a->isLiability())
            ->sum(fn ($a) => $a->balanceInArs($exchangeRate));
        $totalLiabilities = $allAccounts->filter(fn ($a) => $a->isLiability())
            ->sum(fn ($a) => $a->balanceInArs($exchangeRate));
        $netWorth = $totalAssets - $totalLiabilities;

        // ── Evolución del patrimonio neto (últimos $months meses) ─────────────
        // Para cada fin de mes: activos − pasivos, valorizando cada cuenta con
        // su saldo histórico a esa fecha y la tasa USD→ARS vigente al mes.
        $netWorthHistory = collect();
        for ($i = $months - 1; $i >= 0; $i--) {
            $d = now()->startOfMonth()->subMonths($i);
            $monthEnd = $d->copy()->endOfMonth();
            $rate = $rateResolver->rateOn($monthEnd);

            $assets = 0.0;
            $liabs = 0.0;
            foreach ($allAccounts as $acc) {
                $bal = $acc->balanceAsOf($monthEnd);
                if ($acc->currency !== 'ARS' && $rate !== null) {
                    $bal = $acc->currency === 'USD' ? $bal * $rate : $bal;
                }
                if ($acc->isLiability()) {
                    $liabs += $bal;
                } else {
                    $assets += $bal;
                }
            }

            $netWorthHistory->push([
                'label' => ucfirst($d->locale('es')->isoFormat('MMM YY')),
                'assets' => round($assets, 2),
                'liabilities' => round($liabs, 2),
                'net' => round($assets - $liabs, 2),
            ]);
        }

        // ── Historial de ítems de pago (pendientes) ───────────────────────────
        $monthKeys = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $d = now()->startOfMonth()->subMonths($i);
            $monthKeys[] = [
                'key' => "{$d->year}-{$d->month}",
                'label' => ucfirst($d->locale('es')->isoFormat('MMM YY')),
                'month' => $d->month,
                'year' => $d->year,
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
                ->keyBy(fn ($mp) => "{$mp->year}-{$mp->month}");
            $rows = [];
            $prevAmount = null;
            foreach ($monthKeys as $mk) {
                $mp = $byKey->get($mk['key']);
                $amount = $mp ? (float) $mp->amount : null;
                $change = null;
                if ($amount !== null && $prevAmount !== null && $prevAmount > 0) {
                    $change = round((($amount - $prevAmount) / $prevAmount) * 100, 1);
                }
                if ($amount !== null) {
                    $prevAmount = $amount;
                }
                $rows[] = ['label' => $mk['label'], 'amount' => $amount, 'change' => $change];
            }

            return ['item' => $item, 'months' => $rows];
        })->filter(fn ($row) => collect($row['months'])->contains(fn ($m) => $m['amount'] !== null));

        // ── Previsión de cuotas ───────────────────────────────────────────────
        $forecastHorizon = 12;
        $forecastStart = now()->startOfMonth();

        $upcomingInstallments = Installment::with(['transaction', 'account'])
            ->whereHas('account', fn ($q) => $q->where('family_group_id', $groupId))
            ->where('is_paid', false)
            ->where('due_date', '>=', $forecastStart)
            ->where('due_date', '<', $forecastStart->copy()->addMonths($forecastHorizon))
            ->orderBy('due_date')
            ->get();

        $installmentForecast = collect();
        for ($i = 0; $i < $forecastHorizon; $i++) {
            $d = $forecastStart->copy()->addMonths($i);
            $slot = $upcomingInstallments->filter(
                fn ($inst) => $inst->due_date->year === $d->year && $inst->due_date->month === $d->month
            );
            if ($slot->isEmpty()) {
                continue;
            }
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
                'items' => $slot->map(fn ($inst) => [
                    'description' => $inst->transaction?->description ?? 'Sin descripción',
                    'account' => $inst->account?->name ?? '—',
                    'amount' => (float) $inst->amount,
                    'number' => $inst->installment_number,
                    'of' => $inst->transaction?->installments_count ?? '?',
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
            ->sum(fn ($t) => $t->amountInArsOn($rateResolver));

        // Tag IDs de grupos existentes
        $groupsRaw = TagGroup::where('family_group_id', $groupId)
            ->with('tags')
            ->orderBy('name')
            ->get();

        $tagGroupStats = $groupsRaw->map(function ($tg) use ($taggedExpenses, $totalTaggedExpenseBase, $rateResolver) {
            $tagIds = $tg->tags->pluck('id')->all();
            // Transacciones que tienen al menos una tag del grupo
            $matched = $taggedExpenses->filter(
                fn ($tx) => $tx->tags->pluck('id')->intersect($tagIds)->isNotEmpty()
            );
            $expense = round($matched->where('type', 'expense')->sum(fn ($t) => $t->amountInArsOn($rateResolver)), 2);
            $income = round($matched->where('type', 'income')->sum(fn ($t) => $t->amountInArsOn($rateResolver)), 2);
            $pct = $totalTaggedExpenseBase > 0
                ? round($expense / $totalTaggedExpenseBase * 100, 1)
                : 0;

            return [
                'id' => $tg->id,
                'name' => $tg->name,
                'color' => $tg->color,
                'tags' => $tg->tags->map(fn ($t) => ['id' => $t->id, 'name' => $t->name, 'color' => $t->color]),
                'expense' => $expense,
                'income' => $income,
                'count' => $matched->count(),
                'pct' => $pct,
            ];
        })->sortByDesc('expense')->values();

        // "Sin grupo": transacciones cuyas tags no pertenecen a ningún grupo del grupo familiar
        $allGroupedTagIds = $groupsRaw->flatMap(fn ($tg) => $tg->tags->pluck('id'))->unique()->all();
        $noGroupExpenses = $taggedExpenses->filter(function ($tx) use ($allGroupedTagIds) {
            return $tx->tags->pluck('id')->intersect($allGroupedTagIds)->isEmpty();
        });
        $noGroupTotal = round($noGroupExpenses->where('type', 'expense')->sum(fn ($t) => $t->amountInArsOn($rateResolver)), 2);
        $noGroupPct = $totalTaggedExpenseBase > 0 ? round($noGroupTotal / $totalTaggedExpenseBase * 100, 1) : 0;

        // ── Gastos por etiqueta ──────────────────────────────────────────────────
        $tagStats = Tag::where('family_group_id', $groupId)
            ->with(['transactions' => function ($q) use ($startDate, $groupId) {
                $q->where('date', '>=', $startDate)
                    ->where('family_group_id', $groupId)
                    ->whereIn('type', ['expense', 'income']);
            }])
            ->get()
            ->map(function ($tag) use ($rateResolver) {
                $txs = $tag->transactions;
                $expense = round($txs->where('type', 'expense')->sum(fn ($t) => $t->amountInArsOn($rateResolver)), 2);
                $income = round($txs->where('type', 'income')->sum(fn ($t) => $t->amountInArsOn($rateResolver)), 2);

                return [
                    'name' => $tag->name,
                    'color' => $tag->color,
                    'expense' => $expense,
                    'income' => $income,
                    'count' => $txs->count(),
                ];
            })
            ->filter(fn ($t) => $t['expense'] > 0 || $t['income'] > 0)
            ->sortByDesc('expense')
            ->values();

        // ── Cobertura promedio de gastos fijos ────────────────────────────────
        // Gastos fijos promedio del período = suma de MonthlyPayments pagados +
        // cuotas del período (ya incluidas en las transacciones), promediado por mes.
        $avgFixedPaidMonthly = MonthlyPayment::where('family_group_id', $groupId)
            ->where('is_paid', true)
            ->whereIn('payment_item_id', $activePaymentItems->pluck('id'))
            ->where(function ($q) use ($monthKeys) {
                foreach ($monthKeys as $mk) {
                    $q->orWhere(function ($qq) use ($mk) {
                        $qq->where('year', $mk['year'])->where('month', $mk['month']);
                    });
                }
            })
            ->get()
            ->sum(function ($mp) use ($rateResolver, $activePaymentItems) {
                $item = $activePaymentItems->firstWhere('id', $mp->payment_item_id);
                $amount = (float) ($mp->amount ?? $item?->amount ?? 0);
                if ($amount > 0 && $item?->currency === 'USD') {
                    // Tasa vigente al mes del pago fijo.
                    $mpDate = Carbon::create((int) $mp->year, (int) $mp->month, 1)->endOfMonth();

                    return $rateResolver->convert($amount, 'USD', $mpDate);
                }

                return $amount;
            }) / max($months, 1);

        $avgFixedPaidMonthly = round($avgFixedPaidMonthly, 2);
        $fixedCoverageAvg = $avgIncome > 0
            ? round(($avgFixedPaidMonthly / $avgIncome) * 100, 1)
            : null;

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
            'categoryStats',
            'positiveMonths',
            'avgFixedPaidMonthly',
            'fixedCoverageAvg',
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
            'netWorthHistory',
            'exchangeRate',
            'tagStats',
        ));
    }
}
