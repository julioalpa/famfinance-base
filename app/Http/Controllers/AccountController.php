<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAccountRequest;
use App\Models\Account;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function index()
    {
        $groupId  = session('active_family_group_id');
        $group    = auth()->user()->familyGroups()->find($groupId);
        $accounts = $group->accounts()
            ->with('user')
            ->where('is_active', true)
            ->get();

        return view('accounts.index', compact('accounts'));
    }

    public function create()
    {
        return view('accounts.create');
    }

    public function store(StoreAccountRequest $request)
    {
        $groupId = session('active_family_group_id');

        Account::create([
            ...$request->validated(),
            'family_group_id' => $groupId,
            'user_id'         => auth()->id(),
        ]);

        return redirect()
            ->route('accounts.index')
            ->with('success', 'Cuenta creada correctamente.');
    }

    public function show(Account $account)
    {
        $this->authorizeAccount($account);

        $account->load('user');

        $month = request('month', now()->format('Y-m'));
        [$year, $mon] = explode('-', $month);

        $transactions = $account->transactions()
            ->with(['category', 'user'])
            ->whereYear('date', $year)
            ->whereMonth('date', $mon)
            ->orderByDesc('date')
            ->get();

        // Para cuentas pasivo: incluir transferencias entrantes (pagos)
        if ($account->isLiability()) {
            $incomingPayments = \App\Models\Transaction::with(['category', 'user'])
                ->where('target_account_id', $account->id)
                ->where('type', 'transfer')
                ->whereYear('date', $year)
                ->whereMonth('date', $mon)
                ->get()
                ->each(fn($tx) => $tx->setAttribute('is_payment', true));

            $transactions = $transactions
                ->merge($incomingPayments)
                ->sortByDesc('date')
                ->values();
        }

        // Si es tarjeta de crédito, mostrar cuotas del mes seleccionado
        $upcomingInstallments = $account->isCredit()
            ? $account->getUpcomingInstallments((int) $mon, (int) $year)
            : collect();

        // Próximo resumen estimado (solo tarjetas de crédito)
        $nextPaymentSummary = null;
        if ($account->isCredit()) {
            $today   = now();
            $closing = (int) request('closing_override', $account->closing_day ?? 1);
            $dueDay  = (int) request('due_override',     $account->due_day ?? 0);
            $closing = max(1, min(31, $closing));
            $dueDay  = max(0, min(31, $dueDay));

            // Último cierre
            $lastClosing = $today->day >= $closing
                ? $today->copy()->setDay($closing)->startOfDay()
                : $today->copy()->subMonthNoOverflow()->setDay($closing)->startOfDay();

            $periodStart = $lastClosing->copy()->addDay();
            $nextClosing = $lastClosing->copy()->addMonthNoOverflow();

            // Gastos normales (sin cuotas) desde el último cierre hasta hoy
            $periodExpenses = (float) $account->transactions()
                ->where('type', 'expense')
                ->where('has_installments', false)
                ->whereBetween('date', [$periodStart->format('Y-m-d'), $today->format('Y-m-d')])
                ->sum('amount');

            // Cuotas que vencen el próximo mes calendario
            $nextMon          = $today->copy()->addMonthNoOverflow();
            $nextInstallments = $account->getUpcomingInstallments($nextMon->month, $nextMon->year);

            $dueDate = $dueDay
                ? $nextClosing->copy()->addMonthNoOverflow()->setDay($dueDay)
                : null;

            $nextPaymentSummary = [
                'period_start'       => $periodStart,
                'period_end'         => $nextClosing,
                'expenses'           => $periodExpenses,
                'installments'       => $nextInstallments,
                'installments_total' => (float) $nextInstallments->sum('amount'),
                'total'              => $periodExpenses + (float) $nextInstallments->sum('amount'),
                'due_date'           => $dueDate,
                'closing_used'       => $closing,
                'due_day_used'       => $dueDay ?: '',
                'closing_default'    => $account->closing_day ?? 1,
                'due_day_default'    => $account->due_day ?? '',
            ];
        }

        return view('accounts.show', compact('account', 'transactions', 'upcomingInstallments', 'month', 'nextPaymentSummary'));
    }

    public function edit(Account $account)
    {
        $this->authorizeAccount($account);

        return view('accounts.edit', compact('account'));
    }

    public function update(StoreAccountRequest $request, Account $account)
    {
        $this->authorizeAccount($account);

        $account->update($request->validated());

        return redirect()
            ->route('accounts.show', $account)
            ->with('success', 'Cuenta actualizada.');
    }

    public function destroy(Account $account)
    {
        $this->authorizeAccount($account);

        // Soft delete: no borra las transacciones históricas
        $account->update(['is_active' => false]);
        $account->delete();

        return redirect()
            ->route('accounts.index')
            ->with('success', 'Cuenta eliminada.');
    }

    private function authorizeAccount(Account $account): void
    {
        $groupId = session('active_family_group_id');

        abort_if(
            (int) $account->family_group_id !== (int) $groupId,
            403,
            'No tenés permiso para acceder a esta cuenta.'
        );
    }
}
