<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionRequest;
use App\Models\Category;
use App\Models\MonthlyPayment;
use App\Models\PaymentItem;
use App\Models\Tag;
use App\Models\Transaction;
use App\Services\ExchangeRateResolver;
use App\Services\TransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class TransactionController extends Controller
{
    public function __construct(private TransactionService $service) {}

    public function index(Request $request)
    {
        $groupId = session('active_family_group_id');
        $selectedMonth = $request->input('month', now()->format('Y-m'));
        [$statsYear, $statsMonth] = explode('-', $selectedMonth);

        $sortBy = in_array($request->input('sort'), ['date', 'type', 'description', 'amount']) ? $request->input('sort') : 'date';
        $sortDir = $request->input('dir') === 'asc' ? 'asc' : 'desc';

        // Detecta si hay filtros de rango/búsqueda activos. Cuando los hay, el
        // filtro de "mes" se ignora (el rango manda) para poder buscar en todo
        // el historial.
        $hasRange = $request->filled('from') || $request->filled('to')
            || $request->filled('q')
            || $request->filled('min') || $request->filled('max');

        // Closure que aplica TODOS los filtros a un builder (reutilizable para
        // la grilla y para las sumatorias, evitando lógica duplicada).
        $applyFilters = function ($q) use ($request, $statsYear, $statsMonth, $hasRange) {
            $typeFilter = $request->input('type');
            if ($typeFilter === 'card_payment') {
                $q->where('type', 'transfer')->where('is_card_payment', true);
            } elseif ($typeFilter === 'transfer') {
                $q->where('type', 'transfer')->where('is_card_payment', false);
            } elseif ($request->filled('type')) {
                $q->where('type', $typeFilter);
            }

            if ($request->filled('account_id')) {
                $q->where('account_id', $request->account_id);
            }
            if ($request->filled('user_id')) {
                $q->where('user_id', $request->user_id);
            }
            if ($request->filled('category_id')) {
                $q->where('category_id', $request->category_id);
            }
            if ($request->filled('tag_id')) {
                $q->whereHas('tags', fn ($t) => $t->where('tags.id', $request->tag_id));
            }

            // Búsqueda por texto en descripción y notas.
            if ($request->filled('q')) {
                $term = trim($request->input('q'));
                $q->where(function ($w) use ($term) {
                    $w->where('description', 'like', "%{$term}%")
                        ->orWhere('notes', 'like', "%{$term}%");
                });
            }

            // Rango de montos.
            if ($request->filled('min')) {
                $q->where('amount', '>=', (float) $request->input('min'));
            }
            if ($request->filled('max')) {
                $q->where('amount', '<=', (float) $request->input('max'));
            }

            // Fechas: rango explícito (desde/hasta) tiene prioridad sobre el mes.
            if ($request->filled('from')) {
                $q->whereDate('date', '>=', $request->input('from'));
            }
            if ($request->filled('to')) {
                $q->whereDate('date', '<=', $request->input('to'));
            }
            if (! $hasRange && $request->filled('month')) {
                $q->whereYear('date', $statsYear)->whereMonth('date', $statsMonth);
            }
        };

        $query = Transaction::with(['account', 'category', 'user', 'tags'])
            ->where('family_group_id', $groupId)
            ->tap($applyFilters)
            ->orderBy($sortBy, $sortDir);
        if ($sortBy === 'date') {
            $query->orderBy('id', $sortDir);
        }

        // Stats del mes seleccionado (independientes de los filtros de la grilla).
        // Las sumas se valorizan en ARS usando la tasa vigente a la fecha de
        // cada movimiento, para no mezclar ARS y USD sin convertir.
        $rateResolver = ExchangeRateResolver::forGroup($groupId);

        $monthTx = Transaction::where('family_group_id', $groupId)
            ->whereYear('date', $statsYear)
            ->whereMonth('date', $statsMonth)
            ->get(['amount', 'currency', 'date', 'type']);

        $monthStats = [
            'expenses' => round($monthTx->where('type', 'expense')->sum(fn ($t) => $t->amountInArsOn($rateResolver)), 2),
            'income' => round($monthTx->where('type', 'income')->sum(fn ($t) => $t->amountInArsOn($rateResolver)), 2),
            'count' => $monthTx->count(),
        ];
        $monthStats['balance'] = $monthStats['income'] - $monthStats['expenses'];

        $monthLabel = Carbon::createFromDate($statsYear, $statsMonth, 1)
            ->locale('es')
            ->isoFormat('MMMM YYYY');

        // Sumatoria y conteo del filtro actual (todas las páginas), valorizado
        // en ARS por fecha de cada movimiento.
        $filteredSumQuery = Transaction::where('family_group_id', $groupId)->tap($applyFilters);
        $filteredTotal = round(
            (clone $filteredSumQuery)->get(['amount', 'currency', 'date'])
                ->sum(fn ($t) => $t->amountInArsOn($rateResolver)),
            2
        );
        $filteredCount = (clone $filteredSumQuery)->count();

        $transactions = $query->paginate(50)->withQueryString();
        $categories = Category::availableFor($groupId);
        $group = auth()->user()->familyGroups()->with('members')->find($groupId);
        $accounts = $group->accounts()
            ->where('is_active', true)
            ->get();
        $members = $group->members;
        $allTags = Tag::where('family_group_id', $groupId)->orderBy('name')->get();

        return view('transactions.index', compact(
            'transactions', 'categories', 'accounts', 'members',
            'monthStats', 'monthLabel', 'filteredTotal', 'filteredCount', 'allTags',
            'sortBy', 'sortDir', 'hasRange'
        ));
    }

    public function create(Request $request)
    {
        $groupId = session('active_family_group_id');
        $group = auth()->user()->familyGroups()->find($groupId);
        $categories = Category::availableFor($groupId);
        $accounts = $group->accounts()->where('is_active', true)->get();

        $allTags = Tag::where('family_group_id', $groupId)->orderBy('name')->get();
        $bulk = $request->boolean('bulk');
        $bulkCount = $bulk ? session('bulk_count', 0) : 0;
        $defaultDate = $request->input('date');
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
            ->map(fn ($item) => [
                'id' => $item->id,
                'description' => $item->description,
                'account_id' => $item->account_id,
                'category_id' => $item->category_id,
                'last_amount' => $item->lastPaidAmount(now()->month, now()->year),
            ]);

        return view('transactions.create', compact(
            'categories', 'accounts', 'allTags', 'bulk', 'bulkCount', 'defaultDate', 'defaultAccountId', 'pendingItems'
        ));
    }

    public function store(StoreTransactionRequest $request)
    {
        $groupId = session('active_family_group_id');

        $validated = $request->validated();
        $paymentItemId = $validated['payment_item_id'] ?? null;
        $tagIds = $validated['tags'] ?? [];
        unset($validated['payment_item_id'], $validated['tags']);

        $transaction = $this->service->create($validated, $groupId, auth()->id());
        $transaction->tags()->sync($tagIds);

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
                        'is_paid' => true,
                        'paid_at' => now(),
                        'amount' => $transaction->amount,
                        'transaction_id' => $transaction->id,
                        'transaction_was_created_here' => true,
                    ]);
                }
            }
        }

        if ($request->boolean('bulk')) {
            session(['bulk_count' => session('bulk_count', 0) + 1]);

            return redirect()
                ->route('transactions.create', [
                    'bulk' => 1,
                    'date' => $request->input('date'),
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

        $groupId = session('active_family_group_id');
        $group = auth()->user()->familyGroups()->find($groupId);
        $categories = Category::availableFor($groupId);
        $accounts = $group->accounts()->where('is_active', true)->get();
        $allTags = Tag::where('family_group_id', $groupId)->orderBy('name')->get();
        $transaction->load('tags');

        return view('transactions.edit', compact('transaction', 'categories', 'accounts', 'allTags'));
    }

    public function update(StoreTransactionRequest $request, Transaction $transaction)
    {
        $this->authorizeTransaction($transaction);

        $validated = $request->validated();
        $tagIds = $validated['tags'] ?? [];
        unset($validated['tags']);

        $this->service->update($transaction, $validated);
        $transaction->tags()->sync($tagIds);

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

    public function syncTags(Request $request, Transaction $transaction): JsonResponse
    {
        $this->authorizeTransaction($transaction);

        $request->validate([
            'tags' => ['array'],
            'tags.*' => ['integer'],
        ]);

        $groupId = session('active_family_group_id');
        $tagIds = collect($request->input('tags', []))
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0);

        $validIds = Tag::where('family_group_id', $groupId)
            ->whereIn('id', $tagIds)
            ->pluck('id');

        $transaction->tags()->sync($validIds);

        return response()->json([
            'tags' => $transaction->fresh()->tags->map(fn ($t) => [
                'id' => $t->id,
                'name' => $t->name,
                'color' => $t->color,
            ]),
        ]);
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
