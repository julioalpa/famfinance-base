@extends('layouts.admin')

@section('title', 'Editar usuario')

@section('content')

<div style="margin-bottom: 22px;">
    <a href="{{ route('admin.users.show', $user) }}" style="font-size: 12px; color: var(--muted); text-decoration: none;">← Volver al usuario</a>
</div>

<div style="max-width: 540px;">
    <h1 class="font-display" style="font-size: 22px; font-weight: 700; letter-spacing: -0.02em; margin-bottom: 18px;">
        Editar usuario
    </h1>

    <form method="POST" action="{{ route('admin.users.update', $user) }}" class="card">
        @csrf @method('PUT')

        <div style="margin-bottom: 16px;">
            <label class="form-label" for="name">Nombre</label>
            <input id="name" type="text" name="name" class="form-input"
                   value="{{ old('name', $user->name) }}" required>
            @error('name')<div style="font-size:12px;color:var(--danger);margin-top:6px;">{{ $message }}</div>@enderror
        </div>

        <div style="margin-bottom: 16px;">
            <label class="form-label" for="email">Email</label>
            <input id="email" type="email" name="email" class="form-input"
                   value="{{ old('email', $user->email) }}" required>
            @error('email')<div style="font-size:12px;color:var(--danger);margin-top:6px;">{{ $message }}</div>@enderror
        </div>

        <div style="margin-bottom: 22px;">
            <label style="display: flex; align-items: flex-start; gap: 10px; cursor: pointer;">
                <input type="checkbox" name="is_admin" value="1"
                       {{ old('is_admin', $user->is_admin) ? 'checked' : '' }}
                       {{ $user->id === auth()->id() ? 'disabled' : '' }}
                       style="margin-top: 3px; accent-color: var(--admin);">
                <span>
                    <div style="font-weight: 700; font-size: 14px;">Administrador del sistema</div>
                    <div style="font-size: 12px; color: var(--muted); margin-top: 2px;">
                        Acceso al panel de administración y a todos los usuarios y grupos.
                        @if($user->id === auth()->id())
                            <br><span style="color: var(--warn);">No podés cambiar tu propio rol.</span>
                            <input type="hidden" name="is_admin" value="1">
                        @endif
                    </div>
                </span>
            </label>
            @error('is_admin')<div style="font-size:12px;color:var(--danger);margin-top:6px;">{{ $message }}</div>@enderror
        </div>

        <div style="display: flex; gap: 8px; justify-content: flex-end;">
            <a href="{{ route('admin.users.show', $user) }}" class="btn btn-ghost">Cancelar</a>
            <button type="submit" class="btn btn-primary">Guardar cambios</button>
        </div>
    </form>
</div>

@endsection
