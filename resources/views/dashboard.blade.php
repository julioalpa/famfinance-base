@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

<style>
@media (max-width: 768px) {
    /* Ocultar columnas no esenciales en tabla de movimientos */
    .dash-col-hide-md { display: none !important; }
}

@media (max-width: 640px) {
    .dash-grid-3 { grid-template-columns: 1fr !important; }
    .dash-grid-2 { grid-template-columns: 1fr !important; }
    .dash-header { flex-direction: column !important; align-items: flex-start !important; }
    .dash-header-right { width: 100%; }
    .dash-header-right form,
    .dash-header-right .btn { width: 100%; justify-content: center; }
    .dash-header-right .form-input { width: 100% !important; }

    /* Exchange pill: link va abajo, full width */
    .exchange-pill { flex-wrap: wrap; gap: 6px !important; }
    .exchange-pill .exchange-pill-link {
        margin-left: 0 !important;
        width: 100%;
        padding-top: 8px;
        margin-top: 4px;
        border-top: 1px solid var(--border);
    }

    /* Promo alert: touch targets cómodos */
    .promo-actions .btn {
        min-height: 40px;
        padding: 9px 14px !important;
        font-size: 13px !important;
    }
    .promo-actions form { flex: 1; }
    .promo-actions form .btn { width: 100%; justify-content: center; }
}

@media (max-width: 480px) {
    /* Stats: reducir tamaño de números grandes */
    .dash-grid-3 .stat-card .font-display { font-size: 22px !important; }
    .dash-header h1 { font-size: 22px !important; }

    /* Badge decorativo de patrimonio se oculta */
    .dash-badge-decorative { display: none !important; }
}

/* ── Delta pills (comparativas en tarjetas del mes) ─────────────────────── */
.dash-deltas {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-top: 10px;
}
.dash-delta {
    display: inline-flex;
    align-items: center;
    gap: 3px;
    padding: 2px 8px;
    border-radius: 20px;
    font-size: 10px;
    font-weight: 800;
    line-height: 1.4;
    white-space: nowrap;
}
.dash-delta-good { background: rgba(45,216,112,0.12); color: var(--income); }
.dash-delta-bad  { background: rgba(240,64,96,0.12);  color: var(--expense); }
.dash-delta-neu  { background: rgba(78,155,255,0.12); color: var(--accent2); }
.dash-delta-sub  { font-size: 11px; color: var(--muted); font-weight: 600; }
</style>

@php
    // Helper de presentación para las comparativas del mes.
    // $goodWhenUp: true para ingresos (subir es bueno), false para gastos.
    $deltaClass = function (?float $pct, bool $goodWhenUp) {
        if ($pct === null) return 'dash-delta-neu';
        if ($goodWhenUp) {
            return $pct >= 0 ? 'dash-delta-good' : 'dash-delta-bad';
        }
        // Gastos: subir es malo, bajar es bueno; variaciones chicas → neutro.
        if ($pct > 5)  return 'dash-delta-bad';
        if ($pct < -5) return 'dash-delta-good';
        return 'dash-delta-neu';
    };
@endphp

{{-- ── Header ──────────────────────────────────────────────────────────────── --}}
<div class="dash-header" style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 32px; flex-wrap: wrap; gap: 16px;">
    <div>
        <h1 class="font-display" style="font-size: 28px; font-weight: 800; letter-spacing: -0.03em; margin-bottom: 4px; color: var(--text);">
            Dashboard
        </h1>
        <div style="font-size: 13px; color: var(--muted); font-weight: 500;">
            {{ now()->locale('es')->isoFormat('MMMM YYYY') }}
        </div>
    </div>

    <div class="dash-header-right" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
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

{{-- ── Alertas de promociones ───────────────────────────────────────────────── --}}
@if($promotionAlerts->isNotEmpty())
<div style="margin-bottom: 24px; display: flex; flex-direction: column; gap: 10px;">
    @foreach($promotionAlerts as $promo)
        @php $days = $promo->daysUntilExpiry(); @endphp
        <div style="background: rgba(232,184,64,0.08); border: 1px solid rgba(232,184,64,0.35); border-radius: 12px; padding: 14px 18px; display: flex; align-items: center; gap: 14px; flex-wrap: wrap;">
            <svg width="18" height="18" fill="none" stroke="var(--warn)" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink: 0;">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
            <div style="flex: 1; min-width: 180px;">
                <span style="font-size: 14px; font-weight: 700; color: var(--warn);">Promoción por vencer:</span>
                <span style="font-size: 14px; color: var(--text); margin-left: 4px;">{{ $promo->name }}</span>
                <div style="font-size: 12px; color: var(--muted); margin-top: 2px;">
                    {{ $days === 0 ? 'Vence hoy' : "Vence en {$days} " . ($days === 1 ? 'día' : 'días') }}
                    ({{ $promo->expires_at->format('d/m/Y') }}) — {{ $promo->discountLabel() }} de descuento
                    @if($promo->provider) · {{ $promo->provider }} @endif
                </div>
            </div>
            <div class="promo-actions" style="display: flex; gap: 8px; flex-shrink: 0; flex-wrap: wrap;">
                <a href="{{ route('promotions.index') }}" class="btn btn-ghost" style="padding: 6px 14px; font-size: 12px;">
                    Ver promociones
                </a>
                <form method="POST" action="{{ route('promotions.dismiss-alert', $promo) }}">
                    @csrf
                    <button type="submit" class="btn btn-ghost" style="padding: 6px 14px; font-size: 12px; border-color: rgba(232,184,64,0.3); color: var(--warn);">
                        Marcar como leído
                    </button>
                </form>
            </div>
        </div>
    @endforeach
</div>
@endif

{{-- ── Alertas de presupuesto ───────────────────────────────────────────────── --}}
@if($budgetAlerts->isNotEmpty())
<div style="margin-bottom: 24px; display: flex; flex-direction: column; gap: 10px;">
    @foreach($budgetAlerts as $alert)
        @php
            $over = $alert['status'] === 'over';
            $col  = $over ? 'var(--expense)' : 'var(--warn)';
            $bg   = $over ? 'rgba(255,69,58,0.08)' : 'rgba(255,214,10,0.08)';
            $bd   = $over ? 'rgba(255,69,58,0.35)' : 'rgba(255,214,10,0.35)';
            $cat  = $alert['category'];
        @endphp
        <div style="background: {{ $bg }}; border: 1px solid {{ $bd }}; border-radius: 12px; padding: 14px 18px; display: flex; align-items: center; gap: 14px; flex-wrap: wrap;">
            <svg width="18" height="18" fill="none" stroke="{{ $col }}" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink: 0;">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
            <div style="flex: 1; min-width: 180px;">
                <span style="font-size: 14px; font-weight: 700; color: {{ $col }};">
                    {{ $over ? 'Presupuesto excedido:' : 'Cerca del límite:' }}
                </span>
                <span style="font-size: 14px; color: var(--text); margin-left: 4px;">{{ $cat->name }}</span>
                @if($alert['is_annual'])
                    <span style="font-size: 10px; font-weight: 700; padding: 1px 6px; border-radius: 10px; background: rgba(100,210,255,0.14); color: var(--accent2); margin-left: 4px;">ANUAL</span>
                @endif
                <div style="font-size: 12px; color: var(--muted); margin-top: 2px;">
                    Gastado $ {{ number_format($alert['spent'], 0, ',', '.') }} de $ {{ number_format($alert['limit'], 0, ',', '.') }}
                    ({{ $alert['pct'] }}%){{ $alert['is_annual'] ? ' este año' : ' este mes' }}
                </div>
            </div>
            <a href="{{ route('budgets.index') }}" class="btn btn-ghost" style="padding: 6px 14px; font-size: 12px; flex-shrink: 0;">Ver presupuestos</a>
        </div>
    @endforeach
</div>
@endif

{{-- ── Stats Cards ──────────────────────────────────────────────────────────── --}}
<div style="font-size: 11px; letter-spacing: 0.1em; text-transform: uppercase; color: var(--muted); font-weight: 700; margin-bottom: 10px;">
    Movimientos del mes
</div>
<div class="dash-grid-3" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 28px;">

    <div class="stat-card income">
        <div style="font-size: 11px; letter-spacing: 0.09em; text-transform: uppercase; color: var(--muted); margin-bottom: 12px; font-weight: 700;">Ingresos</div>
        <div class="font-display" style="font-size: 28px; font-weight: 800; color: var(--income); letter-spacing: -0.03em; line-height: 1;">
            $ {{ number_format($totalIncome, 2, ',', '.') }}
        </div>
        <div class="dash-deltas">
            @if($incomeVsPrev !== null)
                <span class="dash-delta {{ $deltaClass($incomeVsPrev, true) }}" title="vs mes anterior">
                    {{ $incomeVsPrev >= 0 ? '▲' : '▼' }} {{ abs($incomeVsPrev) }}% vs mes ant.
                </span>
            @endif
            @if($incomeVsAvg !== null)
                <span class="dash-delta {{ $deltaClass($incomeVsAvg, true) }}" title="vs promedio 3 meses">
                    {{ $incomeVsAvg >= 0 ? '▲' : '▼' }} {{ abs($incomeVsAvg) }}% vs prom.
                </span>
            @endif
        </div>
        @if($avgIncome > 0)
            <div class="dash-delta-sub" style="margin-top: 6px;">Prom. 3m: $ {{ number_format($avgIncome, 0, ',', '.') }}</div>
        @endif
    </div>

    <div class="stat-card expense">
        <div style="font-size: 11px; letter-spacing: 0.09em; text-transform: uppercase; color: var(--muted); margin-bottom: 12px; font-weight: 700;">Gastos</div>
        <div class="font-display" style="font-size: 28px; font-weight: 800; color: var(--expense); letter-spacing: -0.03em; line-height: 1;">
            $ {{ number_format($totalExpense, 2, ',', '.') }}
        </div>
        <div class="dash-deltas">
            @if($expenseVsPrev !== null)
                <span class="dash-delta {{ $deltaClass($expenseVsPrev, false) }}" title="vs mes anterior">
                    {{ $expenseVsPrev >= 0 ? '▲' : '▼' }} {{ abs($expenseVsPrev) }}% vs mes ant.
                </span>
            @endif
            @if($expenseVsAvg !== null)
                <span class="dash-delta {{ $deltaClass($expenseVsAvg, false) }}" title="vs promedio 3 meses">
                    {{ $expenseVsAvg >= 0 ? '▲' : '▼' }} {{ abs($expenseVsAvg) }}% vs prom.
                </span>
            @endif
        </div>
        @if($avgExpense > 0)
            <div class="dash-delta-sub" style="margin-top: 6px;">Prom. 3m: $ {{ number_format($avgExpense, 0, ',', '.') }}</div>
        @endif
    </div>

    <div class="stat-card balance">
        <div style="font-size: 11px; letter-spacing: 0.09em; text-transform: uppercase; color: var(--muted); margin-bottom: 12px; font-weight: 700;">Balance del mes</div>
        <div class="font-display" style="font-size: 28px; font-weight: 800; letter-spacing: -0.03em; line-height: 1; color: {{ $balance >= 0 ? 'var(--income)' : 'var(--expense)' }};">
            {{ $balance >= 0 ? '+' : '' }}$ {{ number_format($balance, 2, ',', '.') }}
        </div>
        <div class="dash-deltas">
            @php $balanceDelta = $balance - $prevBalance; @endphp
            @if($prevBalance != 0 || $balance != 0)
                <span class="dash-delta {{ $balanceDelta >= 0 ? 'dash-delta-good' : 'dash-delta-bad' }}" title="variación vs mes anterior">
                    {{ $balanceDelta >= 0 ? '▲' : '▼' }} {{ $balanceDelta >= 0 ? '+' : '−' }}$ {{ number_format(abs($balanceDelta), 0, ',', '.') }} vs mes ant.
                </span>
            @endif
        </div>
        <div class="dash-delta-sub" style="margin-top: 6px;">
            Mes anterior: {{ $prevBalance >= 0 ? '+' : '' }}$ {{ number_format($prevBalance, 0, ',', '.') }}
        </div>
    </div>
</div>

{{-- ── Patrimonio neto ──────────────────────────────────────────────────────── --}}
<div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px; flex-wrap: wrap;">
    <div style="font-size: 11px; letter-spacing: 0.1em; text-transform: uppercase; color: var(--muted); font-weight: 700;">
        Situación financiera general
    </div>
    <div class="dash-badge-decorative" style="font-size: 11px; color: var(--muted); background: var(--surface2); border: 1px solid var(--border); border-radius: 5px; padding: 2px 8px;">
        Total acumulado · todas las cuentas
    </div>
</div>
<div class="dash-grid-3" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 28px;">

    <div class="stat-card income">
        <div style="font-size: 11px; letter-spacing: 0.09em; text-transform: uppercase; color: var(--muted); margin-bottom: 6px; font-weight: 700;">Dinero disponible</div>
        <div style="font-size: 11px; color: var(--muted); margin-bottom: 10px;">Efectivo + cuentas digitales</div>
        <div class="font-display" style="font-size: 26px; font-weight: 800; color: var(--income); letter-spacing: -0.03em; line-height: 1;">
            $ {{ number_format($totalAssets, 2, ',', '.') }}
        </div>
    </div>

    <div class="stat-card expense">
        <div style="font-size: 11px; letter-spacing: 0.09em; text-transform: uppercase; color: var(--muted); margin-bottom: 6px; font-weight: 700;">Deudas totales</div>
        <div style="font-size: 11px; color: var(--muted); margin-bottom: 10px;">Tarjetas de crédito + préstamos</div>
        <div class="font-display" style="font-size: 26px; font-weight: 800; color: var(--expense); letter-spacing: -0.03em; line-height: 1;">
            $ {{ number_format($totalLiabilities, 2, ',', '.') }}
        </div>
    </div>

    <div class="stat-card {{ $netWorth >= 0 ? 'balance' : 'expense' }}">
        <div style="font-size: 11px; letter-spacing: 0.09em; text-transform: uppercase; color: var(--muted); margin-bottom: 6px; font-weight: 700;">Lo que tenés en total</div>
        <div style="font-size: 11px; color: var(--muted); margin-bottom: 10px;">Dinero disponible − deudas</div>
        <div class="font-display" style="font-size: 26px; font-weight: 800; letter-spacing: -0.03em; line-height: 1; color: {{ $netWorth >= 0 ? 'var(--income)' : 'var(--expense)' }};">
            {{ $netWorth >= 0 ? '+' : '' }}$ {{ number_format($netWorth, 2, ',', '.') }}
        </div>
    </div>
</div>

{{-- ── Fila principal: Categorías + Cuotas ────────────────────────────────── --}}
<div class="dash-grid-2" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px;">

    {{-- Gastos por categoría --}}
    <div class="card">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 22px;">
            <h2 class="font-display" style="font-size: 15px; font-weight: 700; letter-spacing: -0.01em;">Gastos por categoría</h2>
            <span style="font-size: 12px; color: var(--muted); font-weight: 500;">{{ now()->locale('es')->isoFormat('MMM YYYY') }}</span>
        </div>

        @if($expensesByCategoryRaw->isEmpty())
            <div style="text-align: center; padding: 32px 0; color: var(--muted); font-size: 13px;">
                Sin gastos registrados este mes
            </div>
        @else
            @php $maxVal = $expensesByCategoryRaw->max('amount'); @endphp
            @foreach($expensesByCategoryRaw as $row)
            @php $cat = $row['category']; $amount = $row['amount']; @endphp
            <div style="margin-bottom: 14px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                    <div style="display:flex;align-items:center;gap:6px;">
                        @include('categories._icon', ['icon' => $cat?->icon, 'color' => $cat?->color, 'type' => $cat?->type ?? 'expense', 'size' => 'xs'])
                        <span style="font-size: 12px; color: var(--text);">{{ $cat?->name ?? 'Sin categoría' }}</span>
                    </div>
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
                <div style="display: flex; justify-content: space-between; gap: 8px; font-size: 11px; color: var(--muted); padding: 3px 0; border-top: 1px solid var(--border);">
                    <span style="min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $inst->transaction->description ?? 'Sin descripción' }}</span>
                    <span style="flex-shrink: 0; white-space: nowrap;">{{ $inst->installment_number }}/{{ $inst->transaction->installments_count }} — $ {{ number_format($inst->amount, 2, ',', '.') }}</span>
                </div>
                @endforeach
            </div>
            @endforeach
        @endif
    </div>
</div>

{{-- ── Presupuestos del mes ─────────────────────────────────────────────── --}}
@if($budgetProgress->isNotEmpty())
<div class="card" style="margin-bottom: 24px;">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px; flex-wrap: wrap; gap: 8px;">
        <div>
            <h2 class="font-display" style="font-size: 15px; font-weight: 700; letter-spacing: -0.01em;">Presupuestos del mes</h2>
            <div style="font-size: 12px; color: var(--muted); margin-top: 2px; font-weight: 500;">
                @if($budgetOverCount > 0)
                    <span style="color: var(--expense); font-weight: 700;">{{ $budgetOverCount }} excedido{{ $budgetOverCount === 1 ? '' : 's' }}</span>
                @elseif($budgetWarnCount > 0)
                    <span style="color: var(--warn); font-weight: 700;">{{ $budgetWarnCount }} cerca del límite</span>
                @else
                    <span style="color: var(--income); font-weight: 700;">Todo en orden</span>
                @endif
            </div>
        </div>
        <a href="{{ route('budgets.index') }}" style="font-size: 13px; color: var(--accent); text-decoration: none; font-weight: 600;">Gestionar →</a>
    </div>

    @foreach($budgetProgress as $bp)
    @php
        $fill = $bp['status'] === 'over' ? 'var(--expense)' : ($bp['status'] === 'warn' ? 'var(--warn)' : 'var(--income)');
        $width = $bp['pct'] === null ? 0 : min($bp['pct'], 100);
    @endphp
    <div style="margin-bottom: 14px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px; gap: 8px;">
            <div style="display: flex; align-items: center; gap: 6px; min-width: 0;">
                @include('categories._icon', ['icon' => $bp['category']->icon, 'color' => $bp['category']->color, 'type' => $bp['category']->type ?? 'expense', 'size' => 'xs'])
                <span style="font-size: 13px; font-weight: 600; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $bp['category']->name }}</span>
                @if($bp['is_annual'])
                    <span style="font-size: 9px; font-weight: 700; padding: 1px 6px; border-radius: 10px; background: rgba(100,210,255,0.14); color: var(--accent2); white-space: nowrap;">ANUAL</span>
                @endif
            </div>
            <span style="font-size: 12px; color: var(--muted); white-space: nowrap;">
                $ {{ number_format($bp['spent'], 0, ',', '.') }} / $ {{ number_format($bp['limit'], 0, ',', '.') }}
                @if($bp['pct'] !== null)
                    <strong style="color: {{ $fill }};">· {{ $bp['pct'] }}%</strong>
                @endif
            </span>
        </div>
        <div style="height: 7px; background: var(--surface2); border-radius: 4px; overflow: hidden;">
            <div style="height: 100%; width: {{ $width }}%; background: {{ $fill }}; border-radius: 4px; transition: width 0.3s ease;"></div>
        </div>
    </div>
    @endforeach
</div>
@endif

{{-- ── Tipo de cambio --}}
@if($exchangeRate)
<div class="exchange-pill" style="background: var(--surface); border: 1px solid var(--border); border-radius: 8px; padding: 12px 20px; display: flex; align-items: center; gap: 16px; margin-bottom: 24px; font-size: 12px;">
    <span style="color: var(--muted);">TIPO DE CAMBIO VIGENTE</span>
    <span style="color: var(--warn); font-weight: 600;">1 USD = $ {{ number_format($exchangeRate->rate, 2, ',', '.') }} ARS</span>
    <span style="color: var(--muted);">· actualizado {{ $exchangeRate->date->locale('es')->diffForHumans() }}</span>
    <a href="{{ route('exchange-rates.index') }}" class="exchange-pill-link" style="margin-left: auto; font-size: 11px; color: var(--accent); text-decoration: none;">Actualizar →</a>
</div>
@else
<div style="background: rgba(255,209,102,0.06); border: 1px solid rgba(255,209,102,0.2); border-radius: 8px; padding: 12px 20px; margin-bottom: 24px; font-size: 12px; color: var(--warn); display: flex; align-items: center; justify-content: space-between;">
    <span>⚠ No hay tipo de cambio configurado. Los totales en ARS/USD no se pueden unificar.</span>
    <a href="{{ route('exchange-rates.index') }}" style="color: var(--warn); font-size: 11px;">Configurar →</a>
</div>
@endif

{{-- ── Pendientes del mes ───────────────────────────────────────────────── --}}
@if($pendingTotalCount > 0)
@php
    $pendingPct = $pendingTotalCount > 0 ? round(($pendingPaidCount / $pendingTotalCount) * 100) : 0;
@endphp
<div class="card" style="margin-bottom: 24px;">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px;">
        <div>
            <h2 class="font-display" style="font-size: 15px; font-weight: 700; letter-spacing: -0.01em;">Pagos del mes</h2>
            <div style="font-size: 12px; color: var(--muted); margin-top: 2px; font-weight: 500;">
                <span style="color: {{ $pendingPaidCount === $pendingTotalCount ? 'var(--income)' : 'var(--text)' }}; font-weight: 700;">{{ $pendingPaidCount }}/{{ $pendingTotalCount }}</span> pagados
            </div>
        </div>
        <a href="{{ route('monthly-payments.index') }}" style="font-size: 13px; color: var(--accent); text-decoration: none; font-weight: 600;">Ver todos →</a>
    </div>

    <div style="height: 4px; background: var(--surface2); border-radius: 2px; overflow: hidden; margin-bottom: 16px;">
        <div style="height: 100%; width: {{ $pendingPct }}%; background: {{ $pendingPct === 100 ? 'var(--income)' : 'linear-gradient(90deg, var(--accent), #f5c842)' }}; border-radius: 2px; transition: width 0.6s ease;"></div>
    </div>

    <div style="display: flex; flex-direction: column; gap: 8px;">
        @foreach($pendingPayments->take(6) as $mp)
        @php
            $pItem    = $mp->paymentItem;
            $isPaid   = $mp->is_paid;
            $dueDay   = $pItem?->day_of_month;
            $isOverdue = !$isPaid && $dueDay && $dueDay < now()->day;
        @endphp
        <div style="display: flex; align-items: center; gap: 12px; padding: 10px 12px; background: var(--surface2); border-radius: 9px; border: 1px solid {{ $isOverdue ? 'rgba(240,64,96,0.2)' : 'transparent' }};">
            <div style="
                width: 20px; height: 20px; border-radius: 6px; flex-shrink: 0;
                background: {{ $isPaid ? 'rgba(45,216,112,0.15)' : 'transparent' }};
                border: 2px solid {{ $isPaid ? 'var(--income)' : 'var(--border)' }};
                display: flex; align-items: center; justify-content: center;
            ">
                @if($isPaid)
                    <svg width="10" height="10" fill="none" stroke="var(--income)" stroke-width="3" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                @endif
            </div>
            <span style="flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 13px; font-weight: 600; color: {{ $isPaid ? 'var(--muted)' : 'var(--text)' }}; {{ $isPaid ? 'text-decoration: line-through;' : '' }}">
                {{ $pItem?->description }}
            </span>
            @if($dueDay && !$isPaid)
                <span style="font-size: 11px; color: {{ $isOverdue ? 'var(--expense)' : 'var(--muted)' }}; font-weight: {{ $isOverdue ? '700' : '500' }};">día {{ $dueDay }}</span>
            @endif
            @if($isPaid && $mp->amount)
                <span style="font-size: 12px; color: var(--income); font-weight: 700;">
                    {{ $pItem?->currency === 'USD' ? 'US$' : '$' }} {{ number_format($mp->amount, 0, ',', '.') }}
                </span>
            @endif
        </div>
        @endforeach

        @if($pendingPayments->count() > 6)
        <div style="text-align: center; padding: 4px 0;">
            <a href="{{ route('monthly-payments.index') }}" style="font-size: 12px; color: var(--muted); text-decoration: none;">
                + {{ $pendingPayments->count() - 6 }} más →
            </a>
        </div>
        @endif
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
        <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Descripción</th>
                    <th>Categoría</th>
                    <th class="dash-col-hide-md">Cuenta</th>
                    <th class="dash-col-hide-md">Quién</th>
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
                        <div style="display:flex;align-items:center;gap:5px;">
                            @include('categories._icon', ['icon' => $tx->category->icon, 'color' => $tx->category->color, 'type' => $tx->category->type, 'size' => 'xs'])
                            <span style="font-size: 12px; color: var(--muted);">{{ $tx->category->name }}</span>
                        </div>
                        @else
                            <span style="color: var(--border);">—</span>
                        @endif
                    </td>
                    <td class="dash-col-hide-md">
                        <span class="badge badge-{{ $tx->account->type }}">{{ $tx->account->name }}</span>
                    </td>
                    <td class="dash-col-hide-md" style="font-size: 12px; color: var(--muted);">{{ $tx->user->name }}</td>
                    <td style="text-align: right; font-weight: 500; white-space: nowrap;">
                        @if($tx->isAdjustment())
                            <span style="color:#a078ff;">
                                {{ $tx->adjustment_direction === 'in' ? '+' : '−' }}
                                {{ $tx->currency === 'USD' ? 'US$' : '$' }} {{ number_format($tx->amount, 2, ',', '.') }}
                            </span>
                        @else
                            <span class="{{ $tx->isIncome() ? 'amount-income' : ($tx->isExpense() ? 'amount-expense' : 'amount-neutral') }}">
                                {{ $tx->isIncome() ? '+' : ($tx->isExpense() ? '−' : '') }}
                                {{ $tx->currency === 'USD' ? 'US$' : '$' }} {{ number_format($tx->amount, 2, ',', '.') }}
                            </span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>{{-- /.table-wrap --}}
    @endif
</div>

@endsection
