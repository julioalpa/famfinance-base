@extends('layouts.app')

@section('title', 'Nuevo movimiento')

@section('content')

<div style="max-width: 860px;">
    <div style="margin-bottom: 24px;">
        <a href="{{ route('transactions.index') }}" style="font-size: 12px; color: var(--muted); text-decoration: none;">← Movimientos</a>
        <h1 class="font-display" style="font-size: 22px; font-weight: 700; margin-top: 8px;">Nuevo movimiento</h1>
    </div>

    <div class="card">
        @include('transactions._form', [
            'transaction' => null,
            'action'      => route('transactions.store'),
            'method'      => 'POST',
            'categories'  => $categories,
            'accounts'    => $accounts,
        ])
    </div>
</div>

@endsection
