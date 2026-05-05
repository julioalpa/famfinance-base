<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
/* ─────────────────────────────────────────────────────────────────────────
   dompdf-safe stylesheet
   Rules:  no flex, no grid, no box-shadow, no gap
   Layout: <table> for columns, floats with clearfix where needed
   ───────────────────────────────────────────────────────────────────────── */

* { box-sizing: border-box; margin: 0; padding: 0; }

body {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 10px;
    color: #eeebe4;
    background: #09090b;
    padding: 26px 30px 20px;
    line-height: 1.45;
}

/* ── Layout helpers ──────────────────────────────────────────────────────── */
.layout-table { width: 100%; border-collapse: separate; border-spacing: 10px 0; }
.layout-table td { vertical-align: top; }
.clearfix::after { content: ''; display: table; clear: both; }

/* ── Section header ──────────────────────────────────────────────────────── */
.section-title {
    font-size: 8px;
    font-weight: 700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: #6a6676;
    margin-bottom: 8px;
    margin-top: 16px;
    padding-bottom: 5px;
    border-bottom: 1px solid #282834;
}

/* ── Cards ───────────────────────────────────────────────────────────────── */
.card {
    background: #111115;
    border: 1px solid #282834;
    border-radius: 6px;
    padding: 12px 14px;
}

/* ── Stat cards ──────────────────────────────────────────────────────────── */
.stat-card {
    background: #111115;
    border: 1px solid #282834;
    border-radius: 6px;
    border-left-width: 3px;
    padding: 12px 14px;
}
.stat-card.income  { border-left-color: #2dd870; }
.stat-card.expense { border-left-color: #f04060; }
.stat-card.balance { border-left-color: #4e9bff; }
.stat-card.savings { border-left-color: #f0a030; }

.stat-label { font-size: 7.5px; letter-spacing: 0.1em; text-transform: uppercase; color: #6a6676; font-weight: 700; margin-bottom: 5px; }
.stat-value { font-size: 19px; font-weight: 800; line-height: 1.1; }
.stat-delta {
    font-size: 7.5px; font-weight: 700; margin-top: 5px;
    display: inline-block; padding: 2px 6px; border-radius: 10px;
}
.stat-sub { font-size: 8px; color: #6a6676; margin-top: 3px; }

/* ── Color helpers ───────────────────────────────────────────────────────── */
.c-income  { color: #2dd870; }
.c-expense { color: #f04060; }
.c-accent  { color: #f0a030; }
.c-accent2 { color: #4e9bff; }
.c-warn    { color: #e8b840; }
.c-muted   { color: #6a6676; }
.c-text    { color: #eeebe4; }
.bg-income  { background: rgba(45,216,112,0.12); }
.bg-expense { background: rgba(240,64,96,0.12); }
.bg-accent2 { background: rgba(78,155,255,0.12); }
.bg-warn    { background: rgba(232,184,64,0.12); }

/* ── Category rows ───────────────────────────────────────────────────────── */
.cat-table { width: 100%; border-collapse: collapse; }
.cat-table td { padding: 5px 4px; border-bottom: 1px solid #1e1e26; vertical-align: middle; }
.cat-table tr:last-child td { border-bottom: none; }
.cat-rank   { font-size: 8.5px; color: #6a6676; font-weight: 700; width: 16px; }
.cat-name   { font-size: 9.5px; font-weight: 600; color: #eeebe4; }
.cat-pct    { font-size: 8.5px; color: #6a6676; text-align: right; width: 30px; }
.cat-bar-td { width: 80px; padding: 0 6px; }
.cat-bar-bg { width: 80px; height: 5px; background: #1e1e26; border-radius: 3px; }
.cat-bar-fill { height: 5px; border-radius: 3px; }
.cat-amount { font-size: 10px; font-weight: 800; color: #f04060; text-align: right; white-space: nowrap; width: 70px; }

/* ── Chart images ────────────────────────────────────────────────────────── */
.chart-img { width: 100%; border-radius: 4px; display: block; }

/* ── Progress bar ────────────────────────────────────────────────────────── */
.pbar-bg   { background: #1e1e26; border-radius: 3px; height: 5px; width: 100%; margin: 5px 0; }
.pbar-fill { height: 5px; border-radius: 3px; }

/* ── Rec rows ────────────────────────────────────────────────────────────── */
.rec-table { width: 100%; border-collapse: collapse; }
.rec-table td { padding: 5px 4px; border-bottom: 1px solid #1e1e26; vertical-align: middle; }
.rec-table tr:last-child td { border-bottom: none; }
.rec-dot  { width: 7px; height: 7px; border-radius: 50%; display: inline-block; vertical-align: middle; }
.rec-name { font-size: 9.5px; font-weight: 600; color: #eeebe4; }
.rec-badge { font-size: 7px; font-weight: 700; padding: 2px 6px; border-radius: 10px; letter-spacing: 0.05em; text-transform: uppercase; white-space: nowrap; }
.rec-amount { font-size: 10px; font-weight: 800; text-align: right; white-space: nowrap; }

/* ── Installment rows ────────────────────────────────────────────────────── */
.inst-table { width: 100%; border-collapse: collapse; }
.inst-table td { padding: 5px 4px; border-bottom: 1px solid #1e1e26; vertical-align: middle; }
.inst-table tr:last-child td { border-bottom: none; }
.inst-dot  { width: 7px; height: 7px; border-radius: 50%; display: inline-block; vertical-align: middle; }
.inst-desc { font-size: 9.5px; font-weight: 600; color: #eeebe4; }
.inst-sub  { font-size: 8px; color: #6a6676; }
.inst-amt  { font-size: 10px; font-weight: 800; text-align: right; white-space: nowrap; }

/* ── Forecast ────────────────────────────────────────────────────────────── */
.forecast-card {
    background: #111115;
    border: 1px solid #3d2f0a;
    border-radius: 6px;
    padding: 12px 14px;
}
.fcast-table { width: 100%; border-collapse: collapse; }
.fcast-table td { padding: 5px 0; border-bottom: 1px solid #1e1e26; font-size: 9.5px; vertical-align: middle; }
.fcast-table tr:last-child td { border-bottom: none; }
.fcast-label { color: #6a6676; font-weight: 600; }
.fcast-val   { text-align: right; font-weight: 800; }

/* ── Accounts ────────────────────────────────────────────────────────────── */
.acc-type { font-size: 7.5px; letter-spacing: 0.1em; text-transform: uppercase; color: #6a6676; font-weight: 700; padding: 7px 0 4px; }
.acc-table { width: 100%; border-collapse: collapse; }
.acc-table td { padding: 5px 8px; background: #17171d; border-radius: 4px; font-size: 9.5px; }
.acc-table tr { margin-bottom: 3px; }

/* ── Net worth ───────────────────────────────────────────────────────────── */
.nw-table { width: 100%; border-collapse: collapse; }
.nw-table td { padding: 8px 0; border-bottom: 1px solid #282834; vertical-align: middle; }
.nw-table tr:last-child td { border-bottom: none; padding-top: 12px; }
.nw-label { font-size: 8.5px; color: #6a6676; text-transform: uppercase; letter-spacing: 0.08em; font-weight: 700; }
.nw-val   { font-size: 16px; font-weight: 800; text-align: right; }

/* ── Gastos hormiga summary ──────────────────────────────────────────────── */
.ant-card {
    background: #17171d;
    border: 1px solid #282834;
    border-radius: 6px;
    padding: 12px 14px;
}

/* ── Footer ──────────────────────────────────────────────────────────────── */
.pdf-footer {
    margin-top: 20px;
    padding-top: 8px;
    border-top: 1px solid #282834;
    font-size: 8px;
    color: #6a6676;
}
.footer-left  { float: left; }
.footer-right { float: right; }
</style>
</head>
<body>

{{-- ══════════════════════════════════════════════════════════
     HEADER
     ══════════════════════════════════════════════════════════ --}}
<table width="100%" style="border-collapse:collapse;margin-bottom:18px;padding-bottom:14px;border-bottom:2px solid #282834;">
    <tr>
        <td>
            <div style="font-size:20px;font-weight:800;color:#eeebe4;letter-spacing:-0.03em;">
                <span style="color:#f0a030;">fam</span>finance
            </div>
            <div style="font-size:9px;color:#6a6676;margin-top:3px;">{{ $group->name }}</div>
        </td>
        <td style="text-align:right;">
            <div style="font-size:15px;font-weight:800;color:#f0a030;">Balance {{ $monthLabel }}</div>
            @if($isCurrentMonth)
                <div style="font-size:8.5px;color:#f0a030;margin-top:2px;">Mes en curso — parcial al día {{ now()->day }}</div>
            @endif
            <div style="font-size:8.5px;color:#6a6676;margin-top:2px;">Generado el {{ now()->locale('es')->isoFormat('D [de] MMMM [de] YYYY') }}</div>
            @if($exchangeRate)
                <div style="font-size:8.5px;color:#6a6676;margin-top:1px;">TC: 1 USD = $ {{ number_format($exchangeRate->rate, 2, ',', '.') }} ARS</div>
            @endif
        </td>
    </tr>
</table>

{{-- ══════════════════════════════════════════════════════════
     1. RESUMEN — 4 columnas
     ══════════════════════════════════════════════════════════ --}}
<div class="section-title" style="margin-top:0;">Resumen del mes</div>
<table width="100%" style="border-collapse:separate;border-spacing:8px 0;margin-bottom:16px;">
    <tr>
        {{-- Ingresos --}}
        <td width="25%">
            <div class="stat-card income">
                <div class="stat-label">Ingresos</div>
                <div class="stat-value c-income">$ {{ number_format($totalIncome, 0, ',', '.') }}</div>
                @if($incomeVsPrev !== null)
                    <span class="stat-delta {{ $incomeVsPrev >= 0 ? 'bg-accent2 c-accent2' : 'bg-income c-income' }}">
                        {{ $incomeVsPrev >= 0 ? '▲' : '▼' }} {{ abs($incomeVsPrev) }}% vs mes ant.
                    </span>
                @endif
                @if($avgIncome > 0)
                    <div class="stat-sub">Prom. 3m: $ {{ number_format($avgIncome, 0, ',', '.') }}</div>
                @endif
            </div>
        </td>
        {{-- Egresos --}}
        <td width="25%">
            <div class="stat-card expense">
                <div class="stat-label">Egresos</div>
                <div class="stat-value c-expense">$ {{ number_format($totalExpense, 0, ',', '.') }}</div>
                @if($expenseVsPrev !== null)
                    <span class="stat-delta {{ $expenseVsPrev > 0 ? 'bg-expense c-expense' : 'bg-income c-income' }}">
                        {{ $expenseVsPrev > 0 ? '▲' : '▼' }} {{ abs($expenseVsPrev) }}% vs mes ant.
                    </span>
                @endif
                @if($expenseVsAvg !== null)
                    <span class="stat-delta {{ $expenseVsAvg > 20 ? 'bg-expense c-expense' : 'bg-accent2 c-accent2' }}" style="margin-left:3px;">
                        {{ $expenseVsAvg > 0 ? '▲' : '▼' }} {{ abs($expenseVsAvg) }}% vs prom.
                    </span>
                @endif
            </div>
        </td>
        {{-- Balance --}}
        <td width="25%">
            <div class="stat-card balance">
                <div class="stat-label">Balance neto</div>
                <div class="stat-value {{ $balance >= 0 ? 'c-accent2' : 'c-expense' }}">
                    {{ $balance >= 0 ? '+' : '' }}$ {{ number_format($balance, 0, ',', '.') }}
                </div>
                @if($prevBalance != 0)
                    <div class="stat-sub">Ant.: {{ $prevBalance >= 0 ? '+' : '' }}$ {{ number_format($prevBalance, 0, ',', '.') }}</div>
                @endif
            </div>
        </td>
        {{-- Tasa de ahorro --}}
        <td width="25%">
            <div class="stat-card savings">
                <div class="stat-label">Tasa de ahorro</div>
                <div class="stat-value {{ $savingsRate >= 0 ? 'c-accent' : 'c-expense' }}">{{ $savingsRate }}%</div>
                <div class="pbar-bg">
                    <div class="pbar-fill" style="width:{{ max(0,min(100,$savingsRate)) }}%;background:{{ $savingsRate>=20 ? '#2dd870' : ($savingsRate>=0 ? '#f0a030' : '#f04060') }};"></div>
                </div>
                @php $avgSavings = $avgIncome > 0 ? round((($avgIncome-$avgExpense)/$avgIncome)*100,1) : 0; @endphp
                <div class="stat-sub">Prom. 3m: {{ $avgSavings }}%</div>
            </div>
        </td>
    </tr>
</table>

{{-- ══════════════════════════════════════════════════════════
     2. GRÁFICOS — 2 columnas
     ══════════════════════════════════════════════════════════ --}}
@if(!empty($chartImages['monthly']) || !empty($chartImages['daily']))
<div class="section-title">Evolución</div>
<table width="100%" style="border-collapse:separate;border-spacing:8px 0;margin-bottom:16px;">
    <tr>
        @if(!empty($chartImages['monthly']))
        <td width="{{ !empty($chartImages['daily']) ? '50%' : '100%' }}">
            <div class="card" style="padding:10px;">
                <div style="font-size:8px;color:#6a6676;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;margin-bottom:7px;">Ingresos vs egresos — últimos 6 meses</div>
                <img src="{{ $chartImages['monthly'] }}" class="chart-img" alt="">
            </div>
        </td>
        @endif
        @if(!empty($chartImages['daily']))
        <td width="{{ !empty($chartImages['monthly']) ? '50%' : '100%' }}">
            <div class="card" style="padding:10px;">
                <div style="font-size:8px;color:#6a6676;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;margin-bottom:7px;">Gasto diario acumulado</div>
                <img src="{{ $chartImages['daily'] }}" class="chart-img" alt="">
            </div>
        </td>
        @endif
    </tr>
</table>
@endif

{{-- ══════════════════════════════════════════════════════════
     3. CATEGORÍAS — lista izq. + donut der.
     ══════════════════════════════════════════════════════════ --}}
<div class="section-title">Gastos por categoría</div>
<table width="100%" style="border-collapse:separate;border-spacing:8px 0;margin-bottom:16px;">
    <tr>
        {{-- Lista --}}
        <td>
            <div class="card">
                <table class="cat-table">
                    @forelse($byCategory as $idx => $cat)
                    <tr>
                        <td class="cat-rank">{{ $idx + 1 }}</td>
                        <td class="cat-name">{{ $cat['name'] }}</td>
                        <td class="cat-pct">{{ $cat['percent'] }}%</td>
                        <td class="cat-bar-td">
                            <div class="cat-bar-bg">
                                <div class="cat-bar-fill" style="width:{{ $cat['percent'] }}%;background:{{ $cat['color'] }};"></div>
                            </div>
                        </td>
                        <td class="cat-amount">$ {{ number_format($cat['total'], 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" style="color:#6a6676;font-size:9px;text-align:center;padding:14px 0;">Sin egresos este mes</td></tr>
                    @endforelse
                </table>
            </div>
        </td>
        {{-- Donut --}}
        @if(!empty($chartImages['cats']))
        <td width="180" style="vertical-align:top;">
            <div class="card" style="padding:10px;text-align:center;">
                <div style="font-size:8px;color:#6a6676;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;margin-bottom:8px;">Distribución</div>
                <img src="{{ $chartImages['cats'] }}" style="width:150px;height:150px;object-fit:contain;" alt="">
            </div>
        </td>
        @endif
    </tr>
</table>

{{-- ══════════════════════════════════════════════════════════
     4. GASTOS HORMIGA
     ══════════════════════════════════════════════════════════ --}}
<div class="section-title">Gastos hormiga — transacciones &lt; $ {{ number_format($antThreshold, 0, ',', '.') }}</div>
<div class="ant-card" style="margin-bottom:16px;">
    <table width="100%" style="border-collapse:collapse;">
        <tr>
            <td width="110" style="text-align:center;vertical-align:middle;border-right:1px solid #282834;padding-right:14px;">
                <div style="font-size:32px;font-weight:800;color:{{ $antScore > 30 ? '#f04060' : ($antScore > 15 ? '#e8b840' : '#2dd870') }};">{{ $antScore }}%</div>
                <div style="font-size:8px;color:#6a6676;margin-top:2px;text-transform:uppercase;letter-spacing:0.08em;">del total</div>
                <div style="font-size:9px;font-weight:800;color:#eeebe4;margin-top:6px;">$ {{ number_format($antTotal, 0, ',', '.') }}</div>
                <div style="font-size:8px;color:#6a6676;">{{ $antCount }} transacciones</div>
            </td>
            <td style="padding-left:14px;vertical-align:top;">
                @if($antScore > 30)
                    <div style="background:rgba(240,64,96,0.1);border:1px solid rgba(240,64,96,0.2);color:#f04060;padding:5px 10px;border-radius:5px;font-size:8.5px;font-weight:700;margin-bottom:10px;">
                        ⚠ Supera el 30% — revisá estos gastos frecuentes
                    </div>
                @elseif($antScore > 15)
                    <div style="background:rgba(232,184,64,0.1);border:1px solid rgba(232,184,64,0.2);color:#e8b840;padding:5px 10px;border-radius:5px;font-size:8.5px;font-weight:700;margin-bottom:10px;">
                        Moderado — vale la pena monitorear
                    </div>
                @else
                    <div style="background:rgba(45,216,112,0.1);border:1px solid rgba(45,216,112,0.2);color:#2dd870;padding:5px 10px;border-radius:5px;font-size:8.5px;font-weight:700;margin-bottom:10px;">
                        Bien controlado
                    </div>
                @endif
                @if($antByCategory->isNotEmpty())
                <table width="100%" style="border-collapse:collapse;">
                    @foreach($antByCategory->take(6)->chunk(2) as $chunk)
                    <tr>
                        @foreach($chunk as $ant)
                        <td width="50%" style="padding:3px 5px 3px 0;vertical-align:top;">
                            <div style="background:#111115;border:1px solid #282834;border-radius:5px;padding:7px 9px;">
                                <span style="font-size:9.5px;font-weight:700;color:#eeebe4;">{{ $ant['name'] }}</span>
                                <span style="font-size:10px;font-weight:800;color:#f04060;float:right;">$ {{ number_format($ant['total'], 0, ',', '.') }}</span>
                                <div style="clear:both;font-size:8px;color:#6a6676;margin-top:1px;">{{ $ant['count'] }} mov.</div>
                            </div>
                        </td>
                        @endforeach
                    </tr>
                    @endforeach
                </table>
                @endif
            </td>
        </tr>
    </table>
</div>

{{-- ══════════════════════════════════════════════════════════
     5. RECURRENTES + CUOTAS — 2 columnas
     ══════════════════════════════════════════════════════════ --}}
<div class="section-title">Compromisos del mes</div>
<table width="100%" style="border-collapse:separate;border-spacing:8px 0;margin-bottom:16px;">
    <tr>
        {{-- Recurrentes --}}
        <td width="50%" style="vertical-align:top;">
            <div class="card">
                <div style="font-size:10px;font-weight:800;color:#eeebe4;margin-bottom:2px;">Gastos recurrentes</div>
                <div style="font-size:8.5px;color:#6a6676;margin-bottom:8px;">{{ $confirmedRecurring->count() }}/{{ $allRecurring->count() }} confirmados</div>
                @if($allRecurring->count() > 0)
                <div class="pbar-bg" style="margin-bottom:10px;">
                    <div class="pbar-fill" style="background:#2dd870;width:{{ $allRecurring->count() > 0 ? round($confirmedRecurring->count()/$allRecurring->count()*100) : 0 }}%;"></div>
                </div>
                @endif
                <table class="rec-table">
                    @foreach($confirmedRecurring as $r)
                    <tr>
                        <td width="10"><span class="rec-dot" style="background:#2dd870;"></span></td>
                        <td class="rec-name">{{ $r->description }}</td>
                        <td><span class="rec-badge bg-income c-income">Ok</span></td>
                        <td class="rec-amount c-expense">{{ $r->currency }} $ {{ number_format($r->amount, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                    @foreach($skippedRecurring as $r)
                    <tr>
                        <td width="10"><span class="rec-dot" style="background:#e8b840;"></span></td>
                        <td class="rec-name">{{ $r->description }}</td>
                        <td><span class="rec-badge bg-warn c-warn">Omitido</span></td>
                        <td class="rec-amount c-muted">{{ $r->currency }} $ {{ number_format($r->amount, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                    @foreach($pendingRecurring as $r)
                    <tr>
                        <td width="10"><span class="rec-dot" style="background:#4e9bff;"></span></td>
                        <td class="rec-name">{{ $r->description }}</td>
                        <td><span class="rec-badge bg-accent2 c-accent2">Pendiente</span></td>
                        <td class="rec-amount c-muted">{{ $r->currency }} $ {{ number_format($r->amount, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                    @if($allRecurring->isEmpty())
                    <tr><td colspan="4" style="color:#6a6676;font-size:9px;text-align:center;padding:12px 0;">Sin gastos recurrentes activos</td></tr>
                    @endif
                </table>
                @if($confirmedRecurringTotal > 0)
                <table width="100%" style="border-collapse:collapse;margin-top:8px;padding-top:8px;border-top:1px solid #282834;">
                    <tr>
                        <td style="font-size:8.5px;color:#6a6676;">Total confirmados</td>
                        <td style="font-size:11px;font-weight:800;color:#f04060;text-align:right;">$ {{ number_format($confirmedRecurringTotal, 0, ',', '.') }}</td>
                    </tr>
                </table>
                @endif
            </div>
        </td>
        {{-- Cuotas --}}
        <td width="50%" style="vertical-align:top;">
            <div class="card">
                <div style="font-size:10px;font-weight:800;color:#eeebe4;margin-bottom:2px;">Cuotas</div>
                <div style="font-size:8.5px;color:#6a6676;margin-bottom:8px;">{{ $installments->count() }} cuota{{ $installments->count() !== 1 ? 's' : '' }} este mes</div>
                @if($installments->isNotEmpty())
                <table width="100%" style="border-collapse:collapse;margin-bottom:10px;">
                    <tr>
                        <td width="33%" style="text-align:center;">
                            <div style="font-size:7.5px;color:#6a6676;font-weight:700;text-transform:uppercase;letter-spacing:0.07em;">Total</div>
                            <div style="font-size:14px;font-weight:800;color:#f04060;">$ {{ number_format($installmentTotal,0,',','.') }}</div>
                        </td>
                        <td width="33%" style="text-align:center;border-left:1px solid #282834;border-right:1px solid #282834;">
                            <div style="font-size:7.5px;color:#6a6676;font-weight:700;text-transform:uppercase;letter-spacing:0.07em;">Pagadas</div>
                            <div style="font-size:14px;font-weight:800;color:#2dd870;">$ {{ number_format($paidInstallmentTotal,0,',','.') }}</div>
                        </td>
                        <td width="33%" style="text-align:center;">
                            <div style="font-size:7.5px;color:#6a6676;font-weight:700;text-transform:uppercase;letter-spacing:0.07em;">Pendiente</div>
                            <div style="font-size:14px;font-weight:800;color:#e8b840;">$ {{ number_format($installmentTotal-$paidInstallmentTotal,0,',','.') }}</div>
                        </td>
                    </tr>
                </table>
                <table class="inst-table">
                    @foreach($installments->take(12) as $inst)
                    <tr>
                        <td width="12"><span class="inst-dot" style="background:{{ $inst->is_paid ? '#2dd870' : '#e8b840' }};"></span></td>
                        <td>
                            <div class="inst-desc">{{ Str::limit($inst->transaction?->description ?? 'Sin descripción', 30) }}</div>
                            <div class="inst-sub">{{ $inst->account?->name }} · {{ $inst->installment_number }}/{{ $inst->transaction?->installments_count ?? '?' }}</div>
                        </td>
                        <td class="inst-amt" style="color:{{ $inst->is_paid ? '#6a6676' : '#f04060' }};">$ {{ number_format($inst->amount,0,',','.') }}</td>
                    </tr>
                    @endforeach
                    @if($installments->count() > 12)
                    <tr><td colspan="3" style="font-size:8.5px;color:#6a6676;text-align:center;padding-top:5px;">... y {{ $installments->count()-12 }} más</td></tr>
                    @endif
                </table>
                @else
                <div style="color:#6a6676;font-size:9px;text-align:center;padding:14px 0;">Sin cuotas este mes</div>
                @endif
            </div>
        </td>
    </tr>
</table>

{{-- ══════════════════════════════════════════════════════════
     6. PAGOS DEL MES
     ══════════════════════════════════════════════════════════ --}}
@if($paymentItemsWithStatus->isNotEmpty())
@php
    $totalPayments = $paymentItemsWithStatus->count();
    $paidCount     = $paidPaymentsItems->count();
    $pendingAmt    = $paymentItemsWithStatus->where('is_paid', false)->sum('amount_ars');
@endphp
<div class="section-title">Pagos del mes — gastos fijos</div>
<div class="card" style="margin-bottom:16px;">
    <table width="100%" style="border-collapse:separate;border-spacing:8px 0;margin-bottom:10px;">
        <tr>
            <td width="33%" style="text-align:center;">
                <div style="font-size:7.5px;color:#6a6676;font-weight:700;text-transform:uppercase;letter-spacing:0.07em;">Pagados</div>
                <div style="font-size:14px;font-weight:800;color:#2dd870;">{{ $paidCount }} / {{ $totalPayments }}</div>
            </td>
            @if($paidPaymentsTotal > 0)
            <td width="33%" style="text-align:center;border-left:1px solid #282834;border-right:1px solid #282834;">
                <div style="font-size:7.5px;color:#6a6676;font-weight:700;text-transform:uppercase;letter-spacing:0.07em;">Abonado</div>
                <div style="font-size:14px;font-weight:800;color:#2dd870;">$ {{ number_format($paidPaymentsTotal,0,',','.') }}</div>
            </td>
            @endif
            @if($pendingAmt > 0)
            <td width="33%" style="text-align:center;">
                <div style="font-size:7.5px;color:#6a6676;font-weight:700;text-transform:uppercase;letter-spacing:0.07em;">Pendiente</div>
                <div style="font-size:14px;font-weight:800;color:#e8b840;">$ {{ number_format($pendingAmt,0,',','.') }}</div>
            </td>
            @endif
        </tr>
    </table>
    <div class="pbar-bg" style="margin-bottom:10px;">
        <div class="pbar-fill" style="background:#2dd870;width:{{ $totalPayments > 0 ? round($paidCount/$totalPayments*100) : 0 }}%;"></div>
    </div>
    <table width="100%" style="border-collapse:collapse;">
        <tr style="background:#17171d;">
            <td width="25" style="padding:4px 6px;font-size:8px;color:#6a6676;font-weight:700;text-transform:uppercase;">Día</td>
            <td style="padding:4px 6px;font-size:8px;color:#6a6676;font-weight:700;text-transform:uppercase;">Concepto</td>
            <td width="60" style="padding:4px 6px;font-size:8px;color:#6a6676;font-weight:700;text-transform:uppercase;text-align:center;">Estado</td>
            <td width="75" style="padding:4px 6px;font-size:8px;color:#6a6676;font-weight:700;text-transform:uppercase;text-align:right;">Importe</td>
        </tr>
        @foreach($paymentItemsWithStatus as $entry)
        @php $item = $entry['item']; @endphp
        <tr style="border-bottom:1px solid #1e1e26;">
            <td style="padding:5px 6px;font-size:9.5px;font-weight:800;color:#6a6676;text-align:center;">{{ $item->day_of_month }}</td>
            <td style="padding:5px 6px;">
                <div style="font-size:9.5px;font-weight:600;color:#eeebe4;">{{ $item->description }}</div>
                <div style="font-size:8px;color:#6a6676;">{{ $item->account?->name ?? '—' }}{{ $item->category ? ' · '.$item->category->name : '' }}</div>
            </td>
            <td style="padding:5px 6px;text-align:center;">
                <span style="font-size:7.5px;font-weight:700;padding:2px 6px;border-radius:10px;{{ $entry['is_paid'] ? 'background:rgba(45,216,112,0.12);color:#2dd870' : 'background:rgba(78,155,255,0.12);color:#4e9bff' }};">
                    {{ $entry['is_paid'] ? 'Pagado' : 'Pendiente' }}
                </span>
            </td>
            <td style="padding:5px 6px;font-size:10px;font-weight:800;text-align:right;color:{{ $entry['is_paid'] ? '#6a6676' : '#f04060' }};">
                @if($entry['amount'] !== null)
                    {{ $item->currency }} $ {{ number_format($entry['amount'],0,',','.') }}
                @else
                    —
                @endif
            </td>
        </tr>
        @endforeach
    </table>
</div>
@endif

{{-- ══════════════════════════════════════════════════════════
     7. PREVISIÓN — solo mes en curso
     ══════════════════════════════════════════════════════════ --}}
@if($isCurrentMonth && $forecast)
<div class="section-title">Previsión de cierre del mes</div>
<div class="forecast-card" style="margin-bottom:16px;">
    <div style="font-size:8.5px;color:#6a6676;margin-bottom:4px;">
        Avance: día {{ $forecast['days_passed'] }} de {{ $forecast['days_in_month'] }} — quedan {{ $forecast['days_remaining'] }} días
    </div>
    <div class="pbar-bg" style="margin-bottom:12px;">
        <div class="pbar-fill" style="width:{{ $forecast['progress_pct'] }}%;background:#f0a030;"></div>
    </div>
    <table width="100%" style="border-collapse:separate;border-spacing:8px 0;margin-bottom:12px;">
        <tr>
            <td width="33%" style="text-align:center;background:#17171d;border-radius:5px;padding:9px;">
                <div style="font-size:7.5px;color:#6a6676;font-weight:700;text-transform:uppercase;letter-spacing:0.07em;margin-bottom:4px;">Gasto diario</div>
                <div style="font-size:15px;font-weight:800;color:#f04060;">$ {{ number_format($forecast['daily_avg'],0,',','.') }}</div>
            </td>
            <td width="33%" style="text-align:center;background:#17171d;border-radius:5px;padding:9px;">
                <div style="font-size:7.5px;color:#6a6676;font-weight:700;text-transform:uppercase;letter-spacing:0.07em;margin-bottom:4px;">Proyectado</div>
                <div style="font-size:15px;font-weight:800;color:#f04060;">$ {{ number_format($forecast['projected_expense'],0,',','.') }}</div>
            </td>
            <td width="33%" style="text-align:center;background:#17171d;border-radius:5px;padding:9px;">
                <div style="font-size:7.5px;color:#6a6676;font-weight:700;text-transform:uppercase;letter-spacing:0.07em;margin-bottom:4px;">Total c/compromisos</div>
                <div style="font-size:15px;font-weight:800;color:#e8b840;">$ {{ number_format($forecast['projected_total'],0,',','.') }}</div>
            </td>
        </tr>
    </table>
    <div style="background:#17171d;border-radius:5px;padding:10px 12px;">
        <table class="fcast-table">
            <tr>
                <td class="fcast-label">Gasto hasta hoy</td>
                <td class="fcast-val c-expense">$ {{ number_format($totalExpense,0,',','.') }}</td>
            </tr>
            @if($forecast['pending_recurring'] > 0)
            <tr>
                <td class="fcast-label">Recurrentes pendientes</td>
                <td class="fcast-val c-warn">+ $ {{ number_format($forecast['pending_recurring'],0,',','.') }}</td>
            </tr>
            @endif
            @if($forecast['pending_installments'] > 0)
            <tr>
                <td class="fcast-label">Cuotas pendientes</td>
                <td class="fcast-val c-warn">+ $ {{ number_format($forecast['pending_installments'],0,',','.') }}</td>
            </tr>
            @endif
            <tr style="border-top:1px solid #282834;">
                <td style="padding-top:8px;font-size:10px;font-weight:700;color:#eeebe4;">Balance proyectado al cierre</td>
                <td style="padding-top:8px;font-size:15px;font-weight:800;text-align:right;color:{{ $forecast['projected_balance']>=0 ? '#2dd870' : '#f04060' }};">
                    {{ $forecast['projected_balance']>=0 ? '+' : '' }}$ {{ number_format($forecast['projected_balance'],0,',','.') }}
                </td>
            </tr>
        </table>
        <div style="font-size:7.5px;color:#6a6676;margin-top:5px;">* Ingreso estimado en base al promedio de los últimos 3 meses: $ {{ number_format($forecast['avg_income'],0,',','.') }}</div>
    </div>
</div>
@endif

{{-- ══════════════════════════════════════════════════════════
     7. CUENTAS + PATRIMONIO
     ══════════════════════════════════════════════════════════ --}}
<div class="section-title">Patrimonio</div>
<table width="100%" style="border-collapse:separate;border-spacing:8px 0;margin-bottom:16px;">
    <tr>
        {{-- Cuentas --}}
        <td width="50%" style="vertical-align:top;">
            <div class="card">
                <div style="font-size:10px;font-weight:800;color:#eeebe4;margin-bottom:8px;">Cuentas activas</div>
                @foreach($allAccounts->groupBy('type') as $type => $accounts)
                <div class="acc-type">{{ match($type) { 'cash'=>'Efectivo','digital'=>'Digital','credit'=>'Tarjetas','loan'=>'Préstamos',default=>$type } }}</div>
                @foreach($accounts as $acc)
                <table class="acc-table" style="margin-bottom:3px;">
                    <tr>
                        <td style="font-size:9.5px;font-weight:600;color:#eeebe4;">{{ $acc->name }}</td>
                        <td style="font-size:10px;font-weight:800;color:{{ $acc->isLiability() ? '#f04060' : '#2dd870' }};text-align:right;">
                            {{ $acc->currency }} $ {{ number_format(abs($acc->balance),0,',','.') }}
                        </td>
                    </tr>
                </table>
                @endforeach
                @endforeach
            </div>
        </td>
        {{-- Patrimonio neto --}}
        <td width="50%" style="vertical-align:top;">
            <div class="card">
                <div style="font-size:10px;font-weight:800;color:#eeebe4;margin-bottom:8px;">Patrimonio neto (ARS)</div>
                <table class="nw-table">
                    <tr>
                        <td class="nw-label">Activos</td>
                        <td class="nw-val c-income">$ {{ number_format($totalAssets,0,',','.') }}</td>
                    </tr>
                    <tr>
                        <td class="nw-label">Pasivos</td>
                        <td class="nw-val c-expense">$ {{ number_format($totalLiabilities,0,',','.') }}</td>
                    </tr>
                    <tr>
                        <td>
                            <div class="nw-label">Patrimonio neto</div>
                            @if($exchangeRate)
                                <div style="font-size:8px;color:#6a6676;margin-top:2px;">TC: 1 USD = $ {{ number_format($exchangeRate->rate,2,',','.') }}</div>
                            @endif
                        </td>
                        <td class="nw-val" style="font-size:20px;color:{{ $netWorth>=0 ? '#f0a030' : '#f04060' }};">
                            {{ $netWorth>=0 ? '+' : '' }}$ {{ number_format($netWorth,0,',','.') }}
                        </td>
                    </tr>
                </table>
            </div>
        </td>
    </tr>
</table>

{{-- Footer --}}
<div class="pdf-footer">
    <div class="footer-left">famfinance · Balance mensual · {{ $group->name }}</div>
    <div class="footer-right">{{ $monthLabel }} · {{ now()->locale('es')->isoFormat('D/M/YYYY') }}</div>
    <div style="clear:both;"></div>
</div>

</body>
</html>
