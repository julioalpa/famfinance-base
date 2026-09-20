@extends('layouts.app')

@section('title', 'Presupuestos')

@section('content')

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>

<style>
    .bdg-bar-track {
        height: 8px;
        background: var(--surface3, var(--surface2));
        border-radius: 5px;
        overflow: hidden;
    }
    .bdg-bar-fill {
        height: 100%;
        border-radius: 5px;
        transition: width 0.3s ease;
    }
    .bdg-fill-ok   { background: var(--income); }
    .bdg-fill-warn { background: var(--warn, #e8b840); }
    .bdg-fill-over { background: var(--expense); }

    .bdg-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 2px 9px;
        border-radius: 20px;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: 0.03em;
        white-space: nowrap;
    }
    .bdg-badge-ok   { background: rgba(45,216,112,0.12); color: var(--income); }
    .bdg-badge-warn { background: rgba(232,184,64,0.14); color: var(--warn, #e8b840); }
    .bdg-badge-over { background: rgba(240,64,96,0.12);  color: var(--expense); }

    .bdg-row {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 16px;
        align-items: center;
        padding: 16px 0;
        border-bottom: 1px solid var(--border);
    }
    .bdg-row:last-child { border-bottom: none; }

    .bdg-inline-form {
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .bdg-inline-form .form-input { width: 120px; padding: 6px 10px; font-size: 13px; }

    @media (max-width: 640px) {
        .bdg-row { grid-template-columns: 1fr; }
        .bdg-inline-form { flex-wrap: wrap; }
    }
</style>

{{-- ── Header ──────────────────────────────────────────────────────────────── --}}
<div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
    <div>
        <h1 class="font-display" style="font-size: 24px; font-weight: 700; letter-spacing: -0.02em;">Presupuestos</h1>
        <div style="font-size: 12px; color: var(--muted); margin-top: 3px;">
            Límite mensual por categoría · {{ ucfirst($date->locale('es')->isoFormat('MMMM YYYY')) }}
        </div>
    </div>
    <form method="GET">
        <input type="month" name="month" value="{{ $month }}"
               class="form-input" style="width: auto; padding: 8px 13px; font-size: 13px;"
               onchange="this.form.submit()">
    </form>
</div>

{{-- ── Resumen global (mensual) ─────────────────────────────────────────────── --}}
@if($monthlyBudgets->isNotEmpty())
@php
    $globalStatus = $totalPct === null ? 'ok' : ($totalPct >= 100 ? 'over' : ($totalPct >= 80 ? 'warn' : 'ok'));
    $globalWidth  = $totalPct === null ? 0 : min($totalPct, 100);
@endphp
<div class="card" style="margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 12px; flex-wrap: wrap; gap: 8px;">
        <div style="font-size: 11px; letter-spacing: 0.1em; text-transform: uppercase; color: var(--muted); font-weight: 700;">
            Total presupuestado del mes
        </div>
        <div style="font-size: 13px; color: var(--muted);">
            <span class="font-display" style="font-size: 20px; font-weight: 800; color: var(--text);">$ {{ number_format($totalSpent, 0, ',', '.') }}</span>
            <span> / $ {{ number_format($totalBudget, 0, ',', '.') }}</span>
            @if($totalPct !== null)
                <span class="bdg-badge bdg-badge-{{ $globalStatus }}" style="margin-left: 8px;">{{ $totalPct }}%</span>
            @endif
        </div>
    </div>
    <div class="bdg-bar-track">
        <div class="bdg-bar-fill bdg-fill-{{ $globalStatus }}" style="width: {{ $globalWidth }}%;"></div>
    </div>
    @if($totalBudget - $totalSpent >= 0)
        <div style="font-size: 12px; color: var(--muted); margin-top: 8px;">Te queda $ {{ number_format($totalBudget - $totalSpent, 0, ',', '.') }} de presupuesto este mes.</div>
    @else
        <div style="font-size: 12px; color: var(--expense); margin-top: 8px; font-weight: 600;">Te excediste $ {{ number_format($totalSpent - $totalBudget, 0, ',', '.') }} sobre el presupuesto.</div>
    @endif
</div>
@endif

{{-- ── Presupuesto vs. real (comparativo) ───────────────────────────────────── --}}
@if($monthlyBudgets->count() > 1)
<div class="card" style="margin-bottom: 24px;">
    <h2 class="font-display" style="font-size: 15px; font-weight: 700; margin-bottom: 4px;">Presupuesto vs. real (mensual)</h2>
    <div style="font-size: 12px; color: var(--muted); margin-bottom: 14px;">Límite mensual (barra clara) contra lo gastado (barra de color) por categoría</div>
    <div style="position: relative; height: {{ min(max($monthlyBudgets->count() * 42, 160), 520) }}px;">
        <canvas id="budgetChart"></canvas>
    </div>
</div>
@endif

{{-- ── Presupuestos mensuales ───────────────────────────────────────────────── --}}
<div class="card" style="margin-bottom: 24px;">
    <h2 class="font-display" style="font-size: 15px; font-weight: 700; margin-bottom: 4px;">Presupuestos mensuales</h2>
    <div style="font-size: 12px; color: var(--muted); margin-bottom: 8px;">Avance del gasto del mes contra el límite mensual</div>

    @if($monthlyBudgets->isEmpty())
        <div style="text-align: center; padding: 24px 0; color: var(--muted); font-size: 13px;">
            Sin presupuestos mensuales. Fijá uno desde la lista de abajo.
        </div>
    @else
        @foreach($monthlyBudgets as $row)
            @include('budgets._row', ['row' => $row])
        @endforeach
    @endif
</div>

{{-- ── Presupuestos anuales ─────────────────────────────────────────────────── --}}
@if($annualBudgets->isNotEmpty())
<div class="card" style="margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: baseline; flex-wrap: wrap; gap: 8px; margin-bottom: 4px;">
        <h2 class="font-display" style="font-size: 15px; font-weight: 700;">Presupuestos anuales · {{ $currentYear }}</h2>
        @if($totalAnnualPct !== null)
            <span style="font-size: 13px; color: var(--muted);">
                $ {{ number_format($totalAnnualSpent, 0, ',', '.') }} / $ {{ number_format($totalAnnualBudget, 0, ',', '.') }}
                <strong style="color: {{ $totalAnnualPct >= 100 ? 'var(--expense)' : ($totalAnnualPct >= 80 ? 'var(--warn)' : 'var(--income)') }};">· {{ $totalAnnualPct }}%</strong>
            </span>
        @endif
    </div>
    <div style="font-size: 12px; color: var(--muted); margin-bottom: 8px;">Gasto acumulado del año contra el límite anual · la línea marca el ritmo esperado a esta altura del año</div>

    @foreach($annualBudgets as $row)
        @include('budgets._row', ['row' => $row])
    @endforeach
</div>
@endif

{{-- ── Categorías sin presupuesto ───────────────────────────────────────────── --}}
@if($withoutBudget->isNotEmpty())
<div class="card">
    <h2 class="font-display" style="font-size: 15px; font-weight: 700; margin-bottom: 4px;">Sin presupuesto</h2>
    <div style="font-size: 12px; color: var(--muted); margin-bottom: 8px;">Definí un límite (mensual o anual) para empezar a controlarlas</div>

    @foreach($withoutBudget as $row)
    @php $cat = $row['category']; @endphp
    <div class="bdg-row">
        <div style="display: flex; align-items: center; gap: 8px; min-width: 0;">
            @include('categories._icon', ['icon' => $cat->icon, 'color' => $cat->color, 'type' => $cat->type ?? 'expense', 'size' => 'xs'])
            <span style="font-size: 14px; font-weight: 600; color: var(--text);">{{ $cat->name }}</span>
            @if($row['spent'] > 0)
                <span style="font-size: 12px; color: var(--muted);">· gastado este mes $ {{ number_format($row['spent'], 0, ',', '.') }}</span>
            @endif
        </div>
        <form method="POST" action="{{ route('budgets.upsert') }}" class="bdg-inline-form">
            @csrf
            <input type="hidden" name="category_id" value="{{ $cat->id }}">
            <input type="number" name="amount" class="form-input" inputmode="decimal"
                   step="0.01" min="0" placeholder="Límite" aria-label="Monto del presupuesto para {{ $cat->name }}">
            <select name="currency" class="form-select" style="width: auto; padding: 6px 26px 6px 8px;">
                <option value="ARS">ARS</option>
                <option value="USD">USD</option>
            </select>
            <select name="period" class="form-select" style="width: auto; padding: 6px 26px 6px 8px;">
                <option value="monthly">Mensual</option>
                <option value="annual">Anual</option>
            </select>
            <button type="submit" class="btn btn-primary" style="padding: 6px 12px; font-size: 12px;">Fijar</button>
        </form>
    </div>
    @endforeach
</div>
@endif

@if($monthlyBudgets->count() > 1)
@php
    $budgetChartData = $monthlyBudgets->map(fn ($r) => [
        'name'   => $r['category']->name,
        'limit'  => $r['limit_ars'],
        'spent'  => $r['spent'],
        'status' => $r['status'],
    ])->values();
@endphp
<script>
(function(){
    const el = document.getElementById('budgetChart');
    if (!el || typeof Chart === 'undefined') return;

    const rows = @json($budgetChartData);

    const colorFor = s => s === 'over' ? '#ff453a' : (s === 'warn' ? '#ffd60a' : '#30d158');

    new Chart(el.getContext('2d'), {
        type: 'bar',
        data: {
            labels: rows.map(r => r.name),
            datasets: [
                {
                    label: 'Presupuesto',
                    data: rows.map(r => r.limit),
                    backgroundColor: 'rgba(255,255,255,0.10)',
                    borderColor: 'rgba(255,255,255,0.22)',
                    borderWidth: 1,
                    borderRadius: 5,
                    borderSkipped: false,
                },
                {
                    label: 'Gastado',
                    data: rows.map(r => r.spent),
                    backgroundColor: rows.map(r => colorFor(r.status)),
                    borderRadius: 5,
                    borderSkipped: false,
                },
            ],
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { position: 'top', labels: { padding: 12, boxWidth: 10, boxHeight: 10, usePointStyle: true, font: { size: 11 }, color: '#98989f' } },
                tooltip: {
                    backgroundColor: '#2c2c2e',
                    borderColor: '#48484a',
                    borderWidth: 1,
                    padding: 10,
                    cornerRadius: 8,
                    callbacks: { label: ctx => ` ${ctx.dataset.label}: $ ${ctx.parsed.x.toLocaleString('es-AR')}` },
                },
            },
            scales: {
                x: {
                    grid: { color: '#48484a' },
                    ticks: { color: '#98989f', callback: v => '$ ' + (v >= 1000 ? Math.round(v/1000) + 'k' : v) },
                },
                y: { grid: { display: false }, ticks: { color: '#98989f', font: { size: 12 } } },
            },
        },
    });
})();
</script>
@endif

@endsection
