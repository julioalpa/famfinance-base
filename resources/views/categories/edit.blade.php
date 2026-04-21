@extends('layouts.app')

@section('title', 'Editar categoría')

@section('content')

<div style="max-width: 480px;">
    <div style="margin-bottom: 24px;">
        <a href="{{ route('categories.index') }}" style="font-size: 12px; color: var(--muted); text-decoration: none;">← Categorías</a>
        <h1 class="font-display" style="font-size: 22px; font-weight: 700; margin-top: 8px;">Editar categoría</h1>
    </div>

    <div class="card">
        <form method="POST" action="{{ route('categories.update', $category) }}">
            @csrf @method('PUT')

            <div style="margin-bottom: 20px;">
                <label class="form-label">Nombre *</label>
                <input type="text" name="name" class="form-input"
                       value="{{ old('name', $category->name) }}" autofocus>
                @error('name') <div style="font-size:12px;color:var(--danger);margin-top:4px;">{{ $message }}</div> @enderror
            </div>

            <div style="margin-bottom: 20px;">
                <label class="form-label">Tipo *</label>
                <select name="type" class="form-select">
                    <option value="expense" {{ old('type', $category->type) === 'expense' ? 'selected' : '' }}>Gasto</option>
                    <option value="income"  {{ old('type', $category->type) === 'income'  ? 'selected' : '' }}>Ingreso</option>
                    <option value="both"    {{ old('type', $category->type) === 'both'    ? 'selected' : '' }}>Ambos</option>
                </select>
                @error('type') <div style="font-size:12px;color:var(--danger);margin-top:4px;">{{ $message }}</div> @enderror
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px;">
                <div>
                    <label class="form-label">Ícono <span style="color:var(--muted)">(opcional)</span></label>
                    <input type="text" name="icon" class="form-input"
                           placeholder="Ej: home, cart, heart"
                           value="{{ old('icon', $category->icon) }}">
                </div>
                <div>
                    <label class="form-label">Color <span style="color:var(--muted)">(opcional)</span></label>
                    <div style="display: flex; gap: 8px; align-items: center;">
                        <input type="color" id="color-picker"
                               value="{{ old('color', $category->color ?? '#6366f1') }}"
                               style="width: 40px; height: 36px; border: 1px solid var(--border); border-radius: 6px; padding: 2px; background: var(--surface); cursor: pointer;">
                        <input type="text" name="color" id="color-text" class="form-input"
                               placeholder="#6366f1"
                               value="{{ old('color', $category->color) }}"
                               style="flex: 1;">
                    </div>
                </div>
            </div>

            <div style="display: flex; gap: 10px; justify-content: flex-end; align-items: center;">
                <a href="{{ route('categories.index') }}" class="btn btn-ghost">Cancelar</a>
                <button type="submit" class="btn btn-primary">Guardar cambios</button>
            </div>
        </form>

        <form method="POST" action="{{ route('categories.destroy', $category) }}"
              style="margin-top: 16px;"
              onsubmit="return confirm('¿Eliminar esta categoría?')">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-danger" style="font-size: 12px;">Eliminar</button>
        </form>
    </div>
</div>

<script>
const picker = document.getElementById('color-picker');
const text   = document.getElementById('color-text');
picker.addEventListener('input', () => text.value = picker.value);
text.addEventListener('input',   () => { if (/^#[0-9a-fA-F]{6}$/.test(text.value)) picker.value = text.value; });
</script>

@endsection
