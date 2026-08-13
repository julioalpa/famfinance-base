@extends('layouts.app')

@section('title', 'Reportes')

@section('content')

{{-- Chart.js CDN --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>

<style>
/* Reports index — responsive layer */
.rpt-page-header { display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:32px; flex-wrap:wrap; gap:16px; }
.rpt-period { display:flex; gap:6px; align-items:center; background:var(--surface); border:1px solid var(--border); border-radius:10px; padding:4px; }
.rpt-period a { padding:9px 14px; border-radius:7px; font-size:13px; font-weight:700; text-decoration:none; transition:all 0.15s; min-height:38px; display:inline-flex; align-items:center; justify-content:center; }
.rpt-stats { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:28px; }
.rpt-row { display:grid; gap:20px; margin-bottom:20px; }
.rpt-row.col-3-2 { grid-template-columns:3fr 2fr; }
.rpt-row.col-2-3 { grid-template-columns:2fr 3fr; }
.rpt-row.col-1-2 { grid-template-columns:1fr 2fr; }
.rpt-chart { position:relative; height:240px; }
.rpt-chart.h-220 { height:220px; }
.rpt-chart.h-200 { height:200px; }
.rpt-tbl-wrap { overflow-x:auto; -webkit-overflow-scrolling:touch; }

@media (max-width: 900px) {
    .rpt-row,
    .rpt-row.col-3-2,
    .rpt-row.col-2-3,
    .rpt-row.col-1-2 { grid-template-columns: 1fr !important; }
    .rpt-stats { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 768px) {
    .rpt-page-header h1 { font-size: 22px !important; }
    .rpt-period { width:100%; }
    .rpt-period a { flex:1; min-height:44px; padding:11px 8px; font-size:13px; }
    .rpt-chart { height:200px !important; }
    .rpt-chart.h-220, .rpt-chart.h-200 { height:180px !important; }
    .rpt-tbl-wrap table { min-width: 480px; }
    .rpt-col-hide-md { display:none !important; }
}

@media (max-width: 480px) {
    .rpt-stats { grid-template-columns: 1fr; }
    .rpt-tbl-wrap table { min-width: 380px; }
}

/* ── Tabla combinada Ingresos / Egresos / Neto ─────────────────────────────
   Reusadas también en Balance mensual. Mismo naming para consistencia. */
.cmb-nums {
    display: grid;
    grid-template-columns: repeat(3, minmax(78px, 1fr));
    gap: 10px;
    align-items: end;
    text-align: right;
    font-family: 'Bricolage Grotesque', sans-serif;
}
.cmb-cell { display: flex; flex-direction: column; align-items: flex-end; }
.cmb-cell-label {
    font-family: 'Nunito', sans-serif;
    font-size: 9px;
    font-weight: 700;
    color: var(--muted);
    letter-spacing: 0.05em;
    text-transform: uppercase;
    margin-bottom: 2px;
    display: none;
}
.cmb-cell-val { font-size: 13px; font-weight: 800; color: var(--text); white-space: nowrap; }
.cmb-cell-val.pos { color: var(--income); }
.cmb-cell-val.neg { color: var(--expense); }
.cmb-cell-val.zero { color: var(--muted); font-weight: 700; }

.cmb-row {
    display: grid;
    grid-template-columns: 28px 1fr auto;
    align-items: center;
    gap: 14px;
    padding: 12px 20px;
    border-bottom: 1px solid rgba(40,40,52,0.5);
}
.cmb-row:last-child { border-bottom: none; }
.cmb-header {
    display: grid;
    grid-template-columns: 28px 1fr auto;
    align-items: end;
    gap: 14px;
    padding: 12px 20px 10px;
    border-bottom: 1px solid var(--border);
    font-family: 'Nunito', sans-serif;
    font-size: 9.5px;
    font-weight: 800;
    color: var(--muted);
    letter-spacing: 0.06em;
    text-transform: uppercase;
}
.cmb-header-nums {
    display: grid;
    grid-template-columns: repeat(3, minmax(78px, 1fr));
    gap: 10px;
    text-align: right;
}
.cmb-rank {
    font-family: 'Bricolage Grotesque', sans-serif;
    font-size: 11px; font-weight: 800; color: var(--muted); text-align: center;
}
.cmb-name {
    font-size: 13px; font-weight: 700; color: var(--text);
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    margin-bottom: 5px; display: flex; align-items: center; gap: 6px;
}
.cmb-bar-track { height: 4px; background: var(--surface2); border-radius: 2px; overflow: hidden; }
.cmb-bar-fill  { height: 100%; border-radius: 2px; transition: width 0.6s ease; }

/* Diverging bar: centered axis, positive → right, negative → left */
.cmb-bar-diverging {
    position: relative;
    height: 4px;
    background: var(--surface2);
    border-radius: 2px;
}
.cmb-bar-diverging::before {
    content: '';
    position: absolute;
    left: 50%;
    top: -2px;
    bottom: -2px;
    width: 1px;
    background: rgba(255, 255, 255, 0.22);
    transform: translateX(-0.5px);
    pointer-events: none;
}
.cmb-bar-diverging .cmb-bar-fill {
    position: absolute;
    top: 0;
    height: 100%;
    max-width: 50%;
}
.cmb-bar-diverging .cmb-bar-fill--pos { left: 50%; }
.cmb-bar-diverging .cmb-bar-fill--neg { right: 50%; }

@media (max-width: 620px) {
    .cmb-nums { grid-template-columns: repeat(3, minmax(64px, auto)); gap: 8px; }
    .cmb-cell-val { font-size: 12px; }
    .cmb-cell-label { display: block; }
    .cmb-header { display: none; }
    .cmb-row { gap: 10px; padding: 12px 16px; align-items: flex-start; }
}

/* Sparkline en card savings */
.spark-wrap { height: 34px; margin-top: 10px; position: relative; }
</style>

@php
    // Serialise data for JS
    $jsMonthLabels  = $monthlyData->pluck('label')->toJson();
    $jsIncome       = $monthlyData->pluck('income')->toJson();
    $jsExpense      = $monthlyData->pluck('expense')->toJson();
    $jsBalance      = $monthlyData->map(fn($m) => round($m['income'] - $m['expense'], 2))->values()->toJson();
    $jsCatLabels    = $expensesByCategory->keys()->toJson();
    $jsCatValues    = $expensesByCategory->values()->toJson();
    $jsDailyLabels  = collect($dailySpending)->pluck('day')->map(fn($d) => "día $d")->toJson();
    $jsDailyValues  = collect($dailySpending)->pluck('total')->toJson();
@endphp

{{-- ── Header ──────────────────────────────────────────────────────────────── --}}
<div class="rpt-page-header">
    <div>
        <h1 class="font-display" style="font-size:28px; font-weight:800; letter-spacing:-0.03em; margin-bottom:4px;">Reportes</h1>
        <div style="font-size:13px; color:var(--muted); font-weight:500;">
            Análisis financiero · {{ ucfirst($startDate->locale('es')->isoFormat('MMMM YYYY')) }} → hoy
        </div>
    </div>

    {{-- Period picker --}}
    <div class="rpt-period">
        @foreach([3 => '3 meses', 6 => '6 meses', 12 => '12 meses'] as $val => $periodLabel)
            <a href="{{ route('reports.index', ['months' => $val]) }}"
               style="{{ $months === $val ? 'background:var(--accent); color:#0c0804;' : 'color:var(--muted);' }}">
                {{ $periodLabel }}
            </a>
        @endforeach
    </div>
</div>

{{-- ── Summary stat cards ────────────────────────────────────────────────── --}}
<div class="rpt-stats">

    <div class="stat-card income">
        <div style="font-size:11px; letter-spacing:0.09em; text-transform:uppercase; color:var(--muted); margin-bottom:10px; font-weight:700;">Ingreso promedio</div>
        <div class="font-display" style="font-size:22px; font-weight:800; color:var(--income); letter-spacing:-0.02em; line-height:1;">
            $ {{ number_format($avgIncome, 0, ',', '.') }}
        </div>
        <div style="font-size:11px; color:var(--muted); margin-top:6px; font-weight:500;">por mes · ARS</div>
    </div>

    <div class="stat-card expense">
        <div style="font-size:11px; letter-spacing:0.09em; text-transform:uppercase; color:var(--muted); margin-bottom:10px; font-weight:700;">Gasto promedio</div>
        <div class="font-display" style="font-size:22px; font-weight:800; color:var(--expense); letter-spacing:-0.02em; line-height:1;">
            $ {{ number_format($avgExpense, 0, ',', '.') }}
        </div>
        <div style="font-size:11px; color:var(--muted); margin-top:6px; font-weight:500;">por mes · ARS</div>
    </div>

    <div class="stat-card balance">
        <div style="font-size:11px; letter-spacing:0.09em; text-transform:uppercase; color:var(--muted); margin-bottom:10px; font-weight:700;">Tasa de ahorro</div>
        <div class="font-display" style="font-size:22px; font-weight:800; letter-spacing:-0.02em; line-height:1;
             color:{{ $savingsRate >= 0 ? 'var(--income)' : 'var(--expense)' }};">
            {{ $savingsRate > 0 ? '+' : '' }}{{ $savingsRate }}%
        </div>
        <div class="spark-wrap"><canvas id="savingsSpark"></canvas></div>
        <div style="font-size:11px; color:var(--muted); margin-top:2px; font-weight:500; display:flex; justify-content:space-between;">
            <span>promedio período</span>
            <span style="opacity:0.7;">últimos {{ $months }} m</span>
        </div>
    </div>

    <div class="stat-card neutral">
        <div style="font-size:11px; letter-spacing:0.09em; text-transform:uppercase; color:var(--muted); margin-bottom:10px; font-weight:700;">Mejor mes</div>
        <div class="font-display" style="font-size:18px; font-weight:800; color:var(--text); letter-spacing:-0.02em; line-height:1;">
            {{ $bestMonth ? $bestMonth['label'] : '—' }}
        </div>
        @if($bestMonth)
        <div style="font-size:11px; color:var(--income); margin-top:6px; font-weight:600;">
            +$ {{ number_format($bestMonth['income'] - $bestMonth['expense'], 0, ',', '.') }} balance
        </div>
        @endif
    </div>
</div>

{{-- Segunda fila de KPIs: cobertura gastos fijos + meses en positivo --}}
<div class="rpt-stats" style="margin-top:-12px; margin-bottom:28px;">
    <div class="stat-card" style="grid-column:span 2;">
        <div style="font-size:11px; letter-spacing:0.09em; text-transform:uppercase; color:var(--muted); margin-bottom:10px; font-weight:700;">Cobertura de gastos fijos</div>
        @if($fixedCoverageAvg !== null)
            @php
                $fcColor = $fixedCoverageAvg >= 100 ? 'var(--expense)' : ($fixedCoverageAvg >= 70 ? 'var(--accent)' : 'var(--income)');
            @endphp
            <div class="font-display" style="font-size:22px; font-weight:800; letter-spacing:-0.02em; line-height:1; color:{{ $fcColor }};">
                {{ $fixedCoverageAvg }}%
            </div>
            <div style="height:5px; background:var(--surface2); border-radius:3px; overflow:hidden; margin-top:12px;">
                <div style="height:100%; width:{{ min(100, $fixedCoverageAvg) }}%; background:{{ $fcColor }}; border-radius:3px;"></div>
            </div>
            <div style="font-size:11px; color:var(--muted); margin-top:8px; font-weight:500;">
                Prom. mensual: pagos fijos $ {{ number_format($avgFixedPaidMonthly, 0, ',', '.') }} · ingreso $ {{ number_format($avgIncome, 0, ',', '.') }}
            </div>
        @else
            <div class="font-display" style="font-size:22px; font-weight:800; color:var(--muted); opacity:0.5;">—</div>
            <div style="font-size:11px; color:var(--muted); margin-top:6px;">Sin ingresos en el período</div>
        @endif
    </div>

    <div class="stat-card" style="grid-column:span 2;">
        <div style="font-size:11px; letter-spacing:0.09em; text-transform:uppercase; color:var(--muted); margin-bottom:10px; font-weight:700;">Meses en positivo</div>
        @php $posColor = $positiveMonths >= $months ? 'var(--income)' : ($positiveMonths >= $months / 2 ? 'var(--accent)' : 'var(--expense)'); @endphp
        <div class="font-display" style="font-size:22px; font-weight:800; letter-spacing:-0.02em; line-height:1; color:{{ $posColor }};">
            {{ $positiveMonths }} <span style="font-size:14px; color:var(--muted); font-weight:600;">/ {{ $months }}</span>
        </div>
        <div style="display:flex; gap:4px; margin-top:12px;">
            @foreach($monthlyData as $m)
                @php $mNet = $m['income'] - $m['expense']; @endphp
                <div style="flex:1; height:8px; border-radius:2px;
                    background:{{ $m['income'] === 0.0 ? 'var(--surface2)' : ($mNet > 0 ? 'var(--income)' : 'var(--expense)') }};
                    opacity:{{ $m['income'] === 0.0 ? '0.4' : '1' }};"
                    title="{{ $m['label'] }}: {{ $mNet >= 0 ? '+' : '' }}$ {{ number_format($mNet, 0, ',', '.') }}"></div>
            @endforeach
        </div>
        <div style="font-size:11px; color:var(--muted); margin-top:8px; font-weight:500;">
            Meses del período en los que el ingreso superó al gasto
        </div>
    </div>
</div>

{{-- ── Row 1: Monthly bars + Balance line ────────────────────────────────── --}}
<div class="rpt-row col-3-2">

    {{-- Ingresos vs Gastos --}}
    <div class="card">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
            <div>
                <h2 class="font-display" style="font-size:15px; font-weight:700; letter-spacing:-0.01em;">Ingresos vs Gastos</h2>
                <div style="font-size:12px; color:var(--muted); margin-top:2px;">Últimos {{ $months }} meses</div>
            </div>
            <div style="display:flex; gap:14px; font-size:12px; font-weight:600;">
                <span style="display:flex; align-items:center; gap:5px; color:var(--income);">
                    <span style="width:10px; height:10px; border-radius:2px; background:var(--income); display:inline-block;"></span>Ingresos
                </span>
                <span style="display:flex; align-items:center; gap:5px; color:var(--expense);">
                    <span style="width:10px; height:10px; border-radius:2px; background:var(--expense); display:inline-block;"></span>Gastos
                </span>
            </div>
        </div>
        <div class="rpt-chart">
            <canvas id="monthlyChart"></canvas>
        </div>
    </div>

    {{-- Balance mensual --}}
    <div class="card">
        <div style="margin-bottom:20px;">
            <h2 class="font-display" style="font-size:15px; font-weight:700; letter-spacing:-0.01em;">Balance mensual</h2>
            <div style="font-size:12px; color:var(--muted); margin-top:2px;">Ingreso − Gasto por mes</div>
        </div>
        <div class="rpt-chart">
            <canvas id="balanceChart"></canvas>
        </div>
    </div>
</div>

{{-- ── Row 2: Category donut + Daily spending ────────────────────────────── --}}
<div class="rpt-row col-2-3">

    {{-- Donut categorías --}}
    <div class="card">
        <div style="margin-bottom:16px;">
            <h2 class="font-display" style="font-size:15px; font-weight:700; letter-spacing:-0.01em;">Gastos por categoría</h2>
            <div style="font-size:12px; color:var(--muted); margin-top:2px;">
                Total: <strong style="color:var(--expense);">$ {{ number_format($totalPeriodExpense, 0, ',', '.') }}</strong>
            </div>
        </div>

        @if($expensesByCategory->isEmpty())
            <div style="text-align:center; padding:40px 0; color:var(--muted); font-size:13px;">Sin gastos en el período</div>
        @else
            <div class="rpt-chart h-200" style="margin-bottom:16px;">
                <canvas id="categoryChart"></canvas>
            </div>
            {{-- Legend table --}}
            <div style="display:flex; flex-direction:column; gap:6px;" id="category-legend"></div>
        @endif
    </div>

    {{-- Daily spending --}}
    <div class="card">
        <div style="margin-bottom:20px;">
            <h2 class="font-display" style="font-size:15px; font-weight:700; letter-spacing:-0.01em;">Gasto diario</h2>
            <div style="font-size:12px; color:var(--muted); margin-top:2px;">{{ now()->locale('es')->isoFormat('MMMM YYYY') }}</div>
        </div>
        <div class="rpt-chart h-220">
            <canvas id="dailyChart"></canvas>
        </div>
    </div>
</div>

{{-- ── Row 3: Top categories table + By member ──────────────────────────── --}}
<div class="rpt-row{{ $byMember->count() > 1 ? ' col-3-2' : '' }}">

    {{-- Categorías: Ing/Egr/Neto --}}
    <div class="card" style="padding:0; overflow:hidden;">
        <div style="padding:20px 24px 16px; border-bottom:1px solid var(--border);">
            <h2 class="font-display" style="font-size:15px; font-weight:700; letter-spacing:-0.01em;">Categorías del período</h2>
            <div style="font-size:12px; color:var(--muted); margin-top:2px;">Últimos {{ $months }} meses · ingresos, egresos y neto</div>
        </div>
        @if($categoryStats->isEmpty())
            <div style="text-align:center; padding:40px; color:var(--muted); font-size:13px;">Sin datos</div>
        @else
        @php
            $catMaxAbsNet = max($categoryStats->max(fn ($c) => abs($c['net'])), 1);
        @endphp
        <div class="cmb-header">
            <div></div>
            <div>Categoría</div>
            <div class="cmb-header-nums">
                <div>Entra</div>
                <div>Sale</div>
                <div>Neto</div>
            </div>
        </div>
        @foreach($categoryStats as $idx => $cat)
        @php
            $catNetPct   = min(50, abs($cat['net']) / $catMaxAbsNet * 50);
            $catNetColor = $cat['net'] > 0 ? 'var(--income)' : ($cat['net'] < 0 ? 'var(--expense)' : 'var(--muted)');
        @endphp
        <div class="cmb-row">
            <div class="cmb-rank">{{ $idx + 1 }}</div>
            <div style="min-width:0;">
                <div class="cmb-name">
                    @include('categories._icon', ['icon' => $cat['icon'], 'color' => $cat['color'], 'type' => $cat['type'] ?? 'expense', 'size' => 'xs', 'label' => null])
                    <span>{{ $cat['name'] }}</span>
                    <span style="font-size:10px;color:var(--muted);font-weight:600;">{{ $cat['count'] }} mov.</span>
                </div>
                <div class="cmb-bar-diverging">
                    @if($cat['net'] >= 0)
                        <div class="cmb-bar-fill cmb-bar-fill--pos" style="width:{{ $catNetPct }}%;background:{{ $catNetColor }};"></div>
                    @else
                        <div class="cmb-bar-fill cmb-bar-fill--neg" style="width:{{ $catNetPct }}%;background:{{ $catNetColor }};"></div>
                    @endif
                </div>
            </div>
            <div class="cmb-nums">
                <div class="cmb-cell">
                    <div class="cmb-cell-label">Entra</div>
                    <div class="cmb-cell-val {{ $cat['income']  > 0 ? 'pos'  : 'zero' }}">{{ $cat['income']  > 0 ? '$ ' . number_format($cat['income'],  0, ',', '.') : '—' }}</div>
                </div>
                <div class="cmb-cell">
                    <div class="cmb-cell-label">Sale</div>
                    <div class="cmb-cell-val {{ $cat['expense'] > 0 ? 'neg'  : 'zero' }}">{{ $cat['expense'] > 0 ? '$ ' . number_format($cat['expense'], 0, ',', '.') : '—' }}</div>
                </div>
                <div class="cmb-cell">
                    <div class="cmb-cell-label">Neto</div>
                    <div class="cmb-cell-val {{ $cat['net'] > 0 ? 'pos' : ($cat['net'] < 0 ? 'neg' : 'zero') }}">
                        {{ $cat['net'] > 0 ? '+$ ' : ($cat['net'] < 0 ? '-$ ' : '$ ') }}{{ number_format(abs($cat['net']), 0, ',', '.') }}
                    </div>
                </div>
            </div>
        </div>
        @endforeach
        @endif
    </div>

    {{-- Por miembro (Ing/Egr/Neto) --}}
    @if($byMember->count() > 1)
    <div class="card">
        <div style="margin-bottom:16px;">
            <h2 class="font-display" style="font-size:15px; font-weight:700; letter-spacing:-0.01em;">Por integrante</h2>
            <div style="font-size:12px; color:var(--muted); margin-top:2px;">Últimos {{ $months }} meses · ingresos y egresos</div>
        </div>
        <div class="rpt-chart h-220" style="margin-bottom:20px;">
            <canvas id="memberChart"></canvas>
        </div>
        @php
            $memberColors = ['#f0a030','#4e9bff','#2dd870','#f04060','#e8b840','#a855f7'];
        @endphp
        @foreach($byMember as $name => $m)
        <div style="padding:10px 0; border-bottom:1px solid rgba(40,40,52,0.5);">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:6px;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <div style="width:10px; height:10px; border-radius:50%; background:{{ $memberColors[$loop->index % count($memberColors)] }};"></div>
                    <span style="font-size:13px; font-weight:700;">{{ $name }}</span>
                    <span style="font-size:10px; color:var(--muted); font-weight:600;">{{ $m['count'] }} mov.</span>
                </div>
                @php $mNet = $m['net']; @endphp
                <div class="font-display" style="font-size:13px; font-weight:800; color:{{ $mNet > 0 ? 'var(--income)' : ($mNet < 0 ? 'var(--expense)' : 'var(--muted)') }};">
                    {{ $mNet > 0 ? '+$ ' : ($mNet < 0 ? '-$ ' : '$ ') }}{{ number_format(abs($mNet), 0, ',', '.') }}
                </div>
            </div>
            <div style="display:flex; gap:16px; font-size:11px; font-family:'Nunito',sans-serif;">
                <div><span style="color:var(--muted); font-weight:700;">Entra:</span>
                    <span style="color:{{ $m['income'] > 0 ? 'var(--income)' : 'var(--muted)' }}; font-weight:800; font-family:'Bricolage Grotesque',sans-serif;">
                        {{ $m['income'] > 0 ? '$ ' . number_format($m['income'], 0, ',', '.') : '—' }}
                    </span>
                </div>
                <div><span style="color:var(--muted); font-weight:700;">Sale:</span>
                    <span style="color:{{ $m['expense'] > 0 ? 'var(--expense)' : 'var(--muted)' }}; font-weight:800; font-family:'Bricolage Grotesque',sans-serif;">
                        {{ $m['expense'] > 0 ? '$ ' . number_format($m['expense'], 0, ',', '.') : '—' }}
                    </span>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

</div>

{{-- ── Grupos de etiquetas: Ing/Egr/Neto ───────────────────────────────── --}}
@if($tagGroupStats->isNotEmpty())
<div style="margin-bottom:20px;">
    <div class="card" style="padding:0; overflow:hidden;">
        <div style="padding:20px 24px 16px; border-bottom:1px solid var(--border);
                    display:flex; align-items:flex-start; justify-content:space-between; gap:12px; flex-wrap:wrap;">
            <div>
                <h2 class="font-display" style="font-size:15px; font-weight:700; letter-spacing:-0.01em;">Por grupo de etiquetas</h2>
                <div style="font-size:12px; color:var(--muted); margin-top:2px;">
                    Últimos {{ $months }} meses · ingresos, egresos y neto
                </div>
            </div>
            <a href="{{ route('tags.index') }}" style="font-size:12px; color:var(--accent); text-decoration:none; font-weight:600;">
                Gestionar grupos →
            </a>
        </div>

        @php
            $tgSorted    = $tagGroupStats->sortByDesc(fn ($g) => $g['expense'] + $g['income'])->values();
            $tgMaxAbsNet = max(
                $tgSorted->max(fn ($g) => abs($g['income'] - $g['expense'])) ?? 0,
                $noGroupTotal ?? 0,
                1
            );
        @endphp
        <div class="cmb-header">
            <div></div>
            <div>Grupo</div>
            <div class="cmb-header-nums">
                <div>Entra</div>
                <div>Sale</div>
                <div>Neto</div>
            </div>
        </div>
        @foreach($tgSorted as $tg)
        @php
            $tgNet      = $tg['income'] - $tg['expense'];
            $tgNetPct   = min(50, abs($tgNet) / $tgMaxAbsNet * 50);
            $tgNetColor = $tgNet > 0 ? 'var(--income)' : ($tgNet < 0 ? 'var(--expense)' : 'var(--muted)');
        @endphp
        <div class="cmb-row" style="grid-template-columns:14px 1fr auto;align-items:flex-start;">
            <div style="width:12px;height:12px;border-radius:3px;background:{{ $tg['color'] }};flex-shrink:0;margin-top:5px;"></div>
            <div style="min-width:0;">
                <div class="cmb-name" style="gap:8px;">
                    <span>{{ $tg['name'] }}</span>
                    <span style="font-size:10px;color:var(--muted);font-weight:600;">{{ $tg['count'] }} mov.</span>
                </div>
                <div style="display:flex;flex-wrap:wrap;gap:4px;margin:2px 0 6px;">
                    @forelse($tg['tags'] as $t)
                        <span style="display:inline-flex;align-items:center;gap:3px;padding:1px 7px 1px 5px;border-radius:10px;font-size:9.5px;font-weight:700;background:{{ $t['color'] }}22;color:{{ $t['color'] }};border:1px solid {{ $t['color'] }}44;">
                            <span style="width:5px;height:5px;border-radius:50%;background:{{ $t['color'] }};"></span>
                            {{ $t['name'] }}
                        </span>
                    @empty
                        <span style="font-size:11px;color:var(--muted);">Sin etiquetas asignadas</span>
                    @endforelse
                </div>
                <div class="cmb-bar-diverging">
                    @if($tgNet >= 0)
                        <div class="cmb-bar-fill cmb-bar-fill--pos" style="width:{{ $tgNetPct }}%;background:{{ $tgNetColor }};"></div>
                    @else
                        <div class="cmb-bar-fill cmb-bar-fill--neg" style="width:{{ $tgNetPct }}%;background:{{ $tgNetColor }};"></div>
                    @endif
                </div>
            </div>
            <div class="cmb-nums" style="align-self:center;">
                <div class="cmb-cell">
                    <div class="cmb-cell-label">Entra</div>
                    <div class="cmb-cell-val {{ $tg['income']  > 0 ? 'pos'  : 'zero' }}">{{ $tg['income']  > 0 ? '$ ' . number_format($tg['income'],  0, ',', '.') : '—' }}</div>
                </div>
                <div class="cmb-cell">
                    <div class="cmb-cell-label">Sale</div>
                    <div class="cmb-cell-val {{ $tg['expense'] > 0 ? 'neg'  : 'zero' }}">{{ $tg['expense'] > 0 ? '$ ' . number_format($tg['expense'], 0, ',', '.') : '—' }}</div>
                </div>
                <div class="cmb-cell">
                    <div class="cmb-cell-label">Neto</div>
                    <div class="cmb-cell-val {{ $tgNet > 0 ? 'pos' : ($tgNet < 0 ? 'neg' : 'zero') }}">
                        {{ $tgNet > 0 ? '+$ ' : ($tgNet < 0 ? '-$ ' : '$ ') }}{{ number_format(abs($tgNet), 0, ',', '.') }}
                    </div>
                </div>
            </div>
        </div>
        @endforeach

        @if($noGroupTotal > 0)
        <div class="cmb-row" style="grid-template-columns:14px 1fr auto;opacity:0.7;">
            <div style="width:12px;height:12px;border-radius:3px;background:var(--muted);flex-shrink:0;align-self:center;"></div>
            <div style="min-width:0;">
                <div class="cmb-name" style="color:var(--muted);gap:8px;">
                    <span>Sin grupo</span>
                    <span style="font-size:10px;color:var(--muted);font-weight:600;">etiquetas sin grupo asignado</span>
                </div>
                <div class="cmb-bar-diverging">
                    <div class="cmb-bar-fill cmb-bar-fill--neg" style="width:{{ min(50, $noGroupTotal / $tgMaxAbsNet * 50) }}%;background:var(--expense);"></div>
                </div>
            </div>
            <div class="cmb-nums">
                <div class="cmb-cell"><div class="cmb-cell-label">Entra</div><div class="cmb-cell-val zero">—</div></div>
                <div class="cmb-cell"><div class="cmb-cell-label">Sale</div><div class="cmb-cell-val neg">$ {{ number_format($noGroupTotal, 0, ',', '.') }}</div></div>
                <div class="cmb-cell"><div class="cmb-cell-label">Neto</div><div class="cmb-cell-val neg">-$ {{ number_format($noGroupTotal, 0, ',', '.') }}</div></div>
            </div>
        </div>
        @endif
    </div>
</div>
@endif

{{-- ── Etiquetas: Ing/Egr/Neto ────────────────────────────────────────── --}}
@if($tagStats->isNotEmpty())
<div style="margin-bottom:20px;">
    <div class="card" style="padding:0; overflow:hidden;">
        <div style="padding:20px 24px 16px; border-bottom:1px solid var(--border); display:flex; align-items:flex-start; justify-content:space-between; gap:12px; flex-wrap:wrap;">
            <div>
                <h2 class="font-display" style="font-size:15px; font-weight:700; letter-spacing:-0.01em;">Por etiqueta</h2>
                <div style="font-size:12px; color:var(--muted); margin-top:2px;">Últimos {{ $months }} meses · solo transacciones etiquetadas</div>
            </div>
            <a href="{{ route('tags.index') }}" style="font-size:12px; color:var(--accent); text-decoration:none; font-weight:600;">Gestionar etiquetas →</a>
        </div>

        @php
            $tagsSorted    = $tagStats->sortByDesc(fn ($t) => $t['expense'] + $t['income'])->values();
            $tagMaxAbsNet  = max($tagsSorted->max(fn ($t) => abs($t['income'] - $t['expense'])), 1);
        @endphp
        <div class="cmb-header">
            <div></div>
            <div>Etiqueta</div>
            <div class="cmb-header-nums">
                <div>Entra</div>
                <div>Sale</div>
                <div>Neto</div>
            </div>
        </div>
        @foreach($tagsSorted as $tag)
        @php
            $tagNet      = $tag['income'] - $tag['expense'];
            $tagNetPct   = min(50, abs($tagNet) / $tagMaxAbsNet * 50);
            $tagNetColor = $tagNet > 0 ? 'var(--income)' : ($tagNet < 0 ? 'var(--expense)' : 'var(--muted)');
        @endphp
        <div class="cmb-row" style="grid-template-columns:14px 1fr auto;">
            <div style="width:10px;height:10px;border-radius:3px;background:{{ $tag['color'] }};flex-shrink:0;align-self:center;"></div>
            <div style="min-width:0;">
                <div class="cmb-name" style="gap:8px;">
                    <span>{{ $tag['name'] }}</span>
                    <span style="font-size:10px;color:var(--muted);font-weight:600;">{{ $tag['count'] }} mov.</span>
                </div>
                <div class="cmb-bar-diverging">
                    @if($tagNet >= 0)
                        <div class="cmb-bar-fill cmb-bar-fill--pos" style="width:{{ $tagNetPct }}%;background:{{ $tagNetColor }};"></div>
                    @else
                        <div class="cmb-bar-fill cmb-bar-fill--neg" style="width:{{ $tagNetPct }}%;background:{{ $tagNetColor }};"></div>
                    @endif
                </div>
            </div>
            <div class="cmb-nums">
                <div class="cmb-cell">
                    <div class="cmb-cell-label">Entra</div>
                    <div class="cmb-cell-val {{ $tag['income']  > 0 ? 'pos'  : 'zero' }}">{{ $tag['income']  > 0 ? '$ ' . number_format($tag['income'],  0, ',', '.') : '—' }}</div>
                </div>
                <div class="cmb-cell">
                    <div class="cmb-cell-label">Sale</div>
                    <div class="cmb-cell-val {{ $tag['expense'] > 0 ? 'neg'  : 'zero' }}">{{ $tag['expense'] > 0 ? '$ ' . number_format($tag['expense'], 0, ',', '.') : '—' }}</div>
                </div>
                <div class="cmb-cell">
                    <div class="cmb-cell-label">Neto</div>
                    <div class="cmb-cell-val {{ $tagNet > 0 ? 'pos' : ($tagNet < 0 ? 'neg' : 'zero') }}">
                        {{ $tagNet > 0 ? '+$ ' : ($tagNet < 0 ? '-$ ' : '$ ') }}{{ number_format(abs($tagNet), 0, ',', '.') }}
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- ── Row 4: Patrimonio neto ─────────────────────────────────────────────── --}}
<div class="rpt-row col-1-2">

    {{-- Resumen --}}
    <div class="card" style="display:flex; flex-direction:column; gap:16px; justify-content:center;">
        <div>
            <div style="font-size:11px; letter-spacing:0.09em; text-transform:uppercase; color:var(--muted); margin-bottom:6px; font-weight:700;">Activos</div>
            <div class="font-display" style="font-size:22px; font-weight:800; color:var(--income);">$ {{ number_format($totalAssets, 0, ',', '.') }}</div>
        </div>
        <div>
            <div style="font-size:11px; letter-spacing:0.09em; text-transform:uppercase; color:var(--muted); margin-bottom:6px; font-weight:700;">Pasivos</div>
            <div class="font-display" style="font-size:22px; font-weight:800; color:var(--expense);">$ {{ number_format($totalLiabilities, 0, ',', '.') }}</div>
        </div>
        <div style="padding-top:14px; border-top:1px solid var(--border);">
            <div style="font-size:11px; letter-spacing:0.09em; text-transform:uppercase; color:var(--muted); margin-bottom:6px; font-weight:700;">Patrimonio neto</div>
            <div class="font-display" style="font-size:26px; font-weight:800; color:{{ $netWorth >= 0 ? 'var(--income)' : 'var(--expense)' }};">
                {{ $netWorth >= 0 ? '+' : '' }}$ {{ number_format($netWorth, 0, ',', '.') }}
            </div>
        </div>
    </div>

    {{-- Desglose por cuenta --}}
    <div class="card" style="padding:0; overflow:hidden;">
        <div style="padding:20px 24px 14px; border-bottom:1px solid var(--border);">
            <h2 class="font-display" style="font-size:15px; font-weight:700; letter-spacing:-0.01em;">Desglose por cuenta</h2>
        </div>
        @php
            $assetAccounts     = $allAccounts->filter(fn($a) => ! $a->isLiability());
            $liabilityAccounts = $allAccounts->filter(fn($a) => $a->isLiability());
            $typeLabelsRep = ['cash' => 'Efectivo', 'digital' => 'Digital', 'credit' => 'Crédito', 'loan' => 'Préstamo'];
        @endphp
        <div class="rpt-tbl-wrap">
        <table class="data-table">
            <tbody>
                @if($assetAccounts->isNotEmpty())
                <tr><td colspan="3" style="font-size:10px; letter-spacing:0.1em; text-transform:uppercase; color:var(--income); font-weight:700; padding:12px 20px 6px;">ACTIVOS</td></tr>
                @foreach($assetAccounts->sortByDesc('balance') as $acc)
                <tr>
                    <td style="font-weight:600; font-size:13px;">{{ $acc->name }}</td>
                    <td style="font-size:12px; color:var(--muted);">{{ $typeLabelsRep[$acc->type] ?? $acc->type }} · {{ $acc->currency }}</td>
                    <td style="text-align:right; font-weight:700; color:var(--income); white-space:nowrap;">+ $ {{ number_format($acc->balance, 0, ',', '.') }}</td>
                </tr>
                @endforeach
                @endif

                @if($liabilityAccounts->isNotEmpty())
                <tr><td colspan="3" style="font-size:10px; letter-spacing:0.1em; text-transform:uppercase; color:var(--expense); font-weight:700; padding:12px 20px 6px;">PASIVOS</td></tr>
                @foreach($liabilityAccounts->sortByDesc('balance') as $acc)
                <tr>
                    <td style="font-weight:600; font-size:13px;">{{ $acc->name }}</td>
                    <td style="font-size:12px; color:var(--muted);">{{ $typeLabelsRep[$acc->type] ?? $acc->type }} · {{ $acc->currency }}</td>
                    <td style="text-align:right; font-weight:700; color:var(--expense); white-space:nowrap;">− $ {{ number_format($acc->balance, 0, ',', '.') }}</td>
                </tr>
                @endforeach
                @endif
            </tbody>
        </table>
        </div>
    </div>
</div>

{{-- ── Row 4: Historial de pendientes de pago ────────────────────────────── --}}
@if($paymentItemHistory->isNotEmpty())
<div style="margin-bottom:20px;">
    <div class="card" style="padding:0; overflow:hidden;">
        <div style="padding:20px 24px 16px; border-bottom:1px solid var(--border); display:flex; align-items:flex-start; justify-content:space-between; gap:12px; flex-wrap:wrap;">
            <div>
                <h2 class="font-display" style="font-size:15px; font-weight:700; letter-spacing:-0.01em;">Evolución de gastos fijos</h2>
                <div style="font-size:12px; color:var(--muted); margin-top:2px;">Monto pagado por ítem · últimos {{ $months }} meses</div>
            </div>
            <a href="{{ route('payment-items.index') }}" style="font-size:12px; color:var(--accent); text-decoration:none; font-weight:600;">Gestionar ítems →</a>
        </div>

        <div style="overflow-x:auto;">
            <table class="data-table" style="min-width:600px;">
                <thead>
                    <tr>
                        <th style="min-width:160px;">Ítem</th>
                        @foreach($monthKeys as $mk)
                            <th style="text-align:right; white-space:nowrap;">{{ $mk['label'] }}</th>
                        @endforeach
                        <th style="text-align:right; white-space:nowrap;">Variación</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($paymentItemHistory as $row)
                    @php
                        $lastPaid   = collect($row['months'])->last(fn($m) => $m['amount'] !== null);
                        $lastChange = $lastPaid ? $lastPaid['change'] : null;
                    @endphp
                    <tr>
                        <td style="font-weight:600; font-size:13px;">
                            {{ $row['item']->description }}
                            <div style="font-size:11px; color:var(--muted); font-weight:400; margin-top:2px;">{{ $row['item']->account?->name }}</div>
                        </td>
                        @foreach($row['months'] as $m)
                        <td style="text-align:right; white-space:nowrap; font-size:13px;">
                            @if($m['amount'] !== null)
                                <span style="color:var(--text); font-weight:600;">$ {{ number_format($m['amount'], 0, ',', '.') }}</span>
                                @if($m['change'] !== null)
                                    <div style="font-size:10px; font-weight:700; color:{{ $m['change'] > 0 ? 'var(--expense)' : ($m['change'] < 0 ? 'var(--income)' : 'var(--muted)') }};">
                                        {{ $m['change'] > 0 ? '+' : '' }}{{ $m['change'] }}%
                                    </div>
                                @endif
                            @else
                                <span style="color:var(--muted);">—</span>
                            @endif
                        </td>
                        @endforeach
                        <td style="text-align:right;">
                            @if($lastChange !== null)
                                <span style="display:inline-block; padding:3px 8px; border-radius:5px; font-size:11px; font-weight:700;
                                    background:{{ $lastChange > 5 ? 'rgba(240,64,96,0.12)' : ($lastChange < -5 ? 'rgba(45,216,112,0.12)' : 'rgba(106,102,118,0.15)') }};
                                    color:{{ $lastChange > 5 ? 'var(--expense)' : ($lastChange < -5 ? 'var(--income)' : 'var(--muted)') }};">
                                    {{ $lastChange > 0 ? '+' : '' }}{{ $lastChange }}%
                                </span>
                            @else
                                <span style="color:var(--muted); font-size:12px;">—</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

{{-- ── Row 5: Previsión de cuotas ─────────────────────────────────────────── --}}
@if($installmentForecast->isNotEmpty())
<div style="margin-bottom:20px;">
    <div class="card" style="padding:0; overflow:hidden;">
        <div style="padding:20px 24px 16px; border-bottom:1px solid var(--border);">
            <h2 class="font-display" style="font-size:15px; font-weight:700; letter-spacing:-0.01em;">Previsión de cuotas</h2>
            <div style="font-size:12px; color:var(--muted); margin-top:2px;">
                Cuotas pendientes · próximos 12 meses ·
                <strong style="color:var(--accent);">Total: $ {{ number_format($installmentForecast->sum('total'), 0, ',', '.') }}</strong>
            </div>
        </div>

        <div class="rpt-tbl-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Mes</th>
                    <th style="text-align:center;">Cuotas</th>
                    <th style="text-align:right;">Total del mes</th>
                    <th class="rpt-col-hide-md">Detalle</th>
                </tr>
            </thead>
            <tbody>
                @foreach($installmentForecast as $slot)
                <tr style="{{ $slot['is_current'] ? 'background: rgba(240,160,48,0.04);' : '' }}">
                    <td style="font-weight:700; font-size:13px; white-space:nowrap;">
                        {{ $slot['label'] }}
                        @if($slot['is_current'])
                            <span style="margin-left:6px; font-size:10px; background:rgba(240,160,48,0.15); color:var(--accent); padding:2px 6px; border-radius:4px; font-weight:700;">HOY</span>
                        @endif
                    </td>
                    <td style="text-align:center; font-size:13px; color:var(--muted); font-weight:600;">{{ $slot['count'] }}</td>
                    <td style="text-align:right; font-weight:800; font-size:14px; color:var(--expense); white-space:nowrap;">
                        $ {{ number_format($slot['total'], 0, ',', '.') }}
                    </td>
                    <td class="rpt-col-hide-md" style="font-size:12px; color:var(--muted);">
                        @foreach($slot['items'] as $inst)
                            <span style="display:inline-block; margin-right:8px; white-space:nowrap;">
                                <span style="color:var(--text); font-weight:600;">{{ Str::limit($inst['description'], 22) }}</span>
                                <span style="color:var(--muted);">({{ $inst['number'] }}/{{ $inst['of'] }})</span>
                                <span style="color:var(--expense);">$&nbsp;{{ number_format($inst['amount'], 0, ',', '.') }}</span>
                            </span>
                        @endforeach
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="border-top:2px solid var(--border);">
                    <td colspan="2" style="font-size:12px; color:var(--muted); font-weight:700; padding:14px 20px;">TOTAL COMPROMETIDO</td>
                    <td style="text-align:right; font-weight:800; font-size:15px; color:var(--accent); padding:14px 20px; white-space:nowrap;">
                        $ {{ number_format($installmentForecast->sum('total'), 0, ',', '.') }}
                    </td>
                    <td class="rpt-col-hide-md"></td>
                </tr>
            </tfoot>
        </table>
        </div>
    </div>
</div>
@endif

{{-- ── Chart.js setup ───────────────────────────────────────────────────── --}}
<script>
(function () {
    // ── Global defaults ──────────────────────────────────────────────────────
    Chart.defaults.color          = '#6a6676';
    Chart.defaults.borderColor    = '#282834';
    Chart.defaults.font.family    = "'Nunito', sans-serif";
    Chart.defaults.font.size      = 12;

    const C = {
        income:  '#2dd870',
        expense: '#f04060',
        balance: '#4e9bff',
        accent:  '#f0a030',
        warn:    '#e8b840',
        muted:   '#6a6676',
        border:  '#282834',
        surface: '#17171d',
        cats: ['#f0a030','#f04060','#2dd870','#4e9bff','#e8b840','#a855f7','#06b6d4','#f97316','#84cc16','#ec4899'],
    };

    const gridOpts = {
        color: 'rgba(40,40,52,0.8)',
        drawBorder: false,
    };

    const tooltipOpts = {
        backgroundColor: '#111115',
        borderColor: '#282834',
        borderWidth: 1,
        titleColor: '#eeebe4',
        bodyColor: '#6a6676',
        padding: 12,
        cornerRadius: 8,
        callbacks: {
            label: ctx => ' $ ' + ctx.parsed.y.toLocaleString('es-AR', { minimumFractionDigits: 0 }),
        },
    };

    // ── Monthly bar chart ────────────────────────────────────────────────────
    new Chart(document.getElementById('monthlyChart'), {
        type: 'bar',
        data: {
            labels: {!! $jsMonthLabels !!},
            datasets: [
                {
                    label: 'Ingresos',
                    data: {!! $jsIncome !!},
                    backgroundColor: 'rgba(45,216,112,0.75)',
                    borderRadius: 5,
                    borderSkipped: false,
                },
                {
                    label: 'Gastos',
                    data: {!! $jsExpense !!},
                    backgroundColor: 'rgba(240,64,96,0.75)',
                    borderRadius: 5,
                    borderSkipped: false,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: tooltipOpts },
            scales: {
                x: { grid: gridOpts, ticks: { color: C.muted } },
                y: {
                    grid: gridOpts,
                    ticks: {
                        color: C.muted,
                        callback: v => '$ ' + v.toLocaleString('es-AR'),
                    },
                },
            },
        },
    });

    // ── Balance line chart ───────────────────────────────────────────────────
    const balanceData = {!! $jsBalance !!};
    new Chart(document.getElementById('balanceChart'), {
        type: 'line',
        data: {
            labels: {!! $jsMonthLabels !!},
            datasets: [{
                label: 'Balance',
                data: balanceData,
                borderColor: C.balance,
                backgroundColor: ctx => {
                    const g = ctx.chart.ctx.createLinearGradient(0, 0, 0, 240);
                    g.addColorStop(0, 'rgba(78,155,255,0.25)');
                    g.addColorStop(1, 'rgba(78,155,255,0)');
                    return g;
                },
                borderWidth: 2.5,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: balanceData.map(v => v >= 0 ? C.income : C.expense),
                pointBorderColor: '#111115',
                pointBorderWidth: 2,
                pointRadius: 5,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    ...tooltipOpts,
                    callbacks: {
                        label: ctx => {
                            const v = ctx.parsed.y;
                            return ' $ ' + v.toLocaleString('es-AR', { minimumFractionDigits: 0 });
                        },
                    },
                },
            },
            scales: {
                x: { grid: gridOpts, ticks: { color: C.muted } },
                y: {
                    grid: gridOpts,
                    ticks: { color: C.muted, callback: v => '$ ' + v.toLocaleString('es-AR') },
                },
            },
        },
    });

    // ── Category donut ───────────────────────────────────────────────────────
    @if($expensesByCategory->isNotEmpty())
    const catLabels = {!! $jsCatLabels !!};
    const catValues = {!! $jsCatValues !!};
    const catColors = C.cats.slice(0, catLabels.length);

    new Chart(document.getElementById('categoryChart'), {
        type: 'doughnut',
        data: {
            labels: catLabels,
            datasets: [{
                data: catValues,
                backgroundColor: catColors,
                borderColor: '#111115',
                borderWidth: 3,
                hoverOffset: 6,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '68%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#111115',
                    borderColor: '#282834',
                    borderWidth: 1,
                    titleColor: '#eeebe4',
                    bodyColor: '#6a6676',
                    padding: 12,
                    cornerRadius: 8,
                    callbacks: {
                        label: ctx => ' $ ' + ctx.parsed.toLocaleString('es-AR', { minimumFractionDigits: 0 }),
                    },
                },
            },
        },
    });

    // Render custom legend
    const legendEl = document.getElementById('category-legend');
    const total = catValues.reduce((a, b) => a + b, 0);
    catLabels.forEach((label, i) => {
        const pct = total > 0 ? Math.round((catValues[i] / total) * 100) : 0;
        legendEl.innerHTML += `
            <div style="display:flex; align-items:center; justify-content:space-between; font-size:12px;">
                <div style="display:flex; align-items:center; gap:7px;">
                    <div style="width:8px; height:8px; border-radius:50%; background:${catColors[i]}; flex-shrink:0;"></div>
                    <span style="color:#eeebe4; font-weight:600;">${label}</span>
                </div>
                <div style="display:flex; align-items:center; gap:10px;">
                    <span style="color:#6a6676; font-size:11px;">${pct}%</span>
                    <span style="color:#f04060; font-weight:700;">$ ${catValues[i].toLocaleString('es-AR', {minimumFractionDigits:0})}</span>
                </div>
            </div>`;
    });
    @endif

    // ── Daily spending bars ──────────────────────────────────────────────────
    const dailyData  = {!! $jsDailyValues !!};
    const todayIdx   = {{ now()->day - 1 }};
    new Chart(document.getElementById('dailyChart'), {
        type: 'bar',
        data: {
            labels: {!! $jsDailyLabels !!},
            datasets: [{
                label: 'Gasto',
                data: dailyData,
                backgroundColor: dailyData.map((_, i) => i === todayIdx ? C.accent : 'rgba(240,64,96,0.55)'),
                borderRadius: 4,
                borderSkipped: false,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: tooltipOpts },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { color: C.muted, maxRotation: 0, font: { size: 10 } },
                },
                y: {
                    grid: gridOpts,
                    ticks: { color: C.muted, callback: v => '$ ' + v.toLocaleString('es-AR') },
                },
            },
        },
    });

    // ── By member: barras Ingresos + Egresos ─────────────────────────────────
    @if($byMember->count() > 1)
    new Chart(document.getElementById('memberChart'), {
        type: 'bar',
        data: {
            labels: {!! $byMember->keys()->toJson() !!},
            datasets: [
                {
                    label: 'Ingresos',
                    data: {!! $byMember->pluck('income')->toJson() !!},
                    backgroundColor: 'rgba(45,216,112,0.85)',
                    borderRadius: 5,
                    borderSkipped: false,
                },
                {
                    label: 'Egresos',
                    data: {!! $byMember->pluck('expense')->toJson() !!},
                    backgroundColor: 'rgba(240,64,96,0.85)',
                    borderRadius: 5,
                    borderSkipped: false,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { padding: 10, boxWidth: 10, boxHeight: 10, usePointStyle: true, font: { size: 11 } } },
                tooltip: {
                    backgroundColor: '#111115',
                    borderColor: '#282834',
                    borderWidth: 1,
                    padding: 10,
                    cornerRadius: 8,
                    callbacks: { label: ctx => ` ${ctx.dataset.label}: $ ${ctx.parsed.y.toLocaleString('es-AR')}` },
                },
            },
            scales: {
                x: { grid: { display: false } },
                y: {
                    grid: { color: C.border },
                    ticks: { callback: v => '$ ' + (v >= 1000 ? Math.round(v/1000) + 'k' : v) },
                },
            },
        },
    });
    @endif

    // ── Sparkline: tasa de ahorro por mes ────────────────────────────────────
    (function(){
        const canvas = document.getElementById('savingsSpark');
        if (!canvas) return;
        const data = @json($monthlyData->values());
        if (!data.length) return;
        const numeric = data.map(m => m.rate === null ? 0 : m.rate);
        new Chart(canvas.getContext('2d'), {
            type: 'line',
            data: {
                labels: data.map(m => m.label),
                datasets: [{
                    data: numeric,
                    borderColor: 'rgba(240,160,48,0.9)',
                    backgroundColor: 'rgba(240,160,48,0.15)',
                    borderWidth: 1.8,
                    pointRadius: numeric.map((_, i) => i === numeric.length - 1 ? 3 : 0),
                    pointBackgroundColor: 'rgba(240,160,48,1)',
                    fill: true,
                    tension: 0.35,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => ` ${ctx.parsed.y}%`,
                            title: ctx => ctx[0].label,
                        },
                    },
                },
                scales: {
                    x: { display: false },
                    y: { display: false, grace: '10%' },
                },
            },
        });
    })();

})();
</script>

@endsection
