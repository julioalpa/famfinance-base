@extends('layouts.app')

@section('title', 'Cuentas')

@section('content')

<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 28px;">
    <div>
        <h1 class="font-display" style="font-size: 24px; font-weight: 700; letter-spacing: -0.02em;">Cuentas</h1>
        <div style="font-size: 12px; color: var(--muted); margin-top: 3px;">Efectivo, digital y tarjetas del grupo</div>
    </div>
    <a href="{{ route('accounts.create') }}" class="btn btn-primary">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
        Nueva cuenta
    </a>
</div>

@php
    $grouped    = $accounts->groupBy('type');
    $typeLabels = ['cash' => 'Efectivo', 'digital' => 'Digital', 'credit' => 'Tarjetas de crédito', 'loan' => 'Préstamos'];
    $typeSubtitles = [
        'cash'    => 'Billetera, caja, efectivo en mano',
        'digital' => 'Billeteras virtuales, cuentas bancarias',
        'credit'  => 'Lo que gastaste y aún debés a la tarjeta',
        'loan'    => 'Préstamos personales o hipotecarios — lo que debés en total',
    ];
    $typeColors = ['cash' => 'var(--income)', 'digital' => 'var(--accent2)', 'credit' => 'var(--warn)', 'loan' => 'var(--expense)'];
    $balanceLabel = ['cash' => 'Saldo disponible', 'digital' => 'Saldo disponible', 'credit' => 'Deuda acumulada', 'loan' => 'Deuda restante'];
@endphp

@foreach(['cash','digital','credit','loan'] as $type)
    @if(isset($grouped[$type]) && $grouped[$type]->isNotEmpty())
    <div style="margin-bottom: 28px;">
        <div style="margin-bottom: 14px;">
            <div style="font-size: 11px; letter-spacing: 0.1em; text-transform: uppercase; color: var(--muted); font-weight: 700;">
                {{ $typeLabels[$type] }}
            </div>
            <div style="font-size: 11px; color: var(--muted); margin-top: 2px; opacity: 0.7;">
                {{ $typeSubtitles[$type] }}
            </div>
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 14px;">
            @foreach($grouped[$type] as $account)
            <a href="{{ route('accounts.show', $account) }}"
               style="text-decoration: none; display: block;">
                <div class="card" style="transition: border-color 0.15s; cursor: pointer; position: relative; overflow: hidden;"
                     onmouseover="this.style.borderColor='var(--accent)'"
                     onmouseout="this.style.borderColor='var(--border)'">

                    {{-- Barra de tipo --}}
                    <div style="position: absolute; top: 0; left: 0; right: 0; height: 2px; background: {{ $typeColors[$type] }};"></div>

                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 14px;">
                        <div>
                            <div class="font-display" style="font-size: 15px; font-weight: 600; color: var(--text); margin-bottom: 4px;">
                                {{ $account->name }}
                            </div>
                            <span class="badge badge-{{ $type }}" style="font-size: 10px;">{{ $typeLabels[$type] }}</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            @include('accounts._brand_logo', ['brand' => $account->brand, 'type' => $account->type, 'size' => 'sm'])
                            <div style="font-size: 11px; color: var(--muted); background: var(--surface2); padding: 3px 8px; border-radius: 4px;">
                                {{ $account->currency }}
                            </div>
                        </div>
                    </div>

                    <div style="font-size: 12px; color: var(--muted); margin-bottom: 4px;">
                        {{ $balanceLabel[$type] }}
                    </div>
                    @php
                        $bal      = $account->balance;
                        $isNeg    = !in_array($type, ['credit','loan']) && $bal < 0;
                        $balColor = $isNeg ? 'var(--expense)' : $typeColors[$type];
                    @endphp
                    <div class="font-display" style="font-size: 20px; font-weight: 700; color: {{ $balColor }};">
                        @if($isNeg)−@endif{{ $account->currency === 'USD' ? 'US$' : '$' }} {{ number_format(abs($bal), 2, ',', '.') }}
                    </div>

                    @if($type === 'loan' && $account->initial_balance)
                    <div style="margin-top: 8px; font-size: 11px; color: var(--muted);">
                        @php $pct = $account->initial_balance > 0 ? round(($account->balance / $account->initial_balance) * 100, 0) : 0; @endphp
                        Pagado: {{ 100 - max(0, $pct) }}% ·
                        <span style="color: var(--expense);">Restante: $ {{ number_format($account->initial_balance, 0, ',', '.') }} original</span>
                    </div>
                    @endif

                    @if($type === 'credit' && $account->closing_day)
                    <div style="margin-top: 10px; padding-top: 10px; border-top: 1px solid var(--border); font-size: 11px; color: var(--muted); display: flex; gap: 12px;">
                        <span>Cierre: día {{ $account->closing_day }}</span>
                        <span>Venc: día {{ $account->due_day }}</span>
                    </div>
                    @endif

                    <div style="margin-top: 8px; font-size: 11px; color: var(--muted);">
                        Registrada por {{ $account->user->name }}
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif
@endforeach

@if($accounts->isEmpty())
<div style="text-align: center; padding: 80px 20px;">
    <div style="font-size: 36px; margin-bottom: 12px;">💳</div>
    <div style="font-size: 14px; color: var(--muted); margin-bottom: 20px;">No hay cuentas cargadas todavía</div>
    <a href="{{ route('accounts.create') }}" class="btn btn-primary" style="display: inline-flex;">Crear primera cuenta</a>
</div>
@endif

@endsection
