@extends('layouts.app')

@section('title', 'Movimientos')

@section('content')

<style>
/* ── Responsive header ───────────────────────────────────────────── */
@media (max-width: 640px) {
    .tx-header { flex-direction: column !important; align-items: flex-start !important; gap: 10px !important; }
    .tx-header-actions { width: 100%; }
    .tx-header-actions .btn { flex: 1; justify-content: center; font-size: 13px; }
    .tx-stat-grid { grid-template-columns: repeat(2, 1fr) !important; }
}
@media (max-width: 380px) {
    .tx-stat-grid { grid-template-columns: 1fr !important; }
}

/* ── Desktop: show table, hide cards ─────────────────────────────── */
.tx-mobile-list { display: none; }
@media (max-width: 640px) {
    .tx-desktop-table { display: none !important; }
    .tx-mobile-list   { display: flex; flex-direction: column; }
    /* hide top action buttons on mobile — FAB handles "nuevo" */
    .tx-header-actions .btn-primary { display: none; }
}

/* ── Mobile transaction card ─────────────────────────────────────── */
.tx-card {
    display: flex; flex-direction: column; gap: 6px;
    padding: 14px 16px;
    border-bottom: 1px solid rgba(40,40,52,0.6);
    text-decoration: none; color: inherit;
    transition: background 0.1s;
    position: relative;
}
.tx-card:last-child { border-bottom: none; }
.tx-card:active { background: rgba(255,255,255,0.02); }

.tx-card-top {
    display: flex; align-items: center;
    justify-content: space-between; gap: 8px;
}
.tx-card-amount {
    font-family: 'Bricolage Grotesque', sans-serif;
    font-size: 17px; font-weight: 800;
    letter-spacing: -0.02em; white-space: nowrap;
}
.tx-card-desc {
    font-size: 14px; font-weight: 600; color: var(--text);
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.tx-card-meta {
    display: flex; align-items: center; gap: 6px;
    flex-wrap: wrap;
    font-size: 11px; color: var(--muted);
}
.tx-card-actions {
    display: flex; gap: 4px;
    position: absolute; right: 12px; bottom: 12px;
}

/* ── Filter bar (mobile only) ────────────────────────────────────── */
.tx-filter-bar { display: none; }
.tx-filter-card { display: block; }
@media (max-width: 640px) {
    .tx-filter-card { display: none; }
    .tx-filter-bar  { display: flex; align-items: center; gap: 8px; margin-bottom: 14px; flex-wrap: wrap; }
}

/* ── Filter bottom sheet ─────────────────────────────────────────── */
.filter-sheet {
    position: fixed;
    bottom: 0; left: 0; right: 0; top: auto;
    width: 100%; max-width: 100%;
    max-height: 88vh;
    margin: 0;
    padding: 0;
    border: none;
    border-radius: 20px 20px 0 0;
    background: var(--surface);
    color: var(--text);
    color-scheme: dark;
    border-top: 1px solid var(--border);
    overflow: auto;
    -webkit-overflow-scrolling: touch;
}
.filter-sheet .form-input,
.filter-sheet input[type=text] {
    background: var(--surface2, #2a2a35);
    color: var(--text);
    border: 1px solid var(--border);
    width: 100%;
}
.filter-sheet .form-input::placeholder,
.filter-sheet input[type=text]::placeholder { color: var(--muted); }
.filter-sheet::backdrop {
    background: rgba(0,0,0,0.55);
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
}
.filter-sheet-handle {
    width: 36px; height: 4px;
    background: var(--border);
    border-radius: 2px;
    margin: 12px auto 0;
}
.filter-sheet-body { padding: 16px 20px 24px; }

/* ── Action links ────────────────────────────────────────────────── */
.tx-action-link {
    display: inline-flex; align-items: center; min-height: 36px;
    padding: 0 10px; border-radius: 6px;
    color: var(--muted); font-size: 12px; text-decoration: none;
    transition: color 0.15s, background 0.15s;
}
.tx-action-link:hover { color: var(--text); background: var(--surface2); }
.tx-action-btn {
    display: inline-flex; align-items: center; min-height: 36px;
    padding: 0 10px; border-radius: 6px; background: none; border: none;
    color: var(--danger); font-size: 12px; cursor: pointer;
    font-family: 'Nunito', sans-serif; transition: background 0.15s;
}
.tx-action-btn:hover { background: rgba(240,64,96,0.08); }

/* ── Filter active chip ──────────────────────────────────────────── */
.filter-chip {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 5px 10px; border-radius: 20px;
    background: var(--accent-dim); border: 1px solid rgba(240,160,48,0.25);
    color: var(--accent); font-size: 11px; font-weight: 700;
    text-decoration: none;
}
.filter-chip-x { font-size: 13px; line-height: 1; opacity: 0.7; }

/* ── Inline tag trigger (desktop table) ──────────────────────────── */
.itag-trigger {
    display: inline-flex; align-items: center; justify-content: center;
    width: 22px; height: 22px; border-radius: 5px;
    border: 1px dashed rgba(255,255,255,0.14);
    background: none; color: var(--muted);
    cursor: pointer; flex-shrink: 0; padding: 0;
    vertical-align: middle; margin-left: 4px;
    transition: border-color 0.15s, color 0.15s, background 0.15s, opacity 0.15s;
    opacity: 0;
}
.tx-desktop-table tr:hover .itag-trigger { opacity: 1; }
.itag-trigger:hover {
    border-color: rgba(240,160,48,0.5);
    color: var(--accent);
    background: rgba(240,160,48,0.08);
}
.itag-trigger[data-state="saving"] { opacity: 0.6; animation: itag-spin 0.7s linear infinite; }
.itag-trigger[data-state="saved"]  { border-color: var(--income); color: var(--income); background: rgba(45,216,112,0.08); opacity: 1; }
.itag-trigger[data-state="error"]  { border-color: var(--danger);  color: var(--danger);  opacity: 1; }
@keyframes itag-spin { to { transform: rotate(360deg); } }

/* ── Inline tag popover (fixed, appended to body) ────────────────── */
.itag-pop {
    position: fixed;
    z-index: 9999;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 12px;
    width: 260px;
    padding: 10px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.55);
}

/* ── Mobile tag button ───────────────────────────────────────────── */
.itag-mobile-btn {
    display: inline-flex; align-items: center; gap: 4px;
    min-height: 30px; padding: 0 8px; border-radius: 6px;
    background: none; border: none;
    color: var(--muted); font-size: 11px; cursor: pointer;
    font-family: 'Nunito', sans-serif;
    transition: background 0.15s, color 0.15s;
}
.itag-mobile-btn:hover, .itag-mobile-btn:active { color: var(--accent); background: rgba(240,160,48,0.08); }
.itag-mobile-btn[data-state="saving"] { animation: itag-spin 0.7s linear infinite; opacity: 0.6; }
.itag-mobile-btn[data-state="saved"]  { color: var(--income); }
.itag-mobile-btn[data-state="error"]  { color: var(--danger); }
</style>

@php
    $mkSort = function(string $col, string $label) use ($sortBy, $sortDir): string {
        $active = $sortBy === $col;
        $newDir = ($active && $sortDir === 'asc') ? 'desc' : 'asc';
        $url    = request()->fullUrlWithQuery(['sort' => $col, 'dir' => $newDir]);
        $upOp   = !$active ? '0.35' : ($sortDir === 'asc'  ? '1' : '0.2');
        $dnOp   = !$active ? '0.35' : ($sortDir === 'desc' ? '1' : '0.2');
        $aria   = $active ? ($sortDir === 'asc' ? 'ascending' : 'descending') : 'none';
        $cls    = $active ? 'sort-link sort-active' : 'sort-link';
        return "<a href=\"{$url}\" class=\"{$cls}\" aria-sort=\"{$aria}\">{$label}"
            . "<svg width=\"8\" height=\"11\" viewBox=\"0 0 8 11\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"1.8\" stroke-linecap=\"round\" stroke-linejoin=\"round\" style=\"display:inline-block;vertical-align:middle;margin-left:4px;\">"
            . "<path d=\"M1 4.5L4 1.5L7 4.5\" style=\"opacity:{$upOp}\"/>"
            . "<path d=\"M1 6.5L4 9.5L7 6.5\" style=\"opacity:{$dnOp}\"/>"
            . "</svg></a>";
    };
@endphp

{{-- Header --}}
<div class="tx-header" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
    <div>
        <h1 class="font-display" style="font-size: 24px; font-weight: 700; letter-spacing: -0.02em;">Movimientos</h1>
        <div style="font-size: 12px; color: var(--muted); margin-top: 3px;">
            Todos los gastos e ingresos del grupo
        </div>
    </div>
    <div class="tx-header-actions" style="display:flex;gap:8px;align-items:center;">
        <a href="{{ route('card-payment.create') }}" class="btn btn-ghost" style="border-color:rgba(78,155,255,0.3);color:var(--accent2);">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
            Pagar tarjeta
        </a>
        <a href="{{ route('transactions.create') }}" class="btn btn-primary">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
            Nuevo movimiento
        </a>
    </div>
</div>

{{-- Stats del mes --}}
<div class="tx-stat-grid" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 20px;">
    <div class="stat-card expense">
        <div style="font-size: 11px; letter-spacing: 0.09em; text-transform: uppercase; color: var(--muted); margin-bottom: 10px; font-weight: 700;">Gastos</div>
        <div class="font-display" style="font-size: 22px; font-weight: 800; color: var(--expense); letter-spacing: -0.03em; line-height: 1;">
            $ {{ number_format($monthStats['expenses'], 2, ',', '.') }}
        </div>
        <div style="font-size: 11px; color: var(--muted); margin-top: 6px; font-weight: 500;">{{ $monthLabel }}</div>
    </div>
    <div class="stat-card income">
        <div style="font-size: 11px; letter-spacing: 0.09em; text-transform: uppercase; color: var(--muted); margin-bottom: 10px; font-weight: 700;">Ingresos</div>
        <div class="font-display" style="font-size: 22px; font-weight: 800; color: var(--income); letter-spacing: -0.03em; line-height: 1;">
            $ {{ number_format($monthStats['income'], 2, ',', '.') }}
        </div>
        <div style="font-size: 11px; color: var(--muted); margin-top: 6px; font-weight: 500;">{{ $monthLabel }}</div>
    </div>
    <div class="stat-card balance">
        <div style="font-size: 11px; letter-spacing: 0.09em; text-transform: uppercase; color: var(--muted); margin-bottom: 10px; font-weight: 700;">Balance</div>
        <div class="font-display" style="font-size: 22px; font-weight: 800; letter-spacing: -0.03em; line-height: 1; color: {{ $monthStats['balance'] >= 0 ? 'var(--income)' : 'var(--expense)' }};">
            {{ $monthStats['balance'] >= 0 ? '+' : '−' }}$ {{ number_format(abs($monthStats['balance']), 2, ',', '.') }}
        </div>
        <div style="font-size: 11px; color: var(--muted); margin-top: 6px; font-weight: 500;">Ingresos − Gastos</div>
    </div>
    <div class="stat-card neutral">
        <div style="font-size: 11px; letter-spacing: 0.09em; text-transform: uppercase; color: var(--muted); margin-bottom: 10px; font-weight: 700;">Movimientos</div>
        <div class="font-display" style="font-size: 22px; font-weight: 800; color: var(--warn); letter-spacing: -0.03em; line-height: 1;">
            {{ number_format($monthStats['count']) }}
        </div>
        <div style="font-size: 11px; color: var(--muted); margin-top: 6px; font-weight: 500;">{{ $monthLabel }}</div>
    </div>
</div>

{{-- ── Filter bar: mobile only ───────────────────────────────────────────── --}}
@php
    $activeFilters = array_filter([
        'month'       => request('month') && request('month') !== now()->format('Y-m') ? request('month') : null,
        'type'        => request('type'),
        'account_id'  => request('account_id'),
        'category_id' => request('category_id'),
    ]);
    $filterCount = count($activeFilters);
@endphp
<div class="tx-filter-bar">
    <button type="button" onclick="document.getElementById('filter-sheet').showModal()"
            class="btn btn-ghost" style="position:relative; gap:8px;">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
        </svg>
        Filtros
        @if($filterCount > 0)
            <span style="position:absolute;top:-5px;right:-5px;width:16px;height:16px;border-radius:50%;background:var(--accent);color:#0c0804;font-size:9px;font-weight:800;display:flex;align-items:center;justify-content:center;">{{ $filterCount }}</span>
        @endif
    </button>

    {{-- Active filter chips --}}
    @if(request('type'))
        <a href="{{ request()->fullUrlWithQuery(['type' => null]) }}" class="filter-chip">
            {{ match(request('type')) {
                'expense'     => 'Gastos',
                'income'      => 'Ingresos',
                'transfer'    => 'Transf.',
                'card_payment'=> 'Tarjeta',
                'adjustment'  => 'Ajuste',
                default       => request('type')
            } }}
            <span class="filter-chip-x">×</span>
        </a>
    @endif
    @if(request('account_id'))
        <a href="{{ request()->fullUrlWithQuery(['account_id' => null]) }}" class="filter-chip">
            {{ $accounts->find(request('account_id'))?->name ?? 'Cuenta' }}
            <span class="filter-chip-x">×</span>
        </a>
    @endif
    @if($filterCount > 0)
        <a href="{{ route('transactions.index') }}" style="font-size:12px;color:var(--muted);text-decoration:none;">Limpiar</a>
    @endif
</div>

{{-- ── Filtros desktop ────────────────────────────────────────────────────── --}}
<div class="tx-filter-card card" style="margin-bottom: 20px;">
    <form method="GET" style="display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;">
        <input type="hidden" name="sort" value="{{ $sortBy }}">
        <input type="hidden" name="dir"  value="{{ $sortDir }}">
        <div>
            <label class="form-label">Mes</label>
            <input type="month" name="month" value="{{ request('month', now()->format('Y-m')) }}" class="form-input" style="width: 160px;">
        </div>
        <div>
            <label class="form-label">Tipo</label>
            <select name="type" class="form-select" style="width: 160px;">
                <option value="">Todos</option>
                <option value="expense"     {{ request('type') === 'expense'     ? 'selected' : '' }}>Gastos</option>
                <option value="income"      {{ request('type') === 'income'      ? 'selected' : '' }}>Ingresos</option>
                <option value="transfer"    {{ request('type') === 'transfer'    ? 'selected' : '' }}>Transferencias</option>
                <option value="card_payment"{{ request('type') === 'card_payment'? 'selected' : '' }}>Pago tarjeta</option>
                <option value="adjustment"  {{ request('type') === 'adjustment'  ? 'selected' : '' }}>Ajustes</option>
            </select>
        </div>
        <div>
            <label class="form-label">Cuenta</label>
            <select name="account_id" class="form-select" style="width: 180px;">
                <option value="">Todas</option>
                @foreach($accounts as $account)
                    <option value="{{ $account->id }}" {{ request('account_id') == $account->id ? 'selected' : '' }}>{{ $account->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Categoría</label>
            <select name="category_id" class="form-select" style="width: 180px;">
                <option value="">Todas</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        @if($allTags->isNotEmpty())
        <div>
            <label class="form-label">Etiqueta</label>
            <select name="tag_id" class="form-select" style="width: 160px;">
                <option value="">Todas</option>
                @foreach($allTags as $tag)
                    <option value="{{ $tag->id }}" {{ request('tag_id') == $tag->id ? 'selected' : '' }}>{{ $tag->name }}</option>
                @endforeach
            </select>
        </div>
        @endif
        <div style="display:flex;gap:8px;">
            <button type="submit" class="btn btn-ghost">Filtrar</button>
            <a href="{{ route('transactions.index') }}" class="btn btn-ghost" style="color: var(--muted);">Limpiar</a>
        </div>
    </form>
</div>

{{-- ── Tabla + Cards ─────────────────────────────────────────────────────── --}}
<div class="card" style="padding: 0; overflow: hidden;">
    @if($transactions->isEmpty())
        <div style="text-align: center; padding: 60px 20px; color: var(--muted);">
            <div style="font-size: 32px; margin-bottom: 12px;">📭</div>
            <div style="font-size: 14px; margin-bottom: 16px;">Sin movimientos para los filtros seleccionados</div>
            <a href="{{ route('transactions.create') }}" class="btn btn-primary" style="display: inline-flex;">Cargar movimiento</a>
        </div>
    @else

        {{-- Desktop: tabla --}}
        <div class="table-wrap tx-desktop-table">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>{!! $mkSort('date', 'Fecha') !!}</th>
                        <th>{!! $mkSort('type', 'Tipo') !!}</th>
                        <th>{!! $mkSort('description', 'Descripción') !!}</th>
                        <th>Categoría</th>
                        <th>Cuenta</th>
                        <th>Quién</th>
                        <th style="text-align:right;">{!! $mkSort('amount', 'Monto') !!}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transactions as $tx)
                    <tr>
                        <td style="color: var(--muted); font-size: 12px; white-space: nowrap;">{{ $tx->date->format('d/m/Y') }}</td>
                        <td>
                            @if($tx->type === 'adjustment')
                                <span class="badge badge-adjustment" style="font-size:10px;">Ajuste {{ $tx->adjustment_direction === 'in' ? '▲' : '▼' }}</span>
                            @elseif($tx->is_card_payment)
                                <span class="badge badge-transfer" style="font-size:10px;background:rgba(78,155,255,0.12);color:var(--accent2);">💳 Pago tarjeta</span>
                            @else
                                <span class="badge badge-{{ $tx->type }}">{{ $tx->type === 'expense' ? 'Gasto' : ($tx->type === 'income' ? 'Ingreso' : 'Transfer.') }}</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('transactions.show', $tx) }}" style="color: var(--text); text-decoration: none;">{{ $tx->description ?? '—' }}</a>
                            @if($tx->has_installments)
                                <span class="badge badge-credit" style="margin-left: 6px; font-size: 10px;">{{ $tx->installments_count }} cuotas</span>
                            @endif
                            <span id="itag-pills-{{ $tx->id }}" style="display:contents;">
                                @foreach($tx->tags as $tag)
                                    <span style="display:inline-flex;align-items:center;gap:3px;padding:2px 7px 2px 5px;
                                                 border-radius:12px;font-size:10px;font-weight:700;margin-left:4px;
                                                 background:{{ $tag->color }}22;color:{{ $tag->color }};
                                                 border:1px solid {{ $tag->color }}44;">
                                        <span style="width:6px;height:6px;border-radius:50%;background:{{ $tag->color }};"></span>
                                        {{ $tag->name }}
                                    </span>
                                @endforeach
                            </span>
                            <button type="button"
                                    id="itag-trigger-{{ $tx->id }}"
                                    class="itag-trigger"
                                    onclick="InlineTags.togglePopover({{ $tx->id }})"
                                    title="Gestionar etiquetas"
                                    aria-label="Gestionar etiquetas">
                                <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/>
                                    <line x1="7" y1="7" x2="7.01" y2="7"/>
                                </svg>
                            </button>
                        </td>
                        <td>
                            @if($tx->category)
                            <div style="display:flex;align-items:center;gap:6px;">
                                @include('categories._icon', ['icon' => $tx->category->icon, 'color' => $tx->category->color, 'type' => $tx->category->type, 'size' => 'xs'])
                                <span style="font-size:12px;color:var(--muted);">{{ $tx->category->name }}</span>
                            </div>
                            @else
                            <span style="font-size:12px;color:var(--muted);">—</span>
                            @endif
                        </td>
                        <td><span class="badge badge-{{ $tx->account?->type ?? 'neutral' }}">{{ $tx->account?->name ?? '—' }}</span></td>
                        <td style="font-size: 12px; color: var(--muted);">{{ $tx->user?->name ?? '—' }}</td>
                        <td style="text-align: right; font-weight: 500; white-space: nowrap;">
                            @if($tx->isAdjustment())
                                <span style="color:#a078ff;">{{ $tx->adjustment_direction === 'in' ? '+' : '−' }} {{ $tx->currency === 'USD' ? 'US$' : '$' }} {{ number_format($tx->amount, 2, ',', '.') }}</span>
                            @else
                                <span class="{{ $tx->isIncome() ? 'amount-income' : ($tx->isExpense() ? 'amount-expense' : 'amount-neutral') }}">
                                    {{ $tx->isIncome() ? '+' : ($tx->isExpense() ? '−' : '') }}
                                    {{ $tx->currency === 'USD' ? 'US$' : '$' }} {{ number_format($tx->amount, 2, ',', '.') }}
                                </span>
                            @endif
                        </td>
                        <td style="white-space: nowrap;">
                            <a href="{{ route('transactions.edit', $tx) }}" class="tx-action-link">Editar</a>
                            <form method="POST" action="{{ route('transactions.destroy', $tx) }}" style="display:inline" onsubmit="return confirm('¿Eliminar este movimiento?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="tx-action-btn">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Mobile: cards --}}
        <div class="tx-mobile-list">
            @foreach($transactions as $tx)
            @php
                $amountClass = $tx->isAdjustment() ? '' : ($tx->isIncome() ? 'amount-income' : ($tx->isExpense() ? 'amount-expense' : 'amount-neutral'));
                $amountColor = $tx->isAdjustment() ? '#a078ff' : '';
                $sign = $tx->isIncome() ? '+' : ($tx->isExpense() ? '−' : '');
                if ($tx->isAdjustment()) $sign = $tx->adjustment_direction === 'in' ? '+' : '−';
            @endphp
            <div class="tx-card">
                <div class="tx-card-top">
                    <div style="display:flex;align-items:center;gap:8px;min-width:0;">
                        @if($tx->type === 'adjustment')
                            <span class="badge badge-adjustment" style="font-size:10px;flex-shrink:0;">Ajuste</span>
                        @elseif($tx->is_card_payment)
                            <span class="badge badge-transfer" style="font-size:10px;flex-shrink:0;background:rgba(78,155,255,0.12);color:var(--accent2);">💳 Tarjeta</span>
                        @else
                            <span class="badge badge-{{ $tx->type }}" style="font-size:10px;flex-shrink:0;">{{ $tx->type === 'expense' ? 'Gasto' : ($tx->type === 'income' ? 'Ingreso' : 'Transfer.') }}</span>
                        @endif
                        <a href="{{ route('transactions.show', $tx) }}" class="tx-card-desc">{{ $tx->description ?? '—' }}</a>
                    </div>
                    <div class="tx-card-amount {{ $amountClass }}" @if($amountColor) style="color:{{ $amountColor }}" @endif>
                        {{ $sign }}{{ $tx->currency === 'USD' ? 'US$' : '$' }}{{ number_format($tx->amount, 2, ',', '.') }}
                    </div>
                </div>
                <div class="tx-card-meta">
                    <span>{{ $tx->date->format('d/m') }}</span>
                    @if($tx->category)
                        <span>·</span>
                        @include('categories._icon', ['icon' => $tx->category->icon, 'color' => $tx->category->color, 'type' => $tx->category->type, 'size' => 'xs'])
                        <span>{{ $tx->category->name }}</span>
                    @endif
                    <span>·</span>
                    <span class="badge badge-{{ $tx->account?->type ?? 'neutral' }}" style="font-size:10px;">{{ $tx->account?->name ?? '—' }}</span>
                    @if($tx->has_installments)
                        <span class="badge badge-credit" style="font-size:10px;">{{ $tx->installments_count }}c</span>
                    @endif
                    <span id="itag-pills-m-{{ $tx->id }}" style="display:contents;">
                        @foreach($tx->tags as $tag)
                            <span style="display:inline-flex;align-items:center;gap:3px;padding:2px 6px 2px 5px;
                                         border-radius:10px;font-size:10px;font-weight:700;
                                         background:{{ $tag->color }}22;color:{{ $tag->color }};
                                         border:1px solid {{ $tag->color }}44;">
                                <span style="width:5px;height:5px;border-radius:50%;background:{{ $tag->color }};"></span>
                                {{ $tag->name }}
                            </span>
                        @endforeach
                    </span>
                </div>
                <div style="display:flex;gap:4px;margin-top:2px;">
                    <a href="{{ route('transactions.edit', $tx) }}" class="tx-action-link" style="font-size:11px;min-height:30px;padding:0 8px;">Editar</a>
                    <form method="POST" action="{{ route('transactions.destroy', $tx) }}" style="display:inline" onsubmit="return confirm('¿Eliminar?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="tx-action-btn" style="font-size:11px;min-height:30px;padding:0 8px;">Eliminar</button>
                    </form>
                    <button type="button"
                            id="itag-m-trigger-{{ $tx->id }}"
                            class="itag-mobile-btn"
                            onclick="InlineTags.openSheet({{ $tx->id }})"
                            data-state="">
                        <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/>
                            <line x1="7" y1="7" x2="7.01" y2="7"/>
                        </svg>
                        Etiquetas
                    </button>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Footer --}}
        <div style="padding: 14px 20px; border-top: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; font-size: 12px; color: var(--muted); flex-wrap: wrap; gap: 10px;">
            <div style="display: flex; align-items: center; gap: 16px;">
                <span>{{ $transactions->total() }} movimientos</span>
                <span style="display: flex; align-items: center; gap: 6px; background: var(--surface2); border: 1px solid var(--border); border-radius: 6px; padding: 4px 10px;">
                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color: var(--accent); flex-shrink: 0;"><path d="M12 20V4M5 13l7 7 7-7"/></svg>
                    <span style="font-weight: 600; color: var(--text);">Total filtrado:</span>
                    <span style="font-weight: 700; color: var(--accent); font-family: 'Bricolage Grotesque', sans-serif; letter-spacing: -0.02em;">
                        $ {{ number_format($filteredTotal, 2, ',', '.') }}
                    </span>
                </span>
            </div>
            @if($transactions->hasPages())
                {{ $transactions->links() }}
            @endif
        </div>
    @endif
</div>

{{-- ── Filter bottom sheet ───────────────────────────────────────────────── --}}
<dialog id="filter-sheet" class="filter-sheet">
    <div class="filter-sheet-handle"></div>
    <div class="filter-sheet-body">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
            <span class="font-display" style="font-size:16px;font-weight:700;">Filtros</span>
            <button type="button" onclick="document.getElementById('filter-sheet').close()"
                    style="background:none;border:none;color:var(--muted);cursor:pointer;padding:4px;font-size:20px;line-height:1;">×</button>
        </div>

        <form method="GET" style="display:flex;flex-direction:column;gap:14px;">
            <input type="hidden" name="sort" value="{{ $sortBy }}">
            <input type="hidden" name="dir"  value="{{ $sortDir }}">
            <div>
                <label class="form-label">Mes</label>
                <input type="month" name="month" value="{{ request('month', now()->format('Y-m')) }}" class="form-input">
            </div>
            <div>
                <label class="form-label">Tipo</label>
                <select name="type" class="form-select">
                    <option value="">Todos</option>
                    <option value="expense"     {{ request('type') === 'expense'     ? 'selected' : '' }}>Gastos</option>
                    <option value="income"      {{ request('type') === 'income'      ? 'selected' : '' }}>Ingresos</option>
                    <option value="transfer"    {{ request('type') === 'transfer'    ? 'selected' : '' }}>Transferencias</option>
                    <option value="card_payment"{{ request('type') === 'card_payment'? 'selected' : '' }}>Pago tarjeta</option>
                    <option value="adjustment"  {{ request('type') === 'adjustment'  ? 'selected' : '' }}>Ajustes</option>
                </select>
            </div>
            <div>
                <label class="form-label">Cuenta</label>
                <select name="account_id" class="form-select">
                    <option value="">Todas</option>
                    @foreach($accounts as $account)
                        <option value="{{ $account->id }}" {{ request('account_id') == $account->id ? 'selected' : '' }}>{{ $account->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Categoría</label>
                <select name="category_id" class="form-select">
                    <option value="">Todas</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            @if($allTags->isNotEmpty())
            <div>
                <label class="form-label">Etiqueta</label>
                <select name="tag_id" class="form-select">
                    <option value="">Todas</option>
                    @foreach($allTags as $tag)
                        <option value="{{ $tag->id }}" {{ request('tag_id') == $tag->id ? 'selected' : '' }}>{{ $tag->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div style="display:flex;gap:10px;margin-top:4px;">
                <button type="submit" class="btn btn-primary" style="flex:1;justify-content:center;">Aplicar</button>
                <a href="{{ route('transactions.index') }}" class="btn btn-ghost" style="flex:1;justify-content:center;">Limpiar</a>
            </div>
        </form>
    </div>
</dialog>

<script>
// Close dialog on backdrop click
document.getElementById('filter-sheet')?.addEventListener('click', function(e) {
    const rect = this.getBoundingClientRect();
    if (e.clientY < rect.top) this.close();
});
</script>

{{-- ── Desktop tag popovers (fuera del card overflow:hidden) ────────────── --}}
@foreach($transactions as $tx)
<div id="itag-pop-{{ $tx->id }}" class="itag-pop" style="display:none;">
    <input type="text"
           id="itag-search-{{ $tx->id }}"
           class="form-input"
           placeholder="Buscar etiqueta…"
           autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"
           style="margin-bottom:8px;font-size:13px;padding:9px 12px;width:100%;"
           oninput="InlineTags.filterList({{ $tx->id }}, this.value)"
           onkeydown="InlineTags._key(event, {{ $tx->id }})">
    <div id="itag-list-{{ $tx->id }}"
         style="max-height:220px;overflow-y:auto;display:flex;flex-direction:column;gap:2px;">
    </div>
</div>
@endforeach

{{-- ── Mobile tag sheet ──────────────────────────────────────────────────── --}}
<dialog id="itag-sheet" class="filter-sheet">
    <div class="filter-sheet-handle"></div>
    <div class="filter-sheet-body">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
            <span class="font-display" style="font-size:16px;font-weight:700;color:var(--text);">Etiquetas</span>
            <button type="button" onclick="document.getElementById('itag-sheet').close()"
                    style="background:none;border:none;color:var(--muted);cursor:pointer;padding:4px;font-size:24px;line-height:1;">×</button>
        </div>
        <input type="text" id="itag-sheet-search" class="form-input"
               placeholder="Buscar etiqueta…"
               autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"
               style="margin-bottom:12px;font-size:16px;padding:12px 14px;">
        <div id="itag-sheet-list"
             style="max-height:55vh;overflow-y:auto;display:flex;flex-direction:column;gap:2px;">
        </div>
        <div style="padding-top:12px;margin-top:8px;border-top:1px solid var(--border);">
            <button type="button" onclick="document.getElementById('itag-sheet').close()"
                    class="btn btn-ghost" style="width:100%;justify-content:center;">Listo</button>
        </div>
    </div>
</dialog>

<script>
(function () {

function _esc(s) {
    return String(s).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[m]);
}

const TAG_SVG = `<svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>`;

window.InlineTags = {
    state:        {},   // txId (number) → Set<tagId>
    activeId:     null, // currently open popover txId (desktop)
    saveTimers:   {},
    activePopEl:  null,
    popQ:         '',
    popHi:        0,
    sheetQ:       '',
    sheetHi:      0,
    sheetTxId:    null,

    init(txId, tagIds) {
        this.state[txId] = new Set(tagIds);
    },

    _sortedFiltered(txId, q) {
        const sel = this.state[txId] || new Set();
        const lq = String(q).trim().toLowerCase();
        const filtered = window.iTagAllTags.filter(t => t.name.toLowerCase().includes(lq));
        filtered.sort((a, b) => {
            const aS = sel.has(a.id) ? 1 : 0;
            const bS = sel.has(b.id) ? 1 : 0;
            if (aS !== bS) return aS - bS;
            return a.name.localeCompare(b.name);
        });
        return filtered;
    },

    // ── Desktop popover ────────────────────────────────────────────
    togglePopover(txId) {
        if (this.activeId === txId) { this._closePopover(); return; }
        this._closePopover();

        const pop = document.getElementById(`itag-pop-${txId}`);
        if (!pop) return;

        // Move to document.body so position:fixed is relative to the viewport,
        // not to .main-content (whose fadeUp animation leaves a residual
        // transform that creates a containing block offset by the sidebar).
        if (pop.parentElement !== document.body) document.body.appendChild(pop);

        const trigger = document.getElementById(`itag-trigger-${txId}`);
        if (trigger) {
            const r = trigger.getBoundingClientRect();
            const vpW = window.innerWidth, vpH = window.innerHeight;
            const POP_W = 260, MARGIN = 12;
            // Prefer left-aligned with trigger; if overflows right edge, right-align.
            let left = r.left;
            if (left + POP_W + MARGIN > vpW) left = Math.max(MARGIN, r.right - POP_W);
            pop.style.left  = left + 'px';
            pop.style.bottom = '';
            pop.style.top    = '';
            const below = vpH - r.bottom;
            if (below < 260 && r.top > 260) {
                pop.style.bottom = (vpH - r.top + 4) + 'px';
            } else {
                pop.style.top = (r.bottom + 4) + 'px';
            }
        }

        pop.style.display = 'block';
        this.activeId    = txId;
        this.activePopEl = pop;
        this.popQ = '';
        this.popHi = 0;

        const search = document.getElementById(`itag-search-${txId}`);
        if (search) { search.value = ''; setTimeout(() => search.focus(), 60); }
        this._renderList(txId, '');
    },

    _closePopover() {
        if (this.activePopEl) { this.activePopEl.style.display = 'none'; }
        this.activeId    = null;
        this.activePopEl = null;
    },

    filterList(txId, q) {
        this.popQ = q;
        this.popHi = 0;
        this._renderList(txId, q);
    },

    _key(event, txId) {
        if (event.key === 'Enter') {
            event.preventDefault();
            const items = this._sortedFiltered(txId, this.popQ);
            const it = items[this.popHi];
            if (!it) return;
            this.toggle(txId, it.id);
            const s = document.getElementById(`itag-search-${txId}`);
            if (s) { s.value = ''; s.focus(); }
            this.popQ = ''; this.popHi = 0;
            this._renderList(txId, '');
        } else if (event.key === 'ArrowDown') {
            event.preventDefault();
            const items = this._sortedFiltered(txId, this.popQ);
            if (!items.length) return;
            this.popHi = (this.popHi + 1) % items.length;
            this._renderList(txId, this.popQ);
        } else if (event.key === 'ArrowUp') {
            event.preventDefault();
            const items = this._sortedFiltered(txId, this.popQ);
            if (!items.length) return;
            this.popHi = (this.popHi - 1 + items.length) % items.length;
            this._renderList(txId, this.popQ);
        } else if (event.key === 'Escape') {
            event.preventDefault();
            this._closePopover();
        } else if (event.key === 'Backspace') {
            if (this.popQ) return;
            const arr = [...(this.state[txId] || [])];
            if (!arr.length) return;
            event.preventDefault();
            this.toggle(txId, arr[arr.length - 1]);
            this._renderList(txId, '');
        }
    },

    // ── Tag toggle + save ──────────────────────────────────────────
    toggle(txId, tagId) {
        const sel = this.state[txId];
        if (!sel) return;
        if (sel.has(tagId)) sel.delete(tagId); else sel.add(tagId);
        this._renderPills(txId);
        this._scheduleSave(txId);
    },

    _renderPills(txId) {
        const sel = this.state[txId];
        const all = window.iTagAllTags;
        const makeHtml = (dot) => {
            let out = '';
            sel.forEach(id => {
                const t = all.find(x => x.id === id);
                if (!t) return;
                out += `<span style="display:inline-flex;align-items:center;gap:3px;padding:2px 7px 2px 5px;
                    border-radius:${dot ? '12' : '10'}px;font-size:10px;font-weight:700;margin-left:4px;
                    background:${t.color}22;color:${t.color};border:1px solid ${t.color}44;">
                    <span style="width:${dot ? 6 : 5}px;height:${dot ? 6 : 5}px;border-radius:50%;background:${t.color};"></span>${_esc(t.name)}</span>`;
            });
            return out;
        };
        const d = document.getElementById(`itag-pills-${txId}`);
        if (d) d.innerHTML = makeHtml(true);
        const m = document.getElementById(`itag-pills-m-${txId}`);
        if (m) m.innerHTML = makeHtml(false);
    },

    _renderList(txId, q) {
        const listEl = document.getElementById(`itag-list-${txId}`);
        if (!listEl) return;
        const sel = this.state[txId];
        const items = this._sortedFiltered(txId, q);
        listEl.innerHTML = '';
        if (!items.length) {
            listEl.innerHTML = '<div style="font-size:12px;color:var(--muted);padding:8px;text-align:center;">Sin resultados</div>';
            return;
        }
        if (this.popHi < 0) this.popHi = 0;
        if (this.popHi >= items.length) this.popHi = items.length - 1;
        items.forEach((t, i) => {
            const isSel = sel.has(t.id);
            const active = i === this.popHi;
            const d = document.createElement('div');
            d.style.cssText = `display:flex;align-items:center;gap:8px;padding:8px 10px;border-radius:7px;cursor:pointer;font-size:13px;color:var(--text);transition:background 0.1s;${active ? 'background:var(--surface2);' : ''}`;
            d.innerHTML = `
                <span style="width:11px;height:11px;border-radius:50%;background:${t.color};flex-shrink:0;"></span>
                <span style="flex:1;min-width:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${_esc(t.name)}</span>
                ${isSel ? `<svg width="14" height="14" fill="none" stroke="var(--income)" stroke-width="2.8" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>` : '<span style="width:14px;height:14px;"></span>'}`;
            d.onmouseenter = () => {
                this.popHi = i;
                [...listEl.children].forEach((el, j) => {
                    el.style.background = j === i ? 'var(--surface2)' : '';
                });
            };
            d.onclick = () => {
                InlineTags.toggle(txId, t.id);
                const s = document.getElementById(`itag-search-${txId}`);
                if (s) { s.value = ''; s.focus(); }
                InlineTags.popQ = ''; InlineTags.popHi = 0;
                InlineTags._renderList(txId, '');
            };
            listEl.appendChild(d);
        });
        const activeEl = listEl.children[this.popHi];
        if (activeEl) activeEl.scrollIntoView({ block: 'nearest' });
    },

    _scheduleSave(txId) {
        clearTimeout(this.saveTimers[txId]);
        this.saveTimers[txId] = setTimeout(() => this._save(txId), 700);
    },

    async _save(txId) {
        const setBtn = state => {
            [document.getElementById(`itag-trigger-${txId}`),
             document.getElementById(`itag-m-trigger-${txId}`)].forEach(b => b && (b.dataset.state = state));
        };
        setBtn('saving');
        try {
            const resp = await fetch(`/movimientos/${txId}/etiquetas`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ tags: [...this.state[txId]] }),
            });
            if (!resp.ok) throw new Error();
            setBtn('saved');
            setTimeout(() => setBtn(''), 1400);
        } catch {
            setBtn('error');
            setTimeout(() => setBtn(''), 2200);
        }
    },

    // ── Mobile sheet ───────────────────────────────────────────────
    openSheet(txId) {
        const sheet = document.getElementById('itag-sheet');
        if (!sheet) return;
        sheet.dataset.txId = String(txId);
        this.sheetTxId = txId;
        this.sheetQ = '';
        this.sheetHi = 0;
        const search = document.getElementById('itag-sheet-search');
        if (search) search.value = '';
        this._renderSheetList(txId, '');
        sheet.showModal();
        // Focus AFTER dialog is open. Two attempts because iOS/Chrome sometimes eat the first.
        setTimeout(() => search && search.focus(), 120);
        setTimeout(() => {
            if (search && document.activeElement !== search) search.focus();
        }, 350);
    },

    _sheetKey(event) {
        const txId = this.sheetTxId;
        if (!txId) return;
        if (event.key === 'Enter') {
            event.preventDefault();
            const items = this._sortedFiltered(txId, this.sheetQ);
            const it = items[this.sheetHi];
            if (!it) return;
            this.toggle(txId, it.id);
            const s = document.getElementById('itag-sheet-search');
            if (s) { s.value = ''; s.focus(); }
            this.sheetQ = ''; this.sheetHi = 0;
            this._renderSheetList(txId, '');
        } else if (event.key === 'ArrowDown') {
            event.preventDefault();
            const items = this._sortedFiltered(txId, this.sheetQ);
            if (!items.length) return;
            this.sheetHi = (this.sheetHi + 1) % items.length;
            this._renderSheetList(txId, this.sheetQ);
        } else if (event.key === 'ArrowUp') {
            event.preventDefault();
            const items = this._sortedFiltered(txId, this.sheetQ);
            if (!items.length) return;
            this.sheetHi = (this.sheetHi - 1 + items.length) % items.length;
            this._renderSheetList(txId, this.sheetQ);
        } else if (event.key === 'Backspace') {
            if (this.sheetQ) return;
            const arr = [...(this.state[txId] || [])];
            if (!arr.length) return;
            event.preventDefault();
            this.toggle(txId, arr[arr.length - 1]);
            this._renderSheetList(txId, '');
        }
    },

    _renderSheetList(txId, q) {
        const listEl = document.getElementById('itag-sheet-list');
        if (!listEl) return;
        const sel = this.state[txId];
        const items = this._sortedFiltered(txId, q);
        listEl.innerHTML = '';
        if (!items.length) {
            listEl.innerHTML = '<div style="font-size:13px;color:var(--muted);padding:12px 8px;text-align:center;">Sin resultados</div>';
            return;
        }
        if (this.sheetHi < 0) this.sheetHi = 0;
        if (this.sheetHi >= items.length) this.sheetHi = items.length - 1;
        items.forEach((t, i) => {
            const isSel = sel.has(t.id);
            const active = i === this.sheetHi;
            const d = document.createElement('div');
            d.style.cssText = `display:flex;align-items:center;gap:10px;padding:12px 10px;border-radius:8px;cursor:pointer;font-size:15px;color:var(--text);transition:background 0.12s;${active ? 'background:var(--surface2);' : (isSel ? `background:${t.color}11;` : '')}`;
            d.innerHTML = `
                <span style="width:14px;height:14px;border-radius:50%;background:${t.color};flex-shrink:0;"></span>
                <span style="flex:1;font-weight:${isSel ? 600 : 400};color:var(--text);">${_esc(t.name)}</span>
                ${isSel ? `<svg width="17" height="17" fill="none" stroke="${t.color}" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>` : '<span style="width:17px;height:17px;"></span>'}`;
            d.onclick = () => {
                InlineTags.toggle(txId, t.id);
                const s = document.getElementById('itag-sheet-search');
                if (s) { s.value = ''; s.focus(); }
                InlineTags.sheetQ = ''; InlineTags.sheetHi = 0;
                InlineTags._renderSheetList(txId, '');
            };
            listEl.appendChild(d);
        });
    },
};

// Cerrar popover al hacer click fuera
document.addEventListener('click', e => {
    if (InlineTags.activeId === null) return;
    const pop     = InlineTags.activePopEl;
    const trigger = document.getElementById(`itag-trigger-${InlineTags.activeId}`);
    if (pop && !pop.contains(e.target) && trigger && !trigger.contains(e.target)) {
        InlineTags._closePopover();
    }
}, true);

// Cerrar popover al hacer scroll
window.addEventListener('scroll', () => InlineTags._closePopover(), { passive: true, capture: true });

// Mobile sheet: cerrar con backdrop
document.getElementById('itag-sheet')?.addEventListener('click', function (e) {
    const rect = this.getBoundingClientRect();
    if (e.clientY < rect.top) this.close();
});

// Mobile sheet: búsqueda
document.getElementById('itag-sheet-search')?.addEventListener('input', function () {
    const txId = parseInt(document.getElementById('itag-sheet')?.dataset.txId ?? '0');
    if (!txId) return;
    InlineTags.sheetQ = this.value;
    InlineTags.sheetHi = 0;
    InlineTags._renderSheetList(txId, this.value);
});

// Mobile sheet: teclado (Enter/flechas/backspace)
document.getElementById('itag-sheet-search')?.addEventListener('keydown', function (e) {
    InlineTags._sheetKey(e);
});

// Cleanup state al cerrar el sheet
document.getElementById('itag-sheet')?.addEventListener('close', function () {
    InlineTags.sheetTxId = null;
    InlineTags.sheetQ = '';
    InlineTags.sheetHi = 0;
});

// Tags globales del grupo
window.iTagAllTags = @json($allTags->map(fn ($t) => ['id' => $t->id, 'name' => $t->name, 'color' => $t->color])->values());

// Estado inicial por movimiento
@foreach($transactions as $tx)
InlineTags.init({{ $tx->id }}, @json($tx->tags->pluck('id')->values()));
@endforeach

})();
</script>

@endsection
