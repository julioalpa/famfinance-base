@extends('layouts.app')

@section('title', 'Editar cuenta')

@section('content')

<div style="max-width: 560px;">
    <div style="margin-bottom: 24px;">
        <a href="{{ route('accounts.show', $account) }}" style="font-size: 12px; color: var(--muted); text-decoration: none;">← {{ $account->name }}</a>
        <h1 class="font-display" style="font-size: 22px; font-weight: 700; margin-top: 8px;">Editar cuenta</h1>
    </div>

    <div class="card">
        <form method="POST" action="{{ route('accounts.update', $account) }}">
            @csrf @method('PUT')

            <div style="margin-bottom: 20px;">
                <label class="form-label">Nombre *</label>
                <input type="text" name="name" class="form-input"
                       value="{{ old('name', $account->name) }}" autofocus>
                @error('name') <div style="font-size:12px;color:var(--danger);margin-top:4px;">{{ $message }}</div> @enderror
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px;">
                <div>
                    <label class="form-label">Tipo</label>
                    <select name="type" id="account-type" class="form-select" onchange="toggleCreditFields()">
                        <option value="cash"    {{ old('type',$account->type) === 'cash'    ? 'selected' : '' }}>Efectivo</option>
                        <option value="digital" {{ old('type',$account->type) === 'digital' ? 'selected' : '' }}>Digital</option>
                        <option value="credit"  {{ old('type',$account->type) === 'credit'  ? 'selected' : '' }}>Tarjeta de crédito</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Moneda</label>
                    <select name="currency" class="form-select">
                        <option value="ARS" {{ old('currency',$account->currency) === 'ARS' ? 'selected' : '' }}>ARS</option>
                        <option value="USD" {{ old('currency',$account->currency) === 'USD' ? 'selected' : '' }}>USD</option>
                    </select>
                </div>
            </div>

            <div id="credit-fields" style="{{ old('type',$account->type) === 'credit' ? '' : 'display:none' }}">
                <div style="background: var(--surface2); border-radius: 8px; padding: 16px; margin-bottom: 20px;">
                    <div style="font-size: 11px; letter-spacing: 0.1em; text-transform: uppercase; color: var(--muted); margin-bottom: 14px;">Configuración de tarjeta</div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px;">
                        <div>
                            <label class="form-label">Día de cierre</label>
                            <input type="number" name="closing_day" class="form-input" min="1" max="31" value="{{ old('closing_day',$account->closing_day) }}">
                        </div>
                        <div>
                            <label class="form-label">Día de vencimiento</label>
                            <input type="number" name="due_day" class="form-input" min="1" max="31" value="{{ old('due_day',$account->due_day) }}">
                        </div>
                        <div>
                            <label class="form-label">Límite</label>
                            <input type="number" name="credit_limit" class="form-input" min="0" step="0.01" value="{{ old('credit_limit',$account->credit_limit) }}">
                        </div>
                    </div>
                </div>
            </div>

            <div style="margin-bottom: 24px;">
                <label class="form-label">Notas</label>
                <input type="text" name="notes" class="form-input" value="{{ old('notes', $account->notes) }}">
            </div>

            <div style="display: flex; gap: 10px; justify-content: space-between; align-items: center;">
                <form method="POST" action="{{ route('accounts.destroy', $account) }}"
                      onsubmit="return confirm('¿Eliminar esta cuenta? Las transacciones históricas se conservan.')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger" style="font-size: 12px;">Eliminar cuenta</button>
                </form>

                <div style="display: flex; gap: 10px;">
                    <a href="{{ route('accounts.show', $account) }}" class="btn btn-ghost">Cancelar</a>
                    <button type="submit" form="" class="btn btn-primary"
                            onclick="this.form.submit()">Guardar cambios</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function toggleCreditFields() {
    const type = document.getElementById('account-type').value;
    document.getElementById('credit-fields').style.display = type === 'credit' ? '' : 'none';
}
</script>

@endsection
