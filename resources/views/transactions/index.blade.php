@extends('layouts.app')

@section('title', 'Movimientos')

@section('content')

<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 28px;">
    <div>
        <h1 class="font-display" style="font-size: 24px; font-weight: 700; letter-spacing: -0.02em;">Movimientos</h1>
        <div style="font-size: 12px; color: var(--muted); margin-top: 3px;">
            Todos los gastos e ingresos del grupo
        </div>
    </div>
    <a href="{{ route('transactions.create') }}" class="btn btn-primary">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
        Nuevo movimiento
    </a>
</div>

{{-- Filtros --}}
<div class="card" style="margin-bottom: 20px;">
    <form method="GET" style="display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;">
        <div>
            <label class="form-label">Mes</label>
            <input type="month" name="month" value="{{ request('month', now()->format('Y-m')) }}" class="form-input" style="width: 160px;">
        </div>
        <div>
            <label class="form-label">Tipo</label>
            <select name="type" class="form-select" style="width: 160px;">
                <option value="">Todos</option>
                <option value="expense"    {{ request('type') === 'expense'    ? 'selected' : '' }}>Gastos</option>
                <option value="income"    {{ request('type') === 'income'    ? 'selected' : '' }}>Ingresos</option>
                <option value="transfer"  {{ request('type') === 'transfer'  ? 'selected' : '' }}>Transferencias</option>
                <option value="adjustment"{{ request('type') === 'adjustment'? 'selected' : '' }}>Ajustes</option>
            </select>
        </div>
        <div>
            <label class="form-label">Cuenta</label>
            <select name="account_id" class="form-select" style="width: 180px;">
                <option value="">Todas</option>
                @foreach($accounts as $account)
                    <option value="{{ $account->id }}" {{ request('account_id') == $account->id ? 'selected' : '' }}>
                        {{ $account->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Categoría</label>
            <select name="category_id" class="form-select" style="width: 180px;">
                <option value="">Todas</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-ghost">Filtrar</button>
        <a href="{{ route('transactions.index') }}" class="btn btn-ghost" style="color: var(--muted);">Limpiar</a>
    </form>
</div>

{{-- Tabla --}}
<div class="card" style="padding: 0; overflow: hidden;">
    @if($transactions->isEmpty())
        <div style="text-align: center; padding: 60px 20px; color: var(--muted);">
            <div style="font-size: 32px; margin-bottom: 12px;">📭</div>
            <div style="font-size: 14px; margin-bottom: 16px;">Sin movimientos para los filtros seleccionados</div>
            <a href="{{ route('transactions.create') }}" class="btn btn-primary" style="display: inline-flex;">Cargar movimiento</a>
        </div>
    @else
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
                    <td style="color: var(--muted); font-size: 12px; white-space: nowrap;">
                        {{ $tx->date->format('d/m/Y') }}
                    </td>
                    <td>
                        @if($tx->type === 'adjustment')
                            <span class="badge badge-adjustment" style="font-size:10px;">
                                Ajuste {{ $tx->adjustment_direction === 'in' ? '▲' : '▼' }}
                            </span>
                        @else
                            <span class="badge badge-{{ $tx->type }}">
                                {{ $tx->type === 'expense' ? 'Gasto' : ($tx->type === 'income' ? 'Ingreso' : 'Transfer.') }}
                            </span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('transactions.show', $tx) }}" style="color: var(--text); text-decoration: none;">
                            {{ $tx->description ?? '—' }}
                        </a>
                        @if($tx->has_installments)
                            <span class="badge badge-credit" style="margin-left: 6px; font-size: 10px;">
                                {{ $tx->installments_count }} cuotas
                            </span>
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
                    <td>
                        <span class="badge badge-{{ $tx->account->type }}">{{ $tx->account->name }}</span>
                    </td>
                    <td style="font-size: 12px; color: var(--muted);">{{ $tx->user->name }}</td>
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
                    <td style="white-space: nowrap;">
                        <a href="{{ route('transactions.edit', $tx) }}" style="color: var(--muted); font-size: 12px; text-decoration: none; margin-right: 10px;">Editar</a>
                        <form method="POST" action="{{ route('transactions.destroy', $tx) }}" style="display:inline"
                              onsubmit="return confirm('¿Eliminar este movimiento?')">
                            @csrf @method('DELETE')
                            <button type="submit" style="background: none; border: none; color: var(--danger); font-size: 12px; cursor: pointer; font-family: 'DM Mono', monospace;">Eliminar</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Paginación --}}
        @if($transactions->hasPages())
        <div style="padding: 16px 20px; border-top: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; font-size: 12px; color: var(--muted);">
            <span>{{ $transactions->total() }} movimientos</span>
            {{ $transactions->links() }}
        </div>
        @endif
    @endif
</div>

@endsection
