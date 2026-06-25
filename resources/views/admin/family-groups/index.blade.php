@extends('layouts.admin')

@section('title', 'Grupos familiares')

@section('content')

<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 22px; gap: 16px; flex-wrap: wrap;">
    <div>
        <h1 class="font-display" style="font-size: 24px; font-weight: 700; letter-spacing: -0.02em;">Grupos familiares</h1>
        <div style="font-size: 12px; color: var(--muted); margin-top: 3px;">{{ $groups->total() }} en total</div>
    </div>

    <form method="GET" action="{{ route('admin.family-groups.index') }}" style="display: flex; gap: 8px; flex: 1; max-width: 360px;">
        <input type="search" name="q" value="{{ $search }}" placeholder="Buscar por nombre" class="form-input">
        @if($search)
            <a href="{{ route('admin.family-groups.index') }}" class="btn btn-ghost">Limpiar</a>
        @endif
    </form>
</div>

<div class="card" style="padding: 0;">
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Grupo</th>
                    <th>Owner</th>
                    <th>Miembros</th>
                    <th>Cuentas</th>
                    <th>Movimientos</th>
                    <th>Alta</th>
                    <th style="text-align: right;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($groups as $g)
                    <tr>
                        <td>
                            <a href="{{ route('admin.family-groups.show', $g) }}" style="color: var(--text); text-decoration: none; font-weight: 600;">{{ $g->name }}</a>
                        </td>
                        <td style="color: var(--muted); font-size: 13px;">{{ $g->owner?->name ?? '—' }}</td>
                        <td>{{ $g->members_count }}</td>
                        <td>{{ $g->accounts_count }}</td>
                        <td>{{ $g->transactions_count }}</td>
                        <td style="color: var(--muted); font-size: 12px;">{{ $g->created_at?->format('d/m/Y') }}</td>
                        <td style="text-align: right; white-space: nowrap;">
                            <a href="{{ route('admin.family-groups.show', $g) }}" class="btn btn-ghost" style="padding: 6px 12px; font-size: 12px;">Ver</a>
                            <a href="{{ route('admin.family-groups.edit', $g) }}" class="btn btn-ghost" style="padding: 6px 12px; font-size: 12px;">Editar</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 40px; color: var(--muted);">
                            No se encontraron grupos.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($groups->hasPages())
    <div style="margin-top: 16px;">
        {{ $groups->links() }}
    </div>
@endif

@endsection
