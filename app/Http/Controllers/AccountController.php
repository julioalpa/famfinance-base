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

        // Si es tarjeta de crédito, mostrar cuotas del mes seleccionado
        $upcomingInstallments = $account->isCredit()
            ? $account->getUpcomingInstallments((int) $mon, (int) $year)
            : collect();

        return view('accounts.show', compact('account', 'transactions', 'upcomingInstallments', 'month'));
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
            $account->family_group_id !== $groupId,
            403,
            'No tenés permiso para acceder a esta cuenta.'
        );
    }
}
