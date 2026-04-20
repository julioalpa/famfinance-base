@extends('layouts.app')

@section('title', 'Editar débito fijo')

@section('content')

<div style="max-width: 860px;">
    <div style="margin-bottom: 24px;">
        <a href="{{ route('recurring-expenses.index') }}" style="font-size:13px; color:var(--muted); text-decoration:none; font-weight:600;">← Débitos fijos</a>
        <h1 class="font-display" style="font-size:24px; font-weight:800; letter-spacing:-0.03em; margin-top:6px;">
            Editar: {{ $recurringExpense->description }}
        </h1>
    </div>

    <div class="card">
        @include('recurring-expenses._form', [
            'recurringExpense' => $recurringExpense,
            'action'           => route('recurring-expenses.update', $recurringExpense),
            'categories'       => $categories,
            'accounts'         => $accounts,
        ])
    </div>
</div>

@endsection
