<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePaymentItemRequest;
use App\Models\Category;
use App\Models\PaymentItem;
use App\Models\Tag;
use Illuminate\Http\Request;

class PaymentItemController extends Controller
{
    public function index()
    {
        $groupId = session('active_family_group_id');

        $items = PaymentItem::with(['account', 'category'])
            ->where('family_group_id', $groupId)
            ->orderBy('day_of_month')
            ->orderBy('description')
            ->get();

        return view('payment-items.index', compact('items'));
    }

    public function create()
    {
        [$categories, $accounts, $allTags] = $this->formData();

        return view('payment-items.create', compact('categories', 'accounts', 'allTags'));
    }

    public function store(StorePaymentItemRequest $request)
    {
        $groupId = session('active_family_group_id');

        $data   = $request->validated();
        $tagIds = $data['tags'] ?? [];
        unset($data['tags']);

        $item = PaymentItem::create([...$data, 'family_group_id' => $groupId]);
        $item->tags()->sync($tagIds);

        return redirect()
            ->route('payment-items.index')
            ->with('success', 'Pago pendiente creado correctamente.');
    }

    public function edit(PaymentItem $paymentItem)
    {
        $this->authorize($paymentItem);

        [$categories, $accounts, $allTags] = $this->formData();
        $paymentItem->load('tags');

        return view('payment-items.edit', compact('paymentItem', 'categories', 'accounts', 'allTags'));
    }

    public function update(StorePaymentItemRequest $request, PaymentItem $paymentItem)
    {
        $this->authorize($paymentItem);

        $data   = $request->validated();
        $tagIds = $data['tags'] ?? [];
        unset($data['tags']);

        $paymentItem->update($data);
        $paymentItem->tags()->sync($tagIds);

        return redirect()
            ->route('payment-items.index')
            ->with('success', 'Pago pendiente actualizado.');
    }

    public function destroy(PaymentItem $paymentItem)
    {
        $this->authorize($paymentItem);

        $paymentItem->delete();

        return redirect()
            ->route('payment-items.index')
            ->with('success', 'Pago pendiente eliminado.');
    }

    public function toggle(PaymentItem $paymentItem)
    {
        $this->authorize($paymentItem);

        $paymentItem->update(['is_active' => ! $paymentItem->is_active]);

        $label = $paymentItem->is_active ? 'activado' : 'pausado';

        return redirect()
            ->route('payment-items.index')
            ->with('success', "Pago pendiente {$label}.");
    }

    /** Dar de baja definitivamente desde el checklist mensual. */
    public function retire(PaymentItem $paymentItem)
    {
        $this->authorize($paymentItem);

        $paymentItem->update([
            'is_active'   => false,
            'is_retiring' => false,
        ]);

        return redirect()
            ->route('monthly-payments.index')
            ->with('success', "«{$paymentItem->description}» dado de baja. No aparecerá en futuros meses.");
    }

    private function authorize(PaymentItem $paymentItem): void
    {
        abort_if(
            $paymentItem->family_group_id !== session('active_family_group_id'),
            403,
            'No tenés permiso para acceder a este ítem.'
        );
    }

    private function formData(): array
    {
        $groupId    = session('active_family_group_id');
        $group      = auth()->user()->familyGroups()->find($groupId);
        $categories = Category::availableFor($groupId);
        $accounts   = $group->accounts()->where('is_active', true)->get();
        $allTags    = Tag::where('family_group_id', $groupId)->orderBy('name')->get();

        return [$categories, $accounts, $allTags];
    }
}
