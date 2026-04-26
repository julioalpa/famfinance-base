@extends('layouts.app')

@section('title', 'Grupo familiar')

@section('content')

<div style="max-width: 720px;">
    <div style="margin-bottom: 28px;">
        <h1 class="font-display" style="font-size: 24px; font-weight: 700;">{{ $familyGroup->name }}</h1>
        <div style="font-size: 12px; color: var(--muted); margin-top: 4px;">
            Administrado por {{ $familyGroup->owner->name }}
        </div>
    </div>

    {{-- Miembros --}}
    <div class="card" style="margin-bottom: 20px;">
        <h2 class="font-display" style="font-size: 14px; font-weight: 600; margin-bottom: 18px;">
            Miembros ({{ $familyGroup->members->count() }})
        </h2>

        @foreach($familyGroup->members as $member)
        <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid var(--border);">
            <div style="display: flex; align-items: center; gap: 12px;">
                @if($member->avatar)
                    <img src="{{ $member->avatar }}" style="width: 32px; height: 32px; border-radius: 50%; border: 1px solid var(--border);">
                @else
                    <div style="width: 32px; height: 32px; border-radius: 50%; background: var(--surface2); display: flex; align-items: center; justify-content: center; font-size: 13px; color: var(--accent);">
                        {{ strtoupper(substr($member->name, 0, 1)) }}
                    </div>
                @endif
                <div>
                    <div style="font-size: 13px; color: var(--text);">
                        {{ $member->name }}
                        @if($member->id === auth()->id())
                            <span style="font-size: 10px; color: var(--muted);">(vos)</span>
                        @endif
                    </div>
                    <div style="font-size: 11px; color: var(--muted);">{{ $member->email }}</div>
                </div>
            </div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <span class="badge {{ $member->pivot->role === 'owner' ? 'badge-income' : '' }}"
                      style="{{ $member->pivot->role !== 'owner' ? 'color:var(--muted);' : '' }}">
                    {{ $member->pivot->role === 'owner' ? 'Admin' : 'Miembro' }}
                </span>
                @if($familyGroup->owner_id === auth()->id() && $member->id !== auth()->id())
                <form method="POST" action="{{ route('family-groups.remove-member', $member->id) }}"
                      onsubmit="return confirm('¿Remover a {{ $member->name }} del grupo?')">
                    @csrf @method('DELETE')
                    <button type="submit" style="background: none; border: none; color: var(--danger); font-size: 11px; cursor: pointer; font-family: 'DM Mono', monospace;">
                        Remover
                    </button>
                </form>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    {{-- Invitar --}}
    <div class="card" style="margin-bottom: 20px;">
        <h2 class="font-display" style="font-size: 14px; font-weight: 600; margin-bottom: 16px;">Invitar a alguien</h2>
        <form method="POST" action="{{ route('family-groups.invite', $familyGroup) }}" style="display: flex; gap: 10px;">
            @csrf
            <input type="email" name="email" class="form-input"
                   placeholder="email@ejemplo.com"
                   value="{{ old('email') }}"
                   required>
            <button type="submit" class="btn btn-primary" style="white-space: nowrap;">Enviar invitación</button>
        </form>
        @error('email')
            <div style="font-size:12px; color:var(--danger); margin-top:6px;">{{ $message }}</div>
        @enderror
        <div style="margin-top: 10px; font-size: 11px; color: var(--muted);">
            La persona recibirá un email con un link para unirse. El link expira en 7 días.
        </div>
    </div>

    {{-- Invitaciones pendientes --}}
    @if($familyGroup->invitations->isNotEmpty())
    <div class="card">
        <h2 class="font-display" style="font-size: 14px; font-weight: 600; margin-bottom: 16px;">
            Invitaciones pendientes ({{ $familyGroup->invitations->count() }})
        </h2>
        @foreach($familyGroup->invitations as $inv)
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid var(--border); font-size: 13px;">
            <div>
                <div>{{ $inv->email }}</div>
                <div style="font-size: 11px; color: var(--muted);">
                    Enviada por {{ $inv->invitedBy->name }} · vence {{ $inv->expires_at->format('d/m/Y') }}
                </div>
            </div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <button type="button"
                        onclick="copyInviteLink('{{ url('/invitacion/' . $inv->token) }}', this)"
                        title="Copiar link de invitación"
                        style="background: none; border: none; cursor: pointer; color: var(--muted); padding: 4px; display: flex; align-items: center; gap: 5px; font-size: 11px; font-family: 'Nunito', sans-serif;">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                    </svg>
                    Copiar link
                </button>
                @if($familyGroup->owner_id === auth()->id())
                <form method="POST" action="{{ route('family-groups.revoke-invitation', $inv) }}"
                      onsubmit="return confirm('¿Revocar la invitación a {{ $inv->email }}?')">
                    @csrf @method('DELETE')
                    <button type="submit" style="background: none; border: none; color: var(--danger); font-size: 11px; cursor: pointer; font-family: 'DM Mono', monospace;">
                        Revocar
                    </button>
                </form>
                @endif
            </div>

            <script>
            function copyInviteLink(url, btn) {
                navigator.clipboard.writeText(url).then(() => {
                    const original = btn.innerHTML;
                    btn.innerHTML = '<svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg> Copiado';
                    btn.style.color = 'var(--income)';
                    setTimeout(() => { btn.innerHTML = original; btn.style.color = 'var(--muted)'; }, 2000);
                });
            }
            </script>
        </div>
        @endforeach
    </div>
    @endif
</div>

@endsection
