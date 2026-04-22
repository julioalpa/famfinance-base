@extends('layouts.app')

@section('title', $account->name)

@section('content')

<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
    <div>
        <a href="{{ route('accounts.index') }}" style="font-size: 12px; color: var(--muted); text-decoration: none;">← Cuentas</a>
        <div style="display: flex; align-items: center; gap: 12px; margin-top: 6px;">
            @include('accounts._brand_logo', ['brand' => $account->brand, 'type' => $account->type, 'size' => 'lg'])
            <h1 class="font-display" style="font-size: 24px; font-weight: 700;">{{ $account->name }}</h1>
        </div>
        <div style="margin-top: 6px; display: flex; align-items: center; gap: 10px;">
            <span class="badge badge-{{ $account->type }}">
                {{ ['cash'=>'Efectivo','digital'=>'Digital','credit'=>'Tarjeta de crédito','loan'=>'Préstamo'][$account->type] ?? $account->type }}
            </span>
            <span style="font-size: 12px; color: var(--muted);">{{ $account->currency }}</span>
            <span style="font-size: 12px; color: var(--muted);">· Registrada por {{ $account->user->name }}</span>
        </div>
    </div>
    <div style="display: flex; gap: 8px;">
        <a href="{{ route('transactions.create') }}?account_id={{ $account->id }}" class="btn btn-primary" style="font-size: 12px;">
            + Movimiento
        </a>
        <button onclick="openAdjustModal()" class="btn btn-ghost" style="font-size: 12px;">
            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M12 20h9M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
            Ajustar saldo
        </button>
        <a href="{{ route('accounts.edit', $account) }}" class="btn btn-ghost" style="font-size: 12px;">Editar</a>
    </div>
</div>

{{-- Stats de la cuenta --}}
<div style="display: grid; grid-template-columns: repeat({{ $account->isCredit() ? 3 : 3 }}, 1fr); gap: 14px; margin-bottom: 24px;">
    <div class="stat-card {{ $account->isCredit() ? 'neutral' : 'balance' }}">
        @php $bal = $account->balance; @endphp
        <div style="font-size: 11px; letter-spacing: 0.1em; text-transform: uppercase; color: var(--muted); margin-bottom: 8px;">
            @if($account->isCredit()) Deuda acumulada
            @elseif($account->isLoan()) Deuda restante
            @elseif($bal < 0) Saldo negativo
            @else Saldo disponible
            @endif
        </div>
        <div class="font-display" style="font-size: 22px; font-weight: 700; color: {{ $account->isCredit() ? 'var(--warn)' : ($bal < 0 ? 'var(--expense)' : 'var(--income)') }};">
            @if(!$account->isLiability() && $bal < 0)−{{ $account->currency === 'USD' ? 'US$' : '$' }} {{ number_format(abs($bal), 2, ',', '.') }}
            @else{{ $account->currency === 'USD' ? 'US$' : '$' }} {{ number_format(abs($bal), 2, ',', '.') }}
            @endif
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

{{-- Próximo resumen estimado --}}
@if($nextPaymentSummary)
<div class="card" style="margin-bottom: 24px; border-color: rgba(240,160,48,0.3);">
    <div style="display: flex; align-items: flex-start; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="font-size: 11px; letter-spacing: 0.1em; text-transform: uppercase; color: var(--accent); font-weight: 700; margin-bottom: 4px;">
                Estimación del próximo resumen de tarjeta
            </div>
            <div style="font-size: 11px; color: var(--muted); margin-bottom: 6px;">Lo que probablemente tengas que pagar cuando llegue el próximo vencimiento</div>
            <div style="font-size: 12px; color: var(--muted);">
                Período: {{ $nextPaymentSummary['period_start']->format('d/m/Y') }} → {{ $nextPaymentSummary['period_end']->format('d/m/Y') }}
                @if($nextPaymentSummary['due_date'])
                    · <span style="color: var(--warn);">Vence {{ $nextPaymentSummary['due_date']->format('d/m/Y') }}</span>
                @endif
            </div>
        </div>
        <div class="font-display" style="font-size: 28px; font-weight: 800; color: var(--warn); letter-spacing: -0.03em;">
            $ {{ number_format($nextPaymentSummary['total'], 2, ',', '.') }}
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 16px; padding-top: 16px; border-top: 1px solid var(--border);">
        <div>
            <div style="font-size: 11px; color: var(--muted); margin-bottom: 4px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.07em;">Gastos nuevos del período</div>
            <div style="font-size: 18px; font-weight: 700; color: var(--expense);">
                $ {{ number_format($nextPaymentSummary['expenses'], 2, ',', '.') }}
            </div>
            <div style="font-size: 11px; color: var(--muted); margin-top: 2px;">Lo que gastaste desde el último cierre (sin contar cuotas)</div>
        </div>
        <div>
            <div style="font-size: 11px; color: var(--muted); margin-bottom: 4px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.07em;">Cuotas que vencen este mes</div>
            <div style="font-size: 18px; font-weight: 700; color: var(--warn);">
                $ {{ number_format($nextPaymentSummary['installments_total'], 2, ',', '.') }}
            </div>
            @if($nextPaymentSummary['installments']->isNotEmpty())
            <div style="font-size: 11px; color: var(--muted); margin-top: 2px;">
                {{ $nextPaymentSummary['installments']->count() }} cuota{{ $nextPaymentSummary['installments']->count() > 1 ? 's' : '' }}
                @foreach($nextPaymentSummary['installments']->take(2) as $inst)
                    · {{ Str::limit($inst->transaction->description ?? '—', 18) }} ({{ $inst->installment_number }}/{{ $inst->transaction->installments_count }})
                @endforeach
                @if($nextPaymentSummary['installments']->count() > 2)
                    · y {{ $nextPaymentSummary['installments']->count() - 2 }} más
                @endif
            </div>
            @endif
        </div>
    </div>

    {{-- Ajuste de fechas --}}
    <div style="margin-top: 16px; padding-top: 14px; border-top: 1px solid var(--border);">
        <button onclick="document.getElementById('date-override-form').classList.toggle('hidden-form')"
                style="background: none; border: none; font-size: 11px; color: var(--muted); cursor: pointer; display: flex; align-items: center; gap: 5px; font-family: inherit; padding: 0;">
            <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            Ajustar fechas del resumen
            @if($nextPaymentSummary['closing_used'] != $nextPaymentSummary['closing_default'] || $nextPaymentSummary['due_day_used'] != $nextPaymentSummary['due_day_default'])
                <span style="color: var(--accent); font-weight: 700;">· modificado</span>
            @endif
        </button>

        <form id="date-override-form" method="GET"
              class="{{ ($nextPaymentSummary['closing_used'] != $nextPaymentSummary['closing_default'] || $nextPaymentSummary['due_day_used'] != $nextPaymentSummary['due_day_default']) ? '' : 'hidden-form' }}"
              style="margin-top: 12px; display: flex; align-items: flex-end; gap: 12px; flex-wrap: wrap;">
            <input type="hidden" name="month" value="{{ $month }}">
            <div>
                <label style="font-size: 11px; color: var(--muted); display: block; margin-bottom: 4px;">
                    Día de cierre
                    <span style="color: var(--surface2);">(default: {{ $nextPaymentSummary['closing_default'] }})</span>
                </label>
                <input type="number" name="closing_override" class="form-input"
                       style="width: 80px; padding: 6px 10px; font-size: 13px;"
                       min="1" max="31"
                       value="{{ $nextPaymentSummary['closing_used'] }}"
                       placeholder="{{ $nextPaymentSummary['closing_default'] }}">
            </div>
            <div>
                <label style="font-size: 11px; color: var(--muted); display: block; margin-bottom: 4px;">
                    Día de vencimiento
                    <span style="color: var(--surface2);">(default: {{ $nextPaymentSummary['due_day_default'] ?: '—' }})</span>
                </label>
                <input type="number" name="due_override" class="form-input"
                       style="width: 80px; padding: 6px 10px; font-size: 13px;"
                       min="1" max="31"
                       value="{{ $nextPaymentSummary['due_day_used'] }}"
                       placeholder="{{ $nextPaymentSummary['due_day_default'] ?: '—' }}">
            </div>
            <button type="submit" class="btn btn-primary" style="font-size: 12px; padding: 7px 14px;">Recalcular</button>
            @if($nextPaymentSummary['closing_used'] != $nextPaymentSummary['closing_default'] || $nextPaymentSummary['due_day_used'] != $nextPaymentSummary['due_day_default'])
                <a href="{{ route('accounts.show', $account) }}?month={{ $month }}"
                   style="font-size: 11px; color: var(--muted); text-decoration: none; align-self: center;">
                    Restablecer defaults
                </a>
            @endif
        </form>
    </div>
</div>

<style>
.hidden-form { display: none !important; }
</style>
@endif

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
                @php $isPayment = $tx->getAttribute('is_payment'); @endphp
                <tr>
                    <td style="color: var(--muted); font-size: 12px;">{{ $tx->date->format('d/m/Y') }}</td>
                    <td>
                        @if($isPayment)
                            <span class="badge badge-income" style="font-size: 10px;">Pago</span>
                        @elseif($tx->type === 'adjustment')
                            <span class="badge badge-adjustment" style="font-size: 10px;">
                                Ajuste {{ $tx->adjustment_direction === 'in' ? '▲' : '▼' }}
                            </span>
                        @else
                            <span class="badge badge-{{ $tx->type }}" style="font-size: 10px;">
                                {{ $tx->type === 'expense' ? 'Gasto' : ($tx->type === 'income' ? 'Ingreso' : 'Transfer.') }}
                            </span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('transactions.show', $tx) }}" style="color: var(--text); text-decoration: none; font-size: 13px;">
                            {{ $tx->description ?? '—' }}
                        </a>
                        @if($tx->has_installments)
                            <span class="badge badge-credit" style="margin-left:4px; font-size:10px;">{{ $tx->installments_count }}c</span>
                        @endif
                    </td>
                    <td>
                        @if($tx->category)
                        <div style="display:flex;align-items:center;gap:5px;">
                            @include('categories._icon', ['icon' => $tx->category->icon, 'color' => $tx->category->color, 'type' => $tx->category->type, 'size' => 'xs'])
                            <span style="font-size:12px;color:var(--muted);">{{ $tx->category->name }}</span>
                        </div>
                        @else
                        <span style="font-size:12px;color:var(--muted);">—</span>
                        @endif
                    </td>
                    <td style="font-size: 12px; color: var(--muted);">{{ $tx->user->name }}</td>
                    <td style="text-align: right; font-weight: 500; white-space: nowrap;">
                        @if($isPayment)
                            <span style="color: var(--income); font-weight: 700;">
                                − {{ $tx->currency === 'USD' ? 'US$' : '$' }} {{ number_format($tx->amount, 2, ',', '.') }}
                            </span>
                        @elseif($tx->isAdjustment())
                            <span style="color:#a078ff; font-weight:600;">
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
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

{{-- ── Modal ajuste de saldo ────────────────────────────────────────────────── --}}
<div id="adjust-modal-backdrop"
     onclick="closeAdjustModal()"
     style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.65); backdrop-filter:blur(4px); z-index:500; align-items:center; justify-content:center;">
    <div onclick="event.stopPropagation()"
         style="background:var(--surface); border:1px solid var(--border); border-radius:16px; padding:28px; width:100%; max-width:440px; margin:16px; position:relative;">

        <div style="display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:6px;">
            <div>
                <h3 class="font-display" style="font-size:17px; font-weight:800; letter-spacing:-0.02em;">Ajustar saldo</h3>
                <div style="font-size:12px; color:var(--muted); margin-top:3px;">
                    Ingresá el saldo real de <strong style="color:var(--text);">{{ $account->name }}</strong> y se registrará un movimiento de ajuste automáticamente.
                </div>
            </div>
            <button onclick="closeAdjustModal()" style="background:none; border:none; cursor:pointer; color:var(--muted); padding:4px; margin-left:12px;" aria-label="Cerrar">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
        </div>

        @php $modalBal = $account->balance; @endphp
        <div style="background:var(--surface2); border-radius:10px; padding:12px 16px; margin-bottom:20px; font-size:13px;">
            <div style="color:var(--muted); font-size:11px; text-transform:uppercase; letter-spacing:0.07em; margin-bottom:3px;">
                {{ $account->isLiability() ? 'Deuda registrada en la app' : 'Saldo registrado en la app' }}
            </div>
            <div class="font-display" style="font-size:20px; font-weight:700; color:{{ $account->isLiability() ? 'var(--warn)' : ($modalBal < 0 ? 'var(--expense)' : 'var(--income)') }};">
                @if(!$account->isLiability() && $modalBal < 0)−@endif{{ $account->currency === 'USD' ? 'US$' : '$' }} {{ number_format(abs($modalBal), 2, ',', '.') }}
            </div>
        </div>

        <form method="POST" action="{{ route('accounts.adjust', $account) }}">
            @csrf

            <div style="margin-bottom:18px;">
                <label class="form-label">{{ $account->isLiability() ? 'Deuda real actual' : 'Saldo real actual' }} *</label>
                <div style="display:flex; gap:8px; align-items:center;">
                    <span style="font-size:14px; color:var(--muted); font-weight:700; white-space:nowrap; padding:10px 0;">
                        {{ $account->currency === 'USD' ? 'US$' : '$' }}
                    </span>
                    <input type="number" name="target_balance" id="adjust-target"
                           class="form-input" step="0.01" required
                           placeholder="0,00"
                           oninput="updateAdjustPreview(this.value)">
                </div>
                <div id="adjust-preview" style="font-size:12px; color:var(--muted); margin-top:6px; min-height:18px;"></div>
            </div>

            <div style="margin-bottom:18px;">
                <label class="form-label" for="adjust-date">Fecha del ajuste</label>
                <input type="date" name="date" id="adjust-date" class="form-input"
                       value="{{ today()->format('Y-m-d') }}">
            </div>

            <div style="margin-bottom:22px;">
                <label class="form-label" for="adjust-notes">Motivo (opcional)</label>
                <input type="text" name="notes" id="adjust-notes" class="form-input"
                       placeholder="Ej: diferencia por comisión bancaria no registrada">
            </div>

            <div style="display:flex; gap:10px; justify-content:flex-end;">
                <button type="button" onclick="closeAdjustModal()" class="btn btn-ghost">Cancelar</button>
                <button type="submit" class="btn btn-primary">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                    Registrar ajuste
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const CURRENT_BALANCE = {{ $account->balance }};
const IS_LIABILITY    = {{ $account->isLiability() ? 'true' : 'false' }};
const CURRENCY_SYMBOL = '{{ $account->currency === 'USD' ? 'US$' : '$' }}';

function openAdjustModal() {
    document.getElementById('adjust-modal-backdrop').style.display = 'flex';
    document.getElementById('adjust-target').value = '';
    document.getElementById('adjust-preview').textContent = '';
    setTimeout(() => document.getElementById('adjust-target').focus(), 50);
}

function closeAdjustModal() {
    document.getElementById('adjust-modal-backdrop').style.display = 'none';
}

function updateAdjustPreview(val) {
    const target = parseFloat(val);
    const preview = document.getElementById('adjust-preview');
    if (isNaN(target) || val === '') { preview.textContent = ''; return; }

    const diff = target - CURRENT_BALANCE;
    if (Math.abs(diff) < 0.01) {
        preview.textContent = '✓ El saldo ya coincide, no se generará ningún ajuste.';
        preview.style.color = 'var(--income)';
        return;
    }

    const absDiff = Math.abs(diff).toLocaleString('es-AR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    if (IS_LIABILITY) {
        if (diff > 0) {
            preview.innerHTML = `→ Se registrará un ajuste que <strong style="color:var(--expense)">aumenta la deuda</strong> en ${CURRENCY_SYMBOL} ${absDiff}`;
        } else {
            preview.innerHTML = `→ Se registrará un ajuste que <strong style="color:var(--income)">reduce la deuda</strong> en ${CURRENCY_SYMBOL} ${absDiff}`;
        }
    } else {
        if (diff > 0) {
            preview.innerHTML = `→ Se registrará un ajuste que <strong style="color:var(--income)">suma</strong> ${CURRENCY_SYMBOL} ${absDiff} al saldo`;
        } else {
            preview.innerHTML = `→ Se registrará un ajuste que <strong style="color:var(--expense)">descuenta</strong> ${CURRENCY_SYMBOL} ${absDiff} del saldo`;
        }
    }
    preview.style.color = 'var(--muted)';
}

document.addEventListener('keydown', e => { if (e.key === 'Escape') closeAdjustModal(); });
</script>

@endsection
