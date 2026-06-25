@extends('layouts.admin')

@section('title', 'Inicio')

@section('content')

<div style="margin-bottom: 28px;">
    <div class="admin-banner">
        <svg width="10" height="10" fill="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="6"/></svg>
        Admin
    </div>
    <h1 class="font-display" style="font-size: 26px; font-weight: 700; letter-spacing: -0.02em;">Panel de administración</h1>
    <div style="font-size: 13px; color: var(--muted); margin-top: 4px;">Resumen general del sistema</div>
</div>

<div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 28px;">
    <div class="stat-card admin">
        <div style="font-size: 11px; letter-spacing: 0.08em; text-transform: uppercase; color: var(--muted); font-weight: 700;">Usuarios</div>
        <div class="font-display" style="font-size: 32px; font-weight: 700; margin-top: 6px;">{{ $stats['users'] }}</div>
    </div>
    <div class="stat-card accent">
        <div style="font-size: 11px; letter-spacing: 0.08em; text-transform: uppercase; color: var(--muted); font-weight: 700;">Administradores</div>
        <div class="font-display" style="font-size: 32px; font-weight: 700; margin-top: 6px;">{{ $stats['admins'] }}</div>
    </div>
    <div class="stat-card info">
        <div style="font-size: 11px; letter-spacing: 0.08em; text-transform: uppercase; color: var(--muted); font-weight: 700;">Grupos familiares</div>
        <div class="font-display" style="font-size: 32px; font-weight: 700; margin-top: 6px;">{{ $stats['family_groups'] }}</div>
    </div>
    <div class="stat-card warn">
        <div style="font-size: 11px; letter-spacing: 0.08em; text-transform: uppercase; color: var(--muted); font-weight: 700;">Altas de hoy</div>
        <div class="font-display" style="font-size: 32px; font-weight: 700; margin-top: 6px;">{{ $stats['users_today'] }}</div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
    <div class="card">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
            <h2 class="font-display" style="font-size: 15px; font-weight: 700;">Últimos usuarios</h2>
            <a href="{{ route('admin.users.index') }}" style="font-size: 12px; color: var(--admin); text-decoration: none;">Ver todos →</a>
        </div>
        @forelse($latestUsers as $u)
            <div style="display: flex; align-items: center; gap: 12px; padding: 9px 0; border-bottom: 1px solid var(--border);">
                <div style="width: 32px; height: 32px; border-radius: 50%; background: var(--surface2); display: flex; align-items: center; justify-content: center; font-size: 13px; color: var(--admin); font-weight: 700;">
                    {{ strtoupper(substr($u->name, 0, 1)) }}
                </div>
                <div style="flex: 1; min-width: 0;">
                    <a href="{{ route('admin.users.show', $u) }}" style="font-size: 13px; color: var(--text); text-decoration: none; font-weight: 600;">{{ $u->name }}</a>
                    <div style="font-size: 11px; color: var(--muted);">{{ $u->email }}</div>
                </div>
                @if($u->is_admin)
                    <span class="badge badge-admin">Admin</span>
                @endif
            </div>
        @empty
            <div style="font-size: 13px; color: var(--muted); padding: 16px 0;">Sin usuarios todavía.</div>
        @endforelse
    </div>

    <div class="card">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
            <h2 class="font-display" style="font-size: 15px; font-weight: 700;">Últimos grupos</h2>
            <a href="{{ route('admin.family-groups.index') }}" style="font-size: 12px; color: var(--admin); text-decoration: none;">Ver todos →</a>
        </div>
        @forelse($latestGroups as $g)
            <div style="display: flex; align-items: center; gap: 12px; padding: 9px 0; border-bottom: 1px solid var(--border);">
                <div style="width: 32px; height: 32px; border-radius: 9px; background: var(--surface2); display: flex; align-items: center; justify-content: center; color: var(--accent2);">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                </div>
                <div style="flex: 1; min-width: 0;">
                    <a href="{{ route('admin.family-groups.show', $g) }}" style="font-size: 13px; color: var(--text); text-decoration: none; font-weight: 600;">{{ $g->name }}</a>
                    <div style="font-size: 11px; color: var(--muted);">Owner: {{ $g->owner?->name ?? '—' }}</div>
                </div>
            </div>
        @empty
            <div style="font-size: 13px; color: var(--muted); padding: 16px 0;">Sin grupos todavía.</div>
        @endforelse
    </div>
</div>

@endsection
