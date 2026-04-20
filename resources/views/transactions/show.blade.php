@extends('layouts.app')

@section('title', 'Detalle de movimiento')

@section('content')

<div style="max-width: 700px;">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
        <a href="{{ route('transactions.index') }}" style="font-size: 12px; color: var(--muted); text-decoration: none;">← Movimientos</a>
        <div style="display: flex; gap: 8px;">
            <a href="{{ route('transactions.edit', $transaction) }}" class="btn btn-ghost" style="font-size: 12px;">Editar</a>
            <form method="POST" action="{{ route('transactions.destroy', $transaction) }}"
                  onsubmit="return confirm('¿Eliminar este movimiento?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger" style="font-size: 12px;">Eliminar</button>
            </form>
        </div>
    </div>

    <div class="card" style="margin-bottom: 20px;">
        {{-- Header del movimiento --}}
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; padding-bottom: 24px; border-bottom: 1px solid var(--border);">
            <div>
                <div style="margin-bottom: 8px;">
                    <span class="badge badge-{{ $transaction->type }}">
                        {{ $transaction->type === 'expense' ? 'Gasto' : ($transaction->type === 'income' ? 'Ingreso' : 'Transferencia') }}
                    </span>
                    @if($transaction->has_installments)
                        <span class="badge badge-credit" style="margin-left: 6px;">{{ $transaction->installments_count }} cuotas</span>
                    @endif
                </div>
                <h1 class="font-display" style="font-size: 22px; font-weight: 700; margin-bottom: 4px;">
                    {{ $transaction->description ?? 'Sin descripción' }}
                </h1>
                <div style="font-size: 12px; color: var(--muted);">
                    {{ $transaction->date->locale('es')->isoFormat('D [de] MMMM YYYY') }}
                </div>
            </div>
            <div style="text-align: right;">
                <div class="font-display" style="font-size: 30px; font-weight: 700;"
                     class="{{ $transaction->isIncome() ? 'amount-income' : ($transaction->isExpense() ? 'amount-expense' : 'amount-neutral') }}"
                     style="color: {{ $transaction->isIncome() ? 'var(--income)' : ($transaction->isExpense() ? 'var(--expense)' : 'var(--accent2)') }}">
                    {{ $transaction->isIncome() ? '+' : ($transaction->isExpense() ? '-' : '') }}
                    {{ $transaction->currency === 'USD' ? 'US$' : '$' }} {{ number_format($transaction->amount, 2, ',', '.') }}
                </div>
                <div style="font-size: 12px; color: var(--muted); margin-top: 2px;">{{ $transaction->currency }}</div>
            </div>
        </div>

        {{-- Detalles --}}
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div>
                <div style="font-size: 11px; letter-spacing: 0.1em; text-transform: uppercase; color: var(--muted); margin-bottom: 4px;">Cuenta</div>
                <div style="font-size: 14px;">
                    <span class="badge badge-{{ $transaction->account->type }}">{{ $transaction->account->name }}</span>
                </div>
            </div>

            <div>
                <div style="font-size: 11px; letter-spacing: 0.1em; text-transform: uppercase; color: var(--muted); margin-bottom: 4px;">Categoría</div>
                <div style="font-size: 14px; color: var(--text);">{{ $transaction->category?->name ?? '—' }}</div>
            </div>

            @if($transaction->isIncome() && $transaction->income_source)
            <div>
                <div style="font-size: 11px; letter-spacing: 0.1em; text-transform: uppercase; color: var(--muted); margin-bottom: 4px;">Origen</div>
                <div style="font-size: 14px; color: var(--text);">
                    {{ ['salary'=>'Sueldo','credit'=>'Crédito','cash'=>'Efectivo','loan'=>'Préstamo','other'=>'Otro'][$transaction->income_source] ?? $transaction->income_source }}
                </div>
            </div>
            @endif

            @if($transaction->isTransfer() && $transaction->targetAccount)
            <div>
                <div style="font-size: 11px; letter-spacing: 0.1em; text-transform: uppercase; color: var(--muted); margin-bottom: 4px;">Cuenta destino</div>
                <div style="font-size: 14px;">
                    <span class="badge badge-{{ $transaction->targetAccount->type }}">{{ $transaction->targetAccount->name }}</span>
                </div>
            </div>
            @endif

            <div>
                <div style="font-size: 11px; letter-spacing: 0.1em; text-transform: uppercase; color: var(--muted); margin-bottom: 4px;">Registrado por</div>
                <div style="font-size: 14px; color: var(--text);">{{ $transaction->user->name }}</div>
            </div>

            @if($transaction->notes)
            <div style="grid-column: span 2;">
                <div style="font-size: 11px; letter-spacing: 0.1em; text-transform: uppercase; color: var(--muted); margin-bottom: 4px;">Notas</div>
                <div style="font-size: 13px; color: var(--muted); line-height: 1.5;">{{ $transaction->notes }}</div>
            </div>
            @endif
        </div>
    </div>

    {{-- Detalle de cuotas --}}
    @if($transaction->has_installments && $transaction->installments->isNotEmpty())
    <div class="card">
        <h2 class="font-display" style="font-size: 14px; font-weight: 600; margin-bottom: 16px;">
            Cuotas — $ {{ number_format($transaction->installment_amount, 2, ',', '.') }} c/u
        </h2>

        <table class="data-table">
            <thead>
                <tr>
                    <th>Cuota</th>
                    <th>Vencimiento</th>
                    <th style="text-align:right;">Monto</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transaction->installments as $inst)
                <tr>
                    <td style="color: var(--muted); font-size: 12px;">
                        {{ $inst->installment_number }} / {{ $transaction->installments_count }}
                    </td>
                    <td style="font-size: 13px;">{{ $inst->due_date->format('d/m/Y') }}</td>
                    <td style="text-align:right; font-size: 13px;">
                        $ {{ number_format($inst->amount, 2, ',', '.') }}
                    </td>
                    <td>
                        @if($inst->is_paid)
                            <span class="badge badge-income">Pagada</span>
                        @elseif($inst->due_date->isPast())
                            <span class="badge badge-expense">Vencida</span>
                        @else
                            <span style="font-size: 11px; color: var(--muted);">Pendiente</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

@endsection
