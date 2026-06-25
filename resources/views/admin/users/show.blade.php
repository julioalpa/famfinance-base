@extends('layouts.admin')

@section('title', $user->name)

@section('content')

<div style="margin-bottom: 22px;">
    <a href="{{ route('admin.users.index') }}" style="font-size: 12px; color: var(--muted); text-decoration: none;">← Volver a usuarios</a>
</div>

<div class="card" style="margin-bottom: 18px;">
    <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 18px;">
        @if($user->avatar)
            <img src="{{ $user->avatar }}" alt="" style="width: 64px; height: 64px; border-radius: 50%; object-fit: cover; border: 1px solid var(--border);">
        @else
            <div style="width: 64px; height: 64px; border-radius: 50%; background: var(--surface2); display: flex; align-items: center; justify-content: center; font-size: 24px; color: var(--admin); font-weight: 700; font-family: 'Bricolage Grotesque', sans-serif;">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
        @endif
        <div style="flex: 1; min-width: 0;">
            <h1 class="font-display" style="font-size: 22px; font-weight: 700; letter-spacing: -0.02em;">
                {{ $user->name }}
                @if($user->is_admin)
                    <span class="badge badge-admin" style="vertical-align: middle; margin-left: 6px;">Admin</span>
                @endif
            </h1>
            <div style="font-size: 13px; color: var(--muted); margin-top: 3px;">{{ $user->email }}</div>
        </div>
        <div style="display: flex; gap: 8px;">
            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary">Editar</a>
            @if($user->id !== auth()->id())
            <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                  onsubmit="return confirm('¿Eliminar a {{ $user->name }}? Se borrarán los grupos que sea owner, junto con todos sus datos. Esta acción no se puede deshacer.')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger">Eliminar</button>
            </form>
            @endif
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px;">
        <div>
            <div style="font-size: 11px; letter-spacing: 0.08em; text-transform: uppercase; color: var(--muted); font-weight: 700;">ID</div>
            <div style="margin-top: 4px; font-family: 'Bricolage Grotesque', sans-serif; font-weight: 600;">#{{ $user->id }}</div>
        </div>
        <div>
            <div style="font-size: 11px; letter-spacing: 0.08em; text-transform: uppercase; color: var(--muted); font-weight: 700;">Alta</div>
            <div style="margin-top: 4px;">{{ $user->created_at?->format('d/m/Y H:i') }}</div>
        </div>
        <div>
            <div style="font-size: 11px; letter-spacing: 0.08em; text-transform: uppercase; color: var(--muted); font-weight: 700;">Autenticación</div>
            <div style="margin-top: 4px;">{{ $user->google_id ? 'Google' : 'Email + contraseña' }}</div>
        </div>
    </div>
</div>

<div class="card">
    <h2 class="font-display" style="font-size: 15px; font-weight: 700; margin-bottom: 14px;">
        Grupos familiares ({{ $user->familyGroups->count() }})
    </h2>

    @forelse($user->familyGroups as $g)
        <div style="display: flex; align-items: center; justify-content: space-between; padding: 11px 0; border-bottom: 1px solid var(--border);">
            <div>
                <a href="{{ route('admin.family-groups.show', $g) }}" style="font-size: 14px; color: var(--text); text-decoration: none; font-weight: 600;">{{ $g->name }}</a>
                <div style="font-size: 11px; color: var(--muted); margin-top: 2px;">
                    Owner: {{ $g->owner?->name ?? '—' }}
                </div>
            </div>
            <span class="badge {{ $g->pivot->role === 'owner' ? 'badge-owner' : 'badge-member' }}">
                {{ $g->pivot->role === 'owner' ? 'Owner' : 'Miembro' }}
            </span>
        </div>
    @empty
        <div style="font-size: 13px; color: var(--muted); padding: 10px 0;">No pertenece a ningún grupo.</div>
    @endforelse
</div>

@endsection
