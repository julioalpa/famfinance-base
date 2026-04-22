@extends('layouts.app')

@section('title', 'Nueva categoría')

@section('content')

<div style="max-width: 480px;">
    <div style="margin-bottom: 24px;">
        <a href="{{ route('categories.index') }}" style="font-size: 12px; color: var(--muted); text-decoration: none;">← Categorías</a>
        <h1 class="font-display" style="font-size: 22px; font-weight: 700; margin-top: 8px;">Nueva categoría</h1>
    </div>

    <div class="card">
        <form method="POST" action="{{ route('categories.store') }}">
            @csrf

            <div style="margin-bottom: 20px;">
                <label class="form-label">Nombre *</label>
                <input type="text" name="name" class="form-input"
                       placeholder="Ej: Supermercado, Sueldo, Salud"
                       value="{{ old('name') }}" autofocus>
                @error('name') <div style="font-size:12px;color:var(--danger);margin-top:4px;">{{ $message }}</div> @enderror
            </div>

            <div style="margin-bottom: 20px;">
                <label class="form-label">Tipo *</label>
                <select name="type" class="form-select">
                    <option value="expense" {{ old('type') === 'expense' ? 'selected' : '' }}>Gasto</option>
                    <option value="income"  {{ old('type') === 'income'  ? 'selected' : '' }}>Ingreso</option>
                    <option value="both"    {{ old('type', 'both') === 'both' ? 'selected' : '' }}>Ambos</option>
                </select>
                @error('type') <div style="font-size:12px;color:var(--danger);margin-top:4px;">{{ $message }}</div> @enderror
            </div>

            <div style="margin-bottom: 20px;">
                <label class="form-label">Color <span style="color:var(--muted); font-weight:400;">(opcional)</span></label>
                <div style="display: flex; gap: 8px; align-items: center;">
                    <input type="color" id="color-picker"
                           value="{{ old('color', '#6366f1') }}"
                           style="width: 40px; height: 36px; border: 1px solid var(--border); border-radius: 6px; padding: 2px; background: var(--surface); cursor: pointer;">
                    <input type="text" name="color" id="color-text" class="form-input"
                           placeholder="#6366f1"
                           value="{{ old('color', '#6366f1') }}"
                           style="flex: 1;">
                </div>
            </div>

            @include('categories._icon_picker', ['currentIcon' => old('icon')])

            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <a href="{{ route('categories.index') }}" class="btn btn-ghost">Cancelar</a>
                <button type="submit" class="btn btn-primary">Crear categoría</button>
            </div>
        </form>
    </div>
</div>

<script>
const picker = document.getElementById('color-picker');
const text   = document.getElementById('color-text');
picker.addEventListener('input', () => { text.value = picker.value; text.dispatchEvent(new Event('input')); });
text.addEventListener('input',   () => { if (/^#[0-9a-fA-F]{6}$/.test(text.value)) picker.value = text.value; });
</script>

@endsection
