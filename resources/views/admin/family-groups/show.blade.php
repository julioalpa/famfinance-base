@extends('layouts.admin')

@section('title', $familyGroup->name)

@section('content')

<div style="margin-bottom: 22px;">
    <a href="{{ route('admin.family-groups.index') }}" style="font-size: 12px; color: var(--muted); text-decoration: none;">← Volver a grupos</a>
</div>

<div class="card" style="margin-bottom: 18px;">
    <div style="display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap;">
        <div>
            <h1 class="font-display" style="font-size: 22px; font-weight: 700; letter-spacing: -0.02em;">{{ $familyGroup->name }}</h1>
            <div style="font-size: 13px; color: var(--muted); margin-top: 4px;">
                Owner:
                <a href="{{ route('admin.users.show', $familyGroup->owner) }}" style="color: var(--accent2); text-decoration: none;">
                    {{ $familyGroup->owner?->name ?? '—' }}
                </a>
                · {{ $familyGroup->members->count() }} miembro{{ $familyGroup->members->count() === 1 ? '' : 's' }}
                · creado {{ $familyGroup->created_at?->format('d/m/Y') }}
            </div>
        </div>
        <div style="display: flex; gap: 8px;">
            <a href="{{ route('admin.family-groups.edit', $familyGroup) }}" class="btn btn-primary">Editar</a>
            <form method="POST" action="{{ route('admin.family-groups.destroy', $familyGroup) }}"
                  onsubmit="return confirm('¿Eliminar el grupo &quot;{{ $familyGroup->name }}&quot;? Se borrarán todas las cuentas, movimientos, categorías y datos asociados. Esta acción no se puede deshacer.')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger">Eliminar grupo</button>
            </form>
        </div>
    </div>
</div>

<div class="card" style="margin-bottom: 18px;">
    <h2 class="font-display" style="font-size: 15px; font-weight: 700; margin-bottom: 14px;">
        Miembros ({{ $familyGroup->members->count() }})
    </h2>

    @foreach($familyGroup->members as $m)
        <div style="display: flex; align-items: center; justify-content: space-between; padding: 11px 0; border-bottom: 1px solid var(--border);">
            <div style="display: flex; align-items: center; gap: 10px; min-width: 0;">
                @if($m->avatar)
                    <img src="{{ $m->avatar }}" alt="" style="width: 30px; height: 30px; border-radius: 50%; object-fit: cover;">
                @else
                    <div style="width: 30px; height: 30px; border-radius: 50%; background: var(--surface2); display: flex; align-items: center; justify-content: center; font-size: 12px; color: var(--admin); font-weight: 700;">
                        {{ strtoupper(substr($m->name, 0, 1)) }}
                    </div>
                @endif
                <div style="min-width: 0;">
                    <a href="{{ route('admin.users.show', $m) }}" style="font-size: 13px; color: var(--text); text-decoration: none; font-weight: 600;">{{ $m->name }}</a>
                    <div style="font-size: 11px; color: var(--muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $m->email }}</div>
                </div>
            </div>
            <div style="display: flex; align-items: center; gap: 12px;">
                <span class="badge {{ $m->pivot->role === 'owner' ? 'badge-owner' : 'badge-member' }}">
                    {{ $m->pivot->role === 'owner' ? 'Owner' : 'Miembro' }}
                </span>
                @if($familyGroup->owner_id !== $m->id)
                    <form method="POST" action="{{ route('admin.family-groups.remove-member', [$familyGroup, $m]) }}"
                          onsubmit="return confirm('¿Quitar a {{ $m->name }} del grupo?')">
                        @csrf @method('DELETE')
                        <button type="submit" style="background: none; border: none; color: var(--danger); font-size: 12px; cursor: pointer; font-weight: 700;">
                            Quitar
                        </button>
                    </form>
                @endif
            </div>
        </div>
    @endforeach
</div>

<div class="card">
    <h2 class="font-display" style="font-size: 15px; font-weight: 700; margin-bottom: 14px;">Agregar miembro</h2>

    @if($availableUsers->isEmpty())
        <div style="font-size: 13px; color: var(--muted);">Todos los usuarios del sistema ya son miembros.</div>
    @else
        <form method="POST" action="{{ route('admin.family-groups.add-member', $familyGroup) }}"
              style="display: flex; gap: 10px; flex-wrap: wrap;">
            @csrf
            <select name="user_id" class="form-select" required style="flex: 1; min-width: 220px;">
                <option value="">Elegí un usuario…</option>
                @foreach($availableUsers as $u)
                    <option value="{{ $u->id }}">{{ $u->name }} — {{ $u->email }}</option>
                @endforeach
            </select>
            <select name="role" class="form-select" style="width: 140px;">
                <option value="member">Miembro</option>
                <option value="owner">Owner</option>
            </select>
            <button type="submit" class="btn btn-primary">Agregar</button>
        </form>
        @error('user_id')<div style="font-size:12px;color:var(--danger);margin-top:6px;">{{ $message }}</div>@enderror
    @endif
</div>

@if($familyGroup->invitations->isNotEmpty())
    <div class="card" style="margin-top: 18px;">
        <h2 class="font-display" style="font-size: 15px; font-weight: 700; margin-bottom: 14px;">
            Invitaciones pendientes ({{ $familyGroup->invitations->count() }})
        </h2>
        @foreach($familyGroup->invitations as $inv)
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid var(--border); font-size: 13px;">
                <div>
                    <div>{{ $inv->email }}</div>
                    <div style="font-size: 11px; color: var(--muted);">Vence {{ $inv->expires_at->format('d/m/Y') }}</div>
                </div>
                <span class="badge badge-info">Pendiente</span>
            </div>
        @endforeach
    </div>
@endif

@endsection
