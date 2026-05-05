@extends('layouts.app')

@section('title', 'Etiquetas')

@section('content')

@php $presets = ['#f0a030','#2dd870','#f04060','#4e9bff','#a078ff','#ff7eb6','#5de4c7','#6a6676','#e8b840']; @endphp

<style>
.tag-action-link { display:inline-flex;align-items:center;min-height:32px;padding:0 8px;border-radius:6px;color:var(--muted);font-size:12px;text-decoration:none;transition:color 0.15s,background 0.15s;cursor:pointer;border:none;background:none;font-family:inherit; }
.tag-action-link:hover { color:var(--text);background:var(--surface2); }
.tag-action-btn  { display:inline-flex;align-items:center;min-height:32px;padding:0 8px;border-radius:6px;background:none;border:none;color:var(--danger);font-size:12px;cursor:pointer;font-family:inherit;transition:background 0.15s; }
.tag-action-btn:hover { background:rgba(240,64,96,0.08); }
</style>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:28px;flex-wrap:wrap;gap:12px;">
    <div>
        <h1 class="font-display" style="font-size:26px;font-weight:800;letter-spacing:-0.03em;">Etiquetas</h1>
        <div style="font-size:13px;color:var(--muted);margin-top:3px;">Organizá tus movimientos y pagos con etiquetas para análisis y filtros</div>
    </div>
</div>

{{-- Create form --}}
<div class="card" style="margin-bottom:24px;padding:24px;">
    <div class="font-display" style="font-size:15px;font-weight:700;margin-bottom:16px;">Nueva etiqueta</div>

    @if(session('success'))
        <div style="padding:10px 14px;background:rgba(45,216,112,0.1);border:1px solid rgba(45,216,112,0.25);border-radius:8px;font-size:13px;color:var(--income);margin-bottom:16px;">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('tags.store') }}"
          style="display:flex;gap:14px;align-items:flex-end;flex-wrap:wrap;">
        @csrf
        <div>
            <label class="form-label">Nombre *</label>
            <input type="text" name="name" class="form-input"
                   value="{{ old('name') }}"
                   placeholder="Ej: Auto, Hogar, Ocio…"
                   maxlength="50" required style="width:220px;">
            @error('name')
                <div style="font-size:12px;color:var(--danger);margin-top:4px;">{{ $message }}</div>
            @enderror
        </div>
        <div>
            <label class="form-label">Color *</label>
            <div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:6px;" id="create-swatches">
                @foreach($presets as $c)
                <label style="cursor:pointer;">
                    <input type="radio" name="color" value="{{ $c }}"
                           {{ old('color', '#f0a030') === $c ? 'checked' : '' }}
                           style="display:none;" class="create-color-radio" data-color="{{ $c }}">
                    <span class="create-swatch"
                          data-color="{{ $c }}"
                          style="display:inline-block;width:24px;height:24px;border-radius:50%;
                                 background:{{ $c }};cursor:pointer;
                                 border:3px solid {{ old('color', '#f0a030') === $c ? '#fff' : 'transparent' }};
                                 box-sizing:border-box;transition:border-color 0.1s;"
                          onclick="selectCreateColor('{{ $c }}')">
                    </span>
                </label>
                @endforeach
            </div>
            @error('color')
                <div style="font-size:12px;color:var(--danger);margin-top:4px;">{{ $message }}</div>
            @enderror
        </div>
        <button type="submit" class="btn btn-primary">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
            Crear
        </button>
    </form>
</div>

{{-- Tag list --}}
@if($tags->isEmpty())
<div class="card" style="padding:48px 20px;text-align:center;">
    <div style="font-size:32px;margin-bottom:12px;">🏷️</div>
    <div style="font-size:15px;font-weight:600;color:var(--text);margin-bottom:6px;">Todavía no hay etiquetas</div>
    <div style="font-size:13px;color:var(--muted);">Creá una etiqueta arriba para empezar a organizar tus movimientos y pagos.</div>
</div>
@else
<div class="card" style="padding:0;overflow:hidden;">
    <table class="data-table">
        <thead>
            <tr>
                <th>Etiqueta</th>
                <th style="color:var(--muted);font-weight:500;">Movimientos</th>
                <th style="color:var(--muted);font-weight:500;">Pagos</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($tags as $tag)
            <tr>
                <td>
                    <span style="display:inline-flex;align-items:center;gap:6px;padding:4px 12px 4px 8px;
                                 border-radius:20px;font-size:12px;font-weight:700;
                                 background:{{ $tag->color }}22;color:{{ $tag->color }};
                                 border:1px solid {{ $tag->color }}55;">
                        <span style="width:8px;height:8px;border-radius:50%;background:{{ $tag->color }};flex-shrink:0;"></span>
                        {{ $tag->name }}
                    </span>
                </td>
                <td style="font-size:12px;color:var(--muted);">{{ $tag->transactions()->count() }}</td>
                <td style="font-size:12px;color:var(--muted);">{{ $tag->paymentItems()->count() }}</td>
                <td style="white-space:nowrap;text-align:right;">
                    <button type="button"
                            onclick="openEditModal({{ $tag->id }}, '{{ addslashes($tag->name) }}', '{{ $tag->color }}')"
                            class="tag-action-link">Editar</button>
                    <form method="POST" action="{{ route('tags.destroy', $tag) }}"
                          style="display:inline;"
                          onsubmit="return confirm('¿Eliminar «{{ addslashes($tag->name) }}»? Se quitará de todos los movimientos y pagos.')">
                        @csrf @method('DELETE')
                        <button type="submit" class="tag-action-btn">Eliminar</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

{{-- Edit modal --}}
<div id="edit-modal-backdrop"
     onclick="closeEditModal()"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.65);backdrop-filter:blur(4px);z-index:500;align-items:center;justify-content:center;">
    <div onclick="event.stopPropagation()"
         style="background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:28px;width:100%;max-width:360px;margin:16px;">
        <h3 class="font-display" style="font-size:16px;font-weight:800;letter-spacing:-0.02em;margin-bottom:20px;">Editar etiqueta</h3>
        <form id="edit-form" method="POST">
            @csrf @method('PUT')
            <div style="margin-bottom:16px;">
                <label class="form-label">Nombre *</label>
                <input type="text" name="name" id="edit-name" class="form-input" maxlength="50" required>
            </div>
            <div style="margin-bottom:22px;">
                <label class="form-label">Color *</label>
                <div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:6px;" id="edit-swatches">
                    @foreach($presets as $c)
                    <span class="edit-swatch"
                          data-color="{{ $c }}"
                          style="display:inline-block;width:24px;height:24px;border-radius:50%;background:{{ $c }};cursor:pointer;border:3px solid transparent;box-sizing:border-box;transition:border-color 0.1s;"
                          onclick="selectEditColor('{{ $c }}')">
                    </span>
                    @endforeach
                </div>
                <input type="hidden" name="color" id="edit-color" value="">
            </div>
            <div style="display:flex;gap:10px;justify-content:flex-end;">
                <button type="button" onclick="closeEditModal()" class="btn btn-ghost">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar cambios</button>
            </div>
        </form>
    </div>
</div>

<script>
function selectCreateColor(color) {
    document.querySelectorAll('.create-swatch').forEach(s => s.style.borderColor = 'transparent');
    document.querySelector(`.create-swatch[data-color="${color}"]`).style.borderColor = '#fff';
    document.querySelector(`.create-color-radio[data-color="${color}"]`).checked = true;
}

function openEditModal(id, name, color) {
    document.getElementById('edit-name').value = name;
    document.getElementById('edit-color').value = color;
    document.getElementById('edit-form').action = '/etiquetas/' + id;
    document.querySelectorAll('.edit-swatch').forEach(s => {
        s.style.borderColor = s.dataset.color === color ? '#fff' : 'transparent';
    });
    document.getElementById('edit-modal-backdrop').style.display = 'flex';
    setTimeout(() => document.getElementById('edit-name').focus(), 50);
}

function closeEditModal() {
    document.getElementById('edit-modal-backdrop').style.display = 'none';
}

function selectEditColor(color) {
    document.getElementById('edit-color').value = color;
    document.querySelectorAll('.edit-swatch').forEach(s => {
        s.style.borderColor = s.dataset.color === color ? '#fff' : 'transparent';
    });
}

document.addEventListener('keydown', e => { if (e.key === 'Escape') closeEditModal(); });
</script>

@endsection
