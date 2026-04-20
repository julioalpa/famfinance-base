@extends('layouts.app')

@section('title', $account->name)

@section('content')

<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
    <div>
        <a href="{{ route('accounts.index') }}" style="font-size: 12px; color: var(--muted); text-decoration: none;">← Cuentas</a>
        <h1 class="font-display" style="font-size: 24px; font-weight: 700; margin-top: 6px;">{{ $account->name }}</h1>
        <div style="margin-top: 6px; display: flex; align-items: center; gap: 10px;">
            <span class="badge badge-{{ $account->type }}">
                {{ ['cash'=>'Efectivo','digital'=>'Digital','credit'=>'Crédito'][$account->type] }}
            </span>
            <span style="font-size: 12px; color: var(--muted);">{{ $account->currency }}</span>
            <span style="font-size: 12px; color: var(--muted);">· Registrada por {{ $account->user->name }}</span>
        </div>
    </div>
    <div style="display: flex; gap: 8px;">
        <a href="{{ route('transactions.create') }}?account_id={{ $account->id }}" class="btn btn-primary" style="font-size: 12px;">
            + Movimiento
        </a>
        <a href="{{ route('accounts.edit', $account) }}" class="btn btn-ghost" style="font-size: 12px;">Editar</a>
    </div>
</div>

{{-- Stats de la cuenta --}}
<div style="display: grid; grid-template-columns: repeat({{ $account->isCredit() ? 3 : 3 }}, 1fr); gap: 14px; margin-bottom: 24px;">
    <div class="stat-card {{ $account->isCredit() ? 'neutral' : 'balance' }}">
        <div style="font-size: 11px; letter-spacing: 0.1em; text-transform: uppercase; color: var(--muted); margin-bottom: 8px;">
            {{ $account->isCredit() ? 'Deuda acumulada' : 'Saldo disponible' }}
        </div>
        <div class="font-display" style="font-size: 22px; font-weight: 700; color: {{ $account->isCredit() ? 'var(--warn)' : 'var(--income)' }};">
            {{ $account->currency === 'USD' ? 'US$' : '$' }} {{ number_format(abs($account->balance), 2, ',', '.') }}
        </div>
    </div>

    @if($account->isCredit() && $account->credit_limit)
    <div class="stat-card neutral">
        <div style="font-size: 11px; letter-spacing: 0.1em; text-transform: uppercase; color: var(--muted); margin-bottom: 8px;">Límite</div>
        <div class="font-display" style="font-size: 22px; font-weight: 700;">
            {{ $account->currency === 'USD' ? 'US$' : '$' }} {{ number_format($account->credit_limit, 2, ',', '.') }}
        </div>
    </div>
    @endif

    @if($account->isCredit())
    <div class="stat-card neutral">
        <div style="font-size: 11px; letter-spacing: 0.1em; text-transform: uppercase; color: var(--muted); margin-bottom: 8px;">Cuotas este mes</div>
        <div class="font-display" style="font-size: 22px; font-weight: 700; color: var(--warn);">
            $ {{ number_format($upcomingInstallments->sum('amount'), 2, ',', '.') }}
        </div>
    </div>
    @endif
</div>

{{-- Selector de mes --}}
<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
    <h2 class="font-display" style="font-size: 15px; font-weight: 600;">Movimientos</h2>
    <form method="GET">
        <input type="month" name="month" value="{{ $month }}" class="form-input"
               style="width: auto; padding: 7px 12px; font-size: 12px;"
               onchange="this.form.submit()">
    </form>
</div>

{{-- Cuotas del mes (solo crédito) --}}
@if($account->isCredit() && $upcomingInstallments->isNotEmpty())
<div class="card" style="margin-bottom: 20px; border-color: rgba(255,209,102,0.3);">
    <div style="font-size: 11px; letter-spacing: 0.1em; text-transform: uppercase; color: var(--warn); margin-bottom: 14px;">
        Cuotas a pagar en {{ \Carbon\Carbon::parse($month . '-01')->locale('es')->isoFormat('MMMM YYYY') }}
    </div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Compra</th>
                <th>Cuota</th>
                <th>Vencimiento</th>
                <th style="text-align:right;">Monto</th>
            </tr>
        </thead>
        <tbody>
            @foreach($upcomingInstallments as $inst)
            <tr>
                <td>
                    <a href="{{ route('transactions.show', $inst->transaction) }}" style="color: var(--text); text-decoration: none; font-size: 13px;">
                        {{ $inst->transaction->description ?? 'Sin descripción' }}
                    </a>
                    <div style="font-size: 11px; color: var(--muted);">
                        {{ $inst->transaction->date->format('d/m/Y') }}
                    </div>
                </td>
                <td style="font-size: 12px; color: var(--muted);">
                    {{ $inst->installment_number }} / {{ $inst->transaction->installments_count }}
                </td>
                <td style="font-size: 13px;">{{ $inst->due_date->format('d/m/Y') }}</td>
                <td style="text-align:right; font-weight: 600; color: var(--warn);">
                    $ {{ number_format($inst->amount, 2, ',', '.') }}
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" style="text-align: right; font-size: 12px; color: var(--muted); padding: 12px 16px;">Total del mes</td>
                <td style="text-align: right; font-weight: 700; font-size: 15px; color: var(--warn); padding: 12px 16px;">
                    $ {{ number_format($upcomingInstallments->sum('amount'), 2, ',', '.') }}
                </td>
            </tr>
        </tfoot>
    </table>
</div>
@endif

{{-- Transacciones del mes --}}
<div class="card" style="padding: 0; overflow: hidden;">
    @if($transactions->isEmpty())
        <div style="text-align: center; padding: 40px; color: var(--muted); font-size: 13px;">
            Sin movimientos en este período
        </div>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Tipo</th>
                    <th>Descripción</th>
                    <th>Categoría</th>
                    <th>Quién</th>
                    <th style="text-align:right;">Monto</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transactions as $tx)
                <tr>
                    <td style="color: var(--muted); font-size: 12px;">{{ $tx->date->format('d/m/Y') }}</td>
                    <td>
                        <span class="badge badge-{{ $tx->type }}" style="font-size: 10px;">
                            {{ $tx->type === 'expense' ? 'Gasto' : ($tx->type === 'income' ? 'Ingreso' : 'Transfer.') }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('transactions.show', $tx) }}" style="color: var(--text); text-decoration: none; font-size: 13px;">
                            {{ $tx->description ?? '—' }}
                        </a>
                        @if($tx->has_installments)
                            <span class="badge badge-credit" style="margin-left:4px; font-size:10px;">{{ $tx->installments_count }}c</span>
                        @endif
                    </td>
                    <td style="font-size: 12px; color: var(--muted);">{{ $tx->category?->name ?? '—' }}</td>
                    <td style="font-size: 12px; color: var(--muted);">{{ $tx->user->name }}</td>
                    <td style="text-align: right; font-weight: 500; white-space: nowrap;"
                        style="color: {{ $tx->isIncome() ? 'var(--income)' : ($tx->isExpense() ? 'var(--expense)' : 'var(--accent2)') }}">
                        <span class="{{ $tx->isIncome() ? 'amount-income' : ($tx->isExpense() ? 'amount-expense' : 'amount-neutral') }}">
                            {{ $tx->isIncome() ? '+' : ($tx->isExpense() ? '-' : '') }}
                            {{ $tx->currency === 'USD' ? 'US$' : '$' }} {{ number_format($tx->amount, 2, ',', '.') }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

@endsection
