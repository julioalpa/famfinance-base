<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExchangeRateRequest;
use App\Models\ExchangeRate;

class ExchangeRateController extends Controller
{
    public function index()
    {
        $groupId = session('active_family_group_id');
        $rates   = ExchangeRate::where('family_group_id', $groupId)
            ->with('user')
            ->orderByDesc('date')
            ->paginate(20);

        return view('exchange-rates.index', compact('rates'));
    }

    public function store(StoreExchangeRateRequest $request)
    {
        $groupId = session('active_family_group_id');

        ExchangeRate::create([
            ...$request->validated(),
            'family_group_id' => $groupId,
            'user_id'         => auth()->id(),
            'from_currency'   => 'USD',
            'to_currency'     => 'ARS',
        ]);

        return back()->with('success', 'Tipo de cambio registrado.');
    }

    public function destroy(ExchangeRate $exchangeRate)
    {
        abort_if($exchangeRate->family_group_id !== session('active_family_group_id'), 403);

        $exchangeRate->delete();

        return back()->with('success', 'Tipo de cambio eliminado.');
    }
}
