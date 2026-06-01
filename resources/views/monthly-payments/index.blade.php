@extends('layouts.app')

@section('title', 'Pagos del mes')

@section('content')

{{-- ── Header ──────────────────────────────────────────────────────────────── --}}
<div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 32px; flex-wrap: wrap; gap: 16px;">
    <div>
        <h1 class="font-display" style="font-size: 28px; font-weight: 800; letter-spacing: -0.03em; margin-bottom: 4px; color: var(--text);">
            Pagos del mes
        </h1>
        <div style="font-size: 13px; color: var(--muted); font-weight: 500;">
            @php
                $monthName = \Illuminate\Support\Carbon::create($year, $mon, 1)->locale('es')->isoFormat('MMMM YYYY');
            @endphp
            {{ ucfirst($monthName) }}
        </div>
    </div>

    <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
        <form method="GET">
            <input type="month" name="month" value="{{ $month }}"
                   class="form-input" style="width: auto; padding: 8px 13px; font-size: 13px;"
                   onchange="this.form.submit()">
        </form>
        <a href="{{ route('payment-items.index') }}" class="btn btn-ghost">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M12 20h9M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
            Gestionar ítems
        </a>
        <a href="{{ route('payment-items.create') }}" class="btn btn-primary">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
            Nuevo ítem
        </a>
    </div>
</div>

{{-- ── Progreso del mes ────────────────────────────────────────────────────── --}}
@if($totalCount > 0)
@php
    $progressPct = $totalCount > 0 ? round(($paidCount / $totalCount) * 100) : 0;
    $dismissedCount = $monthlyPayments->where('is_dismissed', true)->count();
@endphp
<div class="card" style="margin-bottom: 24px; padding: 20px 24px;">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; flex-wrap: wrap; gap: 12px;">
        <div style="display: flex; align-items: center; gap: 16px;">
            <div>
                <div class="font-display" style="font-size: 32px; font-weight: 800; letter-spacing: -0.03em; line-height: 1; color: {{ $paidCount === $totalCount ? 'var(--income)' : 'var(--text)' }};">
                    {{ $paidCount }}<span style="font-size: 18px; color: var(--muted); font-weight: 600;">/{{ $totalCount }}</span>
                </div>
                <div style="font-size: 12px; color: var(--muted); margin-top: 3px; font-weight: 500;">
                    pagos completados
                    @if($dismissedCount > 0)
                        · <span style="color: var(--muted);">{{ $dismissedCount }} descartado{{ $dismissedCount > 1 ? 's' : '' }}</span>
                    @endif
                </div>
            </div>
            @if($paidCount === $totalCount && $totalCount > 0)
                <div style="background: rgba(45,216,112,0.12); border: 1px solid rgba(45,216,112,0.25); border-radius: 10px; padding: 8px 14px; display: flex; align-items: center; gap: 8px;">
                    <svg width="15" height="15" fill="none" stroke="var(--income)" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                    <span style="font-size: 13px; font-weight: 700; color: var(--income);">¡Todo pagado!</span>
                </div>
            @endif
        </div>
        <div style="display: flex; gap: 20px; align-items: flex-end; flex-wrap: wrap; justify-content: flex-end;">
            @if($totalPaid > 0)
            <div style="text-align: right;">
                <div class="font-display" style="font-size: 22px; font-weight: 800; letter-spacing: -0.02em; color: var(--expense);">
                    $ {{ number_format($totalPaid, 2, ',', '.') }}
                </div>
                <div style="font-size: 12px; color: var(--muted); font-weight: 500;">total pagado este mes</div>
            </div>
            @endif
            @if($dispensableTotal > 0)
            <div style="text-align: right; padding: 8px 14px; background: rgba(232,184,64,0.08); border: 1px solid rgba(232,184,64,0.2); border-radius: 10px;">
                <div class="font-display" style="font-size: 16px; font-weight: 800; letter-spacing: -0.02em; color: var(--warn);">
                    $ {{ number_format($dispensableTotal, 2, ',', '.') }}
                </div>
                <div style="font-size: 11px; color: var(--warn); font-weight: 600; opacity: 0.8;">oportunidad de ahorro</div>
            </div>
            @endif
        </div>
    </div>

    <div style="height: 8px; background: var(--surface2); border-radius: 4px; overflow: hidden;">
        <div style="height: 100%; width: {{ $progressPct }}%; background: {{ $progressPct === 100 ? 'var(--income)' : 'linear-gradient(90deg, var(--accent), #f5c842)' }}; border-radius: 4px; transition: width 0.6s ease;"></div>
    </div>
</div>
@endif

{{-- ── Buscador ─────────────────────────────────────────────────────────────── --}}
@if($monthlyPayments->isNotEmpty())
<div style="margin-bottom: 16px;">
    <div style="position: relative;">
        <svg width="15" height="15" fill="none" stroke="var(--muted)" stroke-width="2" viewBox="0 0 24 24"
             style="position:absolute;left:12px;top:50%;transform:translateY(-50%);pointer-events:none;">
            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
        <input type="text" id="checklist-search"
               class="form-input"
               placeholder="Buscar por nombre o etiqueta…"
               style="padding-left: 36px; font-size: 13px;"
               oninput="filterChecklist(this.value)">
    </div>
</div>
@endif

{{-- ── Lista de pendientes ─────────────────────────────────────────────────── --}}
@if($monthlyPayments->isEmpty())
    <div class="card" style="text-align: center; padding: 60px 32px;">
        <div style="font-size: 40px; margin-bottom: 16px;">📋</div>
        <div style="font-size: 16px; font-weight: 700; color: var(--text); margin-bottom: 8px;">No hay pagos configurados para este mes</div>
        <div style="font-size: 13px; color: var(--muted); margin-bottom: 24px;">Creá un ítem por cada pago fijo que hacés cada mes (alquiler, internet, seguro…) y llevalos como un checklist.</div>
        <a href="{{ route('payment-items.create') }}" class="btn btn-primary">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
            Agregar primer ítem
        </a>
    </div>
@else
    <div style="display: flex; flex-direction: column; gap: 10px;">
        @foreach($monthlyPayments as $mp)
        @php
            $item           = $mp->paymentItem;
            $isPaid         = $mp->is_paid;
            $isDismissed    = $mp->is_dismissed;
            $isDirectDebit  = $item?->is_direct_debit;
            $isDispensable  = $item?->is_dispensable;
            $isRetiring     = $item?->is_retiring;
            $lastAmt        = $mp->last_amount;
            $pctChange      = $mp->pct_change;
            $dueDay         = $item?->day_of_month;
            $today          = now()->day;
            $isOverdue      = !$isPaid && !$isDismissed && $dueDay && $dueDay < $today
                              && $mon == now()->month && $year == now()->year;
            $tagNames       = $item ? $item->tags->pluck('name')->join(' ') : '';
            $searchText     = strtolower(($item?->description ?? '') . ' ' . $tagNames);
        @endphp
        <div class="checklist-item"
             data-search="{{ $searchText }}"
             style="
            background: var(--surface);
            border: 1px solid {{ $isDismissed ? 'var(--border)' : ($isPaid ? 'rgba(45,216,112,0.2)' : ($isOverdue ? 'rgba(240,64,96,0.25)' : 'var(--border)')) }};
            border-radius: 14px;
            padding: 18px 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            transition: border-color 0.2s;
            {{ ($isPaid || $isDismissed) ? 'opacity: 0.7;' : '' }}
        ">
            {{-- Checkbox / estado visual --}}
            @if($isDismissed)
                <div style="width: 26px; height: 26px; border-radius: 8px; background: var(--surface2); border: 2px solid var(--border); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <svg width="12" height="12" fill="none" stroke="var(--muted)" stroke-width="2.5" viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></svg>
                </div>
            @elseif($isPaid)
                <div onclick="confirmUnpay({{ $mp->id }}, '{{ addslashes($item?->description) }}')"
                     style="width: 26px; height: 26px; border-radius: 8px; background: rgba(45,216,112,0.15); border: 2px solid var(--income); display: flex; align-items: center; justify-content: center; cursor: pointer; flex-shrink: 0; transition: all 0.15s;"
                     title="Desmarcar como pagado">
                    <svg width="13" height="13" fill="none" stroke="var(--income)" stroke-width="2.8" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                </div>
            @elseif($isDirectDebit)
                <div style="width: 26px; height: 26px; border-radius: 8px; background: rgba(240,160,48,0.1); border: 2px solid rgba(240,160,48,0.4); display: flex; align-items: center; justify-content: center; flex-shrink: 0;"
                     title="Débito directo">
                    <svg width="12" height="12" fill="none" stroke="var(--accent)" stroke-width="2.2" viewBox="0 0 24 24"><path d="M17 1l4 4-4 4"/><path d="M3 11V9a4 4 0 0 1 4-4h14M7 23l-4-4 4-4"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
                </div>
            @else
                <div onclick="openPayModal({{ $mp->id }}, '{{ addslashes($item?->description) }}', '{{ $lastAmt ?? '' }}', '{{ $item?->currency ?? 'ARS' }}')"
                     style="width: 26px; height: 26px; border-radius: 8px; background: transparent; border: 2px solid var(--border); display: flex; align-items: center; justify-content: center; cursor: pointer; flex-shrink: 0; transition: all 0.15s;"
                     onmouseenter="this.style.borderColor='var(--accent)'"
                     onmouseleave="this.style.borderColor='var(--border)'"
                     title="Marcar como pagado">
                </div>
            @endif

            {{-- Info principal --}}
            <div style="flex: 1; min-width: 0;">
                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                    <span style="font-size: 15px; font-weight: 700; color: {{ ($isPaid || $isDismissed) ? 'var(--muted)' : 'var(--text)' }}; {{ $isPaid ? 'text-decoration: line-through;' : '' }}">
                        {{ $item?->description ?? '—' }}
                    </span>
                    @if($isDismissed)
                        <span class="badge" style="background: var(--surface2); color: var(--muted); font-size: 10px;">DESCARTADO</span>
                    @endif
                    @if($isDirectDebit && !$isDismissed)
                        <span class="badge" style="background: rgba(240,160,48,0.1); color: var(--accent); font-size: 10px;">DÉBITO DIRECTO</span>
                    @endif
                    @if($isDispensable && !$isDismissed)
                        <span class="badge" style="background: rgba(232,184,64,0.1); color: var(--warn); font-size: 10px;">PRESCINDIBLE</span>
                    @endif
                    @if($isRetiring && !$isDismissed)
                        <span class="badge" style="background: rgba(240,64,96,0.08); color: var(--expense); font-size: 10px;">A DAR DE BAJA</span>
                    @endif
                    @if($dueDay && !$isDismissed)
                        <span class="badge" style="background: {{ $isOverdue ? 'rgba(240,64,96,0.1)' : 'var(--surface2)' }}; color: {{ $isOverdue ? 'var(--expense)' : 'var(--muted)' }}; font-size: 10px;">
                            día {{ $dueDay }}
                        </span>
                    @endif
                    @if($isOverdue)
                        <span class="badge badge-expense" style="font-size: 10px;">VENCIDO</span>
                    @endif
                    @if($item && $item->tags->isNotEmpty())
                        @foreach($item->tags as $tag)
                            <span style="display:inline-flex;align-items:center;gap:3px;padding:2px 7px 2px 5px;
                                         border-radius:12px;font-size:10px;font-weight:700;
                                         background:{{ $tag->color }}22;color:{{ $tag->color }};
                                         border:1px solid {{ $tag->color }}44;">
                                <span style="width:6px;height:6px;border-radius:50%;background:{{ $tag->color }};flex-shrink:0;"></span>
                                {{ $tag->name }}
                            </span>
                        @endforeach
                    @endif
                </div>
                <div style="display: flex; align-items: center; gap: 10px; margin-top: 5px; flex-wrap: wrap;">
                    @if($item?->account)
                        <span class="badge badge-{{ $item->account->type }}" style="font-size: 10px;">{{ $item->account->name }}</span>
                    @endif
                    @if($item?->category)
                        <span style="font-size: 11px; color: var(--muted);">{{ $item->category->name }}</span>
                    @endif
                    @if($isPaid && $mp->transaction)
                        <a href="{{ route('transactions.show', $mp->transaction) }}"
                           style="font-size: 11px; color: var(--accent); text-decoration: none; display: flex; align-items: center; gap: 4px;">
                            <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                            Ver movimiento
                        </a>
                    @endif
                </div>
            </div>

            {{-- Monto --}}
            <div style="text-align: right; flex-shrink: 0;">
                @if($isDismissed)
                    <div style="font-size: 12px; color: var(--muted);">no aplica</div>
                @elseif($isPaid && $mp->amount)
                    <div style="display: flex; align-items: center; gap: 8px; justify-content: flex-end;">
                        <div class="font-display" style="font-size: 17px; font-weight: 800; color: var(--income); letter-spacing: -0.02em;">
                            {{ $item?->currency === 'USD' ? 'US$' : '$' }} {{ number_format($mp->amount, 2, ',', '.') }}
                        </div>
                        @if($pctChange !== null)
                            <span style="
                                font-size: 11px; font-weight: 700; padding: 2px 7px; border-radius: 20px;
                                background: {{ $pctChange > 0 ? 'rgba(240,64,96,0.12)' : ($pctChange < 0 ? 'rgba(45,216,112,0.12)' : 'var(--surface2)') }};
                                color: {{ $pctChange > 0 ? 'var(--expense)' : ($pctChange < 0 ? 'var(--income)' : 'var(--muted)') }};
                            ">{{ $pctChange > 0 ? '+' : '' }}{{ $pctChange }}%</span>
                        @endif
                    </div>
                    <div style="font-size: 11px; color: var(--muted); margin-top: 2px;">
                        pagado {{ $mp->paid_at?->locale('es')->diffForHumans() }}
                    </div>
                @elseif($isDirectDebit && $item?->amount)
                    <div class="font-display" style="font-size: 16px; font-weight: 700; color: var(--muted); letter-spacing: -0.02em;">
                        {{ $item->currency === 'USD' ? 'US$' : '$' }} {{ number_format($item->amount, 2, ',', '.') }}
                    </div>
                    <div style="font-size: 10px; color: var(--muted); margin-top: 2px;">monto fijo</div>
                @elseif($lastAmt)
                    <div class="font-display" style="font-size: 16px; font-weight: 700; color: var(--muted); letter-spacing: -0.02em;">
                        {{ $item?->currency === 'USD' ? 'US$' : '$' }} {{ number_format($lastAmt, 2, ',', '.') }}
                    </div>
                    <div style="font-size: 10px; color: var(--muted); margin-top: 2px;">último pago</div>
                @else
                    <div style="font-size: 12px; color: var(--muted);">sin historial</div>
                @endif
            </div>

            {{-- Acciones --}}
            @if(!$isDismissed && !$isPaid)
            <div style="flex-shrink: 0; display: flex; flex-direction: column; gap: 6px; align-items: flex-end;">
                @if($isDirectDebit)
                    <form method="POST" action="{{ route('monthly-payments.confirm', $mp) }}">
                        @csrf
                        <button type="submit" class="btn btn-primary" style="padding: 8px 16px; font-size: 13px;">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                            Confirmar
                        </button>
                    </form>
                @else
                    <button onclick="openPayModal({{ $mp->id }}, '{{ addslashes($item?->description) }}', '{{ $lastAmt ?? '' }}', '{{ $item?->currency ?? 'ARS' }}')"
                            class="btn btn-primary" style="padding: 8px 16px; font-size: 13px;">
                        Pagar
                    </button>
                @endif
                <div style="display: flex; gap: 12px; align-items: center;">
                    @if($isRetiring)
                        <button onclick="openRetireModal({{ $item->id }}, '{{ addslashes($item?->description) }}')"
                                style="background: none; border: none; font-size: 11px; color: var(--expense); cursor: pointer; font-family: 'Nunito', sans-serif; font-weight: 700; padding: 2px 0; text-decoration: underline; text-underline-offset: 2px;">
                            Dar de baja
                        </button>
                    @endif
                    <form method="POST" action="{{ route('monthly-payments.dismiss', $mp) }}">
                        @csrf
                        <button type="submit"
                                style="background: none; border: none; font-size: 11px; color: var(--muted); cursor: pointer; font-family: 'Nunito', sans-serif; font-weight: 600; padding: 2px 0; text-decoration: underline; text-underline-offset: 2px;"
                                onclick="return confirm('¿Descartar este pago para el mes?')">
                            Descartar
                        </button>
                    </form>
                </div>
            </div>
            @elseif($isDismissed)
            <div style="flex-shrink: 0;">
                <form method="POST" action="{{ route('monthly-payments.undismiss', $mp) }}">
                    @csrf
                    <button type="submit" class="btn btn-ghost" style="padding: 7px 14px; font-size: 12px;">
                        Restaurar
                    </button>
                </form>
            </div>
            @endif
        </div>
        @endforeach
    </div>
@endif

{{-- ── Modal de pago ───────────────────────────────────────────────────────── --}}
<div id="pay-modal-backdrop"
     onclick="closePayModal()"
     style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.65); backdrop-filter:blur(4px); z-index:500; align-items:center; justify-content:center;">
    <div onclick="event.stopPropagation()"
         style="background:var(--surface); border:1px solid var(--border); border-radius:16px; padding:28px; width:100%; max-width:420px; margin:16px; position:relative;">

        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:22px;">
            <div>
                <h3 class="font-display" style="font-size:17px; font-weight:800; letter-spacing:-0.02em;">Registrar pago</h3>
                <div id="modal-description" style="font-size:13px; color:var(--muted); margin-top:3px;"></div>
            </div>
            <button onclick="closePayModal()" style="background:none; border:none; cursor:pointer; color:var(--muted); padding:4px;" aria-label="Cerrar">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
        </div>

        <form id="pay-modal-form" method="POST">
            @csrf

            <div style="margin-bottom:18px;">
                <label class="form-label">Monto pagado *</label>
                <div style="display:flex; gap:8px; align-items:center;">
                    <span id="modal-currency-label" style="font-size:14px; color:var(--muted); font-weight:700; white-space:nowrap; padding: 10px 0;">$</span>
                    <input type="number" name="amount" id="modal-amount"
                           class="form-input" inputmode="decimal" step="0.01" min="0.01" required
                           placeholder="0,00">
                </div>
                @error('amount')
                    <div style="font-size:12px; color:var(--danger); margin-top:4px;">{{ $message }}</div>
                @enderror
            </div>

            <div style="margin-bottom:18px;">
                <label class="form-label" for="modal-date">Fecha del pago *</label>
                <input type="date" name="date" id="modal-date"
                       class="form-input" required
                       value="{{ now()->format('Y-m-d') }}">
                @error('date')
                    <div style="font-size:12px; color:var(--danger); margin-top:4px;">{{ $message }}</div>
                @enderror
            </div>

            <div style="margin-bottom:22px;">
                <label class="form-label" for="modal-notes">Notas</label>
                <textarea name="notes" id="modal-notes"
                          class="form-input" rows="2"
                          placeholder="Opcional..."
                          style="resize:vertical;"></textarea>
            </div>

            <div style="display:flex; gap:10px; justify-content:flex-end;">
                <button type="button" onclick="closePayModal()" class="btn btn-ghost">Cancelar</button>
                <button type="submit" class="btn btn-primary">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                    Confirmar pago
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ── Modal dar de baja ───────────────────────────────────────────────────── --}}
<div id="retire-modal-backdrop"
     onclick="closeRetireModal()"
     style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.65); backdrop-filter:blur(4px); z-index:500; align-items:center; justify-content:center;">
    <div onclick="event.stopPropagation()"
         style="background:var(--surface); border:1px solid rgba(240,64,96,0.3); border-radius:16px; padding:28px; width:100%; max-width:400px; margin:16px;">
        <h3 class="font-display" style="font-size:17px; font-weight:800; letter-spacing:-0.02em; margin-bottom:10px; color:var(--danger);">¿Dar de baja?</h3>
        <p style="font-size:13px; color:var(--muted); margin-bottom:6px;">
            «<span id="retire-description" style="color:var(--text); font-weight:600;"></span>» dejará de aparecer en los próximos meses.
        </p>
        <p style="font-size:12px; color:var(--muted); margin-bottom:22px;">Podés reactivarlo desde la sección de ítems si cambiás de opinión.</p>
        <div style="display:flex; gap:10px; justify-content:flex-end;">
            <button type="button" onclick="closeRetireModal()" class="btn btn-ghost">Cancelar</button>
            <form id="retire-form" method="POST" style="display:inline;">
                @csrf
                <button type="submit" class="btn btn-danger">
                    Dar de baja
                </button>
            </form>
        </div>
    </div>
</div>

{{-- ── Modal de desmarcar ──────────────────────────────────────────────────── --}}
<div id="unpay-modal-backdrop"
     onclick="closeUnpayModal()"
     style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.65); backdrop-filter:blur(4px); z-index:500; align-items:center; justify-content:center;">
    <div onclick="event.stopPropagation()"
         style="background:var(--surface); border:1px solid var(--border); border-radius:16px; padding:28px; width:100%; max-width:380px; margin:16px;">
        <h3 class="font-display" style="font-size:17px; font-weight:800; letter-spacing:-0.02em; margin-bottom:10px;">¿Desmarcar pago?</h3>
        <p style="font-size:13px; color:var(--muted); margin-bottom:6px;">
            Esto eliminará el movimiento vinculado de «<span id="unpay-description" style="color:var(--text); font-weight:600;"></span>» de tus cuentas.
        </p>
        <p style="font-size:12px; color:var(--danger); margin-bottom:22px;">Esta acción no se puede deshacer.</p>
        <div style="display:flex; gap:10px; justify-content:flex-end;">
            <button type="button" onclick="closeUnpayModal()" class="btn btn-ghost">Cancelar</button>
            <form id="unpay-form" method="POST" style="display:inline;">
                @csrf
                <input type="hidden" name="_method" value="POST">
                <button type="submit" class="btn btn-danger">
                    Desmarcar
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function openPayModal(id, description, lastAmount, currency) {
    const form = document.getElementById('pay-modal-form');
    form.action = '/pendientes/' + id + '/pagar';
    document.getElementById('modal-description').textContent = description;
    document.getElementById('modal-amount').value = lastAmount || '';
    document.getElementById('modal-currency-label').textContent = currency === 'USD' ? 'US$' : '$';
    document.getElementById('modal-notes').value = '';
    const backdrop = document.getElementById('pay-modal-backdrop');
    backdrop.style.display = 'flex';
    setTimeout(() => document.getElementById('modal-amount').focus(), 50);
}

function closePayModal() {
    document.getElementById('pay-modal-backdrop').style.display = 'none';
}

function confirmUnpay(id, description) {
    document.getElementById('unpay-description').textContent = description;
    document.getElementById('unpay-form').action = '/pendientes/' + id + '/desmarcar';
    const backdrop = document.getElementById('unpay-modal-backdrop');
    backdrop.style.display = 'flex';
}

function closeUnpayModal() {
    document.getElementById('unpay-modal-backdrop').style.display = 'none';
}

function openRetireModal(itemId, description) {
    document.getElementById('retire-description').textContent = description;
    document.getElementById('retire-form').action = '/pendientes-items/' + itemId + '/retirar';
    document.getElementById('retire-modal-backdrop').style.display = 'flex';
}

function closeRetireModal() {
    document.getElementById('retire-modal-backdrop').style.display = 'none';
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closePayModal();
        closeUnpayModal();
        closeRetireModal();
    }
});

function filterChecklist(q) {
    const term = q.toLowerCase().trim();
    document.querySelectorAll('.checklist-item').forEach(item => {
        const txt = item.dataset.search || '';
        item.style.display = term === '' || txt.includes(term) ? '' : 'none';
    });
}
</script>

@endsection
