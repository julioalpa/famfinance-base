@extends('layouts.app')

@section('title', 'Editar movimiento')

@section('content')

<div style="max-width: 560px; margin: 0 auto;">
    <div style="margin-bottom: 18px;">
        <a href="{{ route('transactions.show', $transaction) }}" style="font-size: 12px; color: var(--muted); text-decoration: none; font-weight: 600;">← Ver movimiento</a>
        <h1 class="font-display" style="font-size: 22px; font-weight: 800; letter-spacing:-0.03em; margin-top: 4px;">Editar movimiento</h1>
    </div>

    <div class="card">
        @include('transactions._form', [
            'transaction' => $transaction,
            'action'      => route('transactions.update', $transaction),
            'method'      => 'PUT',
            'categories'  => $categories,
            'accounts'    => $accounts,
        ])
    </div>
</div>

@endsection
