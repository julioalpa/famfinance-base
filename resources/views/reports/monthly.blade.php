@extends('layouts.app')

@section('title', 'Balance Mensual')

@section('content')
<style>
/* ═══════════════════════════════════════════════════════════════════════════
   MONTHLY REPORT — Design System
   Principles: data-dense, dark, financial precision, orange accent
   ══════════════════════════════════════════════════════════════════════════ */

/* ── Sección wrapper ─────────────────────────────────────────────────────── */
.report-section {
    margin-bottom: 28px;
}

.section-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 14px;
}

.section-label {
    font-family: 'Bricolage Grotesque', sans-serif;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: var(--muted);
}

.section-line {
    flex: 1;
    height: 1px;
    background: var(--border);
}

/* ── Page header ─────────────────────────────────────────────────────────── */
.report-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 32px;
    flex-wrap: wrap;
}

.report-title {
    font-family: 'Bricolage Grotesque', sans-serif;
    font-size: 28px;
    font-weight: 800;
    letter-spacing: -0.04em;
    line-height: 1;
    color: var(--text);
}

.report-title .accent { color: var(--accent); }

.report-month-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-top: 8px;
    font-size: 13px;
    color: var(--muted);
    font-weight: 600;
}

.live-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    background: rgba(240,160,48,0.12);
    color: var(--accent);
    border: 1px solid rgba(240,160,48,0.2);
}

.live-dot {
    width: 5px;
    height: 5px;
    border-radius: 50%;
    background: var(--accent);
    animation: livepulse 2s ease-in-out infinite;
}

@keyframes livepulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.4; transform: scale(0.8); }
}

.month-controls {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

/* ── Botón PDF ───────────────────────────────────────────────────────────── */
.btn-pdf {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 9px 16px;
    border-radius: 9px;
    font-family: 'Nunito', sans-serif;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    border: 1px solid rgba(240,64,96,0.25);
    background: rgba(240,64,96,0.08);
    color: var(--expense);
    transition: all 0.2s ease;
    white-space: nowrap;
}

.btn-pdf:hover {
    background: rgba(240,64,96,0.16);
    border-color: rgba(240,64,96,0.4);
    transform: translateY(-1px);
}

.btn-pdf.loading { opacity: 0.5; pointer-events: none; }

/* ── Stat cards ──────────────────────────────────────────────────────────── */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 14px;
}

.stat-card-v2 {
    position: relative;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 20px 20px 18px;
    overflow: hidden;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.stat-card-v2:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 40px rgba(0,0,0,0.3);
}

/* Left colored accent bar */
.stat-card-v2::before {
    content: '';
    position: absolute;
    left: 0; top: 0; bottom: 0;
    width: 3px;
    border-radius: 14px 0 0 14px;
}

/* Subtle gradient bg overlay */
.stat-card-v2::after {
    content: '';
    position: absolute;
    inset: 0;
    border-radius: 14px;
    opacity: 0.04;
    pointer-events: none;
}

.stat-card-v2.income::before  { background: var(--income); }
.stat-card-v2.expense::before { background: var(--expense); }
.stat-card-v2.balance::before { background: var(--accent2); }
.stat-card-v2.savings::before { background: var(--accent); }

.stat-card-v2.income::after  { background: radial-gradient(ellipse at top left, var(--income), transparent 70%); }
.stat-card-v2.expense::after { background: radial-gradient(ellipse at top left, var(--expense), transparent 70%); }
.stat-card-v2.balance::after { background: radial-gradient(ellipse at top left, var(--accent2), transparent 70%); }
.stat-card-v2.savings::after { background: radial-gradient(ellipse at top left, var(--accent), transparent 70%); }

.stat-card-v2.income:hover  { border-color: rgba(45,216,112,0.3); }
.stat-card-v2.expense:hover { border-color: rgba(240,64,96,0.3); }
.stat-card-v2.balance:hover { border-color: rgba(78,155,255,0.3); }
.stat-card-v2.savings:hover { border-color: rgba(240,160,48,0.3); }

.stat-top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    margin-bottom: 12px;
}

.stat-icon {
    width: 34px;
    height: 34px;
    border-radius: 9px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.stat-card-v2.income  .stat-icon { background: rgba(45,216,112,0.12);  color: var(--income); }
.stat-card-v2.expense .stat-icon { background: rgba(240,64,96,0.12);   color: var(--expense); }
.stat-card-v2.balance .stat-icon { background: rgba(78,155,255,0.12);  color: var(--accent2); }
.stat-card-v2.savings .stat-icon { background: rgba(240,160,48,0.12);  color: var(--accent); }

.stat-label-v2 {
    font-size: 10px;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: var(--muted);
    font-weight: 800;
    font-family: 'Bricolage Grotesque', sans-serif;
}

.stat-value-v2 {
    font-family: 'Bricolage Grotesque', sans-serif;
    font-size: 27px;
    font-weight: 800;
    letter-spacing: -0.04em;
    line-height: 1.05;
    margin-bottom: 10px;
}

.stat-footer {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.delta-pill {
    display: inline-flex;
    align-items: center;
    gap: 3px;
    padding: 2px 8px;
    border-radius: 20px;
    font-size: 10px;
    font-weight: 800;
    width: fit-content;
}

.delta-bad  { background: rgba(240,64,96,0.12);  color: var(--expense); }
.delta-good { background: rgba(45,216,112,0.12); color: var(--income); }
.delta-neu  { background: rgba(78,155,255,0.12); color: var(--accent2); }

.stat-sub {
    font-size: 11px;
    color: var(--muted);
    font-weight: 600;
}

/* Savings rate mini progress */
.savings-bar-wrap {
    height: 4px;
    background: var(--surface3);
    border-radius: 2px;
    margin-top: 10px;
    overflow: hidden;
}

.savings-bar-fill {
    height: 100%;
    border-radius: 2px;
    transition: width 1s ease;
}

/* ── Charts row ──────────────────────────────────────────────────────────── */
.charts-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
}

.chart-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 20px 22px;
}

.chart-card-title {
    font-family: 'Bricolage Grotesque', sans-serif;
    font-size: 13px;
    font-weight: 700;
    color: var(--text);
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.chart-wrap {
    position: relative;
    height: 200px;
}

/* ── Category section ────────────────────────────────────────────────────── */
.cat-layout {
    display: grid;
    grid-template-columns: 1fr 280px;
    gap: 14px;
    align-items: start;
}

.cat-list-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 20px 22px;
}

.cat-donut-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 20px;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.cat-row-v2 {
    display: grid;
    grid-template-columns: 28px 1fr auto;
    align-items: center;
    gap: 12px;
    padding: 9px 0;
    border-bottom: 1px solid rgba(40,40,52,0.5);
}

.cat-row-v2:last-child { border-bottom: none; }

.cat-rank {
    font-family: 'Bricolage Grotesque', sans-serif;
    font-size: 11px;
    font-weight: 800;
    color: var(--muted);
    text-align: center;
}

.cat-body { min-width: 0; }

.cat-name-v2 {
    font-size: 13px;
    font-weight: 700;
    color: var(--text);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-bottom: 5px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.cat-bar-track {
    height: 4px;
    background: var(--surface3);
    border-radius: 2px;
    overflow: hidden;
}

.cat-bar-fill {
    height: 100%;
    border-radius: 2px;
    transition: width 0.8s cubic-bezier(0.4, 0, 0.2, 1);
}

.cat-right {
    text-align: right;
    flex-shrink: 0;
}

.cat-amount-v2 {
    font-family: 'Bricolage Grotesque', sans-serif;
    font-size: 14px;
    font-weight: 800;
    color: var(--expense);
    white-space: nowrap;
}

.cat-pct-v2 {
    font-size: 10px;
    color: var(--muted);
    font-weight: 700;
    margin-top: 1px;
}

/* ── Tabla combinada Ingresos / Egresos / Neto (categorías, etiquetas) ── */
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
    display: none;                         /* desktop: el header muestra las labels */
}
.cmb-cell-val   { font-size: 13px; font-weight: 800; color: var(--text); white-space: nowrap; }
.cmb-cell-val.pos { color: var(--income); }
.cmb-cell-val.neg { color: var(--expense); }
.cmb-cell-val.zero { color: var(--muted); font-weight: 700; }

/* Row 2-columns: nombre+bar a la izq, 3 números a la der */
.cmb-row {
    display: grid;
    grid-template-columns: 28px 1fr auto;
    align-items: center;
    gap: 14px;
    padding: 10px 0;
    border-bottom: 1px solid rgba(40,40,52,0.5);
}
.cmb-row:last-child { border-bottom: none; }
.cmb-row .cat-rank { align-self: center; }
.cmb-row .cat-body { align-self: center; }

/* Header row (labels visibles solo una vez, no en cada fila) */
.cmb-header {
    display: grid;
    grid-template-columns: 28px 1fr auto;
    align-items: end;
    gap: 14px;
    padding: 0 0 8px 0;
    border-bottom: 1px solid var(--border);
    margin-bottom: 4px;
}
.cmb-header-nums {
    display: grid;
    grid-template-columns: repeat(3, minmax(78px, 1fr));
    gap: 10px;
    text-align: right;
    font-family: 'Nunito', sans-serif;
    font-size: 9.5px;
    font-weight: 800;
    color: var(--muted);
    letter-spacing: 0.06em;
    text-transform: uppercase;
}

@media (max-width: 620px) {
    .cmb-nums {
        grid-template-columns: repeat(3, minmax(64px, auto));
        gap: 8px;
    }
    .cmb-cell-val { font-size: 12px; }
    .cmb-cell-label { display: block; }       /* mobile: mostrar labels por celda */
    .cmb-header { display: none; }            /* mobile: ocultar header (queda en las filas) */
    .cmb-row { gap: 10px; align-items: flex-start; padding: 12px 0; }
}

.donut-label-center {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    pointer-events: none;
}

.donut-center-val {
    font-family: 'Bricolage Grotesque', sans-serif;
    font-size: 18px;
    font-weight: 800;
    color: var(--text);
}

.donut-center-label {
    font-size: 9px;
    color: var(--muted);
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
}

/* ── Gastos hormiga ──────────────────────────────────────────────────────── */
.ant-layout {
    display: grid;
    grid-template-columns: auto 1fr;
    gap: 24px;
    align-items: start;
}

.ant-score-ring {
    flex-shrink: 0;
    position: relative;
    width: 120px;
    height: 120px;
}

.ant-score-ring svg {
    transform: rotate(-90deg);
}

.ant-ring-bg   { fill: none; stroke: var(--surface3); stroke-width: 8; }
.ant-ring-fill { fill: none; stroke-width: 8; stroke-linecap: round; transition: stroke-dashoffset 1.2s cubic-bezier(0.4, 0, 0.2, 1); }

.ant-ring-center {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.ant-ring-pct {
    font-family: 'Bricolage Grotesque', sans-serif;
    font-size: 24px;
    font-weight: 800;
    line-height: 1;
}

.ant-ring-sub {
    font-size: 9px;
    color: var(--muted);
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    margin-top: 2px;
}

.ant-right { min-width: 0; }

.ant-summary-row {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 14px;
    flex-wrap: wrap;
}

.ant-total-block { }
.ant-total-label { font-size: 10px; text-transform: uppercase; letter-spacing: 0.08em; color: var(--muted); font-weight: 700; }
.ant-total-val   { font-family: 'Bricolage Grotesque', sans-serif; font-size: 20px; font-weight: 800; color: var(--warn); }

.ant-alert {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 11px;
    font-weight: 700;
    margin-bottom: 14px;
}

.ant-alert-danger { background: rgba(240,64,96,0.1);  border: 1px solid rgba(240,64,96,0.2);  color: var(--expense); }
.ant-alert-warn   { background: rgba(232,184,64,0.1); border: 1px solid rgba(232,184,64,0.2); color: var(--warn); }
.ant-alert-ok     { background: rgba(45,216,112,0.1); border: 1px solid rgba(45,216,112,0.2); color: var(--income); }

.ant-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.ant-chip {
    display: flex;
    align-items: center;
    gap: 7px;
    padding: 7px 12px;
    background: var(--surface2);
    border: 1px solid var(--border);
    border-radius: 10px;
    transition: border-color 0.15s;
}

.ant-chip:hover { border-color: rgba(240,160,48,0.3); }

.ant-chip-icon  { font-size: 13px; }
.ant-chip-name  { font-size: 12px; font-weight: 700; color: var(--text); }
.ant-chip-val   { font-family: 'Bricolage Grotesque', sans-serif; font-size: 12px; font-weight: 800; color: var(--expense); }
.ant-chip-count { font-size: 10px; color: var(--muted); }

/* ── Recurrentes + Cuotas ────────────────────────────────────────────────── */
.two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }

.panel-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 20px 22px;
}

.panel-title {
    font-family: 'Bricolage Grotesque', sans-serif;
    font-size: 14px;
    font-weight: 800;
    color: var(--text);
    margin-bottom: 4px;
}

.panel-subtitle {
    font-size: 11px;
    color: var(--muted);
    margin-bottom: 14px;
}

.rec-progress-row {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 16px;
}

.rec-progress-bar {
    flex: 1;
    height: 5px;
    background: var(--surface3);
    border-radius: 3px;
    overflow: hidden;
}

.rec-progress-fill {
    height: 100%;
    background: var(--income);
    border-radius: 3px;
    transition: width 1s ease;
}

.rec-counter { font-size: 11px; color: var(--muted); font-weight: 700; white-space: nowrap; }

.rec-item-v2 {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 10px;
    border-radius: 9px;
    transition: background 0.1s;
}

.rec-item-v2:hover { background: rgba(255,255,255,0.025); }

.rec-dot {
    width: 7px; height: 7px;
    border-radius: 50%;
    flex-shrink: 0;
}

.rec-dot.confirmed { background: var(--income); }
.rec-dot.skipped   { background: var(--warn); }
.rec-dot.pending   { background: var(--accent2); }

.rec-name-v2 { flex: 1; font-size: 13px; font-weight: 600; color: var(--text); }
.rec-status-badge {
    font-size: 9px;
    font-weight: 800;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    padding: 2px 7px;
    border-radius: 20px;
}
.s-confirmed { background: rgba(45,216,112,0.1);  color: var(--income); }
.s-skipped   { background: rgba(232,184,64,0.1);  color: var(--warn); }
.s-pending   { background: rgba(78,155,255,0.1);  color: var(--accent2); }

.rec-amount-v2 {
    font-family: 'Bricolage Grotesque', sans-serif;
    font-size: 13px;
    font-weight: 800;
    min-width: 80px;
    text-align: right;
}

.panel-total-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 12px;
    padding-top: 12px;
    border-top: 1px solid var(--border);
}

.panel-total-label { font-size: 11px; color: var(--muted); font-weight: 700; }
.panel-total-val   { font-family: 'Bricolage Grotesque', sans-serif; font-size: 16px; font-weight: 800; }

.inst-mini-row {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 7px 0;
    border-bottom: 1px solid rgba(40,40,52,0.5);
}

.inst-mini-row:last-child { border-bottom: none; }

.inst-paid-dot { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; }
.inst-name { flex: 1; font-size: 12px; font-weight: 600; color: var(--text); min-width: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.inst-sub  { font-size: 10px; color: var(--muted); }
.inst-amount { font-family: 'Bricolage Grotesque', sans-serif; font-size: 13px; font-weight: 800; white-space: nowrap; }

/* ── Accounts + Patrimonio ───────────────────────────────────────────────── */
.acc-type-label {
    font-size: 9px;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: var(--muted);
    font-weight: 800;
    font-family: 'Bricolage Grotesque', sans-serif;
    margin: 10px 0 6px;
}

.acc-row-v2 {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 7px 10px;
    border-radius: 8px;
    margin-bottom: 3px;
    background: var(--surface2);
}

.acc-name-v2 { font-size: 13px; font-weight: 600; color: var(--text); }
.acc-val-v2  { font-family: 'Bricolage Grotesque', sans-serif; font-size: 13px; font-weight: 800; }

.networth-display {
    display: flex;
    flex-direction: column;
    gap: 0;
}

.networth-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 0;
    border-bottom: 1px solid var(--border);
}

.networth-item:last-child { border-bottom: none; padding-top: 18px; }

.nw-label { font-size: 11px; text-transform: uppercase; letter-spacing: 0.08em; color: var(--muted); font-weight: 700; font-family: 'Bricolage Grotesque', sans-serif; }
.nw-val   { font-family: 'Bricolage Grotesque', sans-serif; font-weight: 800; }

/* ── Forecast section ────────────────────────────────────────────────────── */
.forecast-card {
    background: var(--surface);
    border: 1px solid rgba(240,160,48,0.2);
    border-radius: 14px;
    padding: 22px 24px;
    background: linear-gradient(135deg, rgba(240,160,48,0.03) 0%, transparent 50%);
}

.forecast-month-progress {
    margin-bottom: 20px;
}

.forecast-progress-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}

.forecast-prog-label { font-size: 12px; color: var(--muted); font-weight: 600; }
.forecast-prog-pct   { font-family: 'Bricolage Grotesque', sans-serif; font-size: 13px; font-weight: 800; color: var(--accent); }

.forecast-prog-track { height: 8px; background: var(--surface3); border-radius: 4px; overflow: hidden; }
.forecast-prog-fill  { height: 100%; background: linear-gradient(90deg, var(--accent) 0%, #f5b040 100%); border-radius: 4px; transition: width 1s ease; }

.forecast-kpis {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
    margin-bottom: 20px;
}

.forecast-kpi {
    background: var(--surface2);
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 14px;
    text-align: center;
}

.fkpi-label { font-size: 9px; text-transform: uppercase; letter-spacing: 0.08em; color: var(--muted); font-weight: 700; font-family: 'Bricolage Grotesque', sans-serif; margin-bottom: 6px; }
.fkpi-val   { font-family: 'Bricolage Grotesque', sans-serif; font-size: 18px; font-weight: 800; }
.fkpi-sub   { font-size: 10px; color: var(--muted); margin-top: 3px; }

.forecast-breakdown {
    background: var(--surface2);
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 14px 16px;
}

.fcast-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid rgba(40,40,52,0.6);
    font-size: 13px;
}

.fcast-row:last-child { border-bottom: none; font-weight: 800; }

.fcast-label { color: var(--muted); font-weight: 600; }
.fcast-val   { font-family: 'Bricolage Grotesque', sans-serif; font-weight: 700; }

.forecast-result {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 14px;
    padding: 14px 16px;
    border-radius: 10px;
    border: 1px solid;
}

.forecast-result.positive { background: rgba(45,216,112,0.07); border-color: rgba(45,216,112,0.2); }
.forecast-result.negative { background: rgba(240,64,96,0.07);  border-color: rgba(240,64,96,0.2); }

.fr-label { font-size: 12px; font-weight: 700; }
.fr-val   { font-family: 'Bricolage Grotesque', sans-serif; font-size: 22px; font-weight: 800; }

.forecast-note { font-size: 10px; color: var(--muted); margin-top: 8px; }

/* ── Tags section ────────────────────────────────────────────────────────── */
.tag-row {
    display: grid;
    grid-template-columns: 12px 1fr auto;
    align-items: center;
    gap: 14px;
    padding: 10px 0;
    border-bottom: 1px solid rgba(40,40,52,0.5);
}

.tag-row:last-child { border-bottom: none; }

.tag-dot {
    width: 10px;
    height: 10px;
    border-radius: 3px;
    flex-shrink: 0;
}

.tag-body { min-width: 0; }

.tag-name {
    font-size: 13px;
    font-weight: 700;
    color: var(--text);
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 5px;
}

.tag-bar-track {
    height: 4px;
    background: var(--surface3);
    border-radius: 2px;
    overflow: hidden;
}

.tag-bar-fill {
    height: 100%;
    border-radius: 2px;
    transition: width 0.8s cubic-bezier(0.4, 0, 0.2, 1);
}

.tag-right { text-align: right; flex-shrink: 0; min-width: 100px; }

.tag-expense {
    font-family: 'Bricolage Grotesque', sans-serif;
    font-size: 14px;
    font-weight: 800;
    color: var(--expense);
    white-space: nowrap;
}

.tag-income {
    font-size: 11px;
    font-weight: 700;
    color: var(--income);
    margin-top: 1px;
}

.tag-pct {
    font-size: 10px;
    color: var(--muted);
    font-weight: 700;
}

/* ── Savings opportunity section ─────────────────────────────────────────── */
.savings-opp-card {
    background: var(--surface);
    border: 1px solid rgba(232,184,64,0.25);
    border-radius: 14px;
    padding: 22px 24px;
    background: linear-gradient(135deg, rgba(232,184,64,0.04) 0%, transparent 60%);
}

.savings-opp-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.savings-opp-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    background: rgba(232,184,64,0.12);
    color: var(--warn);
    border: 1px solid rgba(232,184,64,0.2);
    margin-bottom: 10px;
    width: fit-content;
}

.savings-opp-desc {
    font-size: 11px;
    color: var(--muted);
    line-height: 1.6;
    max-width: 400px;
}

.savings-opp-total-block { text-align: right; }

.savings-opp-total-label {
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--muted);
    font-weight: 700;
    margin-bottom: 4px;
}

.savings-opp-total-val {
    font-family: 'Bricolage Grotesque', sans-serif;
    font-size: 30px;
    font-weight: 800;
    color: var(--warn);
    line-height: 1;
}

.savings-opp-total-sub {
    font-size: 11px;
    color: var(--muted);
    margin-top: 4px;
    font-weight: 500;
}

.dispensable-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 14px;
    border-radius: 10px;
    background: rgba(232,184,64,0.04);
    border: 1px solid rgba(232,184,64,0.1);
    margin-bottom: 7px;
    transition: border-color 0.15s;
}

.dispensable-item:last-child { margin-bottom: 0; }
.dispensable-item:hover { border-color: rgba(232,184,64,0.25); }

.dispensable-item.paid {
    background: rgba(106,102,118,0.06);
    border-color: rgba(40,40,52,0.6);
    opacity: 0.7;
}

.disp-icon { flex-shrink: 0; color: var(--warn); }
.disp-icon.paid { color: var(--muted); }

.disp-body { flex: 1; min-width: 0; }

.disp-name {
    font-size: 13px;
    font-weight: 700;
    color: var(--text);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.disp-name.paid {
    color: var(--muted);
    text-decoration: line-through;
}

.disp-cat {
    font-size: 10px;
    color: var(--muted);
    margin-top: 2px;
}

.disp-status {
    font-size: 9px;
    font-weight: 800;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    padding: 2px 8px;
    border-radius: 20px;
    white-space: nowrap;
    flex-shrink: 0;
}

.disp-status.pending { background: rgba(232,184,64,0.12); color: var(--warn); }
.disp-status.paid    { background: rgba(106,102,118,0.15); color: var(--muted); }

.disp-amount {
    font-family: 'Bricolage Grotesque', sans-serif;
    font-size: 14px;
    font-weight: 800;
    min-width: 90px;
    text-align: right;
    white-space: nowrap;
    flex-shrink: 0;
}

.disp-amount.warn { color: var(--warn); }
.disp-amount.muted { color: var(--muted); }

.savings-opp-impact {
    margin-top: 18px;
    padding-top: 16px;
    border-top: 1px solid rgba(232,184,64,0.15);
}

.savings-opp-impact-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 7px;
    font-size: 11px;
    font-weight: 600;
    color: var(--muted);
}

.savings-opp-impact-pct {
    font-size: 11px;
    font-weight: 800;
    color: var(--warn);
}

.savings-opp-track {
    height: 6px;
    background: var(--surface3);
    border-radius: 3px;
    overflow: hidden;
}

.savings-opp-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--warn), rgba(232,184,64,0.5));
    border-radius: 3px;
    transition: width 1s ease;
}

/* ── Empty states ────────────────────────────────────────────────────────── */
.empty-state {
    text-align: center;
    padding: 24px 0;
    color: var(--muted);
    font-size: 13px;
    font-weight: 600;
}

/* ── Responsive ──────────────────────────────────────────────────────────── */
@media (max-width: 1024px) {
    .stats-grid   { grid-template-columns: repeat(2, 1fr); }
    .charts-grid  { grid-template-columns: 1fr; }
    .cat-layout   { grid-template-columns: 1fr; }
    .cat-donut-card { order: -1; }
    .ant-layout   { grid-template-columns: 1fr; }
    .ant-score-ring { margin: 0 auto; }
}

@media (max-width: 768px) {
    .two-col       { grid-template-columns: 1fr; }
    .forecast-kpis { grid-template-columns: repeat(2, 1fr); }
    .chart-wrap    { height: 180px; }
    .report-title  { font-size: 22px; }
    .report-header { gap: 12px; margin-bottom: 24px; }
    .month-controls { width: 100%; }
    .month-controls form { flex: 1; }
    .month-controls form input { width: 100% !important; min-height: 44px; }
    .btn-pdf { min-height: 44px; padding: 11px 14px; }
    .stat-card-v2 { padding: 16px 16px 14px; }
    .stat-value-v2 { font-size: 22px; }
    .tag-right { min-width: 80px; }
}

@media (max-width: 480px) {
    .stats-grid    { grid-template-columns: 1fr; }
    .forecast-kpis { grid-template-columns: 1fr; }
    .btn-pdf-label { display: none; }
    .btn-pdf { padding: 11px 12px; }
    .savings-opp-total-val { font-size: 24px; }
    .ant-summary-row { gap: 12px; }

    /* Dispensables: cuando es muy chico, status va abajo del nombre */
    .dispensable-item { flex-wrap: wrap; gap: 8px 12px; }
    .disp-body { flex: 1 1 calc(100% - 130px); min-width: 0; }
    .disp-status { order: 3; flex-basis: auto; }
    .disp-amount { order: 2; min-width: 0; }

    /* Tag row: bajar min-width del lado derecho */
    .tag-right { min-width: 0; }
    .tag-expense { font-size: 13px; }
}

/* ── Spin animation for PDF loading ──────────────────────────────────────── */
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
</style>

{{-- ═══════════════════════════════════════════════════════════════════════
     HEADER
     ═══════════════════════════════════════════════════════════════════════ --}}
<div class="report-header">
    <div>
        <div class="report-title">Balance <span class="accent">Mensual</span></div>
        <div class="report-month-badge">
            {{ $monthLabel }}
            @if($isCurrentMonth)
                <span class="live-pill"><span class="live-dot"></span>En curso</span>
            @endif
        </div>
    </div>

    <div class="month-controls">
        <a href="{{ route('reports.monthly', ['mes' => $date->copy()->subMonth()->format('Y-m')]) }}"
           class="btn btn-ghost" style="padding:8px 12px;" title="Mes anterior">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M15 18l-6-6 6-6"/></svg>
        </a>

        <form method="GET" action="{{ route('reports.monthly') }}" style="display:flex;align-items:center;">
            <input type="month" name="mes" value="{{ $date->format('Y-m') }}"
                   max="{{ now()->format('Y-m') }}"
                   class="form-input" style="width:155px;padding:8px 12px;font-size:13px;"
                   onchange="this.form.submit()">
        </form>

        @if(!$isCurrentMonth)
        <a href="{{ route('reports.monthly', ['mes' => $date->copy()->addMonth()->format('Y-m')]) }}"
           class="btn btn-ghost" style="padding:8px 12px;" title="Mes siguiente">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M9 18l6-6-6-6"/></svg>
        </a>
        @endif

        <button class="btn-pdf" id="btn-pdf" onclick="generarPDF()" aria-label="Exportar PDF">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
                <line x1="16" y1="13" x2="8" y2="13"/>
                <line x1="16" y1="17" x2="8" y2="17"/>
            </svg>
            <span class="btn-pdf-label">Exportar PDF</span>
        </button>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════════════
     1. STAT CARDS
     ═══════════════════════════════════════════════════════════════════════ --}}
<div class="report-section">
    <div class="section-header">
        <span class="section-label">Resumen</span>
        <div class="section-line"></div>
    </div>

    <div class="stats-grid">
        {{-- Ingresos --}}
        <div class="stat-card-v2 income">
            <div class="stat-top">
                <div>
                    <div class="stat-label-v2">Ingresos</div>
                </div>
                <div class="stat-icon">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
                </div>
            </div>
            <div class="stat-value-v2 amount-income">$ {{ number_format($totalIncome, 0, ',', '.') }}</div>
            <div class="stat-footer">
                @if($incomeVsPrev !== null)
                    <span class="delta-pill {{ $incomeVsPrev >= 0 ? 'delta-good' : 'delta-bad' }}" title="vs mes anterior">
                        {{ $incomeVsPrev >= 0 ? '▲' : '▼' }} {{ abs($incomeVsPrev) }}% vs mes anterior
                    </span>
                @endif
                @if($incomeVsAvg !== null)
                    <span class="delta-pill {{ $incomeVsAvg > 0 ? 'delta-good' : ($incomeVsAvg < -10 ? 'delta-bad' : 'delta-neu') }}" title="vs promedio 3 meses">
                        {{ $incomeVsAvg >= 0 ? '▲' : '▼' }} {{ abs($incomeVsAvg) }}% vs promedio
                    </span>
                @endif
                @if($avgIncome > 0)
                    <span class="stat-sub">Prom. 3m: $ {{ number_format($avgIncome, 0, ',', '.') }}</span>
                @endif
            </div>
        </div>

        {{-- Egresos --}}
        <div class="stat-card-v2 expense">
            <div class="stat-top">
                <div>
                    <div class="stat-label-v2">Egresos</div>
                </div>
                <div class="stat-icon">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="23 18 13.5 8.5 8.5 13.5 1 6"/><polyline points="17 18 23 18 23 12"/></svg>
                </div>
            </div>
            <div class="stat-value-v2 amount-expense">$ {{ number_format($totalExpense, 0, ',', '.') }}</div>
            <div class="stat-footer">
                @if($expenseVsPrev !== null)
                    <span class="delta-pill {{ $expenseVsPrev > 0 ? 'delta-bad' : 'delta-good' }}">
                        {{ $expenseVsPrev > 0 ? '▲' : '▼' }} {{ abs($expenseVsPrev) }}% vs mes anterior
                    </span>
                @endif
                @if($expenseVsAvg !== null)
                    <span class="delta-pill {{ $expenseVsAvg > 20 ? 'delta-bad' : ($expenseVsAvg < -10 ? 'delta-good' : 'delta-neu') }}" title="vs promedio 3 meses">
                        {{ $expenseVsAvg > 0 ? '▲' : '▼' }} {{ abs($expenseVsAvg) }}% vs promedio
                    </span>
                @endif
            </div>
        </div>

        {{-- Balance --}}
        <div class="stat-card-v2 balance">
            <div class="stat-top">
                <div>
                    <div class="stat-label-v2">Balance neto</div>
                </div>
                <div class="stat-icon">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                </div>
            </div>
            <div class="stat-value-v2 {{ $balance >= 0 ? 'amount-neutral' : 'amount-expense' }}">
                {{ $balance >= 0 ? '+' : '' }}$ {{ number_format($balance, 0, ',', '.') }}
            </div>
            <div class="stat-footer">
                @if($prevBalance != 0)
                    <span class="stat-sub">Mes anterior: {{ $prevBalance >= 0 ? '+' : '' }}$ {{ number_format($prevBalance, 0, ',', '.') }}</span>
                @endif
            </div>
        </div>

        {{-- Tasa de ahorro --}}
        <div class="stat-card-v2 savings">
            <div class="stat-top">
                <div>
                    <div class="stat-label-v2">Tasa de ahorro</div>
                </div>
                <div class="stat-icon">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                </div>
            </div>
            <div class="stat-value-v2 {{ $savingsRate >= 0 ? 'amount-neutral' : 'amount-expense' }}">{{ $savingsRate }}%</div>
            <div class="savings-bar-wrap">
                <div class="savings-bar-fill" style="width:{{ max(0,min(100,$savingsRate)) }}%;background:{{ $savingsRate >= 20 ? 'var(--income)' : ($savingsRate >= 0 ? 'var(--accent)' : 'var(--expense)') }};"></div>
            </div>
            <div style="margin-top:10px;height:36px;position:relative;">
                <canvas id="chartSavingsSpark" style="width:100%;height:100%;"></canvas>
            </div>
            <div class="stat-footer" style="margin-top:6px;justify-content:space-between;">
                @php $avgSavings = $avgIncome > 0 ? round((($avgIncome-$avgExpense)/$avgIncome)*100,1) : 0; @endphp
                <span class="stat-sub">Prom. 3m: {{ $avgSavings }}%</span>
                <span class="stat-sub" style="opacity:0.7;">últimos 6 meses</span>
            </div>
        </div>
    </div>

    {{-- Sub-row: cobertura de fijos + break-even --}}
    <div class="stats-grid" style="margin-top:12px;">
        {{-- Cobertura de gastos fijos --}}
        <div class="stat-card-v2" style="grid-column:span 2;">
            <div class="stat-top">
                <div>
                    <div class="stat-label-v2">Cobertura de gastos fijos</div>
                </div>
                <div class="stat-icon">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                </div>
            </div>
            @if($fixedCoveragePct !== null)
                @php
                    $fcColor = $fixedCoveragePct >= 100 ? 'var(--expense)' : ($fixedCoveragePct >= 70 ? 'var(--accent)' : 'var(--income)');
                    $fcTone  = $fixedCoveragePct >= 100 ? 'amount-expense' : 'amount-neutral';
                @endphp
                <div class="stat-value-v2 {{ $fcTone }}">{{ $fixedCoveragePct }}%</div>
                <div class="savings-bar-wrap">
                    <div class="savings-bar-fill" style="width:{{ min(100, $fixedCoveragePct) }}%;background:{{ $fcColor }};"></div>
                </div>
                <div class="stat-footer" style="margin-top:8px;">
                    <span class="stat-sub">Pagos fijos + cuotas: $ {{ number_format($fixedCostsTotal, 0, ',', '.') }} · ingreso $ {{ number_format($totalIncome, 0, ',', '.') }}</span>
                </div>
            @else
                <div class="stat-value-v2 amount-neutral" style="opacity:0.5;">—</div>
                <div class="stat-footer"><span class="stat-sub">Sin ingresos para comparar</span></div>
            @endif
        </div>

        {{-- Break-even day --}}
        <div class="stat-card-v2" style="grid-column:span 2;">
            <div class="stat-top">
                <div>
                    <div class="stat-label-v2">Punto de equilibrio</div>
                </div>
                <div class="stat-icon">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                </div>
            </div>
            @if($breakEvenDay !== null)
                @php
                    $bePct = round(($breakEvenDay / $daysInMonth) * 100);
                    $beColor = $breakEvenDay <= 10 ? 'var(--income)' : ($breakEvenDay <= 20 ? 'var(--accent)' : 'var(--expense)');
                @endphp
                <div class="stat-value-v2 amount-neutral">Día {{ $breakEvenDay }} <span style="font-size:14px;color:var(--muted);font-weight:600;">/ {{ $daysInMonth }}</span></div>
                <div class="savings-bar-wrap">
                    <div class="savings-bar-fill" style="width:{{ $bePct }}%;background:{{ $beColor }};"></div>
                </div>
                <div class="stat-footer" style="margin-top:8px;">
                    <span class="stat-sub">Día en que lo que entró iguala o supera lo que salió</span>
                </div>
            @else
                <div class="stat-value-v2 amount-expense" style="font-size:22px;">Aún no</div>
                <div class="stat-footer" style="margin-top:8px;">
                    @if($totalIncome > 0)
                        <span class="stat-sub">Los gastos acumulados superan los ingresos del mes</span>
                    @else
                        <span class="stat-sub">Sin ingresos registrados este mes</span>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════════════
     2. GRÁFICOS
     ═══════════════════════════════════════════════════════════════════════ --}}
<div class="report-section">
    <div class="section-header">
        <span class="section-label">Evolución</span>
        <div class="section-line"></div>
    </div>

    <div class="charts-grid">
        <div class="chart-card">
            <div class="chart-card-title">
                <span>Ingresos vs egresos — últimos 6 meses</span>
            </div>
            <div class="chart-wrap"><canvas id="chartMonthly"></canvas></div>
        </div>
        <div class="chart-card">
            <div class="chart-card-title">
                <span>Gastos e ingresos diarios</span>
                @if($isCurrentMonth)
                    <span style="font-size:10px;color:var(--muted);font-weight:600;font-family:'Nunito',sans-serif;">día {{ now()->day }}/{{ $daysInMonth }}</span>
                @endif
            </div>
            <div class="chart-wrap"><canvas id="chartDaily"></canvas></div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════════════
     3. CATEGORÍAS — Ingresos / Egresos / Neto
     ═══════════════════════════════════════════════════════════════════════ --}}
<div class="report-section">
    <div class="section-header">
        <span class="section-label">Por categoría</span>
        <span style="font-size:10px;color:var(--muted);font-weight:600;">ingresos, egresos y neto</span>
        <div class="section-line"></div>
    </div>

    <div class="cat-layout">
        {{-- Lista combinada Ing/Egr/Neto --}}
        <div class="cat-list-card">
            @if($categoryCombined->isNotEmpty())
            <div class="cmb-header">
                <div></div>
                <div style="font-family:'Nunito',sans-serif;font-size:9.5px;font-weight:800;color:var(--muted);letter-spacing:0.06em;text-transform:uppercase;">Categoría</div>
                <div class="cmb-header-nums">
                    <div>Entra</div>
                    <div>Sale</div>
                    <div>Neto</div>
                </div>
            </div>
            @foreach($categoryCombined as $idx => $cat)
            <div class="cmb-row">
                <div class="cat-rank">{{ $idx + 1 }}</div>
                <div class="cat-body">
                    <div class="cat-name-v2">
                        @include('categories._icon', ['icon' => $cat['icon'], 'color' => $cat['color'], 'type' => $cat['type'] ?? 'expense', 'size' => 'xs'])
                        <span>{{ $cat['name'] }}</span>
                        <span style="font-size:10px;color:var(--muted);font-weight:600;">{{ $cat['count'] }} mov.</span>
                    </div>
                    <div class="cat-bar-track">
                        <div class="cat-bar-fill" style="width:{{ max($cat['percent'], $cat['income_pct']) }}%; background:{{ $cat['color'] }};"></div>
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
            @else
                <div class="empty-state">Sin movimientos este mes</div>
            @endif
        </div>

        {{-- Donuts (gastos arriba, ingresos abajo) --}}
        <div style="display:flex;flex-direction:column;gap:14px;">
            {{-- Gastos --}}
            <div class="cat-donut-card">
                <div style="font-family:'Bricolage Grotesque',sans-serif;font-size:13px;font-weight:700;color:var(--text);margin-bottom:14px;text-align:center;">Distribución de gastos</div>
                <div style="position:relative;width:180px;height:180px;margin:0 auto;">
                    <canvas id="chartCategories" width="180" height="180"></canvas>
                    <div class="donut-label-center">
                        <div class="donut-center-val">$ {{ number_format($totalExpense / 1000, 0, ',', '.') }}k</div>
                        <div class="donut-center-label">Total gasto</div>
                    </div>
                </div>
                @if($byCategory->isNotEmpty())
                <div style="margin-top:16px;display:flex;flex-direction:column;gap:5px;width:100%;">
                    @foreach($byCategory->take(5) as $idx => $cat)
                    <div style="display:flex;align-items:center;gap:7px;">
                        <div style="width:8px;height:8px;border-radius:2px;flex-shrink:0;background:{{ $cat['color'] }};"></div>
                        <div style="flex:1;font-size:10px;font-weight:600;color:var(--muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $cat['name'] }}</div>
                        <div style="font-size:10px;font-weight:800;color:var(--text);">{{ $cat['percent'] }}%</div>
                    </div>
                    @endforeach
                    @if($byCategory->count() > 5)
                        <div style="font-size:10px;color:var(--muted);text-align:center;margin-top:4px;">+ {{ $byCategory->count() - 5 }} más</div>
                    @endif
                </div>
                @endif
            </div>

            {{-- Ingresos --}}
            <div class="cat-donut-card">
                <div style="font-family:'Bricolage Grotesque',sans-serif;font-size:13px;font-weight:700;color:var(--text);margin-bottom:14px;text-align:center;">Distribución de ingresos</div>
                @if($byCategoryIncome->isNotEmpty() && $totalIncome > 0)
                <div style="position:relative;width:180px;height:180px;margin:0 auto;">
                    <canvas id="chartCategoriesIncome" width="180" height="180"></canvas>
                    <div class="donut-label-center">
                        <div class="donut-center-val" style="color:var(--income);">$ {{ number_format($totalIncome / 1000, 0, ',', '.') }}k</div>
                        <div class="donut-center-label">Total ingreso</div>
                    </div>
                </div>
                <div style="margin-top:16px;display:flex;flex-direction:column;gap:5px;width:100%;">
                    @foreach($byCategoryIncome->take(5) as $idx => $cat)
                    <div style="display:flex;align-items:center;gap:7px;">
                        <div style="width:8px;height:8px;border-radius:2px;flex-shrink:0;background:{{ $cat['color'] }};"></div>
                        <div style="flex:1;font-size:10px;font-weight:600;color:var(--muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $cat['name'] }}</div>
                        <div style="font-size:10px;font-weight:800;color:var(--text);">{{ $cat['income_pct'] }}%</div>
                    </div>
                    @endforeach
                    @if($byCategoryIncome->count() > 5)
                        <div style="font-size:10px;color:var(--muted);text-align:center;margin-top:4px;">+ {{ $byCategoryIncome->count() - 5 }} más</div>
                    @endif
                </div>
                @else
                <div style="text-align:center;padding:32px 12px;font-size:12px;color:var(--muted);">Sin ingresos este mes</div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════════════
     4. ETIQUETAS
     ═══════════════════════════════════════════════════════════════════════ --}}
@if($tagStats->isNotEmpty())
<div class="report-section">
    <div class="section-header">
        <span class="section-label">Por etiqueta</span>
        <span style="font-size:10px;color:var(--muted);font-weight:600;">transacciones etiquetadas del mes</span>
        <div class="section-line"></div>
        <a href="{{ route('tags.index') }}" style="font-size:11px;color:var(--accent);text-decoration:none;font-weight:700;white-space:nowrap;">Gestionar →</a>
    </div>

    <div class="panel-card">
        @php
            $tagExpenseTotal = $tagStats->sum('expense');
            $tagIncomeTotal  = $tagStats->sum('income');
            $tagRefTotal     = max($tagExpenseTotal, $tagIncomeTotal, 1);
            $tagStatsSorted  = $tagStats->sortByDesc(fn ($t) => $t['expense'] + $t['income'])->values();
        @endphp
        <div class="cmb-header">
            <div></div>
            <div style="font-family:'Nunito',sans-serif;font-size:9.5px;font-weight:800;color:var(--muted);letter-spacing:0.06em;text-transform:uppercase;">Etiqueta</div>
            <div class="cmb-header-nums">
                <div>Entra</div>
                <div>Sale</div>
                <div>Neto</div>
            </div>
        </div>
        @foreach($tagStatsSorted as $tag)
        @php
            $tagNet   = $tag['income'] - $tag['expense'];
            $tagBar   = max($tag['expense'], $tag['income']);
            $tagBarPct = $tagRefTotal > 0 ? min(100, round(($tagBar / $tagRefTotal) * 100)) : 0;
        @endphp
        <div class="cmb-row" style="grid-template-columns: 14px 1fr auto;">
            <div class="tag-dot" style="background:{{ $tag['color'] }};align-self:center;"></div>
            <div class="cat-body">
                <div class="cat-name-v2" style="gap:8px;">
                    <span>{{ $tag['name'] }}</span>
                    <span style="font-size:10px;color:var(--muted);font-weight:600;">{{ $tag['count'] }} mov.</span>
                </div>
                <div class="cat-bar-track">
                    <div class="cat-bar-fill" style="width:{{ $tagBarPct }}%;background:{{ $tag['color'] }};"></div>
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

{{-- ═══════════════════════════════════════════════════════════════════════
     5. GASTOS HORMIGA
     ═══════════════════════════════════════════════════════════════════════ --}}
<div class="report-section">
    <div class="section-header">
        <span class="section-label">Gastos hormiga</span>
        <span style="font-size:10px;color:var(--muted);font-weight:600;">transacciones &lt; $ {{ number_format($antThreshold, 0, ',', '.') }}</span>
        <div class="section-line"></div>
    </div>

    <div class="panel-card">
        <div class="ant-layout">
            {{-- Ring score --}}
            <div class="ant-score-ring" id="antRingWrap">
                <svg width="120" height="120" viewBox="0 0 120 120">
                    <circle class="ant-ring-bg" cx="60" cy="60" r="50"/>
                    <circle class="ant-ring-fill" cx="60" cy="60" r="50"
                            id="antRingFill"
                            stroke="{{ $antScore > 30 ? 'var(--expense)' : ($antScore > 15 ? 'var(--warn)' : 'var(--income)') }}"
                            stroke-dasharray="{{ round(2 * pi() * 50, 2) }}"
                            stroke-dashoffset="{{ round(2 * pi() * 50, 2) }}"
                            data-target="{{ round(2 * pi() * 50 * (1 - $antScore / 100), 2) }}"/>
                </svg>
                <div class="ant-ring-center">
                    <div class="ant-ring-pct" style="color:{{ $antScore > 30 ? 'var(--expense)' : ($antScore > 15 ? 'var(--warn)' : 'var(--income)') }};">{{ $antScore }}%</div>
                    <div class="ant-ring-sub">del total</div>
                </div>
            </div>

            {{-- Derecha --}}
            <div class="ant-right">
                <div class="ant-summary-row">
                    <div class="ant-total-block">
                        <div class="ant-total-label">Total hormiga</div>
                        <div class="ant-total-val">$ {{ number_format($antTotal, 0, ',', '.') }}</div>
                    </div>
                    <div class="ant-total-block">
                        <div class="ant-total-label">Cantidad</div>
                        <div class="ant-total-val" style="color:var(--text);">{{ $antCount }} txns</div>
                    </div>
                    @if($antCount > 0)
                    <div class="ant-total-block">
                        <div class="ant-total-label">Promedio</div>
                        <div class="ant-total-val" style="color:var(--muted);">$ {{ number_format($antTotal / max(1,$antCount), 0, ',', '.') }}</div>
                    </div>
                    @endif
                </div>

                @if($antScore > 30)
                    <div class="ant-alert ant-alert-danger">
                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        Más del 30% de tus gastos son microtransacciones. Revisá si podés consolidarlos.
                    </div>
                @elseif($antScore > 15)
                    <div class="ant-alert ant-alert-warn">
                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        Nivel moderado — vale la pena monitorear estos gastos frecuentes.
                    </div>
                @else
                    <div class="ant-alert ant-alert-ok">
                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                        Bien controlado — los gastos pequeños representan un porcentaje bajo.
                    </div>
                @endif

                @if($antByCategory->isNotEmpty())
                <div class="ant-chips">
                    @foreach($antByCategory->take(8) as $ant)
                    <div class="ant-chip">
                        @include('categories._icon', ['icon' => $ant['icon'], 'color' => $ant['color'], 'type' => $ant['type'] ?? 'expense', 'size' => 'xs'])
                        <span class="ant-chip-name">{{ $ant['name'] }}</span>
                        <span class="ant-chip-val">$ {{ number_format($ant['total'], 0, ',', '.') }}</span>
                        <span class="ant-chip-count">({{ $ant['count'] }})</span>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════════════
     GASTOS EVITABLES — oportunidad de ahorro
     ═══════════════════════════════════════════════════════════════════════ --}}
@if($avoidableCount > 0 || true)
<div class="report-section">
    <div class="section-header">
        <span class="section-label">Gastos evitables</span>
        <span style="font-size:10px;color:var(--warn);font-weight:600;">oportunidad de ahorro</span>
        <div class="section-line"></div>
    </div>

    <div class="panel-card">
        <div class="ant-layout">
            {{-- Ring score --}}
            <div class="ant-score-ring" id="avoidableRingWrap">
                <svg width="120" height="120" viewBox="0 0 120 120">
                    <circle class="ant-ring-bg" cx="60" cy="60" r="50"/>
                    <circle class="ant-ring-fill" cx="60" cy="60" r="50"
                            id="avoidableRingFill"
                            stroke="{{ $avoidableScore > 20 ? 'var(--expense)' : ($avoidableScore > 10 ? 'var(--warn)' : 'var(--income)') }}"
                            stroke-dasharray="{{ round(2 * pi() * 50, 2) }}"
                            stroke-dashoffset="{{ round(2 * pi() * 50, 2) }}"
                            data-target="{{ round(2 * pi() * 50 * (1 - $avoidableScore / 100), 2) }}"/>
                </svg>
                <div class="ant-ring-center">
                    <div class="ant-ring-pct" style="color:{{ $avoidableScore > 20 ? 'var(--expense)' : ($avoidableScore > 10 ? 'var(--warn)' : 'var(--income)') }};">{{ $avoidableScore }}%</div>
                    <div class="ant-ring-sub">del total</div>
                </div>
            </div>

            {{-- Derecha --}}
            <div class="ant-right">
                <div class="ant-summary-row">
                    <div class="ant-total-block">
                        <div class="ant-total-label">Total evitable</div>
                        <div class="ant-total-val" style="color:var(--warn);">$ {{ number_format($avoidableTotal, 0, ',', '.') }}</div>
                    </div>
                    <div class="ant-total-block">
                        <div class="ant-total-label">Cantidad</div>
                        <div class="ant-total-val" style="color:var(--text);">{{ $avoidableCount }} txns</div>
                    </div>
                    @if($avoidableCount > 0 && $totalIncome > 0)
                    <div class="ant-total-block">
                        <div class="ant-total-label">% ingresos</div>
                        <div class="ant-total-val" style="color:var(--muted);">{{ round(($avoidableTotal / $totalIncome) * 100, 1) }}%</div>
                    </div>
                    @endif
                </div>

                @if($avoidableCount === 0)
                    <div class="ant-alert ant-alert-ok">
                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                        Sin gastos marcados como evitables este mes. ¡Buen control!
                    </div>
                @elseif($avoidableScore > 20)
                    <div class="ant-alert ant-alert-danger">
                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        Más del 20% de tus gastos podrían haberse evitado. Gran oportunidad de ahorro.
                    </div>
                @else
                    <div class="ant-alert ant-alert-warn">
                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        Algunos gastos pueden reducirse. Revisá si podés evitarlos el próximo mes.
                    </div>
                @endif

                @if($avoidableByCategory->isNotEmpty())
                <div class="ant-chips">
                    @foreach($avoidableByCategory->take(8) as $item)
                    <div class="ant-chip">
                        @include('categories._icon', ['icon' => $item['icon'], 'color' => $item['color'], 'type' => 'expense', 'size' => 'xs'])
                        <span class="ant-chip-name">{{ $item['name'] }}</span>
                        <span class="ant-chip-val">$ {{ number_format($item['total'], 0, ',', '.') }}</span>
                        <span class="ant-chip-count">({{ $item['count'] }})</span>
                    </div>
                    @endforeach
                </div>
                @endif

                @if($avoidableCount > 0 && $totalIncome > 0)
                @php
                    $avoidableSavingsRate = round((($totalIncome - ($totalExpense - $avoidableTotal)) / $totalIncome) * 100, 1);
                @endphp
                <div style="margin-top:14px;font-size:11px;color:var(--muted);font-weight:600;">
                    Tasa de ahorro actual: <strong style="color:var(--text);">{{ $savingsRate }}%</strong>
                    &nbsp;→&nbsp;
                    Sin evitables: <strong style="color:var(--income);">{{ $avoidableSavingsRate }}%</strong>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endif

{{-- ═══════════════════════════════════════════════════════════════════════
     4b. POR GRUPO DE ETIQUETAS
     ═══════════════════════════════════════════════════════════════════════ --}}
@if($tagGroupStats->isNotEmpty() || $noGroupTotal > 0)
<div class="report-section">
    <div class="section-header">
        <span class="section-label">Por grupo de etiquetas</span>
        <span style="font-size:10px;color:var(--muted);font-weight:600;">% sobre total etiquetado del mes</span>
        <div class="section-line"></div>
        <a href="{{ route('tags.index') }}" style="font-size:11px;color:var(--accent);text-decoration:none;font-weight:700;white-space:nowrap;">Gestionar →</a>
    </div>

    <div class="panel-card">
        <div class="cmb-header">
            <div></div>
            <div style="font-family:'Nunito',sans-serif;font-size:9.5px;font-weight:800;color:var(--muted);letter-spacing:0.06em;text-transform:uppercase;">Grupo</div>
            <div class="cmb-header-nums">
                <div>Entra</div>
                <div>Sale</div>
                <div>Neto</div>
            </div>
        </div>
        @php
            $tgSorted = $tagGroupStats->sortByDesc(fn ($g) => $g['expense'] + $g['income'])->values();
        @endphp
        @foreach($tgSorted as $tg)
        @php $tgNet = $tg['income'] - $tg['expense']; @endphp
        <div class="cmb-row" style="grid-template-columns: 14px 1fr auto;align-items:flex-start;">
            <div class="tag-dot" style="background:{{ $tg['color'] }};border-radius:3px;margin-top:6px;"></div>
            <div class="cat-body">
                <div class="cat-name-v2" style="gap:8px;">
                    <span>{{ $tg['name'] }}</span>
                    <span style="font-size:10px;color:var(--muted);font-weight:600;">{{ $tg['count'] }} mov.</span>
                </div>
                <div style="display:flex;flex-wrap:wrap;gap:4px;margin:2px 0 6px;">
                    @foreach($tg['tags'] as $t)
                        <span style="display:inline-flex;align-items:center;gap:3px;padding:1px 7px 1px 5px;border-radius:10px;font-size:9.5px;font-weight:700;background:{{ $t['color'] }}22;color:{{ $t['color'] }};border:1px solid {{ $t['color'] }}44;">
                            <span style="width:5px;height:5px;border-radius:50%;background:{{ $t['color'] }};"></span>
                            {{ $t['name'] }}
                        </span>
                    @endforeach
                </div>
                <div class="cat-bar-track">
                    <div class="cat-bar-fill" style="width:{{ min($tg['pct'], 100) }}%;background:{{ $tg['color'] }};"></div>
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
        <div class="cmb-row" style="grid-template-columns: 14px 1fr auto;opacity:0.7;">
            <div class="tag-dot" style="background:var(--muted);border-radius:3px;align-self:center;"></div>
            <div class="cat-body">
                <div class="cat-name-v2" style="color:var(--muted);gap:8px;">
                    <span>Sin grupo</span>
                    <span style="font-size:10px;color:var(--muted);font-weight:600;">etiquetas sin grupo asignado</span>
                </div>
                <div class="cat-bar-track">
                    <div class="cat-bar-fill" style="width:{{ min($noGroupPct, 100) }}%;background:var(--muted);"></div>
                </div>
            </div>
            <div class="cmb-nums">
                <div class="cmb-cell">
                    <div class="cmb-cell-label">Entra</div>
                    <div class="cmb-cell-val zero">—</div>
                </div>
                <div class="cmb-cell">
                    <div class="cmb-cell-label">Sale</div>
                    <div class="cmb-cell-val neg">$ {{ number_format($noGroupTotal, 0, ',', '.') }}</div>
                </div>
                <div class="cmb-cell">
                    <div class="cmb-cell-label">Neto</div>
                    <div class="cmb-cell-val neg">-$ {{ number_format($noGroupTotal, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endif

{{-- ═══════════════════════════════════════════════════════════════════════
     5. CUOTAS DEL MES
     ═══════════════════════════════════════════════════════════════════════ --}}
<div class="report-section">
    <div class="section-header">
        <span class="section-label">Cuotas del mes</span>
        <div class="section-line"></div>
    </div>

    <div class="panel-card">
        <div class="panel-title">Cuotas</div>
        <div class="panel-subtitle">{{ $installments->count() }} cuota{{ $installments->count() !== 1 ? 's' : '' }} este mes</div>

        @if($installments->isNotEmpty())
        <div style="display:flex;gap:20px;margin-bottom:14px;flex-wrap:wrap;">
            <div>
                <div style="font-size:9px;color:var(--muted);text-transform:uppercase;letter-spacing:0.08em;font-weight:700;margin-bottom:3px;">Total</div>
                <div style="font-family:'Bricolage Grotesque',sans-serif;font-size:20px;font-weight:800;color:var(--expense);">$ {{ number_format($installmentTotal, 0, ',', '.') }}</div>
            </div>
            <div>
                <div style="font-size:9px;color:var(--muted);text-transform:uppercase;letter-spacing:0.08em;font-weight:700;margin-bottom:3px;">Pagadas</div>
                <div style="font-family:'Bricolage Grotesque',sans-serif;font-size:20px;font-weight:800;color:var(--income);">$ {{ number_format($paidInstallmentTotal, 0, ',', '.') }}</div>
            </div>
            <div>
                <div style="font-size:9px;color:var(--muted);text-transform:uppercase;letter-spacing:0.08em;font-weight:700;margin-bottom:3px;">Pendiente</div>
                <div style="font-family:'Bricolage Grotesque',sans-serif;font-size:20px;font-weight:800;color:var(--warn);">$ {{ number_format($installmentTotal - $paidInstallmentTotal, 0, ',', '.') }}</div>
            </div>
        </div>

        <div style="max-height:320px;overflow-y:auto;">
            @foreach($installments as $inst)
            <div class="inst-mini-row">
                <div class="inst-paid-dot" style="background:{{ $inst->is_paid ? 'var(--income)' : 'var(--warn)' }};"></div>
                <div style="flex:1;min-width:0;">
                    <div class="inst-name">{{ $inst->transaction?->description ?? 'Sin descripción' }}</div>
                    <div class="inst-sub">{{ $inst->account?->name }} · {{ $inst->installment_number }}/{{ $inst->transaction?->installments_count ?? '?' }}</div>
                </div>
                <div class="inst-amount" style="color:{{ $inst->is_paid ? 'var(--muted)' : 'var(--expense)' }};">$ {{ number_format($inst->amount, 0, ',', '.') }}</div>
            </div>
            @endforeach
        </div>
        @else
            <div class="empty-state">Sin cuotas este mes</div>
        @endif
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════════════
     6. PAGOS DEL MES
     ═══════════════════════════════════════════════════════════════════════ --}}
<div class="report-section">
    <div class="section-header">
        <span class="section-label">Pagos del mes</span>
        <span style="font-size:10px;color:var(--muted);font-weight:600;">gastos fijos / checklist</span>
        <div class="section-line"></div>
    </div>

    @php
        $totalPayments = $paymentItemsWithStatus->count();
        $paidCount     = $paidPaymentsItems->count();
        $pendingAmt    = $paymentItemsWithStatus->where('is_paid', false)->sum('amount_ars');
    @endphp

    <div class="panel-card">
        @if($totalPayments > 0)
        {{-- Summary --}}
        <div style="display:flex;align-items:center;gap:24px;margin-bottom:14px;flex-wrap:wrap;">
            <div>
                <div style="font-size:9px;text-transform:uppercase;letter-spacing:0.08em;color:var(--muted);font-weight:700;margin-bottom:3px;">Pagados</div>
                <div style="font-family:'Bricolage Grotesque',sans-serif;font-size:22px;font-weight:800;color:var(--income);">
                    {{ $paidCount }}<span style="font-size:13px;color:var(--muted);font-weight:600;"> / {{ $totalPayments }}</span>
                </div>
            </div>
            @if($paidPaymentsTotal > 0)
            <div>
                <div style="font-size:9px;text-transform:uppercase;letter-spacing:0.08em;color:var(--muted);font-weight:700;margin-bottom:3px;">Abonado</div>
                <div style="font-family:'Bricolage Grotesque',sans-serif;font-size:22px;font-weight:800;color:var(--income);">$ {{ number_format($paidPaymentsTotal, 0, ',', '.') }}</div>
            </div>
            @endif
            @if($pendingAmt > 0)
            <div>
                <div style="font-size:9px;text-transform:uppercase;letter-spacing:0.08em;color:var(--muted);font-weight:700;margin-bottom:3px;">Pendiente</div>
                <div style="font-family:'Bricolage Grotesque',sans-serif;font-size:22px;font-weight:800;color:var(--warn);">$ {{ number_format($pendingAmt, 0, ',', '.') }}</div>
            </div>
            @endif
        </div>

        {{-- Progress bar --}}
        <div class="rec-progress-row" style="margin-bottom:16px;">
            <div class="rec-progress-bar">
                <div class="rec-progress-fill" style="width:{{ $totalPayments > 0 ? round($paidCount/$totalPayments*100) : 0 }}%;"></div>
            </div>
            <span class="rec-counter">{{ $totalPayments > 0 ? round($paidCount/$totalPayments*100) : 0 }}%</span>
        </div>

        {{-- Item list --}}
        @foreach($paymentItemsWithStatus as $entry)
        @php $item = $entry['item']; @endphp
        <div style="display:flex;align-items:center;gap:10px;padding:8px 10px;border-radius:9px;transition:background 0.1s;"
             onmouseover="this.style.background='rgba(255,255,255,0.025)'"
             onmouseout="this.style.background='transparent'">
            <div style="width:30px;height:30px;border-radius:8px;background:var(--surface2);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <span style="font-family:'Bricolage Grotesque',sans-serif;font-size:11px;font-weight:800;color:var(--muted);">{{ $item->day_of_month }}</span>
            </div>
            <div style="width:7px;height:7px;border-radius:50%;flex-shrink:0;background:{{ $entry['is_paid'] ? 'var(--income)' : 'var(--accent2)' }};"></div>
            <div style="flex:1;min-width:0;">
                <div style="font-size:13px;font-weight:700;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $item->description }}</div>
                <div style="font-size:10px;color:var(--muted);margin-top:1px;">
                    {{ $item->account?->name ?? '—' }}
                    @if($item->category) · {{ $item->category->name }} @endif
                    @if($entry['paid_at']) · <span style="color:var(--income);">{{ \Carbon\Carbon::parse($entry['paid_at'])->format('d/m') }}</span> @endif
                </div>
            </div>
            <span style="font-size:9px;font-weight:800;letter-spacing:0.05em;text-transform:uppercase;padding:2px 8px;border-radius:20px;white-space:nowrap;{{ $entry['is_paid'] ? 'background:rgba(45,216,112,0.1);color:var(--income)' : 'background:rgba(78,155,255,0.1);color:var(--accent2)' }};">
                {{ $entry['is_paid'] ? 'Pagado' : 'Pendiente' }}
            </span>
            @if($entry['amount'] !== null)
            <div style="font-family:'Bricolage Grotesque',sans-serif;font-size:13px;font-weight:800;white-space:nowrap;min-width:80px;text-align:right;color:{{ $entry['is_paid'] ? 'var(--muted)' : 'var(--expense)' }};">
                {{ $item->currency }} $ {{ number_format($entry['amount'], 0, ',', '.') }}
            </div>
            @else
            <div style="font-size:12px;color:var(--muted);min-width:80px;text-align:right;">—</div>
            @endif
        </div>
        @endforeach

        @else
            <div class="empty-state">Sin gastos fijos configurados</div>
        @endif
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════════════
     OPORTUNIDADES DE AHORRO (solo mes en curso)
     ═══════════════════════════════════════════════════════════════════════ --}}
@if($isCurrentMonth && $dispensableItemsStatus->isNotEmpty())
<div class="report-section">
    <div class="section-header">
        <span class="section-label">Oportunidades de ahorro</span>
        <span style="font-size:10px;color:var(--warn);font-weight:700;display:flex;align-items:center;gap:4px;">
            <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            gastos prescindibles
        </span>
        <div class="section-line"></div>
    </div>

    <div class="savings-opp-card">
        <div class="savings-opp-header">
            <div>
                <div class="savings-opp-badge">
                    <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12l2 2 4-4"/></svg>
                    Prescindibles activos este mes
                </div>
                <div class="savings-opp-desc">
                    Estos ítems de pago están marcados como prescindibles. Cancelarlos o posponerlos puede liberar liquidez mensual.
                </div>
            </div>
            <div class="savings-opp-total-block">
                <div class="savings-opp-total-label">Potencial de ahorro</div>
                <div class="savings-opp-total-val">$ {{ number_format($dispensableTotal, 0, ',', '.') }}</div>
                @if($totalIncome > 0)
                <div class="savings-opp-total-sub">
                    {{ round(($dispensableTotal / $totalIncome) * 100, 1) }}% de tus ingresos del mes
                </div>
                @endif
            </div>
        </div>

        {{-- Lista de ítems prescindibles --}}
        @foreach($dispensableItemsStatus as $entry)
        @php $item = $entry['item']; $isPaid = $entry['is_paid']; @endphp
        <div class="dispensable-item {{ $isPaid ? 'paid' : '' }}">
            <div class="disp-icon {{ $isPaid ? 'paid' : '' }}">
                @if($isPaid)
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                @else
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                @endif
            </div>
            <div class="disp-body">
                <div class="disp-name {{ $isPaid ? 'paid' : '' }}">{{ $item->description }}</div>
                @if($item->category || $item->account)
                <div class="disp-cat">
                    @if($item->account){{ $item->account->name }}@endif
                    @if($item->category && $item->account) · @endif
                    @if($item->category){{ $item->category->name }}@endif
                </div>
                @endif
            </div>
            <span class="disp-status {{ $isPaid ? 'paid' : 'pending' }}">
                {{ $isPaid ? 'Ya pagado' : 'Sin pagar' }}
            </span>
            @if($entry['amount_ars'] !== null)
            <div class="disp-amount {{ $isPaid ? 'muted' : 'warn' }}">
                $ {{ number_format($entry['amount_ars'], 0, ',', '.') }}
            </div>
            @elseif($item->amount)
            <div class="disp-amount muted">
                {{ $item->currency }} $ {{ number_format($item->amount, 0, ',', '.') }}
            </div>
            @else
            <div class="disp-amount muted">—</div>
            @endif
        </div>
        @endforeach

        {{-- Barra de impacto sobre ingresos --}}
        @if($totalIncome > 0)
        @php $savingImpactPct = min(100, round(($dispensableTotal / $totalIncome) * 100)); @endphp
        <div class="savings-opp-impact">
            <div class="savings-opp-impact-header">
                <span>Impacto sobre ingresos del mes</span>
                <span class="savings-opp-impact-pct">{{ $savingImpactPct }}%</span>
            </div>
            <div class="savings-opp-track">
                <div class="savings-opp-fill" style="width:{{ $savingImpactPct }}%;"></div>
            </div>
            @php
                $newSavingsRate = $totalIncome > 0
                    ? round((($totalIncome - ($totalExpense - $dispensableTotal)) / $totalIncome) * 100, 1)
                    : 0;
            @endphp
            <div style="display:flex;justify-content:space-between;margin-top:10px;">
                <span style="font-size:11px;color:var(--muted);font-weight:600;">
                    Tasa de ahorro actual: <strong style="color:var(--text);">{{ $savingsRate }}%</strong>
                </span>
                <span style="font-size:11px;color:var(--muted);font-weight:600;">
                    Sin prescindibles: <strong style="color:var(--income);">{{ $newSavingsRate }}%</strong>
                </span>
            </div>
        </div>
        @endif
    </div>
</div>
@endif

{{-- ═══════════════════════════════════════════════════════════════════════
     PREVISIÓN (solo mes en curso)
     ═══════════════════════════════════════════════════════════════════════ --}}
@if($isCurrentMonth && $forecast)
<div class="report-section">
    <div class="section-header">
        <span class="section-label">Previsión de cierre</span>
        <div class="section-line"></div>
    </div>

    <div class="forecast-card">
        {{-- Progress del mes --}}
        <div class="forecast-month-progress">
            <div class="forecast-progress-header">
                <span class="forecast-prog-label">Avance del mes — día {{ $forecast['days_passed'] }} de {{ $forecast['days_in_month'] }}, quedan {{ $forecast['days_remaining'] }} días</span>
                <span class="forecast-prog-pct">{{ $forecast['progress_pct'] }}%</span>
            </div>
            <div class="forecast-prog-track">
                <div class="forecast-prog-fill" style="width:{{ $forecast['progress_pct'] }}%;"></div>
            </div>
        </div>

        {{-- KPIs --}}
        <div class="forecast-kpis">
            <div class="forecast-kpi">
                <div class="fkpi-label">Gasto diario actual</div>
                <div class="fkpi-val amount-expense">$ {{ number_format($forecast['daily_avg'], 0, ',', '.') }}</div>
                <div class="fkpi-sub">por día promedio</div>
            </div>
            <div class="forecast-kpi">
                <div class="fkpi-label">Gasto proyectado</div>
                <div class="fkpi-val amount-expense">$ {{ number_format($forecast['projected_expense'], 0, ',', '.') }}</div>
                <div class="fkpi-sub">al ritmo actual</div>
            </div>
            <div class="forecast-kpi">
                <div class="fkpi-label">Total proyectado</div>
                <div class="fkpi-val" style="color:var(--warn);">$ {{ number_format($forecast['projected_total'], 0, ',', '.') }}</div>
                <div class="fkpi-sub">incl. gastos fijos y cuotas</div>
            </div>
        </div>

        {{-- Breakdown --}}
        <div class="forecast-breakdown">
            <div class="fcast-row">
                <span class="fcast-label">Gasto registrado hasta hoy</span>
                <span class="fcast-val amount-expense">$ {{ number_format($totalExpense, 0, ',', '.') }}</span>
            </div>
            <div class="fcast-row">
                <span class="fcast-label">Gasto proyectado resto del mes</span>
                <span class="fcast-val" style="color:var(--muted);">+ $ {{ number_format($forecast['projected_expense'] - $totalExpense, 0, ',', '.') }}</span>
            </div>
            @if($forecast['pending_payments'] > 0)
            <div class="fcast-row">
                <span class="fcast-label">Gastos fijos pendientes</span>
                <span class="fcast-val" style="color:var(--warn);">+ $ {{ number_format($forecast['pending_payments'], 0, ',', '.') }}</span>
            </div>
            @endif
            @if($forecast['pending_installments'] > 0)
            <div class="fcast-row">
                <span class="fcast-label">Cuotas pendientes</span>
                <span class="fcast-val" style="color:var(--warn);">+ $ {{ number_format($forecast['pending_installments'], 0, ',', '.') }}</span>
            </div>
            @endif
            <div class="fcast-row" style="font-size:14px;">
                <span class="fcast-label" style="color:var(--text);">Total proyectado</span>
                <span class="fcast-val" style="color:var(--warn);font-size:15px;">$ {{ number_format($forecast['projected_total'], 0, ',', '.') }}</span>
            </div>
        </div>

        {{-- Resultado --}}
        <div class="forecast-result {{ $forecast['projected_balance'] >= 0 ? 'positive' : 'negative' }}">
            <div>
                <div class="fr-label" style="color:{{ $forecast['projected_balance'] >= 0 ? 'var(--income)' : 'var(--expense)' }};">
                    Balance proyectado al cierre
                </div>
                <div class="forecast-note">Estimado con ingreso promedio de $ {{ number_format($forecast['avg_income'], 0, ',', '.') }} (prom. 3 meses)</div>
            </div>
            <div class="fr-val" style="color:{{ $forecast['projected_balance'] >= 0 ? 'var(--income)' : 'var(--expense)' }};">
                {{ $forecast['projected_balance'] >= 0 ? '+' : '' }}$ {{ number_format($forecast['projected_balance'], 0, ',', '.') }}
            </div>
        </div>
    </div>
</div>
@endif

{{-- ═══════════════════════════════════════════════════════════════════════
     7. CUENTAS Y PATRIMONIO
     ═══════════════════════════════════════════════════════════════════════ --}}
<div class="report-section">
    <div class="section-header">
        <span class="section-label">Patrimonio</span>
        <div class="section-line"></div>
    </div>

    <div class="two-col">
        <div class="panel-card">
            <div class="panel-title">Cuentas activas</div>
            <div class="panel-subtitle" style="margin-bottom:10px;">Balance actual</div>
            @foreach($allAccounts->groupBy('type') as $type => $accounts)
            <div class="acc-type-label">
                {{ match($type) { 'cash'=>'Efectivo','digital'=>'Digital','credit'=>'Tarjetas de crédito','loan'=>'Préstamos',default=>$type } }}
            </div>
            @foreach($accounts as $acc)
            <div class="acc-row-v2">
                <span class="acc-name-v2">{{ $acc->name }}</span>
                <span class="acc-val-v2" style="color:{{ $acc->isLiability() ? 'var(--expense)' : 'var(--income)' }};">
                    {{ $acc->currency }} $ {{ number_format(abs($acc->balance), 0, ',', '.') }}
                </span>
            </div>
            @endforeach
            @endforeach
        </div>

        <div class="panel-card">
            <div class="panel-title">Patrimonio neto</div>
            <div class="panel-subtitle" style="margin-bottom:0;">Todo convertido a ARS</div>
            <div class="networth-display">
                <div class="networth-item">
                    <div>
                        <div class="nw-label">Activos</div>
                    </div>
                    <div class="nw-val amount-income" style="font-size:22px;">$ {{ number_format($totalAssets, 0, ',', '.') }}</div>
                </div>
                <div class="networth-item">
                    <div>
                        <div class="nw-label">Pasivos</div>
                    </div>
                    <div class="nw-val amount-expense" style="font-size:22px;">$ {{ number_format($totalLiabilities, 0, ',', '.') }}</div>
                </div>
                <div class="networth-item">
                    <div>
                        <div class="nw-label">Patrimonio neto</div>
                        @if($exchangeRate)
                            <div style="font-size:10px;color:var(--muted);margin-top:2px;">TC: 1 USD = $ {{ number_format($exchangeRate->rate, 2, ',', '.') }}</div>
                        @endif
                    </div>
                    <div class="nw-val" style="font-size:28px;color:{{ $netWorth >= 0 ? 'var(--accent)' : 'var(--expense)' }};">
                        {{ $netWorth >= 0 ? '+' : '' }}$ {{ number_format($netWorth, 0, ',', '.') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Form PDF oculto --}}
<form id="form-pdf" method="POST" action="{{ route('reports.monthly.pdf', ['mes' => $date->format('Y-m')]) }}" style="display:none;">
    @csrf
    <input type="hidden" name="mes" value="{{ $date->format('Y-m') }}">
    <input type="hidden" name="charts[monthly]" id="img-monthly">
    <input type="hidden" name="charts[daily]"   id="img-daily">
    <input type="hidden" name="charts[cats]"    id="img-cats">
</form>

{{-- ═══════════════════════════════════════════════════════════════════════
     SCRIPTS
     ═══════════════════════════════════════════════════════════════════════ --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
const C = {
    income:  '#2dd870',
    expense: '#f04060',
    accent:  '#f0a030',
    accent2: '#4e9bff',
    warn:    '#e8b840',
    muted:   '#6a6676',
    border:  '#282834',
    surface: '#17171d',
    text:    '#eeebe4',
};

Chart.defaults.color         = C.muted;
Chart.defaults.borderColor   = C.border;
Chart.defaults.font.family   = 'Nunito';
Chart.defaults.font.size     = 11;

// ── Barras mensuales ─────────────────────────────────────────────────────
const monthlyData = @json($chartData);
const ctxMonthly  = document.getElementById('chartMonthly').getContext('2d');
const chartMonthly = new Chart(ctxMonthly, {
    type: 'bar',
    data: {
        labels: monthlyData.map(m => m.label),
        datasets: [
            {
                label: 'Ingresos',
                data: monthlyData.map(m => m.income),
                backgroundColor: monthlyData.map(m => m.isCurrent ? C.income : 'rgba(45,216,112,0.3)'),
                borderRadius: 5,
                borderSkipped: false,
            },
            {
                label: 'Egresos',
                data: monthlyData.map(m => m.expense),
                backgroundColor: monthlyData.map(m => m.isCurrent ? C.expense : 'rgba(240,64,96,0.3)'),
                borderRadius: 5,
                borderSkipped: false,
            },
        ],
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'bottom', labels: { padding: 14, boxWidth: 10, boxHeight: 10, usePointStyle: true } },
            tooltip: { callbacks: { label: ctx => ` $ ${ctx.parsed.y.toLocaleString('es-AR')}` } },
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

// ── Gasto diario (línea) ─────────────────────────────────────────────────
const dailyData = @json($dailySpending);
const today     = {{ now()->day }};
const isCurrent = {{ $isCurrentMonth ? 'true' : 'false' }};

const ctxDaily = document.getElementById('chartDaily').getContext('2d');

const gradBlue = ctxDaily.createLinearGradient(0, 0, 0, 200);
gradBlue.addColorStop(0, 'rgba(78,155,255,0.22)');
gradBlue.addColorStop(1, 'rgba(78,155,255,0)');

const gradGreen = ctxDaily.createLinearGradient(0, 0, 0, 200);
gradGreen.addColorStop(0, 'rgba(45,216,112,0.18)');
gradGreen.addColorStop(1, 'rgba(45,216,112,0)');

const chartDaily = new Chart(ctxDaily, {
    type: 'line',
    data: {
        labels: dailyData.map(d => d.day),
        datasets: [
            {
                label: 'Gasto acumulado',
                data: dailyData.map(d => d.cumulative),
                borderColor: C.accent2,
                backgroundColor: gradBlue,
                borderWidth: 2,
                fill: true,
                tension: 0.4,
                pointRadius: isCurrent ? dailyData.map((d, i) => i + 1 === today ? 5 : 0) : 0,
                pointBackgroundColor: C.accent,
                pointBorderColor: C.surface,
                pointBorderWidth: 2,
            },
            {
                label: 'Ingreso acumulado',
                data: dailyData.map(d => d.income_cumulative),
                borderColor: 'rgba(45,216,112,0.85)',
                backgroundColor: gradGreen,
                borderWidth: 2,
                fill: true,
                tension: 0.4,
                pointRadius: isCurrent ? dailyData.map((d, i) => i + 1 === today ? 5 : 0) : 0,
                pointBackgroundColor: 'rgba(45,216,112,1)',
                pointBorderColor: C.surface,
                pointBorderWidth: 2,
            },
            {
                label: 'Gasto diario',
                data: dailyData.map(d => d.amount),
                borderColor: 'rgba(240,64,96,0.5)',
                backgroundColor: 'transparent',
                borderWidth: 1.5,
                borderDash: [4, 4],
                tension: 0.3,
                pointRadius: 0,
            },
            {
                label: 'Ingreso diario',
                data: dailyData.map(d => d.income_amount),
                borderColor: 'rgba(45,216,112,0.45)',
                backgroundColor: 'transparent',
                borderWidth: 1.5,
                borderDash: [4, 4],
                tension: 0.3,
                pointRadius: 0,
            },
        ],
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'bottom', labels: { padding: 14, boxWidth: 10, boxHeight: 10, usePointStyle: true } },
            tooltip: { callbacks: { label: ctx => ` $ ${ctx.parsed.y.toLocaleString('es-AR')}` } },
        },
        scales: {
            x: { grid: { display: false }, ticks: { maxTicksLimit: 8 } },
            y: {
                grid: { color: C.border },
                ticks: { callback: v => '$ ' + (v >= 1000 ? Math.round(v/1000) + 'k' : v) },
            },
        },
    },
});

// ── Donut categorías (gastos) ────────────────────────────────────────────
const catData  = @json($byCategory);
const ctxCats  = document.getElementById('chartCategories').getContext('2d');
const chartCats = new Chart(ctxCats, {
    type: 'doughnut',
    data: {
        labels: catData.map(c => c.name),
        datasets: [{
            data: catData.map(c => c.total),
            backgroundColor: catData.map(c => c.color || C.muted),
            borderWidth: 2,
            borderColor: '#111115',
            hoverOffset: 6,
        }],
    },
    options: {
        responsive: false,
        cutout: '70%',
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: ctx => ` $ ${ctx.parsed.toLocaleString('es-AR')} (${catData[ctx.dataIndex].percent}%)`,
                },
            },
        },
    },
});

// ── Donut categorías (ingresos) ──────────────────────────────────────────
const incCatData = @json($byCategoryIncome);
const ctxIncCats = document.getElementById('chartCategoriesIncome');
let chartIncCats = null;
if (ctxIncCats && incCatData.length) {
    chartIncCats = new Chart(ctxIncCats.getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: incCatData.map(c => c.name),
            datasets: [{
                data: incCatData.map(c => c.income),
                backgroundColor: incCatData.map(c => c.color || C.muted),
                borderWidth: 2,
                borderColor: '#111115',
                hoverOffset: 6,
            }],
        },
        options: {
            responsive: false,
            cutout: '70%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ` $ ${ctx.parsed.toLocaleString('es-AR')} (${incCatData[ctx.dataIndex].income_pct}%)`,
                    },
                },
            },
        },
    });
}

// ── Ring animación gastos hormiga ────────────────────────────────────────
(function animateAntRing() {
    const ring   = document.getElementById('antRingFill');
    if (!ring) return;
    const target = parseFloat(ring.dataset.target);
    const total  = parseFloat(ring.getAttribute('stroke-dasharray'));

    let start = null;
    const duration = 1200;

    function step(ts) {
        if (!start) start = ts;
        const progress = Math.min((ts - start) / duration, 1);
        const ease = 1 - Math.pow(1 - progress, 3);
        ring.setAttribute('stroke-dashoffset', total - (total - target) * ease);
        if (progress < 1) requestAnimationFrame(step);
    }
    requestAnimationFrame(step);
})();

// ── Sparkline: tasa de ahorro últimos 6 meses ───────────────────────────
(function drawSavingsSpark() {
    const canvas = document.getElementById('chartSavingsSpark');
    if (!canvas) return;
    const data = @json($savingsHistory);
    if (!data.length) return;
    const points = data.map(m => m.rate === null ? null : m.rate);
    // Reemplazamos nulls por 0 para dibujar
    const numeric = points.map(v => v === null ? 0 : v);
    new Chart(canvas.getContext('2d'), {
        type: 'line',
        data: {
            labels: data.map(m => m.label),
            datasets: [{
                data: numeric,
                borderColor: C.accent,
                backgroundColor: 'rgba(240,160,48,0.15)',
                borderWidth: 1.8,
                pointRadius: numeric.map((_, i) => i === numeric.length - 1 ? 3 : 0),
                pointBackgroundColor: C.accent,
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

// ── Ring animación gastos evitables ─────────────────────────────────────
(function animateAvoidableRing() {
    const ring = document.getElementById('avoidableRingFill');
    if (!ring) return;
    const target = parseFloat(ring.dataset.target);
    const total  = parseFloat(ring.getAttribute('stroke-dasharray'));
    let start = null;
    const duration = 1200;
    function step(ts) {
        if (!start) start = ts;
        const progress = Math.min((ts - start) / duration, 1);
        const ease = 1 - Math.pow(1 - progress, 3);
        ring.setAttribute('stroke-dashoffset', total - (total - target) * ease);
        if (progress < 1) requestAnimationFrame(step);
    }
    requestAnimationFrame(step);
})();

// ── Generar PDF ──────────────────────────────────────────────────────────
function generarPDF() {
    const btn = document.getElementById('btn-pdf');
    btn.classList.add('loading');
    btn.innerHTML = `<svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="animation:spin 0.8s linear infinite"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg> Generando...`;

    try {
        document.getElementById('img-monthly').value = chartMonthly.toBase64Image();
        document.getElementById('img-daily').value   = chartDaily.toBase64Image();
        document.getElementById('img-cats').value    = chartCats.toBase64Image();
    } catch (e) {}

    document.getElementById('form-pdf').submit();

    setTimeout(() => {
        btn.classList.remove('loading');
        btn.innerHTML = `<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg> Exportar PDF`;
    }, 3500);
}
</script>

@endsection
