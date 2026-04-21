@extends('layouts.app')

@section('title', 'Pendientes del mes')

@section('content')

{{-- ── Header ──────────────────────────────────────────────────────────────── --}}
<div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 32px; flex-wrap: wrap; gap: 16px;">
    <div>
        <h1 class="font-display" style="font-size: 28px; font-weight: 800; letter-spacing: -0.03em; margin-bottom: 4px; color: var(--text);">
            Pendientes del mes
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
@endphp
<div class="card" style="margin-bottom: 24px; padding: 20px 24px;">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; flex-wrap: wrap; gap: 12px;">
        <div style="display: flex; align-items: center; gap: 16px;">
            <div>
                <div class="font-display" style="font-size: 32px; font-weight: 800; letter-spacing: -0.03em; line-height: 1; color: {{ $paidCount === $totalCount ? 'var(--income)' : 'var(--text)' }};">
                    {{ $paidCount }}<span style="font-size: 18px; color: var(--muted); font-weight: 600;">/{{ $totalCount }}</span>
                </div>
                <div style="font-size: 12px; color: var(--muted); margin-top: 3px; font-weight: 500;">pagos completados</div>
            </div>
            @if($paidCount === $totalCount && $totalCount > 0)
                <div style="background: rgba(45,216,112,0.12); border: 1px solid rgba(45,216,112,0.25); border-radius: 10px; padding: 8px 14px; display: flex; align-items: center; gap: 8px;">
                    <svg width="15" height="15" fill="none" stroke="var(--income)" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                    <span style="font-size: 13px; font-weight: 700; color: var(--income);">¡Todo pagado!</span>
                </div>
            @endif
        </div>
        @if($totalPaid > 0)
        <div style="text-align: right;">
            <div class="font-display" style="font-size: 22px; font-weight: 800; letter-spacing: -0.02em; color: var(--expense);">
                $ {{ number_format($totalPaid, 2, ',', '.') }}
            </div>
            <div style="font-size: 12px; color: var(--muted); font-weight: 500;">total pagado este mes</div>
        </div>
        @endif
    </div>

    <div style="height: 8px; background: var(--surface2); border-radius: 4px; overflow: hidden;">
        <div style="height: 100%; width: {{ $progressPct }}%; background: {{ $progressPct === 100 ? 'var(--income)' : 'linear-gradient(90deg, var(--accent), #f5c842)' }}; border-radius: 4px; transition: width 0.6s ease;"></div>
    </div>
</div>
@endif

{{-- ── Lista de pendientes ─────────────────────────────────────────────────── --}}
@if($monthlyPayments->isEmpty())
    <div class="card" style="text-align: center; padding: 60px 32px;">
        <div style="font-size: 40px; margin-bottom: 16px;">📋</div>
        <div style="font-size: 16px; font-weight: 700; color: var(--text); margin-bottom: 8px;">Sin ítems de pago configurados</div>
        <div style="font-size: 13px; color: var(--muted); margin-bottom: 24px;">Agregá los pagos que tenés que hacer cada mes y llevalos como checklist.</div>
        <a href="{{ route('payment-items.create') }}" class="btn btn-primary">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
            Agregar primer ítem
        </a>
    </div>
@else
    <div style="display: flex; flex-direction: column; gap: 10px;">
        @foreach($monthlyPayments as $mp)
        @php
            $item     = $mp->paymentItem;
            $isPaid   = $mp->is_paid;
            $lastAmt  = $mp->last_amount;
            $dueDay   = $item?->day_of_month;
            $today    = now()->day;
            $isOverdue = !$isPaid && $dueDay && $dueDay < $today
                         && $mon == now()->month && $year == now()->year;
        @endphp
        <div style="
            background: var(--surface);
            border: 1px solid {{ $isPaid ? 'rgba(45,216,112,0.2)' : ($isOverdue ? 'rgba(240,64,96,0.25)' : 'var(--border)') }};
            border-radius: 14px;
            padding: 18px 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            transition: border-color 0.2s;
            {{ $isPaid ? 'opacity: 0.75;' : '' }}
        ">
            {{-- Checkbox visual --}}
            @if($isPaid)
                <div onclick="confirmUnpay({{ $mp->id }}, '{{ addslashes($item?->description) }}')"
                     style="width: 26px; height: 26px; border-radius: 8px; background: rgba(45,216,112,0.15); border: 2px solid var(--income); display: flex; align-items: center; justify-content: center; cursor: pointer; flex-shrink: 0; transition: all 0.15s;"
                     title="Desmarcar como pagado">
                    <svg width="13" height="13" fill="none" stroke="var(--income)" stroke-width="2.8" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
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
                    <span style="font-size: 15px; font-weight: 700; color: {{ $isPaid ? 'var(--muted)' : 'var(--text)' }}; {{ $isPaid ? 'text-decoration: line-through;' : '' }}">
                        {{ $item?->description ?? '—' }}
                    </span>
                    @if($dueDay)
                        <span class="badge" style="background: {{ $isOverdue ? 'rgba(240,64,96,0.1)' : 'var(--surface2)' }}; color: {{ $isOverdue ? 'var(--expense)' : 'var(--muted)' }}; font-size: 10px;">
                            día {{ $dueDay }}
                        </span>
                    @endif
                    @if($isOverdue)
                        <span class="badge badge-expense" style="font-size: 10px;">VENCIDO</span>
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
                @if($isPaid && $mp->amount)
                    <div class="font-display" style="font-size: 17px; font-weight: 800; color: var(--income); letter-spacing: -0.02em;">
                        {{ $item?->currency === 'USD' ? 'US$' : '$' }} {{ number_format($mp->amount, 2, ',', '.') }}
                    </div>
                    <div style="font-size: 11px; color: var(--muted); margin-top: 2px;">
                        pagado {{ $mp->paid_at?->locale('es')->diffForHumans() }}
                    </div>
                @elseif($lastAmt)
                    <div class="font-display" style="font-size: 16px; font-weight: 700; color: var(--muted); letter-spacing: -0.02em;">
                        {{ $item?->currency === 'USD' ? 'US$' : '$' }} {{ number_format($lastAmt, 2, ',', '.') }}
                    </div>
                    <div style="font-size: 10px; color: var(--muted); margin-top: 2px;">último pago</div>
                @else
                    <div style="font-size: 12px; color: var(--muted);">sin historial</div>
                @endif
            </div>

            {{-- Acción --}}
            @if(!$isPaid)
            <div style="flex-shrink: 0;">
                <button onclick="openPayModal({{ $mp->id }}, '{{ addslashes($item?->description) }}', '{{ $lastAmt ?? '' }}', '{{ $item?->currency ?? 'ARS' }}')"
                        class="btn btn-primary" style="padding: 8px 16px; font-size: 13px;">
                    Pagar
                </button>
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
                           class="form-input" step="0.01" min="0.01" required
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

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closePayModal();
        closeUnpayModal();
    }
});
</script>

@endsection
