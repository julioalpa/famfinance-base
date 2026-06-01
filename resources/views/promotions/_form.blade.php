{{-- Nombre --}}
<div class="form-group">
    <label class="form-label">Nombre de la promoción <span style="color:var(--danger)">*</span></label>
    <input type="text" name="name" class="form-input" value="{{ old('name', $promotion->name ?? '') }}"
           placeholder="Ej: Descuento DirecTV 80%" required maxlength="255">
    @error('name') <div class="form-error">{{ $message }}</div> @enderror
</div>

{{-- Proveedor --}}
<div class="form-group">
    <label class="form-label">Proveedor / Empresa</label>
    <input type="text" name="provider" class="form-input" value="{{ old('provider', $promotion->provider ?? '') }}"
           placeholder="Ej: DirecTV, Movistar, Naranja X…" maxlength="255">
    @error('provider') <div class="form-error">{{ $message }}</div> @enderror
</div>

{{-- Ítem de pago vinculado --}}
<div class="form-group">
    <label class="form-label">Ítem de pago vinculado</label>
    <select name="payment_item_id" class="form-select">
        <option value="">— Sin vincular —</option>
        @foreach($paymentItems as $item)
            <option value="{{ $item->id }}" {{ old('payment_item_id', $promotion->payment_item_id ?? '') == $item->id ? 'selected' : '' }}>
                {{ $item->description }}
            </option>
        @endforeach
    </select>
    <div class="form-hint">Opcional: enlazá esta promoción con un pago mensual.</div>
    @error('payment_item_id') <div class="form-error">{{ $message }}</div> @enderror
</div>

{{-- Tipo y valor del descuento --}}
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
    <div class="form-group">
        <label class="form-label">Tipo de descuento <span style="color:var(--danger)">*</span></label>
        <select name="discount_type" id="discount_type" class="form-select" onchange="updateDiscountHint()">
            <option value="percentage" {{ old('discount_type', $promotion->discount_type ?? 'percentage') === 'percentage' ? 'selected' : '' }}>Porcentaje (%)</option>
            <option value="fixed_amount" {{ old('discount_type', $promotion->discount_type ?? '') === 'fixed_amount' ? 'selected' : '' }}>Monto fijo ($)</option>
        </select>
        @error('discount_type') <div class="form-error">{{ $message }}</div> @enderror
    </div>
    <div class="form-group">
        <label class="form-label">Valor del descuento <span style="color:var(--danger)">*</span></label>
        <div style="position: relative;">
            <span id="discount_prefix" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--muted); font-size: 14px; pointer-events: none;"></span>
            <input type="number" name="discount_value" id="discount_value" class="form-input"
                   inputmode="decimal"
                   value="{{ old('discount_value', $promotion->discount_value ?? '') }}"
                   step="0.01" min="0.01" required style="padding-left: 28px;">
        </div>
        @error('discount_value') <div class="form-error">{{ $message }}</div> @enderror
    </div>
</div>

{{-- Moneda y monto original --}}
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
    <div class="form-group">
        <label class="form-label">Moneda <span style="color:var(--danger)">*</span></label>
        <select name="currency" class="form-select">
            <option value="ARS" {{ old('currency', $promotion->currency ?? 'ARS') === 'ARS' ? 'selected' : '' }}>ARS — Pesos</option>
            <option value="USD" {{ old('currency', $promotion->currency ?? '') === 'USD' ? 'selected' : '' }}>USD — Dólares</option>
        </select>
        @error('currency') <div class="form-error">{{ $message }}</div> @enderror
    </div>
    <div class="form-group">
        <label class="form-label">Monto original</label>
        <input type="number" name="original_amount" class="form-input"
               inputmode="decimal"
               value="{{ old('original_amount', $promotion->original_amount ?? '') }}"
               step="0.01" min="0.01" placeholder="Precio sin descuento">
        <div class="form-hint">Opcional: para calcular el precio con descuento.</div>
        @error('original_amount') <div class="form-error">{{ $message }}</div> @enderror
    </div>
</div>

{{-- Fechas --}}
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
    <div class="form-group">
        <label class="form-label">Fecha de inicio</label>
        <input type="date" name="starts_at" class="form-input"
               value="{{ old('starts_at', isset($promotion->starts_at) ? $promotion->starts_at->format('Y-m-d') : '') }}">
        @error('starts_at') <div class="form-error">{{ $message }}</div> @enderror
    </div>
    <div class="form-group">
        <label class="form-label">Fecha de vencimiento <span style="color:var(--danger)">*</span></label>
        <input type="date" name="expires_at" class="form-input"
               value="{{ old('expires_at', isset($promotion->expires_at) ? $promotion->expires_at->format('Y-m-d') : '') }}"
               required>
        @error('expires_at') <div class="form-error">{{ $message }}</div> @enderror
    </div>
</div>

{{-- Recordatorio --}}
<div class="form-group">
    <label class="form-label">Recordarme con cuántos días de anticipación <span style="color:var(--danger)">*</span></label>
    <div style="display: flex; align-items: center; gap: 10px;">
        <input type="number" name="reminder_days_before" class="form-input" inputmode="numeric" style="max-width: 120px;"
               value="{{ old('reminder_days_before', $promotion->reminder_days_before ?? 30) }}"
               min="1" max="365" required>
        <span style="font-size: 13px; color: var(--muted);">días antes del vencimiento</span>
    </div>
    @error('reminder_days_before') <div class="form-error">{{ $message }}</div> @enderror
</div>

{{-- Notas --}}
<div class="form-group">
    <label class="form-label">Notas</label>
    <textarea name="notes" class="form-input" rows="3"
              placeholder="Condiciones, número de caso, pasos para renovar…" maxlength="2000">{{ old('notes', $promotion->notes ?? '') }}</textarea>
    @error('notes') <div class="form-error">{{ $message }}</div> @enderror
</div>

{{-- Activa --}}
<div class="form-group">
    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" value="1" style="width: 16px; height: 16px; accent-color: var(--accent);"
               {{ old('is_active', $promotion->is_active ?? true) ? 'checked' : '' }}>
        <span style="font-size: 14px; font-weight: 600; color: var(--text);">Promoción activa</span>
    </label>
</div>

<script>
function updateDiscountHint() {
    const type   = document.getElementById('discount_type').value;
    const prefix = document.getElementById('discount_prefix');
    const input  = document.getElementById('discount_value');
    if (type === 'percentage') {
        prefix.textContent = '%';
        input.max = 100;
        input.placeholder = 'Ej: 80';
    } else {
        prefix.textContent = '$';
        input.removeAttribute('max');
        input.placeholder = 'Ej: 1500';
    }
}
document.addEventListener('DOMContentLoaded', updateDiscountHint);
</script>
