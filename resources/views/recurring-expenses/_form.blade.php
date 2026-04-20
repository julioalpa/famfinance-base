@php
    $re     = $recurringExpense ?? null;
    $isEdit = $re !== null;
@endphp

<form method="POST" action="{{ $action }}">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">

        {{-- Columna izquierda --}}
        <div>

            {{-- Descripción --}}
            <div style="margin-bottom: 20px;">
                <label class="form-label" for="description">Nombre del débito *</label>
                <input type="text" name="description" id="description"
                       class="form-input"
                       placeholder="Ej: Netflix, Gym, Seguro del auto"
                       value="{{ old('description', $re?->description) }}"
                       maxlength="255" required>
                @error('description')
                    <div style="font-size:12px; color:var(--danger); margin-top:4px;">{{ $message }}</div>
                @enderror
            </div>

            {{-- Monto + Moneda --}}
            <div style="margin-bottom: 20px;">
                <label class="form-label">Monto mensual *</label>
                <div style="display: flex; gap: 8px;">
                    <select name="currency" class="form-select" style="width: 90px;">
                        <option value="ARS" {{ old('currency', $re?->currency ?? 'ARS') === 'ARS' ? 'selected' : '' }}>ARS $</option>
                        <option value="USD" {{ old('currency', $re?->currency) === 'USD' ? 'selected' : '' }}>USD US$</option>
                    </select>
                    <input type="number" name="amount" id="amount"
                           class="form-input"
                           placeholder="0,00"
                           step="0.01" min="0.01"
                           value="{{ old('amount', $re?->amount) }}"
                           required>
                </div>
                @error('amount')
                    <div style="font-size:12px; color:var(--danger); margin-top:4px;">{{ $message }}</div>
                @enderror
            </div>

            {{-- Día del mes --}}
            <div style="margin-bottom: 20px;">
                <label class="form-label" for="day_of_month">Día del mes de débito *</label>
                <div style="display: flex; align-items: center; gap: 12px;">
                    <input type="number" name="day_of_month" id="day_of_month"
                           class="form-input"
                           style="width: 100px;"
                           placeholder="Ej: 15"
                           min="1" max="31"
                           value="{{ old('day_of_month', $re?->day_of_month) }}"
                           required>
                    <span style="font-size:13px; color:var(--muted);">de cada mes</span>
                </div>
                <div style="font-size:11px; color:var(--muted); margin-top:5px;">
                    Si el mes tiene menos días, se ejecuta el último día disponible.
                </div>
                @error('day_of_month')
                    <div style="font-size:12px; color:var(--danger); margin-top:4px;">{{ $message }}</div>
                @enderror
            </div>

        </div>

        {{-- Columna derecha --}}
        <div>

            {{-- Cuenta --}}
            <div style="margin-bottom: 20px;">
                <label class="form-label" for="account_id">Cuenta *</label>
                <select name="account_id" id="account_id" class="form-select" required>
                    <option value="">Seleccioná una cuenta</option>
                    @foreach($accounts->groupBy('type') as $type => $group)
                        <optgroup label="{{ ['cash'=>'Efectivo','digital'=>'Digital','credit'=>'Crédito'][$type] ?? $type }}">
                            @foreach($group as $account)
                                <option value="{{ $account->id }}"
                                    {{ old('account_id', $re?->account_id) == $account->id ? 'selected' : '' }}>
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

            {{-- Categoría --}}
            <div style="margin-bottom: 20px;">
                <label class="form-label" for="category_id">Categoría</label>
                <select name="category_id" id="category_id" class="form-select">
                    <option value="">Sin categoría</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}"
                            {{ old('category_id', $re?->category_id) == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Activo --}}
            <div style="margin-bottom: 20px;">
                <label class="form-label">Estado</label>
                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; padding: 10px 14px; background: var(--surface2); border: 1px solid var(--border); border-radius: 9px;">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" id="is_active" value="1"
                           style="accent-color: var(--accent); width: 16px; height: 16px;"
                           {{ old('is_active', $re?->is_active ?? true) ? 'checked' : '' }}>
                    <div>
                        <div style="font-size:14px; font-weight:600; color:var(--text);">Débito activo</div>
                        <div style="font-size:11px; color:var(--muted); margin-top:1px;">Aparece en el dashboard y en el resumen mensual</div>
                    </div>
                </label>
            </div>

            {{-- Notas --}}
            <div style="margin-bottom: 20px;">
                <label class="form-label" for="notes">Notas</label>
                <textarea name="notes" id="notes"
                          class="form-input"
                          rows="3"
                          placeholder="Opcional..."
                          style="resize: vertical;">{{ old('notes', $re?->notes) }}</textarea>
            </div>

        </div>
    </div>

    {{-- Botones --}}
    <div style="display: flex; gap: 10px; justify-content: flex-end; padding-top: 20px; border-top: 1px solid var(--border);">
        <a href="{{ route('recurring-expenses.index') }}" class="btn btn-ghost">Cancelar</a>
        <button type="submit" class="btn btn-primary">
            {{ $isEdit ? 'Guardar cambios' : 'Crear débito fijo' }}
        </button>
    </div>
</form>
