@php
    $pi     = $paymentItem ?? null;
    $isEdit = $pi !== null;
@endphp

<form method="POST" action="{{ $action }}">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">

        {{-- Columna izquierda --}}
        <div>

            <div style="margin-bottom: 20px;">
                <label class="form-label" for="description">Nombre del pago *</label>
                <input type="text" name="description" id="description"
                       class="form-input"
                       placeholder="Ej: Alquiler, Internet, Seguro"
                       value="{{ old('description', $pi?->description) }}"
                       maxlength="255" required>
                @error('description')
                    <div style="font-size:12px; color:var(--danger); margin-top:4px;">{{ $message }}</div>
                @enderror
            </div>

            <div style="margin-bottom: 20px;">
                <label class="form-label">Moneda *</label>
                <select name="currency" class="form-select">
                    <option value="ARS" {{ old('currency', $pi?->currency ?? 'ARS') === 'ARS' ? 'selected' : '' }}>ARS $</option>
                    <option value="USD" {{ old('currency', $pi?->currency) === 'USD' ? 'selected' : '' }}>USD US$</option>
                </select>
                @error('currency')
                    <div style="font-size:12px; color:var(--danger); margin-top:4px;">{{ $message }}</div>
                @enderror
            </div>

            <div style="margin-bottom: 20px;">
                <label class="form-label" for="day_of_month">Día esperado de pago</label>
                <div style="display: flex; align-items: center; gap: 12px;">
                    <input type="number" name="day_of_month" id="day_of_month"
                           class="form-input"
                           style="width: 100px;"
                           placeholder="Ej: 10"
                           min="1" max="31"
                           value="{{ old('day_of_month', $pi?->day_of_month) }}">
                    <span style="font-size:13px; color:var(--muted);">de cada mes (opcional)</span>
                </div>
                <div style="font-size:11px; color:var(--muted); margin-top:5px;">
                    Se usa para mostrar avisos de vencimiento en el checklist.
                </div>
                @error('day_of_month')
                    <div style="font-size:12px; color:var(--danger); margin-top:4px;">{{ $message }}</div>
                @enderror
            </div>

        </div>

        {{-- Columna derecha --}}
        <div>

            <div style="margin-bottom: 20px;">
                <label class="form-label" for="account_id">Cuenta de débito *</label>
                <select name="account_id" id="account_id" class="form-select" required>
                    <option value="">Seleccioná una cuenta</option>
                    @foreach($accounts->groupBy('type') as $type => $group)
                        <optgroup label="{{ ['cash'=>'Efectivo','digital'=>'Digital','credit'=>'Crédito'][$type] ?? $type }}">
                            @foreach($group as $account)
                                <option value="{{ $account->id }}"
                                    {{ old('account_id', $pi?->account_id) == $account->id ? 'selected' : '' }}>
                                    {{ $account->name }} ({{ $account->currency }})
                                </option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
                @error('account_id')
                    <div style="font-size:12px; color:var(--danger); margin-top:4px;">{{ $message }}</div>
                @enderror
            </div>

            <div style="margin-bottom: 20px;">
                <label class="form-label" for="category_id">Categoría</label>
                <select name="category_id" id="category_id" class="form-select">
                    <option value="">Sin categoría</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}"
                            {{ old('category_id', $pi?->category_id) == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="margin-bottom: 20px;">
                <label class="form-label">Estado</label>
                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; padding: 10px 14px; background: var(--surface2); border: 1px solid var(--border); border-radius: 9px;">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" id="is_active" value="1"
                           style="accent-color: var(--accent); width: 16px; height: 16px;"
                           {{ old('is_active', $pi?->is_active ?? true) ? 'checked' : '' }}>
                    <div>
                        <div style="font-size:14px; font-weight:600; color:var(--text);">Ítem activo</div>
                        <div style="font-size:11px; color:var(--muted); margin-top:1px;">Aparece en el checklist mensual</div>
                    </div>
                </label>
            </div>

        </div>
    </div>

    <div style="display: flex; gap: 10px; justify-content: flex-end; padding-top: 20px; border-top: 1px solid var(--border);">
        <a href="{{ route('payment-items.index') }}" class="btn btn-ghost">Cancelar</a>
        <button type="submit" class="btn btn-primary">
            {{ $isEdit ? 'Guardar cambios' : 'Crear ítem' }}
        </button>
    </div>
</form>
