@extends('layouts.admin')

@section('title', 'Editar grupo')

@section('content')

<div style="margin-bottom: 22px;">
    <a href="{{ route('admin.family-groups.show', $familyGroup) }}" style="font-size: 12px; color: var(--muted); text-decoration: none;">← Volver al grupo</a>
</div>

<div style="max-width: 540px;">
    <h1 class="font-display" style="font-size: 22px; font-weight: 700; letter-spacing: -0.02em; margin-bottom: 18px;">
        Editar grupo familiar
    </h1>

    <form method="POST" action="{{ route('admin.family-groups.update', $familyGroup) }}" class="card">
        @csrf @method('PUT')

        <div style="margin-bottom: 16px;">
            <label class="form-label" for="name">Nombre del grupo</label>
            <input id="name" type="text" name="name" class="form-input"
                   value="{{ old('name', $familyGroup->name) }}" required>
            @error('name')<div style="font-size:12px;color:var(--danger);margin-top:6px;">{{ $message }}</div>@enderror
        </div>

        <div style="margin-bottom: 22px;">
            <label class="form-label" for="owner_id">Owner (debe ser miembro)</label>
            <select id="owner_id" name="owner_id" class="form-select" required>
                @foreach($familyGroup->members as $m)
                    <option value="{{ $m->id }}" {{ old('owner_id', $familyGroup->owner_id) == $m->id ? 'selected' : '' }}>
                        {{ $m->name }} — {{ $m->email }}
                    </option>
                @endforeach
            </select>
            @error('owner_id')<div style="font-size:12px;color:var(--danger);margin-top:6px;">{{ $message }}</div>@enderror
            <div style="font-size: 11px; color: var(--muted); margin-top: 8px;">
                Si querés transferir a alguien que aún no es miembro, agregalo primero desde la vista del grupo.
            </div>
        </div>

        <div style="display: flex; gap: 8px; justify-content: flex-end;">
            <a href="{{ route('admin.family-groups.show', $familyGroup) }}" class="btn btn-ghost">Cancelar</a>
            <button type="submit" class="btn btn-primary">Guardar cambios</button>
        </div>
    </form>
</div>

@endsection
