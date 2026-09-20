<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use App\Services\ExchangeRateResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class BudgetController extends Controller
{
    /**
     * Panel de presupuestos: para cada categoría de gasto muestra el límite
     * mensual (si está definido) y el gasto real del mes, con % de avance.
     */
    public function index(Request $request)
    {
        $groupId = session('active_family_group_id');

        // Mes a visualizar (default: mes actual)
        $month = $request->get('month', now()->format('Y-m'));
        try {
            $date = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        } catch (\Exception) {
            $date = now()->startOfMonth();
            $month = $date->format('Y-m');
        }
        $endDate = $date->copy()->endOfMonth();
        $yearStart = $date->copy()->startOfYear();
        $yearEnd = $date->copy()->endOfYear();

        $rateResolver = ExchangeRateResolver::forGroup($groupId);

        // Categorías disponibles para el grupo que aplican a gastos.
        $categories = Category::where('type', '!=', 'income')
            ->where(fn ($q) => $q->where('is_system', true)->orWhere('family_group_id', $groupId))
            ->orderBy('name')
            ->get();

        // Presupuestos definidos por el grupo, indexados por categoría.
        $budgets = Budget::where('family_group_id', $groupId)
            ->get()
            ->keyBy('category_id');

        // Gasto real del MES por categoría (para presupuestos mensuales).
        $spentMonth = Transaction::where('family_group_id', $groupId)
            ->where('type', 'expense')
            ->whereBetween('date', [$date->toDateString(), $endDate->toDateString()])
            ->get()
            ->groupBy(fn ($t) => $t->category_id ?? 0)
            ->map(fn ($items) => round($items->sum(fn ($t) => $t->amountInArsOn($rateResolver)), 2));

        // Gasto real del AÑO calendario por categoría (para presupuestos anuales).
        $spentYear = Transaction::where('family_group_id', $groupId)
            ->where('type', 'expense')
            ->whereBetween('date', [$yearStart->toDateString(), $yearEnd->toDateString()])
            ->get()
            ->groupBy(fn ($t) => $t->category_id ?? 0)
            ->map(fn ($items) => round($items->sum(fn ($t) => $t->amountInArsOn($rateResolver)), 2));

        // Filas del panel. Se muestran primero las categorías con presupuesto
        // definido, ordenadas por % de avance descendente.
        $rows = $categories->map(function (Category $cat) use ($budgets, $spentMonth, $spentYear, $rateResolver, $endDate, $date) {
            $budget = $budgets->get($cat->id);
            $isAnnual = $budget?->isAnnual() ?? false;

            $limit = $budget ? $budget->amountInArs($rateResolver, $endDate) : null;
            $spent = (float) (($isAnnual ? $spentYear : $spentMonth)->get($cat->id) ?? 0.0);

            $pct = ($limit !== null && $limit > 0) ? round(($spent / $limit) * 100, 1) : null;
            $remaining = $limit !== null ? round($limit - $spent, 2) : null;

            // Ritmo esperado: para anuales, cuánto "debería" llevar gastado a
            // esta altura del año si el gasto fuera parejo (mes actual / 12).
            $expectedPct = null;
            if ($isAnnual) {
                $expectedPct = round(($date->month / 12) * 100, 1);
            }

            return [
                'category' => $cat,
                'has_budget' => $budget !== null,
                'budget_id' => $budget?->id,
                'amount' => $budget ? (float) $budget->amount : null,   // monto en su moneda original
                'currency' => $budget?->currency ?? 'ARS',
                'period' => $budget?->period ?? 'monthly',
                'is_annual' => $isAnnual,
                'limit_ars' => $limit,
                'spent' => $spent,
                'pct' => $pct,
                'expected_pct' => $expectedPct,
                'remaining' => $remaining,
                'status' => $this->status($pct),
            ];
        });

        $withBudget = $rows->filter(fn ($r) => $r['has_budget'])
            ->sortByDesc(fn ($r) => $r['pct'] ?? -1)
            ->values();
        $withoutBudget = $rows->reject(fn ($r) => $r['has_budget'])
            ->sortByDesc('spent')
            ->values();

        // Separar por período para poder mostrarlos y totalizarlos por separado
        // (mezclar límites mensuales con anuales en un mismo total no tiene sentido).
        $monthlyBudgets = $withBudget->reject(fn ($r) => $r['is_annual'])->values();
        $annualBudgets = $withBudget->filter(fn ($r) => $r['is_annual'])->values();

        // Total del mes (sólo presupuestos mensuales).
        $totalBudget = round($monthlyBudgets->sum(fn ($r) => $r['limit_ars'] ?? 0), 2);
        $totalSpent = round($monthlyBudgets->sum('spent'), 2);
        $totalPct = $totalBudget > 0 ? round(($totalSpent / $totalBudget) * 100, 1) : null;

        // Total anual (sólo presupuestos anuales, acumulado del año).
        $totalAnnualBudget = round($annualBudgets->sum(fn ($r) => $r['limit_ars'] ?? 0), 2);
        $totalAnnualSpent = round($annualBudgets->sum('spent'), 2);
        $totalAnnualPct = $totalAnnualBudget > 0 ? round(($totalAnnualSpent / $totalAnnualBudget) * 100, 1) : null;

        $isCurrentMonth = $date->isSameMonth(now());
        $currentYear = $date->year;

        return view('budgets.index', compact(
            'month',
            'date',
            'isCurrentMonth',
            'currentYear',
            'withBudget',
            'monthlyBudgets',
            'annualBudgets',
            'withoutBudget',
            'totalBudget',
            'totalSpent',
            'totalPct',
            'totalAnnualBudget',
            'totalAnnualSpent',
            'totalAnnualPct',
        ));
    }

    /**
     * Crea o actualiza el presupuesto de una categoría. Si el monto es 0 o
     * vacío, elimina el presupuesto existente (equivale a "sin presupuesto").
     */
    public function upsert(Request $request)
    {
        $groupId = session('active_family_group_id');

        $data = $request->validate([
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'amount' => ['nullable', 'numeric', 'min:0', 'max:999999999999'],
            'currency' => ['nullable', 'in:ARS,USD'],
            'period' => ['nullable', 'in:monthly,annual'],
        ]);

        // La categoría debe ser del sistema o pertenecer al grupo.
        $category = Category::where('id', $data['category_id'])
            ->where(fn ($q) => $q->where('is_system', true)->orWhere('family_group_id', $groupId))
            ->firstOrFail();

        $amount = (float) ($data['amount'] ?? 0);

        if ($amount <= 0) {
            Budget::where('family_group_id', $groupId)
                ->where('category_id', $category->id)
                ->delete();

            return back()->with('success', 'Presupuesto eliminado.');
        }

        Budget::updateOrCreate(
            ['family_group_id' => $groupId, 'category_id' => $category->id],
            [
                'amount' => $amount,
                'currency' => $data['currency'] ?? 'ARS',
                'period' => $data['period'] ?? 'monthly',
            ],
        );

        return back()->with('success', 'Presupuesto guardado.');
    }

    public function destroy(Budget $budget)
    {
        abort_if($budget->family_group_id !== session('active_family_group_id'), 403);

        $budget->delete();

        return back()->with('success', 'Presupuesto eliminado.');
    }

    /**
     * Estado semántico según el % de avance.
     */
    private function status(?float $pct): string
    {
        if ($pct === null) {
            return 'none';
        }
        if ($pct >= 100) {
            return 'over';
        }
        if ($pct >= 80) {
            return 'warn';
        }

        return 'ok';
    }
}
