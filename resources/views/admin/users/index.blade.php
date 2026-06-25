@extends('layouts.admin')

@section('title', 'Usuarios')

@section('content')

<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 22px; gap: 16px; flex-wrap: wrap;">
    <div>
        <h1 class="font-display" style="font-size: 24px; font-weight: 700; letter-spacing: -0.02em;">Usuarios</h1>
        <div style="font-size: 12px; color: var(--muted); margin-top: 3px;">{{ $users->total() }} en total</div>
    </div>

    <form method="GET" action="{{ route('admin.users.index') }}" style="display: flex; gap: 8px; flex: 1; max-width: 360px;">
        <input type="search" name="q" value="{{ $search }}" placeholder="Buscar por nombre o email" class="form-input">
        @if($search)
            <a href="{{ route('admin.users.index') }}" class="btn btn-ghost">Limpiar</a>
        @endif
    </form>
</div>

<div class="card" style="padding: 0;">
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Usuario</th>
                    <th>Email</th>
                    <th>Grupos</th>
                    <th>Rol</th>
                    <th>Alta</th>
                    <th style="text-align: right;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $u)
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                @if($u->avatar)
                                    <img src="{{ $u->avatar }}" alt="" style="width: 28px; height: 28px; border-radius: 50%; object-fit: cover;">
                                @else
                                    <div style="width: 28px; height: 28px; border-radius: 50%; background: var(--surface2); display: flex; align-items: center; justify-content: center; font-size: 12px; color: var(--admin); font-weight: 700;">
                                        {{ strtoupper(substr($u->name, 0, 1)) }}
                                    </div>
                                @endif
                                <a href="{{ route('admin.users.show', $u) }}" style="color: var(--text); text-decoration: none; font-weight: 600;">{{ $u->name }}</a>
                                @if($u->id === auth()->id())
                                    <span style="font-size: 10px; color: var(--muted);">(vos)</span>
                                @endif
                            </div>
                        </td>
                        <td style="color: var(--muted); font-size: 13px;">{{ $u->email }}</td>
                        <td>{{ $u->family_groups_count }}</td>
                        <td>
                            @if($u->is_admin)
                                <span class="badge badge-admin">Admin</span>
                            @else
                                <span class="badge badge-member">Usuario</span>
                            @endif
                        </td>
                        <td style="color: var(--muted); font-size: 12px;">{{ $u->created_at?->format('d/m/Y') }}</td>
                        <td style="text-align: right; white-space: nowrap;">
                            <a href="{{ route('admin.users.show', $u) }}" class="btn btn-ghost" style="padding: 6px 12px; font-size: 12px;">Ver</a>
                            <a href="{{ route('admin.users.edit', $u) }}" class="btn btn-ghost" style="padding: 6px 12px; font-size: 12px;">Editar</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 40px; color: var(--muted);">
                            No se encontraron usuarios.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($users->hasPages())
    <div style="margin-top: 16px;">
        {{ $users->links() }}
    </div>
@endif

@endsection
