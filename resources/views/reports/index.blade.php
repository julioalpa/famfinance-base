@extends('layouts.app')

@section('title', 'Reportes')

@section('content')

{{-- Chart.js CDN --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>

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
<div style="display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:32px; flex-wrap:wrap; gap:16px;">
    <div>
        <h1 class="font-display" style="font-size:28px; font-weight:800; letter-spacing:-0.03em; margin-bottom:4px;">Reportes</h1>
        <div style="font-size:13px; color:var(--muted); font-weight:500;">
            Análisis financiero · {{ ucfirst($startDate->locale('es')->isoFormat('MMMM YYYY')) }} → hoy
        </div>
    </div>

    {{-- Period picker --}}
    <div style="display:flex; gap:6px; align-items:center; background:var(--surface); border:1px solid var(--border); border-radius:10px; padding:4px;">
        @foreach([3 => '3 meses', 6 => '6 meses', 12 => '12 meses'] as $val => $label)
            <a href="{{ route('reports.index', ['months' => $val]) }}"
               style="padding:7px 14px; border-radius:7px; font-size:13px; font-weight:700; text-decoration:none; transition:all 0.15s;
                      {{ $months === $val ? 'background:var(--accent); color:#0c0804;' : 'color:var(--muted);' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>
</div>

{{-- ── Summary stat cards ────────────────────────────────────────────────── --}}
<div style="display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:28px;">

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
        <div style="font-size:11px; color:var(--muted); margin-top:6px; font-weight:500;">del ingreso ahorrado</div>
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

{{-- ── Row 1: Monthly bars + Balance line ────────────────────────────────── --}}
<div style="display:grid; grid-template-columns:3fr 2fr; gap:20px; margin-bottom:20px;">

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
        <div style="position:relative; height:240px;">
            <canvas id="monthlyChart"></canvas>
        </div>
    </div>

    {{-- Balance mensual --}}
    <div class="card">
        <div style="margin-bottom:20px;">
            <h2 class="font-display" style="font-size:15px; font-weight:700; letter-spacing:-0.01em;">Balance mensual</h2>
            <div style="font-size:12px; color:var(--muted); margin-top:2px;">Ingreso − Gasto por mes</div>
        </div>
        <div style="position:relative; height:240px;">
            <canvas id="balanceChart"></canvas>
        </div>
    </div>
</div>

{{-- ── Row 2: Category donut + Daily spending ────────────────────────────── --}}
<div style="display:grid; grid-template-columns:2fr 3fr; gap:20px; margin-bottom:20px;">

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
            <div style="position:relative; height:200px; margin-bottom:16px;">
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
        <div style="position:relative; height:220px;">
            <canvas id="dailyChart"></canvas>
        </div>
    </div>
</div>

{{-- ── Row 3: Top categories table + By member ──────────────────────────── --}}
<div style="display:grid; grid-template-columns:{{ $byMember->count() > 1 ? '3fr 2fr' : '1fr' }}; gap:20px; margin-bottom:20px;">

    {{-- Top categorías --}}
    <div class="card" style="padding:0; overflow:hidden;">
        <div style="padding:20px 24px 16px; border-bottom:1px solid var(--border);">
            <h2 class="font-display" style="font-size:15px; font-weight:700; letter-spacing:-0.01em;">Top categorías de gasto</h2>
            <div style="font-size:12px; color:var(--muted); margin-top:2px;">Últimos {{ $months }} meses</div>
        </div>
        @if($expensesByCategory->isEmpty())
            <div style="text-align:center; padding:40px; color:var(--muted); font-size:13px;">Sin datos</div>
        @else
        @php $catTotal = $expensesByCategory->sum(); @endphp
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Categoría</th>
                    <th style="text-align:right;">Total</th>
                    <th style="text-align:right;">% del gasto</th>
                    <th style="width:120px;">Proporción</th>
                </tr>
            </thead>
            <tbody>
                @foreach($expensesByCategory as $cat => $amount)
                @php $pct = $catTotal > 0 ? round(($amount / $catTotal) * 100, 1) : 0; @endphp
                <tr>
                    <td style="color:var(--muted); font-size:12px; width:32px;">{{ $loop->iteration }}</td>
                    <td style="font-weight:600; font-size:13px;">{{ $cat }}</td>
                    <td style="text-align:right; font-weight:700; color:var(--expense); white-space:nowrap;">
                        $ {{ number_format($amount, 0, ',', '.') }}
                    </td>
                    <td style="text-align:right; font-size:13px; color:var(--muted); font-weight:600;">{{ $pct }}%</td>
                    <td style="padding-right:20px;">
                        <div style="height:5px; background:var(--surface2); border-radius:3px; overflow:hidden;">
                            <div style="height:100%; width:{{ $pct }}%; background:linear-gradient(90deg,var(--expense),rgba(240,64,96,0.5)); border-radius:3px;"></div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

    {{-- Por miembro --}}
    @if($byMember->count() > 1)
    <div class="card">
        <div style="margin-bottom:20px;">
            <h2 class="font-display" style="font-size:15px; font-weight:700; letter-spacing:-0.01em;">Gasto por integrante</h2>
            <div style="font-size:12px; color:var(--muted); margin-top:2px;">Últimos {{ $months }} meses</div>
        </div>
        <div style="position:relative; height:220px; margin-bottom:20px;">
            <canvas id="memberChart"></canvas>
        </div>
        @php
            $memberTotal = $byMember->sum();
            $memberColors = ['#f0a030','#4e9bff','#2dd870','#f04060','#e8b840','#a855f7'];
        @endphp
        @foreach($byMember as $name => $amount)
        @php $pct = $memberTotal > 0 ? round(($amount / $memberTotal) * 100, 1) : 0; @endphp
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px;">
            <div style="display:flex; align-items:center; gap:8px;">
                <div style="width:10px; height:10px; border-radius:50%; background:{{ $memberColors[$loop->index % count($memberColors)] }};"></div>
                <span style="font-size:13px; font-weight:600;">{{ $name }}</span>
            </div>
            <div style="text-align:right;">
                <div style="font-size:13px; font-weight:700; color:var(--expense);">$ {{ number_format($amount, 0, ',', '.') }}</div>
                <div style="font-size:11px; color:var(--muted);">{{ $pct }}%</div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

</div>

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

    // ── By member doughnut ───────────────────────────────────────────────────
    @if($byMember->count() > 1)
    new Chart(document.getElementById('memberChart'), {
        type: 'doughnut',
        data: {
            labels: {!! $byMember->keys()->toJson() !!},
            datasets: [{
                data: {!! $byMember->values()->toJson() !!},
                backgroundColor: C.cats.slice(0, {{ $byMember->count() }}),
                borderColor: '#111115',
                borderWidth: 3,
                hoverOffset: 6,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '65%',
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
    @endif

})();
</script>

@endsection
