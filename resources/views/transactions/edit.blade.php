@extends('layouts.app')

@section('title', 'Editar movimiento')

@section('content')

<div style="max-width: 860px;">
    <div style="margin-bottom: 24px;">
        <a href="{{ route('transactions.show', $transaction) }}" style="font-size: 12px; color: var(--muted); text-decoration: none;">← Ver movimiento</a>
        <h1 class="font-display" style="font-size: 22px; font-weight: 700; margin-top: 8px;">Editar movimiento</h1>
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
