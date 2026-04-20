<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Category;
use App\Models\ExchangeRate;
use App\Models\Installment;
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

        // ── Totales del mes ─────────────────────────────────────────────────
        $baseQuery = Transaction::where('family_group_id', $groupId)
            ->whereYear('date', $year)
            ->whereMonth('date', $mon);

        $totalIncome  = (clone $baseQuery)->where('type', 'income')->sum('amount');
        $totalExpense = (clone $baseQuery)->where('type', 'expense')->sum('amount');
        $balance      = $totalIncome - $totalExpense;

        // ── Gastos por categoría (para gráfico) ─────────────────────────────
        $expensesByCategory = (clone $baseQuery)
            ->where('type', 'expense')
            ->with('category')
            ->get()
            ->groupBy(fn($t) => $t->category?->name ?? 'Sin categoría')
            ->map(fn($items) => $items->sum('amount'))
            ->sortDesc()
            ->take(8);

        // ── Cuotas pendientes por cuenta de crédito en el mes ───────────────
        $creditAccounts = $accounts->where('type', 'credit');
        $installmentSummary = $creditAccounts->map(function (Account $account) use ($mon, $year) {
            $installments = $account->getUpcomingInstallments($mon, $year);
            return [
                'account'      => $account,
                'installments' => $installments,
                'total'        => $installments->sum('amount'),
            ];
        })->filter(fn($item) => $item['installments']->isNotEmpty());

        // ── Últimas 10 transacciones ─────────────────────────────────────────
        $recentTransactions = Transaction::with(['account', 'category', 'user'])
            ->where('family_group_id', $groupId)
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        // ── Tipo de cambio vigente ───────────────────────────────────────────
        $exchangeRate = $group->latestExchangeRate();

        return view('dashboard', compact(
            'group',
            'accounts',
            'month',
            'totalIncome',
            'totalExpense',
            'balance',
            'expensesByCategory',
            'installmentSummary',
            'recentTransactions',
            'exchangeRate',
        ));
    }
}
