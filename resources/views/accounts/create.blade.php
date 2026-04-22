@extends('layouts.app')

@section('title', 'Nueva cuenta')

@section('content')

<div style="max-width: 560px;">
    <div style="margin-bottom: 24px;">
        <a href="{{ route('accounts.index') }}" style="font-size: 12px; color: var(--muted); text-decoration: none;">← Cuentas</a>
        <h1 class="font-display" style="font-size: 22px; font-weight: 700; margin-top: 8px;">Nueva cuenta</h1>
    </div>

    <div class="card">
        <form method="POST" action="{{ route('accounts.store') }}">
            @csrf

            <div style="margin-bottom: 20px;">
                <label class="form-label">Nombre de la cuenta *</label>
                <input type="text" name="name" class="form-input"
                       placeholder="Ej: Galicia, Mercado Pago, Visa Naranja"
                       value="{{ old('name') }}" autofocus>
                @error('name') <div style="font-size:12px;color:var(--danger);margin-top:4px;">{{ $message }}</div> @enderror
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px;">
                <div>
                    <label class="form-label">Tipo *</label>
                    <select name="type" id="account-type" class="form-select" onchange="toggleTypeFields()">
                        <option value="cash"    {{ old('type') === 'cash'    ? 'selected' : '' }}>Efectivo</option>
                        <option value="digital" {{ old('type') === 'digital' ? 'selected' : '' }}>Digital</option>
                        <option value="credit"  {{ old('type') === 'credit'  ? 'selected' : '' }}>Tarjeta de crédito</option>
                        <option value="loan"    {{ old('type') === 'loan'    ? 'selected' : '' }}>Préstamo</option>
                    </select>
                    @error('type') <div style="font-size:12px;color:var(--danger);margin-top:4px;">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="form-label">Moneda *</label>
                    <select name="currency" class="form-select">
                        <option value="ARS" {{ old('currency','ARS') === 'ARS' ? 'selected' : '' }}>ARS — Pesos</option>
                        <option value="USD" {{ old('currency') === 'USD' ? 'selected' : '' }}>USD — Dólares</option>
                    </select>
                </div>
            </div>

            {{-- Campos exclusivos de préstamo --}}
            <div id="loan-fields" style="{{ old('type') === 'loan' ? '' : 'display:none' }}">
                <div style="background: var(--surface2); border-radius: 8px; padding: 16px; margin-bottom: 20px;">
                    <div style="font-size: 11px; letter-spacing: 0.1em; text-transform: uppercase; color: var(--muted); margin-bottom: 14px;">Configuración del préstamo</div>
                    <div>
                        <label class="form-label">Monto total de la deuda *</label>
                        <input type="number" name="initial_balance" class="form-input"
                               placeholder="Ej: 500000" min="0.01" step="0.01"
                               value="{{ old('initial_balance') }}">
                        <div style="font-size: 11px; color: var(--muted); margin-top: 4px;">El saldo inicial que pediste y debés devolver. Cada pago lo irá reduciendo.</div>
                        @error('initial_balance') <div style="font-size:11px;color:var(--danger);margin-top:3px;">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            {{-- Campos exclusivos de crédito --}}
            <div id="credit-fields" style="{{ old('type') === 'credit' ? '' : 'display:none' }}">
                <div style="background: var(--surface2); border-radius: 8px; padding: 16px; margin-bottom: 20px;">
                    <div style="font-size: 11px; letter-spacing: 0.1em; text-transform: uppercase; color: var(--muted); margin-bottom: 14px;">Configuración de la tarjeta</div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px;">
                        <div>
                            <label class="form-label">Día de cierre</label>
                            <input type="number" name="closing_day" class="form-input"
                                   placeholder="Ej: 13" min="1" max="31"
                                   value="{{ old('closing_day') }}">
                            @error('closing_day') <div style="font-size:11px;color:var(--danger);margin-top:3px;">{{ $message }}</div> @enderror
                        </div>
                        <div>
                            <label class="form-label">Día de vencimiento</label>
                            <input type="number" name="due_day" class="form-input"
                                   placeholder="Ej: 3" min="1" max="31"
                                   value="{{ old('due_day') }}">
                            @error('due_day') <div style="font-size:11px;color:var(--danger);margin-top:3px;">{{ $message }}</div> @enderror
                        </div>
                        <div>
                            <label class="form-label">Límite de crédito</label>
                            <input type="number" name="credit_limit" class="form-input"
                                   placeholder="Opcional" min="0" step="0.01"
                                   value="{{ old('credit_limit') }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Selector de marca/logo --}}
            <div id="brand-picker" style="margin-bottom: 20px; display: none;">
                <label class="form-label">Logo / Banco</label>
                <div id="brand-options" style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
                </div>
                <div style="font-size: 11px; color: var(--muted); margin-top: 5px;">Opcional. Se mostrará en la tarjeta de la cuenta.</div>
            </div>

            <div style="margin-bottom: 24px;">
                <label class="form-label">Notas</label>
                <input type="text" name="notes" class="form-input"
                       placeholder="Opcional"
                       value="{{ old('notes') }}">
            </div>

            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <a href="{{ route('accounts.index') }}" class="btn btn-ghost">Cancelar</a>
                <button type="submit" class="btn btn-primary">Crear cuenta</button>
            </div>
        </form>
    </div>
</div>

@include('accounts._brand_picker_script', ['currentBrand' => old('brand')])

<script>
function toggleTypeFields() {
    const type = document.getElementById('account-type').value;
    document.getElementById('credit-fields').style.display = type === 'credit' ? '' : 'none';
    document.getElementById('loan-fields').style.display   = type === 'loan'   ? '' : 'none';
    updateBrandPicker(type);
}
toggleTypeFields();
</script>

@endsection
