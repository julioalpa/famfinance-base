<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePromotionRequest;
use App\Models\PaymentItem;
use App\Models\Promotion;

class PromotionController extends Controller
{
    public function index()
    {
        $groupId = session('active_family_group_id');

        $promotions = Promotion::with('paymentItem')
            ->where('family_group_id', $groupId)
            ->orderByRaw("CASE WHEN is_active = 1 AND expires_at >= date('now') THEN 0 ELSE 1 END")
            ->orderBy('expires_at')
            ->get();

        return view('promotions.index', compact('promotions'));
    }

    public function create()
    {
        $paymentItems = $this->availablePaymentItems();

        return view('promotions.create', compact('paymentItems'));
    }

    public function store(StorePromotionRequest $request)
    {
        $groupId = session('active_family_group_id');

        Promotion::create([...$request->validated(), 'family_group_id' => $groupId]);

        return redirect()
            ->route('promotions.index')
            ->with('success', 'Promoción guardada correctamente.');
    }

    public function edit(Promotion $promotion)
    {
        $this->authorize($promotion);

        $paymentItems = $this->availablePaymentItems();

        return view('promotions.edit', compact('promotion', 'paymentItems'));
    }

    public function update(StorePromotionRequest $request, Promotion $promotion)
    {
        $this->authorize($promotion);

        $promotion->update($request->validated());

        return redirect()
            ->route('promotions.index')
            ->with('success', 'Promoción actualizada.');
    }

    public function destroy(Promotion $promotion)
    {
        $this->authorize($promotion);

        $promotion->delete();

        return redirect()
            ->route('promotions.index')
            ->with('success', 'Promoción eliminada.');
    }

    public function dismissAlert(Promotion $promotion)
    {
        $this->authorize($promotion);

        $promotion->update(['alerted_at' => now()]);

        return redirect()
            ->route('promotions.index')
            ->with('success', 'Alerta marcada como leída.');
    }

    private function authorize(Promotion $promotion): void
    {
        abort_if(
            $promotion->family_group_id !== session('active_family_group_id'),
            403,
            'No tenés permiso para acceder a esta promoción.'
        );
    }

    private function availablePaymentItems()
    {
        return PaymentItem::where('family_group_id', session('active_family_group_id'))
            ->where('is_active', true)
            ->orderBy('description')
            ->get();
    }
}
