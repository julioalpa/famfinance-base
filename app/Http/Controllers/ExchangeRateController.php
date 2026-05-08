<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExchangeRateRequest;
use App\Models\ExchangeRate;
use Illuminate\Http\Request;

class ExchangeRateController extends Controller
{
    public function index(Request $request)
    {
        $groupId = session('active_family_group_id');

        $sortBy  = in_array($request->input('sort'), ['date', 'rate']) ? $request->input('sort') : 'date';
        $sortDir = $request->input('dir') === 'asc' ? 'asc' : 'desc';

        $rates = ExchangeRate::where('family_group_id', $groupId)
            ->with('user')
            ->orderBy($sortBy, $sortDir)
            ->paginate(20)
            ->withQueryString();

        return view('exchange-rates.index', compact('rates', 'sortBy', 'sortDir'));
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
