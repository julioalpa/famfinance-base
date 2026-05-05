@extends('layouts.app')

@section('title', 'Pago de tarjeta')

@section('content')
<style>
.cp-container {
    max-width: 520px;
    margin: 0 auto;
}

.cp-header {
    margin-bottom: 28px;
}

.cp-title {
    font-family: 'Bricolage Grotesque', sans-serif;
    font-size: 26px;
    font-weight: 800;
    letter-spacing: -0.04em;
    color: var(--text);
    line-height: 1;
    margin-bottom: 6px;
}

.cp-title .accent { color: var(--accent2); }

.cp-subtitle {
    font-size: 13px;
    color: var(--muted);
    font-weight: 500;
}

.cp-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 28px;
}

.cp-field {
    margin-bottom: 20px;
}

.cp-label {
    display: block;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: var(--muted);
    margin-bottom: 8px;
    font-family: 'Bricolage Grotesque', sans-serif;
}

.cp-select, .cp-input {
    width: 100%;
    background: var(--surface2);
    border: 1px solid var(--border);
    border-radius: 10px;
    color: var(--text);
    font-family: 'Nunito', sans-serif;
    font-size: 14px;
    font-weight: 600;
    padding: 11px 14px;
    transition: border-color 0.15s;
    appearance: none;
    -webkit-appearance: none;
}

.cp-select:focus, .cp-input:focus {
    outline: none;
    border-color: var(--accent2);
    background: var(--surface3);
}

.cp-select-wrap {
    position: relative;
}

.cp-select-wrap::after {
    content: '';
    position: absolute;
    right: 14px;
    top: 50%;
    transform: translateY(-50%);
    width: 0;
    height: 0;
    border-left: 4px solid transparent;
    border-right: 4px solid transparent;
    border-top: 5px solid var(--muted);
    pointer-events: none;
}

/* Card balance preview */
.card-preview {
    margin-top: 10px;
    padding: 12px 14px;
    background: var(--surface2);
    border: 1px solid var(--border);
    border-radius: 10px;
    display: none;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}

.card-preview.visible { display: flex; }

.card-preview-name {
    font-size: 13px;
    font-weight: 700;
    color: var(--text);
}

.card-preview-balance-label {
    font-size: 10px;
    color: var(--muted);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.07em;
}

.card-preview-balance {
    font-family: 'Bricolage Grotesque', sans-serif;
    font-size: 18px;
    font-weight: 800;
    color: var(--expense);
    text-align: right;
}

/* Amount row with currency toggle */
.amount-row {
    display: grid;
    grid-template-columns: 1fr 100px;
    gap: 10px;
}

/* Divider */
.cp-divider {
    height: 1px;
    background: var(--border);
    margin: 20px 0;
}

/* Action row */
.cp-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-top: 24px;
}

.btn-cp-submit {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    border-radius: 10px;
    background: var(--accent2);
    color: #fff;
    font-family: 'Nunito', sans-serif;
    font-size: 14px;
    font-weight: 800;
    border: none;
    cursor: pointer;
    transition: all 0.2s ease;
    letter-spacing: 0.01em;
}

.btn-cp-submit:hover {
    background: #6aaeff;
    transform: translateY(-1px);
}

/* Transfer arrow visual */
.cp-flow {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 14px;
    background: var(--surface2);
    border: 1px dashed var(--border);
    border-radius: 10px;
    margin-bottom: 20px;
    font-size: 12px;
    color: var(--muted);
    font-weight: 600;
}

.cp-flow .arrow {
    color: var(--accent2);
    font-size: 16px;
}

.cp-flow-account {
    padding: 3px 8px;
    background: var(--surface3);
    border-radius: 6px;
    font-weight: 700;
    color: var(--text);
    font-size: 12px;
}

.cp-flow-account.card-name {
    color: var(--accent2);
    border: 1px solid rgba(78,155,255,0.2);
}
</style>

<div class="cp-container">
    <div class="cp-header">
        <div class="cp-title">Pago de <span class="accent">tarjeta</span></div>
        <div class="cp-subtitle">Debitá de una cuenta y reducí el saldo de tu tarjeta</div>
    </div>

    @if($creditAccounts->isEmpty())
        <div class="card" style="text-align:center;padding:40px 20px;color:var(--muted);">
            <div style="font-size:28px;margin-bottom:12px;">💳</div>
            <div style="font-size:14px;margin-bottom:16px;">No tenés tarjetas de crédito configuradas.</div>
            <a href="{{ route('accounts.create') }}" class="btn btn-primary">Agregar tarjeta</a>
        </div>
    @elseif($sourceAccounts->isEmpty())
        <div class="card" style="text-align:center;padding:40px 20px;color:var(--muted);">
            <div style="font-size:14px;">Necesitás al menos una cuenta de efectivo o digital para pagar.</div>
        </div>
    @else
    <form method="POST" action="{{ route('card-payment.store') }}" id="cpForm">
        @csrf

        <div class="cp-card">

            {{-- Tarjeta a pagar --}}
            <div class="cp-field">
                <label class="cp-label" for="target_account_id">Tarjeta a pagar</label>
                <div class="cp-select-wrap">
                    <select name="target_account_id" id="target_account_id" class="cp-select" required>
                        <option value="">— Seleccioná una tarjeta —</option>
                        @foreach($creditAccounts as $card)
                            <option value="{{ $card->id }}"
                                data-name="{{ $card->name }}"
                                data-balance="{{ abs($card->balance) }}"
                                data-currency="{{ $card->currency }}"
                                data-limit="{{ $card->credit_limit ?? 0 }}"
                                {{ old('target_account_id', $preselectedCard) == $card->id ? 'selected' : '' }}>
                                {{ $card->name }}{{ $card->brand ? ' — '.$card->brand : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Balance preview de la tarjeta --}}
                <div class="card-preview" id="cardPreview">
                    <div>
                        <div class="card-preview-name" id="previewName">—</div>
                        @if($creditAccounts->first()?->credit_limit)
                        <div style="font-size:10px;color:var(--muted);margin-top:2px;" id="previewLimit"></div>
                        @endif
                    </div>
                    <div style="text-align:right;">
                        <div class="card-preview-balance-label">Saldo adeudado</div>
                        <div class="card-preview-balance" id="previewBalance">$ 0</div>
                    </div>
                </div>
            </div>

            {{-- Flujo visual --}}
            <div class="cp-flow" id="flowVisual" style="display:none;">
                <span class="cp-flow-account" id="flowSource">Cuenta origen</span>
                <span class="arrow">→</span>
                <span class="cp-flow-account card-name" id="flowCard">Tarjeta</span>
            </div>

            {{-- Cuenta origen --}}
            <div class="cp-field">
                <label class="cp-label" for="account_id">Pagás desde</label>
                <div class="cp-select-wrap">
                    <select name="account_id" id="account_id" class="cp-select" required>
                        <option value="">— Seleccioná una cuenta —</option>
                        @foreach($sourceAccounts as $acc)
                            <option value="{{ $acc->id }}"
                                data-name="{{ $acc->name }}"
                                data-currency="{{ $acc->currency }}"
                                {{ old('account_id') == $acc->id ? 'selected' : '' }}>
                                {{ $acc->name }}
                                @if($acc->type === 'digital') (Digital) @else (Efectivo) @endif
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="cp-divider"></div>

            {{-- Monto + Moneda --}}
            <div class="cp-field">
                <label class="cp-label">Monto del pago</label>
                <div class="amount-row">
                    <input type="number"
                           name="amount"
                           id="amount"
                           class="cp-input"
                           placeholder="0,00"
                           step="0.01"
                           min="0.01"
                           value="{{ old('amount') }}"
                           required>
                    <div class="cp-select-wrap">
                        <select name="currency" id="currency" class="cp-select">
                            <option value="ARS" {{ old('currency', 'ARS') === 'ARS' ? 'selected' : '' }}>ARS</option>
                            <option value="USD" {{ old('currency') === 'USD' ? 'selected' : '' }}>USD</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Fecha --}}
            <div class="cp-field">
                <label class="cp-label" for="date">Fecha del pago</label>
                <input type="date"
                       name="date"
                       id="date"
                       class="cp-input"
                       value="{{ old('date', now()->format('Y-m-d')) }}"
                       required>
            </div>

            {{-- Notas --}}
            <div class="cp-field" style="margin-bottom:0;">
                <label class="cp-label" for="notes">Notas (opcional)</label>
                <input type="text"
                       name="notes"
                       id="notes"
                       class="cp-input"
                       placeholder="Ej: Pago mínimo, pago total..."
                       maxlength="255"
                       value="{{ old('notes') }}">
            </div>
        </div>

        {{-- Errors --}}
        @if($errors->any())
        <div style="margin-top:14px;padding:12px 14px;background:rgba(240,64,96,0.08);border:1px solid rgba(240,64,96,0.2);border-radius:10px;font-size:13px;color:var(--expense);">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
        @endif

        <div class="cp-actions">
            <a href="{{ route('transactions.index') }}" class="btn btn-ghost" style="color:var(--muted);">Cancelar</a>
            <button type="submit" class="btn-cp-submit">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                    <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/>
                    <line x1="1" y1="10" x2="23" y2="10"/>
                </svg>
                Registrar pago
            </button>
        </div>
    </form>
    @endif
</div>

<script>
(function () {
    const cardSelect   = document.getElementById('target_account_id');
    const sourceSelect = document.getElementById('account_id');
    const preview      = document.getElementById('cardPreview');
    const previewName  = document.getElementById('previewName');
    const previewBal   = document.getElementById('previewBalance');
    const previewLimit = document.getElementById('previewLimit');
    const flowVisual   = document.getElementById('flowVisual');
    const flowSource   = document.getElementById('flowSource');
    const flowCard     = document.getElementById('flowCard');

    function fmt(n) {
        return '$ ' + parseFloat(n).toLocaleString('es-AR', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
    }

    function updateCardPreview() {
        const opt = cardSelect.options[cardSelect.selectedIndex];
        if (!opt || !opt.value) {
            preview.classList.remove('visible');
            return;
        }
        previewName.textContent  = opt.dataset.name;
        previewBal.textContent   = fmt(opt.dataset.balance);
        if (previewLimit) {
            previewLimit.textContent = opt.dataset.limit > 0
                ? 'Límite: ' + fmt(opt.dataset.limit)
                : '';
        }
        preview.classList.add('visible');
        updateFlow();
    }

    function updateFlow() {
        const cardOpt   = cardSelect.options[cardSelect.selectedIndex];
        const sourceOpt = sourceSelect.options[sourceSelect.selectedIndex];
        if (!cardOpt?.value && !sourceOpt?.value) {
            flowVisual.style.display = 'none';
            return;
        }
        flowSource.textContent = sourceOpt?.value ? sourceOpt.dataset.name : '—';
        flowCard.textContent   = cardOpt?.value   ? cardOpt.dataset.name   : '—';
        flowVisual.style.display = 'flex';
    }

    cardSelect?.addEventListener('change', updateCardPreview);
    sourceSelect?.addEventListener('change', updateFlow);

    // Init on page load if values are preselected
    updateCardPreview();
    updateFlow();
})();
</script>

@endsection
