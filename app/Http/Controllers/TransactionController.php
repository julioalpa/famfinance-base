<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionRequest;
use App\Models\Category;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function __construct(private TransactionService $service) {}

    public function index(Request $request)
    {
        $groupId = session('active_family_group_id');

        $query = Transaction::with(['account', 'category', 'user'])
            ->where('family_group_id', $groupId)
            ->orderByDesc('date')
            ->orderByDesc('id');

        // Filtros opcionales
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('account_id')) {
            $query->where('account_id', $request->account_id);
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('month')) {
            [$year, $month] = explode('-', $request->month);
            $query->whereYear('date', $year)->whereMonth('date', $month);
        }

        $transactions = $query->paginate(50)->withQueryString();
        $categories   = Category::availableFor($groupId);
        $accounts     = auth()->user()->familyGroups()
            ->find($groupId)
            ->accounts()
            ->where('is_active', true)
            ->get();

        return view('transactions.index', compact('transactions', 'categories', 'accounts'));
    }

    public function create()
    {
        $groupId    = session('active_family_group_id');
        $group      = auth()->user()->familyGroups()->find($groupId);
        $categories = Category::availableFor($groupId);
        $accounts   = $group->accounts()->where('is_active', true)->get();

        return view('transactions.create', compact('categories', 'accounts'));
    }

    public function store(StoreTransactionRequest $request)
    {
        $groupId = session('active_family_group_id');

        $transaction = $this->service->create(
            $request->validated(),
            $groupId,
            auth()->id()
        );

        return redirect()
            ->route('transactions.index')
            ->with('success', 'Movimiento registrado correctamente.');
    }

    public function show(Transaction $transaction)
    {
        $this->authorizeTransaction($transaction);

        $transaction->load(['account', 'category', 'user', 'installments', 'targetAccount']);

        return view('transactions.show', compact('transaction'));
    }

    public function edit(Transaction $transaction)
    {
        $this->authorizeTransaction($transaction);

        $groupId    = session('active_family_group_id');
        $group      = auth()->user()->familyGroups()->find($groupId);
        $categories = Category::availableFor($groupId);
        $accounts   = $group->accounts()->where('is_active', true)->get();

        return view('transactions.edit', compact('transaction', 'categories', 'accounts'));
    }

    public function update(StoreTransactionRequest $request, Transaction $transaction)
    {
        $this->authorizeTransaction($transaction);

        $this->service->update($transaction, $request->validated());

        return redirect()
            ->route('transactions.index')
            ->with('success', 'Movimiento actualizado.');
    }

    public function destroy(Transaction $transaction)
    {
        $this->authorizeTransaction($transaction);

        $transaction->delete(); // soft delete

        return redirect()
            ->route('transactions.index')
            ->with('success', 'Movimiento eliminado.');
    }

    private function authorizeTransaction(Transaction $transaction): void
    {
        $groupId = session('active_family_group_id');

        abort_if(
            $transaction->family_group_id !== $groupId,
            403,
            'No tenés permiso para acceder a este movimiento.'
        );
    }
}
