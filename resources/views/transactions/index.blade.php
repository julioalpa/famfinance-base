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
    border-top: 1px solid var(--border);
    overflow: auto;
    -webkit-overflow-scrolling: touch;
}
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
</style>

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
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Descripción</th>
                        <th>Categoría</th>
                        <th>Cuenta</th>
                        <th>Quién</th>
                        <th style="text-align:right;">Monto</th>
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
                        <td><span class="badge badge-{{ $tx->account->type }}">{{ $tx->account->name }}</span></td>
                        <td style="font-size: 12px; color: var(--muted);">{{ $tx->user->name }}</td>
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
                    <span class="badge badge-{{ $tx->account->type }}" style="font-size:10px;">{{ $tx->account->name }}</span>
                    @if($tx->has_installments)
                        <span class="badge badge-credit" style="font-size:10px;">{{ $tx->installments_count }}c</span>
                    @endif
                </div>
                <div style="display:flex;gap:4px;margin-top:2px;">
                    <a href="{{ route('transactions.edit', $tx) }}" class="tx-action-link" style="font-size:11px;min-height:30px;padding:0 8px;">Editar</a>
                    <form method="POST" action="{{ route('transactions.destroy', $tx) }}" style="display:inline" onsubmit="return confirm('¿Eliminar?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="tx-action-btn" style="font-size:11px;min-height:30px;padding:0 8px;">Eliminar</button>
                    </form>
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

@endsection
