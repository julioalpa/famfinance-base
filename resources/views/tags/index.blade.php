@extends('layouts.app')

@section('title', 'Etiquetas')

@section('content')

@php $presets = ['#f0a030','#2dd870','#f04060','#4e9bff','#a078ff','#ff7eb6','#5de4c7','#6a6676','#e8b840']; @endphp

<style>
.tag-action-link { display:inline-flex;align-items:center;min-height:32px;padding:0 8px;border-radius:6px;color:var(--muted);font-size:12px;text-decoration:none;transition:color 0.15s,background 0.15s;cursor:pointer;border:none;background:none;font-family:inherit; }
.tag-action-link:hover { color:var(--text);background:var(--surface2); }
.tag-action-btn  { display:inline-flex;align-items:center;min-height:32px;padding:0 8px;border-radius:6px;background:none;border:none;color:var(--danger);font-size:12px;cursor:pointer;font-family:inherit;transition:background 0.15s; }
.tag-action-btn:hover { background:rgba(240,64,96,0.08); }

/* ── Grupos ── */
.tg-card {
    border:1px solid var(--border);
    border-radius:12px;
    padding:16px 18px;
    display:flex;
    align-items:flex-start;
    gap:14px;
    background:var(--surface);
    transition:border-color 0.15s;
}
.tg-card:hover { border-color:rgba(255,255,255,0.12); }
.tg-dot {
    width:14px; height:14px; border-radius:4px; flex-shrink:0; margin-top:3px;
}
.tg-pill-area {
    display:flex; flex-wrap:wrap; gap:5px; margin-top:8px; min-height:22px;
}
.tg-add-tag-btn {
    display:inline-flex; align-items:center; gap:4px;
    padding:2px 9px; border-radius:12px; font-size:11px; font-weight:600;
    border:1px dashed rgba(255,255,255,0.2); background:none; color:var(--muted);
    cursor:pointer; transition:border-color 0.15s, color 0.15s;
}
.tg-add-tag-btn:hover { border-color:var(--accent); color:var(--accent); }
.tg-pop {
    position:fixed; z-index:9999;
    background:var(--surface); border:1px solid var(--border);
    border-radius:12px; width:280px; padding:10px;
    box-shadow:0 8px 32px rgba(0,0,0,0.55);
}
.tg-backdrop { display:none; }
.tg-sheet-header { display:none; }
.tg-row {
    display:flex; align-items:center; gap:8px;
    padding:8px 10px; border-radius:7px; cursor:pointer;
    font-size:13px; transition:background 0.1s; user-select:none;
}
.tg-row.tg-active { background:var(--surface2); }
.tg-row:hover { background:var(--surface2); }
.tg-row .tg-check { width:14px; height:14px; flex-shrink:0; }
.tg-row .tg-dot   { width:11px; height:11px; border-radius:50%; flex-shrink:0; }
.tg-row .tg-name  { flex:1; min-width:0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.tg-empty { font-size:12px; color:var(--muted); padding:10px 8px; text-align:center; }

@media (max-width: 640px) {
    .tg-pop {
        position:fixed !important;
        left:0 !important; right:0 !important;
        top:auto !important; bottom:0 !important;
        width:100% !important; max-width:100% !important;
        border-radius:18px 18px 0 0;
        padding:8px 16px 24px 16px;
        box-shadow:0 -8px 40px rgba(0,0,0,0.6);
        z-index:10001;
        max-height:80vh;
        display:none;
        flex-direction:column;
        animation:tgSlideUp 0.18s ease-out;
    }
    .tg-pop.tg-open { display:flex !important; }
    .tg-backdrop.tg-open {
        display:block !important;
        position:fixed; inset:0;
        background:rgba(0,0,0,0.55);
        z-index:10000;
        animation:tgFadeIn 0.18s ease-out;
    }
    .tg-sheet-header { display:block !important; padding-bottom:8px; margin-bottom:4px; }
    .tg-sheet-grip {
        width:36px; height:4px; border-radius:2px;
        background:var(--border);
        margin:4px auto 12px auto;
    }
    .tg-sheet-close {
        display:inline-flex; align-items:center; justify-content:center;
        width:28px; height:28px;
        background:none; border:none;
        color:var(--muted); font-size:22px; line-height:1;
        cursor:pointer; padding:0;
    }
    .tg-pop input[type=text] { font-size:16px !important; padding:12px 14px !important; }
    .tg-row { padding:12px 10px; font-size:14px; }
    .tg-row .tg-dot { width:13px; height:13px; }
}
@keyframes tgSlideUp { from { transform:translateY(100%); } to { transform:translateY(0); } }
@keyframes tgFadeIn { from { opacity:0; } to { opacity:1; } }
</style>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:28px;flex-wrap:wrap;gap:12px;">
    <div>
        <h1 class="font-display" style="font-size:26px;font-weight:800;letter-spacing:-0.03em;">Etiquetas</h1>
        <div style="font-size:13px;color:var(--muted);margin-top:3px;">Organizá tus movimientos y pagos con etiquetas para análisis y filtros</div>
    </div>
</div>

{{-- ── Sección grupos de etiquetas ─────────────────────────────────────────── --}}
<div class="card" style="margin-bottom:24px;padding:24px;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;gap:12px;flex-wrap:wrap;">
        <div>
            <div class="font-display" style="font-size:15px;font-weight:700;">Grupos de etiquetas</div>
            <div style="font-size:12px;color:var(--muted);margin-top:2px;">
                Agrupá etiquetas para reportes consolidados. Una etiqueta puede estar en múltiples grupos.
            </div>
        </div>
        <button type="button" onclick="openNewGroupModal()"
                class="btn btn-ghost" style="font-size:12px;gap:6px;">
            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
            Nuevo grupo
        </button>
    </div>

    @if(session('group_success'))
        <div style="padding:10px 14px;background:rgba(45,216,112,0.1);border:1px solid rgba(45,216,112,0.25);border-radius:8px;font-size:13px;color:var(--income);margin-bottom:16px;">
            {{ session('group_success') }}
        </div>
    @endif

    @if($tagGroups->isEmpty())
        <div style="text-align:center;padding:24px 16px;border:1px dashed rgba(255,255,255,0.1);border-radius:10px;">
            <div style="font-size:12px;color:var(--muted);">Todavía no hay grupos. Creá uno para consolidar etiquetas en los reportes.</div>
        </div>
    @else
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:12px;">
            @foreach($tagGroups as $tg)
            <div class="tg-card">
                <div class="tg-dot" style="background:{{ $tg->color }};"></div>
                <div style="flex:1;min-width:0;">
                    <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;">
                        <span style="font-size:13px;font-weight:700;">{{ $tg->name }}</span>
                        <div style="display:flex;gap:0;flex-shrink:0;">
                            <button type="button"
                                    onclick="openEditGroupModal({{ $tg->id }},'{{ addslashes($tg->name) }}','{{ $tg->color }}')"
                                    class="tag-action-link" style="min-height:26px;font-size:11px;">Editar</button>
                            <form method="POST" action="{{ route('tag-groups.destroy', $tg) }}" style="display:inline;"
                                  onsubmit="return confirm('¿Eliminar el grupo «{{ addslashes($tg->name) }}»?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="tag-action-btn" style="min-height:26px;font-size:11px;">Eliminar</button>
                            </form>
                        </div>
                    </div>
                    <div class="tg-pill-area" id="tg-pills-{{ $tg->id }}">
                        @foreach($tg->tags as $t)
                            <span style="display:inline-flex;align-items:center;gap:3px;padding:2px 7px 2px 5px;
                                         border-radius:12px;font-size:10px;font-weight:700;
                                         background:{{ $t->color }}22;color:{{ $t->color }};
                                         border:1px solid {{ $t->color }}44;">
                                <span style="width:5px;height:5px;border-radius:50%;background:{{ $t->color }};"></span>
                                {{ $t->name }}
                            </span>
                        @endforeach
                        <button type="button"
                                class="tg-add-tag-btn"
                                onclick="openTgPicker({{ $tg->id }}, this)"
                                id="tg-addbtn-{{ $tg->id }}">
                            <svg width="9" height="9" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
                            Agregar
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @endif
</div>

{{-- ── Backdrop compartido para bottom-sheet mobile ─────────────────────────── --}}
<div id="tg-backdrop" class="tg-backdrop" onclick="closeTgPicker()"></div>

{{-- ── Popovers de tag picker por grupo ────────────────────────────────────── --}}
@foreach($tagGroups as $tg)
<div id="tg-pop-{{ $tg->id }}" class="tg-pop" style="display:none;">
    <div class="tg-sheet-header">
        <div class="tg-sheet-grip"></div>
        <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;">
            <span style="font-size:13px;font-weight:700;color:var(--text);">Agregar al grupo</span>
            <button type="button" class="tg-sheet-close" onclick="closeTgPicker()" aria-label="Cerrar">×</button>
        </div>
    </div>
    <input type="text" id="tg-search-{{ $tg->id }}" class="form-input"
           placeholder="Buscar etiqueta…"
           autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"
           style="margin-bottom:8px;font-size:13px;padding:9px 12px;width:100%;"
           oninput="tgFilterList({{ $tg->id }}, this.value)"
           onkeydown="tgKey(event, {{ $tg->id }})">
    <div id="tg-list-{{ $tg->id }}"
         style="max-height:220px;overflow-y:auto;display:flex;flex-direction:column;gap:2px;">
    </div>
</div>
@endforeach

{{-- ── Modal: nuevo grupo ─────────────────────────────────────────────────── --}}
<div id="new-group-backdrop" onclick="closeNewGroupModal()"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.65);backdrop-filter:blur(4px);z-index:500;align-items:center;justify-content:center;">
    <div onclick="event.stopPropagation()"
         style="background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:28px;width:100%;max-width:360px;margin:16px;">
        <h3 class="font-display" style="font-size:16px;font-weight:800;letter-spacing:-0.02em;margin-bottom:20px;">Nuevo grupo</h3>
        <form method="POST" action="{{ route('tag-groups.store') }}">
            @csrf
            <div style="margin-bottom:16px;">
                <label class="form-label">Nombre *</label>
                <input type="text" name="name" id="new-group-name" class="form-input" maxlength="100" required>
            </div>
            <div style="margin-bottom:22px;">
                <label class="form-label">Color *</label>
                <div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:6px;">
                    @foreach($presets as $i => $c)
                    <span class="ng-swatch"
                          data-color="{{ $c }}"
                          style="display:inline-block;width:24px;height:24px;border-radius:50%;background:{{ $c }};cursor:pointer;
                                 border:3px solid {{ $i === 0 ? '#fff' : 'transparent' }};box-sizing:border-box;transition:border-color 0.1s;"
                          onclick="selectNgColor('{{ $c }}')"></span>
                    @endforeach
                </div>
                <input type="hidden" name="color" id="new-group-color" value="{{ $presets[0] }}">
            </div>
            <div style="display:flex;gap:10px;justify-content:flex-end;">
                <button type="button" onclick="closeNewGroupModal()" class="btn btn-ghost">Cancelar</button>
                <button type="submit" class="btn btn-primary">Crear</button>
            </div>
        </form>
    </div>
</div>

{{-- ── Modal: editar grupo ────────────────────────────────────────────────── --}}
<div id="edit-group-backdrop" onclick="closeEditGroupModal()"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.65);backdrop-filter:blur(4px);z-index:500;align-items:center;justify-content:center;">
    <div onclick="event.stopPropagation()"
         style="background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:28px;width:100%;max-width:360px;margin:16px;">
        <h3 class="font-display" style="font-size:16px;font-weight:800;letter-spacing:-0.02em;margin-bottom:20px;">Editar grupo</h3>
        <form id="edit-group-form" method="POST">
            @csrf @method('PUT')
            <div style="margin-bottom:16px;">
                <label class="form-label">Nombre *</label>
                <input type="text" name="name" id="edit-group-name" class="form-input" maxlength="100" required>
            </div>
            <div style="margin-bottom:22px;">
                <label class="form-label">Color *</label>
                <div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:6px;">
                    @foreach($presets as $c)
                    <span class="eg-swatch"
                          data-color="{{ $c }}"
                          style="display:inline-block;width:24px;height:24px;border-radius:50%;background:{{ $c }};cursor:pointer;
                                 border:3px solid transparent;box-sizing:border-box;transition:border-color 0.1s;"
                          onclick="selectEgColor('{{ $c }}')"></span>
                    @endforeach
                </div>
                <input type="hidden" name="color" id="edit-group-color" value="">
            </div>
            <div style="display:flex;gap:10px;justify-content:flex-end;">
                <button type="button" onclick="closeEditGroupModal()" class="btn btn-ghost">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>
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
    <table class="data-table" id="tags-table">
        <thead>
            <tr>
                <th data-sort="text">Etiqueta</th>
                <th data-sort="number" style="color:var(--muted);font-weight:500;">Movimientos</th>
                <th data-sort="number" style="color:var(--muted);font-weight:500;">Pagos</th>
                <th data-sort="number" style="color:var(--muted);font-weight:500;">Monto ARS</th>
                <th data-sort="number" style="color:var(--muted);font-weight:500;">% del total</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($tags as $tag)
            <tr>
                <td data-val="{{ $tag->name }}">
                    <span style="display:inline-flex;align-items:center;gap:6px;padding:4px 12px 4px 8px;
                                 border-radius:20px;font-size:12px;font-weight:700;
                                 background:{{ $tag->color }}22;color:{{ $tag->color }};
                                 border:1px solid {{ $tag->color }}55;">
                        <span style="width:8px;height:8px;border-radius:50%;background:{{ $tag->color }};flex-shrink:0;"></span>
                        {{ $tag->name }}
                    </span>
                </td>
                <td data-val="{{ $tag->tx_count }}">
                    <span style="font-size:13px;font-weight:600;color:var(--text);">{{ $tag->tx_count }}</span>
                    @if($tag->tx_amount > 0)
                    <div style="font-size:11px;color:var(--muted);margin-top:1px;">$ {{ number_format($tag->tx_amount, 0, ',', '.') }}</div>
                    @endif
                </td>
                <td data-val="{{ $tag->py_count }}">
                    <span style="font-size:13px;font-weight:600;color:var(--text);">{{ $tag->py_count }}</span>
                    @if($tag->py_amount > 0)
                    <div style="font-size:11px;color:var(--muted);margin-top:1px;">$ {{ number_format($tag->py_amount, 0, ',', '.') }}</div>
                    @endif
                </td>
                <td data-val="{{ $tag->total_amount }}" style="font-size:13px;font-weight:600;color:var(--text);white-space:nowrap;">
                    @if($tag->total_amount > 0)
                        $ {{ number_format($tag->total_amount, 0, ',', '.') }}
                    @else
                        <span style="color:var(--muted);font-weight:400;">—</span>
                    @endif
                </td>
                <td data-val="{{ $tag->percentage }}">
                    @if($tag->percentage > 0)
                    <div style="display:flex;align-items:center;gap:8px;">
                        <div style="flex:1;min-width:60px;height:4px;border-radius:2px;background:var(--surface2);overflow:hidden;">
                            <div style="width:{{ min($tag->percentage, 100) }}%;height:100%;background:{{ $tag->color }};border-radius:2px;"></div>
                        </div>
                        <span style="font-size:12px;font-weight:600;color:var(--text);white-space:nowrap;">{{ number_format($tag->percentage, 1) }}%</span>
                    </div>
                    @else
                        <span style="font-size:12px;color:var(--muted);">—</span>
                    @endif
                </td>
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

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        closeEditModal();
        closeNewGroupModal();
        closeEditGroupModal();
        closeTgPicker();
    }
});

// ── Tag groups JS ─────────────────────────────────────────────────────────
// Declaradas antes de initTableSort para que no queden en TDZ si initTableSort lanza
var tgActivePicker = null;  // { groupId, popEl, query, highlight }

const TG_ALL_TAGS   = @json($tags->map(fn ($t) => ['id' => $t->id, 'name' => $t->name, 'color' => $t->color])->values());
const TG_GROUP_TAGS = @json($tagGroups->mapWithKeys(fn ($tg) => [$tg->id => $tg->tags->pluck('id')->values()]));

// Mutable state per group (mirrors server state after saves)
var tgState = {};
for (const [gid, ids] of Object.entries(TG_GROUP_TAGS)) {
    tgState[gid] = new Set(ids.map(Number));
}

try { initTableSort('tags-table'); } catch (_) {}

function _tgEsc(s) {
    return String(s).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[m]);
}

function tgIsMobile() { return window.innerWidth <= 640; }

const TG_POP_W = 280;   // .tg-pop width in CSS
const TG_MARGIN = 12;   // viewport edge margin

function openTgPicker(groupId, triggerEl) {
    closeTgPicker();
    const pop = document.getElementById(`tg-pop-${groupId}`);
    if (!pop) return;

    // Move to document.body so position:fixed uses the viewport, not the
    // .main-content containing block (which is offset by the sidebar because
    // of the fadeUp animation's residual transform).
    if (pop.parentElement !== document.body) {
        pop.dataset.origParent = 'yes';
        document.body.appendChild(pop);
    }

    if (tgIsMobile()) {
        // Bottom sheet: CSS handles positioning
        pop.classList.add('tg-open');
        document.getElementById('tg-backdrop').classList.add('tg-open');
        pop.style.left = '';
        pop.style.top  = '';
        pop.style.bottom = '';
    } else {
        const rect = triggerEl.getBoundingClientRect();
        const vpW = window.innerWidth;
        const vpH = window.innerHeight;

        // Horizontal: prefer left-align with trigger; if that overflows the
        // right edge, right-align with the trigger so it opens to the left.
        let left = rect.left;
        if (left + TG_POP_W + TG_MARGIN > vpW) {
            left = Math.max(TG_MARGIN, rect.right - TG_POP_W);
        }
        pop.style.left = left + 'px';

        // Vertical: open below if there's room, otherwise above.
        pop.style.top    = '';
        pop.style.bottom = '';
        const below = vpH - rect.bottom;
        if (below < 260 && rect.top > 260) {
            pop.style.bottom = (vpH - rect.top + 4) + 'px';
        } else {
            pop.style.top = (rect.bottom + 4) + 'px';
        }
        pop.style.display = 'block';
    }
    tgActivePicker = { groupId, popEl: pop, query: '', highlight: 0 };
    const search = document.getElementById(`tg-search-${groupId}`);
    if (search) { search.value = ''; setTimeout(() => search.focus(), 60); }
    tgRenderList(groupId);
}

function closeTgPicker() {
    if (tgActivePicker) {
        tgActivePicker.popEl.classList.remove('tg-open');
        tgActivePicker.popEl.style.display = 'none';
        document.getElementById('tg-backdrop')?.classList.remove('tg-open');
        tgActivePicker = null;
    }
}

function tgFilterList(groupId, q) {
    if (!tgActivePicker || tgActivePicker.groupId !== groupId) return;
    tgActivePicker.query = q;
    tgActivePicker.highlight = 0;
    tgRenderList(groupId);
}

function _tgComputeItems(groupId) {
    const q = (tgActivePicker && tgActivePicker.query) || '';
    const lq = q.trim().toLowerCase();
    const sel = tgState[groupId] || new Set();
    const filtered = TG_ALL_TAGS.filter(t => t.name.toLowerCase().includes(lq));
    filtered.sort((a, b) => {
        const aSel = sel.has(a.id) ? 1 : 0;
        const bSel = sel.has(b.id) ? 1 : 0;
        if (aSel !== bSel) return aSel - bSel;
        return a.name.localeCompare(b.name);
    });
    return filtered;
}

function tgRenderList(groupId) {
    const listEl = document.getElementById(`tg-list-${groupId}`);
    if (!listEl) return;
    const sel = tgState[groupId] || new Set();
    const items = _tgComputeItems(groupId);
    listEl.innerHTML = '';
    if (!items.length) {
        listEl.innerHTML = '<div class="tg-empty">Sin resultados</div>';
        return;
    }
    if (tgActivePicker.highlight < 0) tgActivePicker.highlight = 0;
    if (tgActivePicker.highlight >= items.length) tgActivePicker.highlight = items.length - 1;

    items.forEach((t, i) => {
        const isSel = sel.has(t.id);
        const d = document.createElement('div');
        d.className = 'tg-row' + (i === tgActivePicker.highlight ? ' tg-active' : '');
        d.innerHTML = `
            <span class="tg-dot" style="background:${t.color};"></span>
            <span class="tg-name">${_tgEsc(t.name)}</span>
            ${isSel
                ? '<svg class="tg-check" fill="none" stroke="var(--income)" stroke-width="2.8" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>'
                : '<span class="tg-check"></span>'}`;
        d.onmouseenter = () => {
            tgActivePicker.highlight = i;
            [...listEl.children].forEach((el, j) => el.classList.toggle('tg-active', j === i));
        };
        d.onclick = () => tgToggleTag(groupId, t.id);
        listEl.appendChild(d);
    });
    const activeEl = listEl.querySelector('.tg-active');
    if (activeEl) activeEl.scrollIntoView({ block: 'nearest' });
}

function tgToggleTag(groupId, tagId) {
    const sel = tgState[groupId] || new Set();
    if (sel.has(tagId)) sel.delete(tagId); else sel.add(tagId);
    tgState[groupId] = sel;
    tgSave(groupId);
    const search = document.getElementById(`tg-search-${groupId}`);
    if (search) { search.value = ''; search.focus(); }
    if (tgActivePicker) { tgActivePicker.query = ''; tgActivePicker.highlight = 0; }
    tgRenderList(groupId);
}

function tgKey(event, groupId) {
    if (!tgActivePicker) return;
    if (event.key === 'Enter') {
        event.preventDefault();
        const items = _tgComputeItems(groupId);
        const it = items[tgActivePicker.highlight];
        if (it) tgToggleTag(groupId, it.id);
    } else if (event.key === 'ArrowDown') {
        event.preventDefault();
        const items = _tgComputeItems(groupId);
        if (!items.length) return;
        tgActivePicker.highlight = (tgActivePicker.highlight + 1) % items.length;
        tgRenderList(groupId);
    } else if (event.key === 'ArrowUp') {
        event.preventDefault();
        const items = _tgComputeItems(groupId);
        if (!items.length) return;
        tgActivePicker.highlight = (tgActivePicker.highlight - 1 + items.length) % items.length;
        tgRenderList(groupId);
    } else if (event.key === 'Escape') {
        event.preventDefault();
        closeTgPicker();
    } else if (event.key === 'Backspace') {
        if (tgActivePicker.query) return;
        const arr = [...(tgState[groupId] || [])];
        if (!arr.length) return;
        event.preventDefault();
        tgToggleTag(groupId, arr[arr.length - 1]);
    }
}

async function tgSave(groupId) {
    try {
        const resp = await fetch(`/etiquetas/grupos/${groupId}/etiquetas`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ tags: [...tgState[groupId]] }),
        });
        if (!resp.ok) throw new Error();
        const data = await resp.json();
        tgRenderPills(groupId, data.tags);
    } catch {
        // silent fail — pills might be slightly out of sync until reload
    }
}

function tgRenderPills(groupId, tags) {
    const pillsEl = document.getElementById(`tg-pills-${groupId}`);
    if (!pillsEl) return;
    // Re-render pills (keep the "Agregar" button at the end)
    const btn = document.getElementById(`tg-addbtn-${groupId}`);
    pillsEl.innerHTML = '';
    tags.forEach(t => {
        const span = document.createElement('span');
        span.style.cssText = `display:inline-flex;align-items:center;gap:3px;padding:2px 7px 2px 5px;
            border-radius:12px;font-size:10px;font-weight:700;
            background:${t.color}22;color:${t.color};border:1px solid ${t.color}44;`;
        span.innerHTML = `<span style="width:5px;height:5px;border-radius:50%;background:${t.color};"></span>${_tgEsc(t.name)}`;
        pillsEl.appendChild(span);
    });
    if (btn) pillsEl.appendChild(btn);
}

// Close tg picker on outside click (desktop). Mobile uses backdrop.
document.addEventListener('click', e => {
    if (!tgActivePicker) return;
    if (tgIsMobile()) return; // handled by backdrop
    const { groupId, popEl } = tgActivePicker;
    const btn = document.getElementById(`tg-addbtn-${groupId}`);
    if (!popEl.contains(e.target) && btn && !btn.contains(e.target)) closeTgPicker();
}, true);

// Close on window scroll only when desktop-anchored (mobile sheet is fixed to viewport)
window.addEventListener('scroll', (e) => {
    if (!tgActivePicker || tgIsMobile()) return;
    // Ignore scroll bubbles from inside the list itself
    if (e.target && tgActivePicker.popEl.contains(e.target)) return;
    closeTgPicker();
}, { passive: true, capture: true });

// ── Modal helpers: nuevo grupo ────────────────────────────────────────────
function openNewGroupModal() {
    document.getElementById('new-group-name').value = '';
    document.getElementById('new-group-backdrop').style.display = 'flex';
    setTimeout(() => document.getElementById('new-group-name').focus(), 50);
}
function closeNewGroupModal() { document.getElementById('new-group-backdrop').style.display = 'none'; }
function selectNgColor(c) {
    document.getElementById('new-group-color').value = c;
    document.querySelectorAll('.ng-swatch').forEach(s => s.style.borderColor = s.dataset.color === c ? '#fff' : 'transparent');
}

// ── Modal helpers: editar grupo ───────────────────────────────────────────
function openEditGroupModal(id, name, color) {
    document.getElementById('edit-group-name').value  = name;
    document.getElementById('edit-group-color').value = color;
    document.getElementById('edit-group-form').action = `/etiquetas/grupos/${id}`;
    document.querySelectorAll('.eg-swatch').forEach(s => s.style.borderColor = s.dataset.color === color ? '#fff' : 'transparent');
    document.getElementById('edit-group-backdrop').style.display = 'flex';
    setTimeout(() => document.getElementById('edit-group-name').focus(), 50);
}
function closeEditGroupModal() { document.getElementById('edit-group-backdrop').style.display = 'none'; }
function selectEgColor(c) {
    document.getElementById('edit-group-color').value = c;
    document.querySelectorAll('.eg-swatch').forEach(s => s.style.borderColor = s.dataset.color === c ? '#fff' : 'transparent');
}
</script>

@endsection
