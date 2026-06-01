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
    <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
        @if($account->isCredit())
        <a href="{{ route('card-payment.create', ['card_id' => $account->id]) }}"
           class="btn btn-ghost"
           style="font-size:12px;border-color:rgba(78,155,255,0.3);color:var(--accent2);gap:6px;">
            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
            Pagar tarjeta
        </a>
        @endif
        @if($account->isLoan())
        <a href="{{ route('loans.setup', $account) }}"
           class="btn btn-ghost"
           style="font-size:12px;border-color:rgba(78,155,255,0.3);color:var(--accent2);gap:6px;">
            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            Plan de cuotas
        </a>
        @endif
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
                       inputmode="numeric"
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
                       inputmode="numeric"
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

{{-- ── Plan de cuotas (préstamos) ──────────────────────────────────────────── --}}
@if($account->isLoan())
<div style="margin-bottom:24px;">

    @if(is_null($loanInstallments) || $loanInstallments->isEmpty())
    {{-- Sin plan configurado --}}
    <div class="card" style="border-style:dashed;text-align:center;padding:28px 20px;">
        <div style="font-size:22px;margin-bottom:10px;">📋</div>
        <div style="font-size:14px;font-weight:700;color:var(--text);margin-bottom:4px;">Sin plan de cuotas</div>
        <div style="font-size:13px;color:var(--muted);margin-bottom:18px;">Configurá un plan para llevar registro de cada cuota y cancelarlas mes a mes.</div>
        <a href="{{ route('loans.setup', $account) }}" class="btn btn-primary" style="display:inline-flex;">
            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
            Configurar plan de cuotas
        </a>
    </div>

    @else
    {{-- Plan configurado --}}
    @php
        $totalInstallments   = $loanInstallments->count();
        $paidInstallments    = $loanInstallments->where('is_paid', true);
        $pendingInstallments = $loanInstallments->where('is_paid', false);
        $overdueInstallments = $pendingInstallments->filter(fn($i) => $i->isOverdue());
        $nextDue             = $pendingInstallments->sortBy('due_date')->first();
        $paidTotal           = $paidInstallments->sum('amount');
        $pendingTotal        = $pendingInstallments->sum('amount');
        $progressPct         = $totalInstallments > 0 ? round($paidInstallments->count() / $totalInstallments * 100) : 0;
    @endphp

    {{-- Summary strip --}}
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr auto;gap:12px;margin-bottom:16px;">
        <div class="card" style="padding:14px 16px;">
            <div style="font-size:10px;text-transform:uppercase;letter-spacing:0.08em;color:var(--muted);font-weight:700;margin-bottom:4px;">Progreso</div>
            <div style="font-family:'Bricolage Grotesque',sans-serif;font-size:20px;font-weight:800;color:var(--income);">
                {{ $paidInstallments->count() }}<span style="font-size:13px;color:var(--muted);font-weight:600;"> / {{ $totalInstallments }}</span>
            </div>
            <div style="height:4px;background:var(--surface3);border-radius:2px;margin-top:8px;overflow:hidden;">
                <div style="height:100%;width:{{ $progressPct }}%;background:var(--income);border-radius:2px;transition:width 0.8s ease;"></div>
            </div>
        </div>
        <div class="card" style="padding:14px 16px;">
            <div style="font-size:10px;text-transform:uppercase;letter-spacing:0.08em;color:var(--muted);font-weight:700;margin-bottom:4px;">Pagado</div>
            <div style="font-family:'Bricolage Grotesque',sans-serif;font-size:20px;font-weight:800;color:var(--income);">
                $ {{ number_format($paidTotal, 0, ',', '.') }}
            </div>
        </div>
        <div class="card" style="padding:14px 16px;{{ $overdueInstallments->isNotEmpty() ? 'border-color:rgba(240,64,96,0.3);' : '' }}">
            <div style="font-size:10px;text-transform:uppercase;letter-spacing:0.08em;color:var(--muted);font-weight:700;margin-bottom:4px;">Pendiente</div>
            <div style="font-family:'Bricolage Grotesque',sans-serif;font-size:20px;font-weight:800;color:{{ $overdueInstallments->isNotEmpty() ? 'var(--expense)' : 'var(--warn)' }};">
                $ {{ number_format($pendingTotal, 0, ',', '.') }}
            </div>
            @if($overdueInstallments->isNotEmpty())
            <div style="font-size:10px;color:var(--expense);font-weight:700;margin-top:3px;">
                ⚠ {{ $overdueInstallments->count() }} vencida{{ $overdueInstallments->count() > 1 ? 's' : '' }}
            </div>
            @endif
        </div>
        <div style="display:flex;align-items:center;">
            <a href="{{ route('loans.setup', $account) }}" class="btn btn-ghost" style="font-size:12px;white-space:nowrap;">
                <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Editar plan
            </a>
        </div>
    </div>

    {{-- Installment list --}}
    <div class="card" style="padding:0;overflow:hidden;">
        <div style="padding:14px 18px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;">
            <span style="font-family:'Bricolage Grotesque',sans-serif;font-size:14px;font-weight:800;color:var(--text);">Cuotas del préstamo</span>
            @if($nextDue)
            <span style="font-size:12px;color:var(--muted);">
                Próxima: <strong style="color:var(--warn);">{{ $nextDue->due_date->format('d/m/Y') }}</strong>
            </span>
            @endif
        </div>

        <div style="max-height:320px;overflow-y:auto;">
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="background:var(--surface2);">
                        <th style="padding:8px 16px;font-size:10px;font-weight:800;letter-spacing:0.08em;text-transform:uppercase;color:var(--muted);text-align:left;">#</th>
                        <th style="padding:8px 8px;font-size:10px;font-weight:800;letter-spacing:0.08em;text-transform:uppercase;color:var(--muted);text-align:left;">Vencimiento</th>
                        <th style="padding:8px 8px;font-size:10px;font-weight:800;letter-spacing:0.08em;text-transform:uppercase;color:var(--muted);text-align:right;">Importe</th>
                        <th style="padding:8px 16px;font-size:10px;font-weight:800;letter-spacing:0.08em;text-transform:uppercase;color:var(--muted);text-align:left;">Estado</th>
                        <th style="padding:8px 16px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($loanInstallments as $inst)
                    <tr style="border-bottom:1px solid rgba(40,40,52,0.5);{{ $inst->is_paid ? 'opacity:0.45;' : '' }}">
                        <td style="padding:10px 16px;font-family:'Bricolage Grotesque',sans-serif;font-size:13px;font-weight:800;color:var(--muted);">{{ $inst->installment_number }}</td>
                        <td style="padding:10px 8px;font-size:13px;color:{{ $inst->isOverdue() ? 'var(--expense)' : 'var(--text)' }};">
                            {{ $inst->due_date->format('d/m/Y') }}
                            @if($inst->isOverdue())
                            <span style="font-size:9px;font-weight:800;color:var(--expense);background:rgba(240,64,96,0.1);padding:1px 5px;border-radius:4px;margin-left:4px;">VENCIDA</span>
                            @endif
                        </td>
                        <td style="padding:10px 8px;text-align:right;font-family:'Bricolage Grotesque',sans-serif;font-size:13px;font-weight:800;color:{{ $inst->is_paid ? 'var(--muted)' : 'var(--expense)' }};">
                            {{ $account->currency === 'USD' ? 'US$' : '$' }} {{ number_format($inst->amount, 0, ',', '.') }}
                        </td>
                        <td style="padding:10px 8px;">
                            @if($inst->is_paid)
                            <span style="font-size:11px;font-weight:800;color:var(--income);">✓ Pagada</span>
                            @if($inst->paid_at)
                            <span style="font-size:10px;color:var(--muted);margin-left:4px;">{{ $inst->paid_at->format('d/m') }}</span>
                            @endif
                            @else
                            <span style="font-size:11px;font-weight:700;color:{{ $inst->isOverdue() ? 'var(--expense)' : 'var(--accent2)' }};">
                                {{ $inst->isOverdue() ? 'Vencida' : 'Pendiente' }}
                            </span>
                            @endif
                        </td>
                        <td style="padding:10px 16px;text-align:right;">
                            @if(!$inst->is_paid)
                            <button onclick="openPayModal({{ $inst->id }}, {{ $inst->installment_number }}, {{ $totalInstallments }}, {{ $inst->amount }}, '{{ $inst->due_date->format('Y-m-d') }}')"
                                    class="btn btn-ghost"
                                    style="font-size:11px;padding:5px 12px;color:var(--accent2);border-color:rgba(78,155,255,0.25);">
                                Pagar
                            </button>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>

{{-- ── Modal pago de cuota ──────────────────────────────────────────────────── --}}
<div id="pay-modal-backdrop"
     onclick="closePayModal()"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.65);backdrop-filter:blur(4px);z-index:500;align-items:center;justify-content:center;">
    <div onclick="event.stopPropagation()"
         style="background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:28px;width:100%;max-width:420px;margin:16px;">

        <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:20px;">
            <div>
                <div style="font-family:'Bricolage Grotesque',sans-serif;font-size:17px;font-weight:800;letter-spacing:-0.02em;color:var(--text);">Registrar pago</div>
                <div style="font-size:12px;color:var(--muted);margin-top:3px;" id="payModalSubtitle">—</div>
            </div>
            <button onclick="closePayModal()" style="background:none;border:none;cursor:pointer;color:var(--muted);padding:4px;">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
        </div>

        <form method="POST" id="payForm">
            @csrf

            <div style="margin-bottom:16px;">
                <label style="display:block;font-size:11px;font-weight:800;letter-spacing:0.1em;text-transform:uppercase;color:var(--muted);margin-bottom:7px;font-family:'Bricolage Grotesque',sans-serif;">
                    Pagás desde
                </label>
                <select name="source_account_id" class="form-select" required style="width:100%;">
                    <option value="">— Seleccioná una cuenta —</option>
                    @foreach($sourceAccounts ?? [] as $src)
                    <option value="{{ $src->id }}">{{ $src->name }} ({{ $src->type === 'digital' ? 'Digital' : 'Efectivo' }})</option>
                    @endforeach
                </select>
            </div>

            <div style="margin-bottom:16px;">
                <label style="display:block;font-size:11px;font-weight:800;letter-spacing:0.1em;text-transform:uppercase;color:var(--muted);margin-bottom:7px;font-family:'Bricolage Grotesque',sans-serif;">
                    Importe
                </label>
                <div style="display:flex;align-items:center;gap:8px;">
                    <span style="font-size:14px;color:var(--muted);font-weight:700;white-space:nowrap;">
                        {{ $account->currency === 'USD' ? 'US$' : '$' }}
                    </span>
                    <input type="number" name="amount" id="payAmount" class="form-input"
                           inputmode="decimal"
                           step="0.01" min="0.01" placeholder="0,00" required>
                </div>
            </div>

            <div style="margin-bottom:16px;">
                <label style="display:block;font-size:11px;font-weight:800;letter-spacing:0.1em;text-transform:uppercase;color:var(--muted);margin-bottom:7px;font-family:'Bricolage Grotesque',sans-serif;">
                    Fecha
                </label>
                <input type="date" name="date" id="payDate" class="form-input"
                       value="{{ today()->format('Y-m-d') }}" required>
            </div>

            <input type="hidden" name="currency" value="{{ $account->currency }}">

            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:22px;">
                <button type="button" onclick="closePayModal()" class="btn btn-ghost">Cancelar</button>
                <button type="submit" class="btn btn-primary">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                    Confirmar pago
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openPayModal(id, num, total, amount, dueDate) {
    document.getElementById('payModalSubtitle').textContent =
        'Cuota ' + num + ' de ' + total + ' · vence ' + dueDate.split('-').reverse().join('/');
    document.getElementById('payAmount').value = amount;
    document.getElementById('payForm').action = '/prestamos/cuotas/' + id + '/pagar';
    document.getElementById('pay-modal-backdrop').style.display = 'flex';
    setTimeout(() => document.querySelector('#payForm [name="source_account_id"]').focus(), 50);
}

function closePayModal() {
    document.getElementById('pay-modal-backdrop').style.display = 'none';
}

document.addEventListener('keydown', e => { if (e.key === 'Escape') closePayModal(); });
</script>
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
                            <span class="badge badge-transfer" style="font-size:10px;background:rgba(78,155,255,0.12);color:var(--accent2);border-color:rgba(78,155,255,0.2);">
                                💳 Pago tarjeta
                            </span>
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
                           class="form-input" inputmode="decimal" step="0.01" required
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
