@extends('layouts.app')

@section('title', 'Editar ítem de pago')

@section('content')

<div style="margin-bottom: 28px;">
    <a href="{{ route('payment-items.index') }}" style="font-size: 13px; color: var(--muted); text-decoration: none; display: inline-flex; align-items: center; gap: 6px; margin-bottom: 12px;">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
        Volver
    </a>
    <h1 class="font-display" style="font-size: 26px; font-weight: 800; letter-spacing: -0.03em; color: var(--text);">
        Editar: {{ $paymentItem->description }}
    </h1>
</div>

<div class="card" style="max-width: 760px;">
    @include('payment-items._form', [
        'action'      => route('payment-items.update', $paymentItem),
        'paymentItem' => $paymentItem,
    ])
</div>

@endsection
