@extends('layouts.app')

@section('title', $bulk ? 'Carga masiva' : 'Nuevo movimiento')

@section('content')

<div style="max-width: 560px; margin: 0 auto;">

    @if($bulk)
    {{-- ── Banner compacto modo carga masiva ─────────────────────────────────── --}}
    <div style="
        display: flex;
        align-items: center;
        gap: 10px;
        background: var(--accent-dim);
        border: 1px solid rgba(240,160,48,0.28);
        border-radius: 10px;
        padding: 8px 12px;
        margin-bottom: 16px;
        font-size: 12px;
    ">
        <div style="
            width: 22px; height: 22px;
            background: var(--accent);
            border-radius: 6px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        ">
            <svg width="13" height="13" fill="none" stroke="#0c0804" stroke-width="2.8" viewBox="0 0 24 24">
                <path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/>
                <polyline points="13 2 13 9 20 9"/>
            </svg>
        </div>
        <div style="flex: 1; min-width: 0; color: var(--text); font-weight: 600;">
            @if(session('bulk_success'))
                <span style="color: var(--income);">✓</span>
            @endif
            Carga masiva ·
            @if($bulkCount > 0)
                <strong>{{ $bulkCount }}</strong> {{ $bulkCount === 1 ? 'registrado' : 'registrados' }}
            @else
                <span style="color: var(--muted); font-weight: 500;">fecha y cuenta se mantienen</span>
            @endif
        </div>
        <a href="{{ route('transactions.index') }}" style="font-size:12px; color: var(--accent); text-decoration:none; font-weight:700; padding: 4px 8px; flex-shrink:0;">
            Finalizar
        </a>
    </div>
    @else
    {{-- ── Header normal ───────────────────────────────────────────────────────── --}}
    <div style="margin-bottom: 18px; display:flex; align-items:center; justify-content:space-between; gap:12px;">
        <div style="min-width: 0;">
            <a href="{{ route('transactions.index') }}" style="font-size:12px; color:var(--muted); text-decoration:none; font-weight:600;">← Movimientos</a>
            <h1 class="font-display" style="font-size:22px; font-weight:800; letter-spacing:-0.03em; margin-top:4px;">Nuevo movimiento</h1>
        </div>
        <a href="{{ route('transactions.create', ['bulk' => 1]) }}" class="btn btn-ghost" style="font-size:12px; padding: 7px 12px; flex-shrink:0;" title="Modo carga masiva">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/>
                <polyline points="13 2 13 9 20 9"/>
            </svg>
            Masiva
        </a>
    </div>
    @endif

    <div class="card">
        @include('transactions._form', [
            'transaction'      => null,
            'action'           => route('transactions.store'),
            'method'           => 'POST',
            'categories'       => $categories,
            'accounts'         => $accounts,
            'bulk'             => $bulk,
            'defaultDate'      => $defaultDate,
            'defaultAccountId' => $defaultAccountId,
        ])
    </div>
</div>

@endsection
