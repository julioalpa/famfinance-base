<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRecurringExpenseRequest;
use App\Models\Category;
use App\Models\FamilyGroup;
use App\Models\RecurringExpense;
use Illuminate\Http\Request;

class RecurringExpenseController extends Controller
{
    public function index()
    {
        $groupId = session('active_family_group_id');
        $group   = auth()->user()->familyGroups()->find($groupId);
        $rate    = $group->latestExchangeRate();

        $recurring = RecurringExpense::with(['account', 'category'])
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

        return view('recurring-expenses.index', compact('recurring', 'totalActive'));
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
