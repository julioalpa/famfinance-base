@extends('layouts.app')

@section('title', 'Contraseña')

@section('content')
<div style="max-width: 480px; margin: 0 auto; padding: 32px 16px;">

    <div style="margin-bottom: 28px;">
        <h1 style="font-size: 22px; font-weight: 700; color: var(--text); margin-bottom: 4px;">
            {{ auth()->user()->password ? 'Cambiar contraseña' : 'Establecer contraseña' }}
        </h1>
        <p style="font-size: 13px; color: var(--muted);">
            {{ auth()->user()->password
                ? 'Ingresá tu contraseña actual y la nueva.'
                : 'Establecé una contraseña para poder ingresar sin Google.' }}
        </p>
    </div>

    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom: 20px;">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('profile.password.update') }}" style="display: flex; flex-direction: column; gap: 16px;">
        @csrf

        @if(auth()->user()->password)
            <div>
                <label class="form-label">Contraseña actual</label>
                <input type="password" name="current_password" class="form-input {{ $errors->has('current_password') ? 'border-red-500' : '' }}" autocomplete="current-password">
                @error('current_password')
                    <p style="color: var(--danger); font-size: 12px; margin-top: 4px;">{{ $message }}</p>
                @enderror
            </div>
        @endif

        <div>
            <label class="form-label">Nueva contraseña <span style="color: var(--muted); font-size: 11px;">(mínimo 8 caracteres)</span></label>
            <input type="password" name="password" class="form-input {{ $errors->has('password') ? 'border-red-500' : '' }}" autocomplete="new-password">
            @error('password')
                <p style="color: var(--danger); font-size: 12px; margin-top: 4px;">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="form-label">Confirmar contraseña</label>
            <input type="password" name="password_confirmation" class="form-input" autocomplete="new-password">
        </div>

        <div style="display: flex; gap: 10px; margin-top: 4px;">
            <button type="submit" class="btn btn-primary">Guardar contraseña</button>
            <a href="{{ url()->previous() }}" class="btn btn-ghost">Cancelar</a>
        </div>
    </form>
</div>
@endsection
