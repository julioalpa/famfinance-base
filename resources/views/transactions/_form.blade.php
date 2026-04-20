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
    $tx = $transaction ?? null;
    $isEdit = $tx !== null;
@endphp

<form method="POST" action="{{ $action }}">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">

        {{-- ── Columna izquierda ────────────────────────────────────────── --}}
        <div>

            {{-- Tipo --}}
            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label">Tipo de movimiento *</label>
                <div style="display: flex; gap: 8px;">
                    @foreach(['expense' => 'Gasto', 'income' => 'Ingreso', 'transfer' => 'Transferencia'] as $val => $label)
                    <label style="flex: 1; cursor: pointer;">
                        <input type="radio" name="type" value="{{ $val }}"
                               id="type-{{ $val }}"
                               {{ old('type', $tx?->type ?? 'expense') === $val ? 'checked' : '' }}
                               style="display:none;"
                               class="type-radio">
                        <div class="type-btn badge" style="display:flex; justify-content:center; padding: 8px 0; border: 1px solid var(--border); border-radius: 6px; cursor: pointer; transition: all 0.15s; font-size: 12px; color: var(--muted);"
                             id="type-btn-{{ $val }}">
                            {{ $label }}
                        </div>
                    </label>
                    @endforeach
                </div>
                @error('type') <div class="error" style="font-size:12px; color:var(--danger); margin-top:4px;">{{ $message }}</div> @enderror
            </div>

            {{-- Fecha --}}
            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label" for="date">Fecha *</label>
                <input type="date" name="date" id="date"
                       class="form-input"
                       value="{{ old('date', $tx?->date?->format('Y-m-d') ?? today()->format('Y-m-d')) }}"
                       required>
                @error('date') <div style="font-size:12px; color:var(--danger); margin-top:4px;">{{ $message }}</div> @enderror
            </div>

            {{-- Monto + Moneda --}}
            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label">Monto *</label>
                <div style="display: flex; gap: 8px;">
                    <select name="currency" class="form-select" style="width: 90px;">
                        <option value="ARS" {{ old('currency', $tx?->currency ?? 'ARS') === 'ARS' ? 'selected' : '' }}>ARS $</option>
                        <option value="USD" {{ old('currency', $tx?->currency) === 'USD' ? 'selected' : '' }}>USD US$</option>
                    </select>
                    <input type="number" name="amount" id="amount"
                           class="form-input"
                           placeholder="0,00"
                           step="0.01" min="0.01"
                           value="{{ old('amount', $tx?->amount) }}"
                           required>
                </div>
                @error('amount') <div style="font-size:12px; color:var(--danger); margin-top:4px;">{{ $message }}</div> @enderror
            </div>

            {{-- Cuenta --}}
            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label" for="account_id">Cuenta *</label>
                <select name="account_id" id="account_id" class="form-select" required>
                    <option value="">Seleccioná una cuenta</option>
                    @foreach($accounts->groupBy('type') as $type => $group)
                        <optgroup label="{{ ['cash'=>'Efectivo','digital'=>'Digital','credit'=>'Crédito'][$type] ?? $type }}">
                            @foreach($group as $account)
                                <option value="{{ $account->id }}"
                                    data-type="{{ $account->type }}"
                                    {{ old('account_id', $tx?->account_id) == $account->id ? 'selected' : '' }}>
                                    {{ $account->name }} ({{ $account->currency }})
                                </option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
                @error('account_id') <div style="font-size:12px; color:var(--danger); margin-top:4px;">{{ $message }}</div> @enderror
            </div>

            {{-- Cuenta destino (solo para transferencias) --}}
            <div class="form-group" id="target-account-group" style="margin-bottom: 20px; display: none;">
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
        </div>

        {{-- ── Columna derecha ──────────────────────────────────────────── --}}
        <div>

            {{-- Categoría --}}
            <div class="form-group" style="margin-bottom: 20px;">
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

            {{-- Origen del ingreso --}}
            <div class="form-group" id="income-source-group" style="margin-bottom: 20px; display: none;">
                <label class="form-label" for="income_source">Origen del ingreso *</label>
                <select name="income_source" id="income_source" class="form-select">
                    <option value="">Seleccioná</option>
                    <option value="salary"  {{ old('income_source', $tx?->income_source) === 'salary'  ? 'selected' : '' }}>Sueldo</option>
                    <option value="credit"  {{ old('income_source', $tx?->income_source) === 'credit'  ? 'selected' : '' }}>Crédito</option>
                    <option value="cash"    {{ old('income_source', $tx?->income_source) === 'cash'    ? 'selected' : '' }}>Efectivo</option>
                    <option value="loan"    {{ old('income_source', $tx?->income_source) === 'loan'    ? 'selected' : '' }}>Préstamo</option>
                    <option value="other"   {{ old('income_source', $tx?->income_source) === 'other'   ? 'selected' : '' }}>Otro</option>
                </select>
            </div>

            {{-- Descripción --}}
            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label" for="description">Descripción</label>
                <input type="text" name="description" id="description"
                       class="form-input"
                       placeholder="Ej: Supermercado Jumbo"
                       value="{{ old('description', $tx?->description) }}"
                       maxlength="255">
            </div>

            {{-- Cuotas (solo para gastos con tarjeta de crédito) --}}
            <div id="installments-group" style="display: none;">
                <div style="background: var(--surface2); border-radius: 8px; padding: 16px; margin-bottom: 20px;">
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 14px;">
                        <input type="checkbox" name="has_installments" id="has_installments"
                               value="1"
                               {{ old('has_installments', $tx?->has_installments) ? 'checked' : '' }}
                               style="accent-color: var(--accent); width: 14px; height: 14px;">
                        <label for="has_installments" class="form-label" style="margin: 0; cursor: pointer;">Pago en cuotas</label>
                    </div>

                    <div id="installments-count-group" style="{{ old('has_installments', $tx?->has_installments) ? '' : 'display:none' }}">
                        <label class="form-label" for="installments_count">Cantidad de cuotas</label>
                        <input type="number" name="installments_count" id="installments_count"
                               class="form-input"
                               placeholder="Ej: 12"
                               min="2" max="120"
                               value="{{ old('installments_count', $tx?->installments_count) }}">

                        @if(old('amount') || $tx?->amount)
                        <div id="installment-preview" style="margin-top: 8px; font-size: 11px; color: var(--accent);">
                        </div>
                        @endif

                        @error('installments_count') <div style="font-size:12px; color:var(--danger); margin-top:4px;">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            {{-- Notas --}}
            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label" for="notes">Notas adicionales</label>
                <textarea name="notes" id="notes"
                          class="form-input"
                          rows="3"
                          placeholder="Opcional..."
                          style="resize: vertical;">{{ old('notes', $tx?->notes) }}</textarea>
            </div>
        </div>
    </div>

    {{-- Botones --}}
    <div style="display: flex; gap: 10px; justify-content: flex-end; padding-top: 20px; border-top: 1px solid var(--border); margin-top: 4px;">
        <a href="{{ route('transactions.index') }}" class="btn btn-ghost">Cancelar</a>
        <button type="submit" class="btn btn-primary">
            {{ $isEdit ? 'Guardar cambios' : 'Registrar movimiento' }}
        </button>
    </div>
</form>

<script>
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
            if (radio.value === selected) {
                btn.style.borderColor = typeColors[radio.value];
                btn.style.color       = typeColors[radio.value];
                btn.style.background  = 'rgba(79,255,176,0.06)';
            } else {
                btn.style.borderColor = 'var(--border)';
                btn.style.color       = 'var(--muted)';
                btn.style.background  = 'transparent';
            }
        });

        // Mostrar/ocultar secciones según tipo
        const accountSel = document.getElementById('account_id');
        const selectedOption = accountSel.options[accountSel.selectedIndex];
        const isCredit = selectedOption?.dataset.type === 'credit';

        document.getElementById('target-account-group').style.display   = selected === 'transfer' ? '' : 'none';
        document.getElementById('income-source-group').style.display     = selected === 'income'   ? '' : 'none';
        document.getElementById('installments-group').style.display      = (selected === 'expense' && isCredit) ? '' : 'none';
    }

    document.querySelectorAll('input[name="type"]').forEach(r => r.addEventListener('change', updateTypeUI));
    document.getElementById('account_id').addEventListener('change', updateTypeUI);
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
