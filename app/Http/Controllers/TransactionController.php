<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionRequest;
use App\Models\Category;
use App\Models\MonthlyPayment;
use App\Models\PaymentItem;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

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

    public function create(Request $request)
    {
        $groupId    = session('active_family_group_id');
        $group      = auth()->user()->familyGroups()->find($groupId);
        $categories = Category::availableFor($groupId);
        $accounts   = $group->accounts()->where('is_active', true)->get();

        $bulk             = $request->boolean('bulk');
        $bulkCount        = $bulk ? session('bulk_count', 0) : 0;
        $defaultDate      = $request->input('date');
        $defaultAccountId = $request->input('account_id');

        if (! $bulk) {
            session()->forget('bulk_count');
        }

        $paidThisMonth = MonthlyPayment::where('family_group_id', $groupId)
            ->where('month', now()->month)
            ->where('year', now()->year)
            ->where('is_paid', true)
            ->pluck('payment_item_id');

        $pendingItems = PaymentItem::with(['account', 'category'])
            ->where('family_group_id', $groupId)
            ->where('is_active', true)
            ->whereNotIn('id', $paidThisMonth)
            ->orderBy('description')
            ->get()
            ->map(fn($item) => [
                'id'          => $item->id,
                'description' => $item->description,
                'account_id'  => $item->account_id,
                'category_id' => $item->category_id,
                'last_amount' => $item->lastPaidAmount(now()->month, now()->year),
            ]);

        return view('transactions.create', compact(
            'categories', 'accounts', 'bulk', 'bulkCount', 'defaultDate', 'defaultAccountId', 'pendingItems'
        ));
    }

    public function store(StoreTransactionRequest $request)
    {
        $groupId = session('active_family_group_id');

        $validated = $request->validated();
        $paymentItemId = $validated['payment_item_id'] ?? null;
        unset($validated['payment_item_id']);

        $transaction = $this->service->create($validated, $groupId, auth()->id());

        if ($paymentItemId) {
            $item = PaymentItem::where('id', $paymentItemId)
                ->where('family_group_id', $groupId)
                ->first();

            if ($item) {
                $date = Carbon::parse($transaction->date);
                $mp = MonthlyPayment::firstOrCreate(
                    ['payment_item_id' => $item->id, 'month' => $date->month, 'year' => $date->year],
                    ['family_group_id' => $groupId]
                );

                if (! $mp->is_paid) {
                    $mp->update([
                        'is_paid'        => true,
                        'paid_at'        => now(),
                        'amount'         => $transaction->amount,
                        'transaction_id' => $transaction->id,
                    ]);
                }
            }
        }

        if ($request->boolean('bulk')) {
            session(['bulk_count' => session('bulk_count', 0) + 1]);

            return redirect()
                ->route('transactions.create', [
                    'bulk'       => 1,
                    'date'       => $request->input('date'),
                    'account_id' => $request->input('account_id'),
                ])
                ->with('bulk_success', true);
        }

        session()->forget('bulk_count');

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
            (int) $transaction->family_group_id !== (int) $groupId,
            403,
            'No tenés permiso para acceder a este movimiento.'
        );
    }
}
