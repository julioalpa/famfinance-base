<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Http\Request;

class CardPaymentController extends Controller
{
    public function create(Request $request)
    {
        $groupId = session('active_family_group_id');
        $accounts = auth()->user()->familyGroups()
            ->find($groupId)
            ->accounts()
            ->where('is_active', true)
            ->get();

        $creditAccounts = $accounts->where('type', 'credit')->values();
        $sourceAccounts = $accounts->whereIn('type', ['cash', 'digital'])->values();

        $preselectedCard = $request->input('card_id');

        return view('transactions.card-payment', compact('creditAccounts', 'sourceAccounts', 'preselectedCard'));
    }

    public function store(Request $request)
    {
        $groupId = session('active_family_group_id');

        $validated = $request->validate([
            'target_account_id' => ['required', 'integer', 'exists:accounts,id'],
            'account_id'        => ['required', 'integer', 'exists:accounts,id', 'different:target_account_id'],
            'amount'            => ['required', 'numeric', 'min:0.01'],
            'currency'          => ['required', 'in:ARS,USD'],
            'date'              => ['required', 'date'],
            'notes'             => ['nullable', 'string', 'max:1000'],
        ]);

        // Verify both accounts belong to the group
        $card   = Account::where('id', $validated['target_account_id'])
            ->where('family_group_id', $groupId)
            ->where('type', 'credit')
            ->firstOrFail();

        Account::where('id', $validated['account_id'])
            ->where('family_group_id', $groupId)
            ->whereIn('type', ['cash', 'digital'])
            ->firstOrFail();

        Transaction::create([
            'family_group_id'   => $groupId,
            'user_id'           => auth()->id(),
            'type'              => 'transfer',
            'is_card_payment'   => true,
            'account_id'        => $validated['account_id'],
            'target_account_id' => $validated['target_account_id'],
            'amount'            => $validated['amount'],
            'currency'          => $validated['currency'],
            'date'              => $validated['date'],
            'description'       => 'Pago de tarjeta — ' . $card->name,
            'notes'             => $validated['notes'] ?? null,
        ]);

        return redirect()
            ->route('transactions.index')
            ->with('success', 'Pago de tarjeta registrado correctamente.');
    }
}
