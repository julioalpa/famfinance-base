@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

{{-- ── Header ──────────────────────────────────────────────────────────────── --}}
<div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 32px; flex-wrap: wrap; gap: 16px;">
    <div>
        <h1 class="font-display" style="font-size: 28px; font-weight: 800; letter-spacing: -0.03em; margin-bottom: 4px; color: var(--text);">
            Dashboard
        </h1>
        <div style="font-size: 13px; color: var(--muted); font-weight: 500;">
            {{ now()->locale('es')->isoFormat('MMMM YYYY') }}
        </div>
    </div>

    <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
        <form method="GET">
            <input type="month" name="month" value="{{ $month }}"
                   class="form-input" style="width: auto; padding: 8px 13px; font-size: 13px;"
                   onchange="this.form.submit()">
        </form>

        <a href="{{ route('transactions.create') }}" class="btn btn-primary">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
            Nuevo movimiento
        </a>
    </div>
</div>

{{-- ── Stats Cards ──────────────────────────────────────────────────────────── --}}
<div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 28px;">

    <div class="stat-card income">
        <div style="font-size: 11px; letter-spacing: 0.09em; text-transform: uppercase; color: var(--muted); margin-bottom: 12px; font-weight: 700;">Ingresos</div>
        <div class="font-display" style="font-size: 28px; font-weight: 800; color: var(--income); letter-spacing: -0.03em; line-height: 1;">
            $ {{ number_format($totalIncome, 2, ',', '.') }}
        </div>
        <div style="font-size: 12px; color: var(--muted); margin-top: 8px; font-weight: 500;">ARS · {{ now()->locale('es')->isoFormat('MMMM') }}</div>
    </div>

    <div class="stat-card expense">
        <div style="font-size: 11px; letter-spacing: 0.09em; text-transform: uppercase; color: var(--muted); margin-bottom: 12px; font-weight: 700;">Gastos</div>
        <div class="font-display" style="font-size: 28px; font-weight: 800; color: var(--expense); letter-spacing: -0.03em; line-height: 1;">
            $ {{ number_format($totalExpense, 2, ',', '.') }}
        </div>
        <div style="font-size: 12px; color: var(--muted); margin-top: 8px; font-weight: 500;">ARS · {{ now()->locale('es')->isoFormat('MMMM') }}</div>
    </div>

    <div class="stat-card balance">
        <div style="font-size: 11px; letter-spacing: 0.09em; text-transform: uppercase; color: var(--muted); margin-bottom: 12px; font-weight: 700;">Balance</div>
        <div class="font-display" style="font-size: 28px; font-weight: 800; letter-spacing: -0.03em; line-height: 1; color: {{ $balance >= 0 ? 'var(--income)' : 'var(--expense)' }};">
            {{ $balance >= 0 ? '+' : '' }}$ {{ number_format($balance, 2, ',', '.') }}
        </div>
        <div style="font-size: 12px; color: var(--muted); margin-top: 8px; font-weight: 500;">Ingresos − Gastos</div>
    </div>
</div>

{{-- ── Fila principal: Categorías + Cuotas ────────────────────────────────── --}}
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px;">

    {{-- Gastos por categoría --}}
    <div class="card">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 22px;">
            <h2 class="font-display" style="font-size: 15px; font-weight: 700; letter-spacing: -0.01em;">Gastos por categoría</h2>
            <span style="font-size: 12px; color: var(--muted); font-weight: 500;">{{ now()->locale('es')->isoFormat('MMM YYYY') }}</span>
        </div>

        @if($expensesByCategory->isEmpty())
            <div style="text-align: center; padding: 32px 0; color: var(--muted); font-size: 13px;">
                Sin gastos registrados este mes
            </div>
        @else
            @php $maxVal = $expensesByCategory->max(); @endphp
            @foreach($expensesByCategory as $cat => $amount)
            <div style="margin-bottom: 14px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                    <span style="font-size: 12px; color: var(--text);">{{ $cat }}</span>
                    <span style="font-size: 12px; color: var(--muted);">$ {{ number_format($amount, 0, ',', '.') }}</span>
                </div>
                <div style="height: 5px; background: var(--surface2); border-radius: 3px; overflow: hidden;">
                    <div style="height: 100%; width: {{ $maxVal > 0 ? round(($amount / $maxVal) * 100) : 0 }}%; background: linear-gradient(90deg, var(--expense), rgba(240,64,96,0.6)); border-radius: 3px; transition: width 0.5s ease;"></div>
                </div>
            </div>
            @endforeach
        @endif
    </div>

    {{-- Cuotas de crédito --}}
    <div class="card">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 22px;">
            <h2 class="font-display" style="font-size: 15px; font-weight: 700; letter-spacing: -0.01em;">Cuotas de tarjetas</h2>
            <span style="font-size: 12px; color: var(--muted); font-weight: 500;">{{ now()->locale('es')->isoFormat('MMM YYYY') }}</span>
        </div>

        @if($installmentSummary->isEmpty())
            <div style="text-align: center; padding: 32px 0; color: var(--muted); font-size: 13px;">
                Sin cuotas pendientes este mes
            </div>
        @else
            @foreach($installmentSummary as $item)
            <div style="background: var(--surface2); border-radius: 8px; padding: 14px 16px; margin-bottom: 12px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                    <span class="font-display" style="font-size: 13px; font-weight: 600;">{{ $item['account']->name }}</span>
                    <span style="font-size: 15px; font-weight: 600; color: var(--warn);">
                        $ {{ number_format($item['total'], 2, ',', '.') }}
                    </span>
                </div>
                @foreach($item['installments'] as $inst)
                <div style="display: flex; justify-content: space-between; font-size: 11px; color: var(--muted); padding: 3px 0; border-top: 1px solid var(--border);">
                    <span>{{ $inst->transaction->description ?? 'Sin descripción' }}</span>
                    <span>{{ $inst->installment_number }}/{{ $inst->transaction->installments_count }} — $ {{ number_format($inst->amount, 2, ',', '.') }}</span>
                </div>
                @endforeach
            </div>
            @endforeach
        @endif
    </div>
</div>

{{-- ── Tipo de cambio --}}
@if($exchangeRate)
<div style="background: var(--surface); border: 1px solid var(--border); border-radius: 8px; padding: 12px 20px; display: flex; align-items: center; gap: 16px; margin-bottom: 24px; font-size: 12px;">
    <span style="color: var(--muted);">TIPO DE CAMBIO VIGENTE</span>
    <span style="color: var(--warn); font-weight: 600;">1 USD = $ {{ number_format($exchangeRate->rate, 2, ',', '.') }} ARS</span>
    <span style="color: var(--muted);">· actualizado {{ $exchangeRate->date->locale('es')->diffForHumans() }}</span>
    <a href="{{ route('exchange-rates.index') }}" style="margin-left: auto; font-size: 11px; color: var(--accent); text-decoration: none;">Actualizar →</a>
</div>
@else
<div style="background: rgba(255,209,102,0.06); border: 1px solid rgba(255,209,102,0.2); border-radius: 8px; padding: 12px 20px; margin-bottom: 24px; font-size: 12px; color: var(--warn); display: flex; align-items: center; justify-content: space-between;">
    <span>⚠ No hay tipo de cambio configurado. Los totales en ARS/USD no se pueden unificar.</span>
    <a href="{{ route('exchange-rates.index') }}" style="color: var(--warn); font-size: 11px;">Configurar →</a>
</div>
@endif

{{-- ── Débitos fijos del mes ────────────────────────────────────────────── --}}
@if($recurringExpenses->isNotEmpty())
@php
    $today        = now()->day;
    $totalRecurring = $recurringExpenses->sum('amount');
@endphp
<div class="card" style="margin-bottom: 24px;">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
        <div>
            <h2 class="font-display" style="font-size: 15px; font-weight: 700; letter-spacing: -0.01em;">Débitos fijos del mes</h2>
            <div style="font-size: 12px; color: var(--muted); margin-top: 2px; font-weight: 500;">
                Total: <span style="color: var(--expense); font-weight: 700;">$ {{ number_format($totalRecurring, 2, ',', '.') }}</span> ARS
            </div>
        </div>
        <a href="{{ route('recurring-expenses.index') }}" style="font-size: 13px; color: var(--accent); text-decoration: none; font-weight: 600;">Gestionar →</a>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 10px;">
        @foreach($recurringExpenses as $re)
        @php
            $diff      = $re->day_of_month - $today;
            $isToday   = $diff === 0;
            $isPast    = $diff < 0;
            $isUpcoming = $diff > 0 && $diff <= 7;
        @endphp
        <div style="
            background: var(--surface2);
            border: 1px solid {{ $isToday ? 'rgba(240,160,48,0.5)' : ($isPast ? 'var(--border)' : 'var(--border)') }};
            border-radius: 11px;
            padding: 14px 16px;
            position: relative;
            overflow: hidden;
            {{ $isPast ? 'opacity: 0.6;' : '' }}
        ">
            {{-- Indicador de día --}}
            <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 10px;">
                <div style="
                    background: {{ $isToday ? 'var(--accent)' : ($isPast ? 'var(--surface3)' : 'var(--surface3)') }};
                    border-radius: 7px;
                    padding: 4px 9px;
                    display: inline-flex;
                    align-items: center;
                    gap: 4px;
                ">
                    <span class="font-display" style="font-size: 13px; font-weight: 800; color: {{ $isToday ? '#0c0804' : ($isPast ? 'var(--muted)' : 'var(--text)') }};">
                        día {{ $re->day_of_month }}
                    </span>
                </div>

                @if($isToday)
                    <span class="badge" style="background:rgba(240,160,48,0.15); color:var(--accent); font-size:10px;">HOY</span>
                @elseif($isPast)
                    <span class="badge" style="background:var(--surface3); color:var(--muted); font-size:10px;">PASADO</span>
                @elseif($isUpcoming)
                    <span class="badge badge-transfer" style="font-size:10px;">{{ $diff }}d</span>
                @endif
            </div>

            {{-- Nombre y monto --}}
            <div style="font-size: 13px; font-weight: 700; color: var(--text); margin-bottom: 3px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                {{ $re->description }}
            </div>
            <div style="font-size: 15px; font-weight: 800; color: var(--expense); font-family: 'Bricolage Grotesque', sans-serif; letter-spacing: -0.02em;">
                {{ $re->currency === 'USD' ? 'US$' : '$' }} {{ number_format($re->amount, 2, ',', '.') }}
            </div>

            {{-- Cuenta --}}
            <div style="margin-top: 8px; padding-top: 8px; border-top: 1px solid var(--border);">
                <span class="badge badge-{{ $re->account->type }}" style="font-size: 10px;">{{ $re->account->name }}</span>
                @if($re->category)
                    <span style="font-size: 11px; color: var(--muted); margin-left: 4px;">· {{ $re->category->name }}</span>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- ── Últimos movimientos ───────────────────────────────────────────────── --}}
<div class="card">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 22px;">
        <h2 class="font-display" style="font-size: 15px; font-weight: 700; letter-spacing: -0.01em;">Últimos movimientos</h2>
        <a href="{{ route('transactions.index') }}" style="font-size: 13px; color: var(--accent); text-decoration: none; font-weight: 600;">Ver todos →</a>
    </div>

    @if($recentTransactions->isEmpty())
        <div style="text-align: center; padding: 40px 0; color: var(--muted); font-size: 13px;">
            No hay movimientos registrados aún.
            <br><br>
            <a href="{{ route('transactions.create') }}" class="btn btn-primary" style="display: inline-flex;">
                Cargar el primero
            </a>
        </div>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Descripción</th>
                    <th>Categoría</th>
                    <th>Cuenta</th>
                    <th>Quién</th>
                    <th style="text-align:right;">Monto</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recentTransactions as $tx)
                <tr>
                    <td style="color: var(--muted); font-size: 12px; white-space: nowrap;">
                        {{ $tx->date->format('d/m/Y') }}
                    </td>
                    <td>
                        <a href="{{ route('transactions.show', $tx) }}" style="color: var(--text); text-decoration: none; font-size: 13px;">
                            {{ $tx->description ?? '—' }}
                        </a>
                        @if($tx->has_installments)
                            <span class="badge badge-credit" style="margin-left: 6px;">{{ $tx->installments_count }}c</span>
                        @endif
                    </td>
                    <td>
                        @if($tx->category)
                            <span style="font-size: 12px; color: var(--muted);">{{ $tx->category->name }}</span>
                        @else
                            <span style="color: var(--border);">—</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge badge-{{ $tx->account->type }}">{{ $tx->account->name }}</span>
                    </td>
                    <td style="font-size: 12px; color: var(--muted);">{{ $tx->user->name }}</td>
                    <td style="text-align: right; font-weight: 500; white-space: nowrap;"
                        class="{{ $tx->isIncome() ? 'amount-income' : ($tx->isExpense() ? 'amount-expense' : 'amount-neutral') }}">
                        {{ $tx->isIncome() ? '+' : ($tx->isExpense() ? '-' : '') }}
                        {{ $tx->currency === 'USD' ? 'US$' : '$' }} {{ number_format($tx->amount, 2, ',', '.') }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

@endsection
