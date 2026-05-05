<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\LoanInstallment;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class LoanController extends Controller
{
    public function setup(Account $account)
    {
        $this->authorizeLoan($account);

        $installments = LoanInstallment::where('account_id', $account->id)
            ->orderBy('installment_number')
            ->get();

        $sourceAccounts = $this->sourceAccounts();

        return view('accounts.loan-schedule', compact('account', 'installments', 'sourceAccounts'));
    }

    public function storeSchedule(Request $request, Account $account)
    {
        $this->authorizeLoan($account);

        $validated = $request->validate([
            'installments_count'  => ['required', 'integer', 'min:1', 'max:600'],
            'installment_amount'  => ['required', 'numeric', 'min:0.01'],
            'first_due_date'      => ['required', 'date'],
        ], [
            'installments_count.required' => 'Ingresá la cantidad de cuotas.',
            'installments_count.min'      => 'Mínimo 1 cuota.',
            'installment_amount.required' => 'Ingresá el importe por cuota.',
            'installment_amount.min'      => 'El importe debe ser mayor a cero.',
            'first_due_date.required'     => 'Indicá la fecha de la primera cuota.',
        ]);

        $groupId = session('active_family_group_id');

        // Keep paid installments; only replace unpaid ones
        $paidCount = LoanInstallment::where('account_id', $account->id)
            ->where('is_paid', true)
            ->count();

        LoanInstallment::where('account_id', $account->id)
            ->where('is_paid', false)
            ->delete();

        $rows = [];
        $firstDate = Carbon::parse($validated['first_due_date']);

        for ($i = 0; $i < (int) $validated['installments_count']; $i++) {
            $rows[] = [
                'account_id'          => $account->id,
                'family_group_id'     => $groupId,
                'installment_number'  => $paidCount + $i + 1,
                'due_date'            => $firstDate->copy()->addMonths($i)->format('Y-m-d'),
                'amount'              => $validated['installment_amount'],
                'currency'            => $account->currency,
                'is_paid'             => false,
                'created_at'          => now(),
                'updated_at'          => now(),
            ];
        }

        LoanInstallment::insert($rows);

        return redirect()
            ->route('accounts.show', $account)
            ->with('success', 'Plan de cuotas configurado correctamente.');
    }

    public function pay(Request $request, LoanInstallment $installment)
    {
        $this->authorizeLoanInstallment($installment);

        if ($installment->is_paid) {
            return back()->with('info', 'Esta cuota ya está registrada como pagada.');
        }

        $validated = $request->validate([
            'source_account_id' => ['required', 'integer', 'exists:accounts,id'],
            'amount'            => ['required', 'numeric', 'min:0.01'],
            'date'              => ['required', 'date'],
            'notes'             => ['nullable', 'string', 'max:500'],
        ]);

        $groupId      = session('active_family_group_id');
        $loanAccount  = $installment->account;
        $totalCount   = LoanInstallment::where('account_id', $installment->account_id)->count();

        Account::where('id', $validated['source_account_id'])
            ->where('family_group_id', $groupId)
            ->whereIn('type', ['cash', 'digital'])
            ->firstOrFail();

        $transaction = Transaction::create([
            'family_group_id'   => $groupId,
            'user_id'           => auth()->id(),
            'type'              => 'transfer',
            'account_id'        => $validated['source_account_id'],
            'target_account_id' => $installment->account_id,
            'amount'            => $validated['amount'],
            'currency'          => $installment->currency,
            'date'              => $validated['date'],
            'description'       => 'Cuota ' . $installment->installment_number . '/' . $totalCount . ' — ' . $loanAccount->name,
            'notes'             => $validated['notes'] ?? null,
        ]);

        $installment->update([
            'is_paid'        => true,
            'paid_at'        => now(),
            'transaction_id' => $transaction->id,
        ]);

        return redirect()
            ->route('accounts.show', $installment->account_id)
            ->with('success', 'Cuota ' . $installment->installment_number . ' registrada como pagada.');
    }

    public function destroySchedule(Account $account)
    {
        $this->authorizeLoan($account);

        LoanInstallment::where('account_id', $account->id)
            ->where('is_paid', false)
            ->delete();

        return redirect()
            ->route('accounts.show', $account)
            ->with('success', 'Plan de cuotas eliminado. Las cuotas ya pagadas se conservan.');
    }

    private function sourceAccounts()
    {
        $groupId = session('active_family_group_id');
        return auth()->user()->familyGroups()
            ->find($groupId)
            ->accounts()
            ->where('is_active', true)
            ->whereIn('type', ['cash', 'digital'])
            ->get();
    }

    private function authorizeLoan(Account $account): void
    {
        $groupId = session('active_family_group_id');
        abort_if((int) $account->family_group_id !== (int) $groupId, 403);
        abort_if($account->type !== 'loan', 404);
    }

    private function authorizeLoanInstallment(LoanInstallment $installment): void
    {
        $groupId = session('active_family_group_id');
        abort_if((int) $installment->family_group_id !== (int) $groupId, 403);
    }
}
