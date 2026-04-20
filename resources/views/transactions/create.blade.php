@extends('layouts.app')

@section('title', $bulk ? 'Carga masiva' : 'Nuevo movimiento')

@section('content')

<div style="max-width: 860px;">

    @if($bulk)
    {{-- ── Banner modo carga masiva ──────────────────────────────────────────── --}}
    <div style="
        background: var(--accent-dim);
        border: 1px solid rgba(240,160,48,0.28);
        border-radius: 14px;
        padding: 14px 20px;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
    ">
        <div style="display:flex; align-items:center; gap: 10px; flex: 1; min-width: 0;">
            <div style="
                width: 36px; height: 36px;
                background: var(--accent);
                border-radius: 9px;
                display: flex; align-items: center; justify-content: center;
                flex-shrink: 0;
            ">
                <svg width="18" height="18" fill="none" stroke="#0c0804" stroke-width="2.5" viewBox="0 0 24 24">
                    <path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/>
                    <polyline points="13 2 13 9 20 9"/>
                    <line x1="12" y1="12" x2="12" y2="18"/>
                    <line x1="9" y1="15" x2="15" y2="15"/>
                </svg>
            </div>
            <div>
                <div class="font-display" style="font-size:14px; font-weight:700; color: var(--accent); letter-spacing:-0.01em;">
                    Modo carga masiva activo
                </div>
                <div style="font-size:12px; color: var(--muted); margin-top:1px;">
                    @if(session('bulk_success'))
                        ✓ Movimiento guardado —
                    @endif
                    @if($bulkCount > 0)
                        <strong style="color: var(--text);">{{ $bulkCount }}</strong> {{ $bulkCount === 1 ? 'movimiento registrado' : 'movimientos registrados' }} en esta sesión
                    @else
                        La fecha y cuenta se mantienen entre movimientos
                    @endif
                </div>
            </div>
        </div>

        <div style="display:flex; gap:8px; flex-shrink:0;">
            <a href="{{ route('dashboard') }}" class="btn btn-ghost" style="font-size:13px; padding: 8px 14px;">
                Dashboard
            </a>
            <a href="{{ route('transactions.index') }}" class="btn btn-primary" style="font-size:13px; padding: 8px 16px;">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                Finalizar
            </a>
        </div>
    </div>
    @else
    {{-- ── Header normal ───────────────────────────────────────────────────────── --}}
    <div style="margin-bottom: 24px; display:flex; align-items:flex-start; justify-content:space-between; flex-wrap:wrap; gap:12px;">
        <div>
            <a href="{{ route('transactions.index') }}" style="font-size:13px; color:var(--muted); text-decoration:none; font-weight:600;">← Movimientos</a>
            <h1 class="font-display" style="font-size:24px; font-weight:800; letter-spacing:-0.03em; margin-top:6px;">Nuevo movimiento</h1>
        </div>
        <a href="{{ route('transactions.create', ['bulk' => 1]) }}" class="btn btn-ghost" style="font-size:13px; align-self:flex-end;">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/>
                <polyline points="13 2 13 9 20 9"/>
                <line x1="12" y1="12" x2="12" y2="18"/>
                <line x1="9" y1="15" x2="15" y2="15"/>
            </svg>
            Carga masiva
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
