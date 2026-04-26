<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRecurringExpenseRequest;
use App\Models\Category;
use App\Models\FamilyGroup;
use App\Models\RecurringExpense;
use App\Models\RecurringExpenseLog;
use App\Models\Transaction;
use Illuminate\Http\Request;

class RecurringExpenseController extends Controller
{
    public function index()
    {
        $groupId = session('active_family_group_id');
        $group   = auth()->user()->familyGroups()->find($groupId);
        $rate    = $group->latestExchangeRate();

        $recurring = RecurringExpense::with(['account', 'category', 'logs'])
            ->where('family_group_id', $groupId)
            ->orderBy('day_of_month')
            ->get();

        $totalActive = $recurring
            ->where('is_active', true)
            ->sum(function ($r) use ($rate) {
                $amt = (float) $r->amount;
                if ($r->currency === 'USD' && $rate) {
                    return $rate->convert($amt, 'USD');
                }
                return $amt;
            });

        $currentMonth = now()->month;
        $currentYear  = now()->year;

        return view('recurring-expenses.index', compact('recurring', 'totalActive', 'currentMonth', 'currentYear'));
    }

    public function create()
    {
        [$categories, $accounts] = $this->formData();

        return view('recurring-expenses.create', compact('categories', 'accounts'));
    }

    public function store(StoreRecurringExpenseRequest $request)
    {
        $groupId = session('active_family_group_id');

        RecurringExpense::create([
            ...$request->validated(),
            'family_group_id' => $groupId,
            'user_id'         => auth()->id(),
        ]);

        return redirect()
            ->route('recurring-expenses.index')
            ->with('success', 'Débito fijo creado correctamente.');
    }

    public function edit(RecurringExpense $recurringExpense)
    {
        $this->authorize($recurringExpense);

        [$categories, $accounts] = $this->formData();

        return view('recurring-expenses.edit', compact('recurringExpense', 'categories', 'accounts'));
    }

    public function update(StoreRecurringExpenseRequest $request, RecurringExpense $recurringExpense)
    {
        $this->authorize($recurringExpense);

        $recurringExpense->update($request->validated());

        return redirect()
            ->route('recurring-expenses.index')
            ->with('success', 'Débito fijo actualizado.');
    }

    public function destroy(RecurringExpense $recurringExpense)
    {
        $this->authorize($recurringExpense);

        $recurringExpense->delete();

        return redirect()
            ->route('recurring-expenses.index')
            ->with('success', 'Débito fijo eliminado.');
    }

    public function confirm(RecurringExpense $recurringExpense)
    {
        $this->authorize($recurringExpense);

        $groupId = session('active_family_group_id');
        $month   = now()->month;
        $year    = now()->year;

        // Idempotent: si ya fue procesado este mes, redirigir sin hacer nada
        if (RecurringExpenseLog::where('recurring_expense_id', $recurringExpense->id)
            ->where('month', $month)->where('year', $year)->exists()) {
            return redirect()->route('dashboard');
        }

        $transaction = Transaction::create([
            'family_group_id'      => $groupId,
            'user_id'              => auth()->id(),
            'account_id'           => $recurringExpense->account_id,
            'category_id'          => $recurringExpense->category_id,
            'type'                 => 'expense',
            'amount'               => $recurringExpense->amount,
            'currency'             => $recurringExpense->currency,
            'date'                 => $recurringExpense->executionDateThisMonth(),
            'description'          => $recurringExpense->description,
            'recurring_expense_id' => $recurringExpense->id,
        ]);

        RecurringExpenseLog::create([
            'family_group_id'      => $groupId,
            'recurring_expense_id' => $recurringExpense->id,
            'transaction_id'       => $transaction->id,
            'month'                => $month,
            'year'                 => $year,
            'status'               => 'confirmed',
        ]);

        return redirect()->route('dashboard')
            ->with('success', "Débito registrado: {$recurringExpense->description}.");
    }

    public function skip(RecurringExpense $recurringExpense)
    {
        $this->authorize($recurringExpense);

        $groupId = session('active_family_group_id');
        $month   = now()->month;
        $year    = now()->year;

        RecurringExpenseLog::firstOrCreate(
            [
                'recurring_expense_id' => $recurringExpense->id,
                'month'                => $month,
                'year'                 => $year,
            ],
            [
                'family_group_id' => $groupId,
                'status'          => 'skipped',
            ]
        );

        return redirect()->route('dashboard');
    }

    public function toggle(RecurringExpense $recurringExpense)
    {
        $this->authorize($recurringExpense);

        $recurringExpense->update(['is_active' => ! $recurringExpense->is_active]);

        $label = $recurringExpense->is_active ? 'activado' : 'pausado';

        return redirect()
            ->route('recurring-expenses.index')
            ->with('success', "Débito fijo {$label}.");
    }

    private function authorize(RecurringExpense $recurringExpense): void
    {
        abort_if(
            $recurringExpense->family_group_id !== session('active_family_group_id'),
            403,
            'No tenés permiso para acceder a este débito.'
        );
    }

    private function formData(): array
    {
        $groupId    = session('active_family_group_id');
        $group      = auth()->user()->familyGroups()->find($groupId);
        $categories = Category::availableFor($groupId);
        $accounts   = $group->accounts()->where('is_active', true)->get();

        return [$categories, $accounts];
    }
}
