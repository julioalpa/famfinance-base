{{--
    Partial: _form.blade.php
    Variables esperadas:
      $transaction  (puede ser null en create)
      $categories   Collection
      $accounts     Collection
      $action       string (URL del form)
      $method       string ('POST' | 'PUT')
--}}

@php
    $tx               = $transaction ?? null;
    $isEdit           = $tx !== null;
    $bulk             = $bulk ?? false;
    $defaultDate      = $defaultDate ?? null;
    $defaultAccountId = $defaultAccountId ?? null;
    $pendingItems     = $pendingItems ?? collect();
    $allTags          = $allTags ?? collect();
    $selectedTagIds   = old('tags', $tx?->tags?->pluck('id')->toArray() ?? []);

    // Auto-expandir "Más opciones" si hay datos secundarios cargados
    $expandMore =
        old('notes', $tx?->notes) ||
        old('is_avoidable', $tx?->is_avoidable) ||
        old('income_source', $tx?->income_source) ||
        old('has_installments', $tx?->has_installments) ||
        !empty($selectedTagIds);
@endphp

<style>
    /* Sticky bottom actions: oculta el FAB y la bottom-nav cuando hay form */
    @media (max-width: 768px) {
        .fab { display: none !important; }
    }

    .tx-amount-wrap {
        display: flex;
        align-items: stretch;
        gap: 10px;
        background: var(--surface2);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 4px 6px 4px 4px;
        transition: border-color 0.15s, box-shadow 0.15s;
    }
    .tx-amount-wrap:focus-within {
        border-color: var(--accent);
        box-shadow: 0 0 0 3px rgba(240,160,48,0.1);
    }
    .tx-amount-currency {
        background: transparent;
        border: none;
        color: var(--muted);
        font-weight: 700;
        font-size: 13px;
        padding: 0 10px;
        cursor: pointer;
        appearance: none;
        background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='10' height='6' viewBox='0 0 10 6' fill='none' stroke='%236a6676' stroke-width='1.8' stroke-linecap='round' stroke-linejoin='round'><polyline points='1 1 5 5 9 1'/></svg>");
        background-repeat: no-repeat;
        background-position: right 6px center;
        padding-right: 22px;
    }
    .tx-amount-input {
        flex: 1;
        min-width: 0;
        background: transparent;
        border: none;
        outline: none;
        color: var(--text);
        font-family: 'Bricolage Grotesque', sans-serif;
        font-size: 32px;
        font-weight: 800;
        letter-spacing: -0.02em;
        text-align: right;
        padding: 10px 12px;
        width: 100%;
    }
    .tx-amount-input::placeholder { color: var(--muted); opacity: 0.55; }
    .tx-amount-input::-webkit-outer-spin-button,
    .tx-amount-input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
    .tx-amount-input { -moz-appearance: textfield; }

    /* Type pills */
    .tx-type-row { display: flex; gap: 6px; }
    .tx-type-btn {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 44px;
        padding: 10px 8px;
        border: 1px solid var(--border);
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.15s;
        font-size: 13px;
        font-weight: 700;
        color: var(--muted);
        background: var(--surface2);
        user-select: none;
    }
    .tx-type-btn:hover { color: var(--text); }
    .type-radio:focus-visible + .tx-type-btn {
        outline: 2px solid var(--accent);
        outline-offset: 2px;
    }

    /* Más opciones */
    .tx-more { margin-top: 6px; }
    .tx-more summary {
        list-style: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 14px;
        background: var(--surface2);
        border: 1px solid var(--border);
        border-radius: 10px;
        font-size: 13px;
        font-weight: 700;
        color: var(--text);
        transition: background 0.15s;
    }
    .tx-more summary:hover { background: var(--surface3); }
    .tx-more summary::-webkit-details-marker { display: none; }
    .tx-more summary .chev {
        margin-left: auto;
        transition: transform 0.2s;
    }
    .tx-more[open] summary .chev { transform: rotate(180deg); }
    .tx-more summary .hint {
        font-size: 11px;
        color: var(--muted);
        font-weight: 600;
        margin-left: 2px;
    }
    .tx-more-content {
        padding: 18px 2px 4px;
        display: flex;
        flex-direction: column;
        gap: 16px;
    }
    .tx-more-content .form-group { margin-bottom: 0; }

    /* Pendiente shortcut card */
    .tx-pending-shortcut {
        background: var(--accent-dim);
        border: 1px solid rgba(240,160,48,0.28);
        border-radius: 12px;
        padding: 12px 14px;
        margin-bottom: 16px;
    }
    .tx-pending-shortcut label.form-label {
        margin-bottom: 6px;
        color: var(--accent);
    }

    /* Sticky bottom actions */
    .tx-actions {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
        padding-top: 18px;
        border-top: 1px solid var(--border);
        margin-top: 22px;
    }

    @media (max-width: 768px) {
        .tx-form-spacer { height: 84px; }
        .tx-actions {
            position: fixed;
            left: 0; right: 0;
            bottom: 56px;
            padding: 10px 14px calc(10px + env(safe-area-inset-bottom));
            background: var(--surface);
            border-top: 1px solid var(--border);
            margin: 0;
            z-index: 150;
        }
        .tx-actions .btn {
            flex: 1;
            justify-content: center;
            min-height: 48px;
            font-size: 15px;
        }
        .tx-amount-input { font-size: 28px; padding: 8px 10px; }
    }
</style>

<form method="POST" action="{{ $action }}" autocomplete="off">
    @csrf
    @if($isEdit) @method('PUT') @endif
    @if($bulk) <input type="hidden" name="bulk" value="1"> @endif

    {{-- ── Pendiente del mes (atajo, sólo create + expense + hay items) ─────── --}}
    @if(! $isEdit && $pendingItems->isNotEmpty())
    <div class="tx-pending-shortcut" id="pending-item-group" style="display: none;">
        <label class="form-label" for="payment_item_id">
            ¿Pagás algo pendiente?
            <span style="font-weight:500; text-transform:none; letter-spacing:0;">(autocompleta los campos)</span>
        </label>
        <select name="payment_item_id" id="payment_item_id" class="form-select"
                onchange="applyPendingItem(this.value)">
            <option value="">— No, es un gasto normal —</option>
            @foreach($pendingItems as $item)
                <option value="{{ $item['id'] }}"
                        {{ old('payment_item_id') == $item['id'] ? 'selected' : '' }}>
                    {{ $item['description'] }}
                    @if($item['last_amount']) · última: ${{ number_format($item['last_amount'], 2, ',', '.') }}@endif
                </option>
            @endforeach
        </select>
    </div>
    @endif

    {{-- ── Monto (primary action) ─────────────────────────────────────────── --}}
    <div class="form-group" style="margin-bottom: 16px;">
        <label class="form-label" for="amount">Monto *</label>
        <div class="tx-amount-wrap">
            <select name="currency" class="tx-amount-currency" aria-label="Moneda">
                <option value="ARS" {{ old('currency', $tx?->currency ?? 'ARS') === 'ARS' ? 'selected' : '' }}>ARS $</option>
                <option value="USD" {{ old('currency', $tx?->currency) === 'USD' ? 'selected' : '' }}>USD US$</option>
            </select>
            <input type="number" name="amount" id="amount"
                   class="tx-amount-input"
                   inputmode="decimal"
                   placeholder="0,00"
                   step="0.01" min="0.01"
                   value="{{ old('amount', $tx?->amount) }}"
                   required
                   @if(! $isEdit) autofocus @endif>
        </div>
        @error('amount') <div style="font-size:12px; color:var(--danger); margin-top:6px;">{{ $message }}</div> @enderror
    </div>

    {{-- ── Cuenta ─────────────────────────────────────────────────────────── --}}
    <div class="form-group" style="margin-bottom: 16px;">
        <label class="form-label" for="account_id">Cuenta *</label>
        <select name="account_id" id="account_id" class="form-select" required>
            <option value="">Seleccioná una cuenta</option>
            @foreach($accounts->groupBy('type') as $type => $group)
                <optgroup label="{{ ['cash'=>'Efectivo','digital'=>'Digital','credit'=>'Crédito'][$type] ?? $type }}">
                    @foreach($group as $account)
                        <option value="{{ $account->id }}"
                            data-type="{{ $account->type }}"
                            {{ old('account_id', $tx?->account_id ?? $defaultAccountId) == $account->id ? 'selected' : '' }}>
                            {{ $account->name }} ({{ $account->currency }})
                        </option>
                    @endforeach
                </optgroup>
            @endforeach
        </select>
        @error('account_id') <div style="font-size:12px; color:var(--danger); margin-top:4px;">{{ $message }}</div> @enderror
    </div>

    {{-- ── Cuenta destino (sólo transferencias) ───────────────────────────── --}}
    <div class="form-group" id="target-account-group" style="margin-bottom: 16px; display: none;">
        <label class="form-label" for="target_account_id">Cuenta destino *</label>
        <select name="target_account_id" id="target_account_id" class="form-select">
            <option value="">Seleccioná cuenta destino</option>
            @foreach($accounts as $account)
                <option value="{{ $account->id }}"
                        {{ old('target_account_id', $tx?->target_account_id) == $account->id ? 'selected' : '' }}>
                    {{ $account->name }} ({{ $account->currency }})
                </option>
            @endforeach
        </select>
    </div>

    {{-- ── Categoría (oculta en transferencias) ───────────────────────────── --}}
    <div class="form-group" id="category-group" style="margin-bottom: 16px;">
        <label class="form-label" for="category_id">Categoría</label>
        <select name="category_id" id="category_id" class="form-select">
            <option value="">Sin categoría</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}"
                        {{ old('category_id', $tx?->category_id) == $cat->id ? 'selected' : '' }}>
                    {{ $cat->name }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- ── Descripción ────────────────────────────────────────────────────── --}}
    <div class="form-group" style="margin-bottom: 18px;">
        <label class="form-label" for="description">Descripción</label>
        <input type="text" name="description" id="description"
               class="form-input"
               placeholder="Ej: Supermercado Jumbo"
               value="{{ old('description', $tx?->description) }}"
               maxlength="255">
    </div>

    {{-- ── Tipo (compacto) ────────────────────────────────────────────────── --}}
    <div class="form-group" style="margin-bottom: 16px;">
        <label class="form-label">Tipo</label>
        <div class="tx-type-row">
            @foreach(['expense' => 'Gasto', 'income' => 'Ingreso', 'transfer' => 'Transferencia'] as $val => $label)
            <label style="flex: 1; cursor: pointer;">
                <input type="radio" name="type" value="{{ $val }}"
                       id="type-{{ $val }}"
                       {{ old('type', $tx?->type ?? 'expense') === $val ? 'checked' : '' }}
                       class="type-radio"
                       style="position:absolute; opacity:0; pointer-events:none;">
                <div class="tx-type-btn" id="type-btn-{{ $val }}">{{ $label }}</div>
            </label>
            @endforeach
        </div>
        @error('type') <div style="font-size:12px; color:var(--danger); margin-top:4px;">{{ $message }}</div> @enderror
    </div>

    {{-- ── Fecha ──────────────────────────────────────────────────────────── --}}
    <div class="form-group" style="margin-bottom: 18px;">
        <label class="form-label" for="date">Fecha *</label>
        <input type="date" name="date" id="date"
               class="form-input"
               value="{{ old('date', $tx?->date?->format('Y-m-d') ?? $defaultDate ?? today()->format('Y-m-d')) }}"
               required>
        @error('date') <div style="font-size:12px; color:var(--danger); margin-top:4px;">{{ $message }}</div> @enderror
    </div>

    {{-- ── Más opciones (collapsible) ─────────────────────────────────────── --}}
    <details class="tx-more" {{ $expandMore ? 'open' : '' }} id="more-options">
        <summary>
            Más opciones
            <span class="hint">cuotas · etiquetas · notas · gasto evitable…</span>
            <svg class="chev" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
        </summary>

        <div class="tx-more-content">

            {{-- Cuotas (sólo crédito + gasto) --}}
            <div id="installments-group" style="display: none;">
                <div style="background: var(--surface2); border-radius: 10px; padding: 14px;">
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px;">
                        <input type="checkbox" name="has_installments" id="has_installments"
                               value="1"
                               {{ old('has_installments', $tx?->has_installments) ? 'checked' : '' }}
                               style="accent-color: var(--accent); width: 16px; height: 16px;">
                        <label for="has_installments" class="form-label" style="margin: 0; cursor: pointer;">Pago en cuotas</label>
                    </div>

                    <div id="installments-count-group" style="{{ old('has_installments', $tx?->has_installments) ? '' : 'display:none' }}">
                        <label class="form-label" for="installments_count">Cantidad de cuotas</label>
                        <input type="number" name="installments_count" id="installments_count"
                               class="form-input"
                               inputmode="numeric"
                               placeholder="Ej: 12"
                               min="2" max="120"
                               value="{{ old('installments_count', $tx?->installments_count) }}">

                        <div id="installment-preview" style="margin-top: 8px; font-size: 12px; color: var(--accent);"></div>

                        @error('installments_count') <div style="font-size:12px; color:var(--danger); margin-top:4px;">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            {{-- Origen del ingreso --}}
            <div class="form-group" id="income-source-group" style="display: none;">
                <label class="form-label" for="income_source">¿De dónde viene este ingreso?</label>
                <select name="income_source" id="income_source" class="form-select">
                    <option value="">Seleccioná</option>
                    <option value="salary"  {{ old('income_source', $tx?->income_source) === 'salary'  ? 'selected' : '' }}>Sueldo / cobro de trabajo</option>
                    <option value="credit"  {{ old('income_source', $tx?->income_source) === 'credit'  ? 'selected' : '' }}>Crédito bancario</option>
                    <option value="cash"    {{ old('income_source', $tx?->income_source) === 'cash'    ? 'selected' : '' }}>Efectivo recibido</option>
                    <option value="loan"    {{ old('income_source', $tx?->income_source) === 'loan'    ? 'selected' : '' }}>Préstamo recibido</option>
                    <option value="other"   {{ old('income_source', $tx?->income_source) === 'other'   ? 'selected' : '' }}>Otro</option>
                </select>
                <div style="font-size: 11px; color: var(--muted); margin-top: 4px;">Opcional. Sirve para filtrar reportes de ingresos.</div>
            </div>

            {{-- Gasto evitable --}}
            <div id="avoidable-group" style="display: none;">
                <div style="background: var(--surface2); border-radius: 10px; padding: 14px;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <input type="checkbox" name="is_avoidable" id="is_avoidable"
                               value="1"
                               {{ old('is_avoidable', $tx?->is_avoidable) ? 'checked' : '' }}
                               style="accent-color: var(--expense); width: 16px; height: 16px;">
                        <label for="is_avoidable" class="form-label" style="margin: 0; cursor: pointer;">Gasto evitable</label>
                    </div>
                    <div style="font-size: 11px; color: var(--muted); margin-top: 6px; padding-left: 26px;">
                        Marcá si este gasto podría haberse evitado. Se usa para calcular oportunidades de ahorro.
                    </div>
                </div>
            </div>

            {{-- Etiquetas --}}
            <x-tag-picker :allTags="$allTags" :selectedIds="$selectedTagIds" />

            {{-- Notas --}}
            <div class="form-group">
                <label class="form-label" for="notes">Notas adicionales</label>
                <textarea name="notes" id="notes"
                          class="form-input"
                          rows="3"
                          placeholder="Opcional..."
                          style="resize: vertical;">{{ old('notes', $tx?->notes) }}</textarea>
            </div>
        </div>
    </details>

    {{-- Spacer para que la barra fixed no tape el último campo en mobile --}}
    <div class="tx-form-spacer" aria-hidden="true"></div>

    {{-- ── Acciones (sticky bottom en mobile) ─────────────────────────────── --}}
    <div class="tx-actions">
        @if($bulk)
            <a href="{{ route('transactions.index') }}" class="btn btn-ghost">Finalizar</a>
            <button type="submit" class="btn btn-primary">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
                Guardar y seguir
            </button>
        @else
            <a href="{{ route('transactions.index') }}" class="btn btn-ghost">Cancelar</a>
            <button type="submit" class="btn btn-primary">
                {{ $isEdit ? 'Guardar cambios' : 'Registrar' }}
            </button>
        @endif
    </div>
</form>

<script>
@if(! $isEdit && $pendingItems->isNotEmpty())
const PENDING_ITEMS = @json($pendingItems->keyBy('id'));

function applyPendingItem(id) {
    if (!id) return;
    const item = PENDING_ITEMS[id];
    if (!item) return;

    if (item.last_amount) {
        document.getElementById('amount').value = item.last_amount;
        document.getElementById('amount').dispatchEvent(new Event('input'));
    }
    if (item.account_id) {
        document.getElementById('account_id').value = item.account_id;
        document.getElementById('account_id').dispatchEvent(new Event('change'));
    }
    if (item.category_id) {
        document.getElementById('category_id').value = item.category_id;
    }
}
@endif

(function() {
    // ── Lógica de tipo de movimiento ─────────────────────────────────────
    const typeColors = {
        expense:  'var(--expense)',
        income:   'var(--income)',
        transfer: 'var(--accent2)',
    };

    function updateTypeUI() {
        const selected = document.querySelector('input[name="type"]:checked')?.value;
        document.querySelectorAll('.type-radio').forEach(radio => {
            const btn = document.getElementById('type-btn-' + radio.value);
            if (!btn) return;
            if (radio.value === selected) {
                btn.style.borderColor = typeColors[radio.value];
                btn.style.color       = typeColors[radio.value];
                btn.style.background  = 'rgba(255,255,255,0.02)';
            } else {
                btn.style.borderColor = '';
                btn.style.color       = '';
                btn.style.background  = '';
            }
        });

        const accountSel = document.getElementById('account_id');
        const selectedOption = accountSel.options[accountSel.selectedIndex];
        const isCredit = selectedOption?.dataset.type === 'credit';

        document.getElementById('target-account-group').style.display = selected === 'transfer' ? '' : 'none';
        document.getElementById('income-source-group').style.display  = selected === 'income'   ? '' : 'none';
        document.getElementById('installments-group').style.display   = (selected === 'expense' && isCredit) ? '' : 'none';
        document.getElementById('avoidable-group').style.display      = selected === 'expense'  ? '' : 'none';
        if (selected !== 'expense') document.getElementById('is_avoidable').checked = false;

        const categoryGroup = document.getElementById('category-group');
        categoryGroup.style.display = selected === 'transfer' ? 'none' : '';
        if (selected === 'transfer') {
            document.getElementById('category_id').value = '';
        }

        const pendingGroup = document.getElementById('pending-item-group');
        if (pendingGroup) pendingGroup.style.display = selected === 'expense' ? '' : 'none';

        // Auto-abrir "Más opciones" cuando hay cuotas disponibles
        const more = document.getElementById('more-options');
        if (more && (selected === 'expense' && isCredit) && !more.dataset.userToggled) {
            more.open = true;
        }
    }

    document.querySelectorAll('input[name="type"]').forEach(r => r.addEventListener('change', updateTypeUI));
    document.getElementById('account_id').addEventListener('change', updateTypeUI);

    // Marcar cuando el usuario interactúa manualmente con el details
    document.getElementById('more-options')?.addEventListener('toggle', (e) => {
        e.currentTarget.dataset.userToggled = '1';
    });

    updateTypeUI(); // inicial

    // ── Cuotas ───────────────────────────────────────────────────────────
    const hasInstCb  = document.getElementById('has_installments');
    const instGroup  = document.getElementById('installments-count-group');
    const instCount  = document.getElementById('installments_count');
    const amountInput = document.getElementById('amount');

    hasInstCb?.addEventListener('change', () => {
        instGroup.style.display = hasInstCb.checked ? '' : 'none';
    });

    function updatePreview() {
        const preview = document.getElementById('installment-preview');
        if (!preview) return;
        const amount = parseFloat(amountInput?.value) || 0;
        const count  = parseInt(instCount?.value) || 0;
        if (amount > 0 && count > 1) {
            const cuota = (amount / count).toFixed(2);
            preview.textContent = `→ ${count} cuotas de $ ${parseFloat(cuota).toLocaleString('es-AR', {minimumFractionDigits:2})}`;
        } else {
            preview.textContent = '';
        }
    }

    instCount?.addEventListener('input', updatePreview);
    amountInput?.addEventListener('input', updatePreview);
    updatePreview();
})();
</script>
