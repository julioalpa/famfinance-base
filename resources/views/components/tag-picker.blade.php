@props([
    'allTags'     => collect(),
    'selectedIds' => [],
    'name'        => 'tags',
])

@php
    $cid = 'tp' . str_replace('.', '', uniqid('', true));
    $presetColors = ['#f0a030','#2dd870','#f04060','#4e9bff','#a078ff','#ff7eb6','#5de4c7','#6a6676','#e8b840'];
@endphp

<div class="form-group" style="margin-bottom:20px;">
    <label class="form-label">Etiquetas</label>
    <div id="{{ $cid }}" class="tp-root" style="position:relative;"
         data-field="{{ $name }}"
         data-all='@json($allTags->map(fn($t)=>["id"=>$t->id,"name"=>$t->name,"color"=>$t->color])->values())'
         data-selected='@json(array_map("intval",(array)$selectedIds))'
         data-presets='@json($presetColors)'>

        {{-- Selected pills --}}
        <div id="{{ $cid }}-pills"
             style="display:flex;flex-wrap:wrap;gap:5px;min-height:20px;margin-bottom:8px;">
        </div>

        {{-- Hidden inputs placeholder --}}
        <div id="{{ $cid }}-inputs"></div>

        {{-- Trigger button --}}
        <button type="button"
                onclick="tpAction('{{ $cid }}','toggle')"
                class="btn btn-ghost"
                style="font-size:12px;padding:5px 11px;gap:5px;">
            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/>
                <line x1="7" y1="7" x2="7.01" y2="7"/>
            </svg>
            Agregar etiqueta
        </button>

        {{-- Backdrop (only visible in mobile bottom-sheet mode) --}}
        <div id="{{ $cid }}-backdrop"
             class="tp-backdrop"
             onclick="tpAction('{{ $cid }}','close')"></div>

        {{-- Dropdown --}}
        <div id="{{ $cid }}-drop" class="tp-drop">

            {{-- Mobile drag-handle / header --}}
            <div class="tp-sheet-header">
                <div class="tp-sheet-grip"></div>
                <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;">
                    <span style="font-size:13px;font-weight:700;color:var(--text);">Etiquetas</span>
                    <button type="button" class="tp-sheet-close"
                            onclick="tpAction('{{ $cid }}','close')" aria-label="Cerrar">×</button>
                </div>
            </div>

            <input type="text"
                   id="{{ $cid }}-search"
                   class="form-input tp-search"
                   placeholder="Buscar o crear…"
                   autocomplete="off"
                   autocorrect="off"
                   autocapitalize="off"
                   spellcheck="false"
                   oninput="tpAction('{{ $cid }}','filter',this.value)"
                   onkeydown="tpKey(event,'{{ $cid }}')">

            <div id="{{ $cid }}-list" class="tp-list"></div>

            {{-- Color palette (only shown when a "create new" row is highlighted) --}}
            <div id="{{ $cid }}-palette" class="tp-palette" style="display:none;">
                <div style="font-size:11px;color:var(--muted);margin-bottom:6px;font-weight:600;">Color de la nueva etiqueta</div>
                <div style="display:flex;flex-wrap:wrap;gap:6px;">
                    @foreach($presetColors as $i => $c)
                    <button type="button"
                            class="{{ $cid }}-dot tp-swatch"
                            data-color="{{ $c }}"
                            style="background:{{ $c }};border-color:{{ $i === 0 ? '#fff' : 'transparent' }};"
                            onclick="tpAction('{{ $cid }}','color','{{ $c }}',this)"
                            aria-label="Color {{ $c }}"></button>
                    @endforeach
                </div>
                <input type="hidden" id="{{ $cid }}-color" value="#f0a030">
            </div>
        </div>
    </div>
</div>

@once
<style>
/* Dropdown / sheet base */
.tp-drop {
    display:none;
    position:absolute;
    z-index:200;
    left:0;
    top:calc(100% + 6px);
    background:var(--surface);
    border:1px solid var(--border);
    border-radius:12px;
    width:min(300px, calc(100vw - 32px));
    max-width:100%;
    padding:10px;
    box-shadow:0 8px 32px rgba(0,0,0,0.4);
}
.tp-backdrop { display:none; }
.tp-sheet-header { display:none; }
.tp-sheet-close { display:none; }
.tp-search {
    margin-bottom:8px;
    font-size:13px;
    padding:9px 12px;
    width:100%;
}
.tp-list {
    max-height:220px;
    overflow-y:auto;
    display:flex;
    flex-direction:column;
    gap:2px;
}
.tp-row {
    display:flex;
    align-items:center;
    gap:8px;
    padding:8px 10px;
    border-radius:7px;
    cursor:pointer;
    font-size:13px;
    transition:background 0.1s;
    user-select:none;
}
.tp-row.tp-active { background:var(--surface2); }
.tp-row:hover { background:var(--surface2); }
.tp-row .tp-check { width:14px; height:14px; flex-shrink:0; }
.tp-row .tp-dot { width:11px; height:11px; border-radius:50%; flex-shrink:0; }
.tp-row .tp-name { flex:1; min-width:0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.tp-row-create {
    color:var(--accent, #4e9bff);
    font-weight:600;
}
.tp-row-create .tp-plus {
    width:14px;height:14px;border-radius:50%;
    display:inline-flex;align-items:center;justify-content:center;
    background:var(--accent, #4e9bff);color:#fff;font-size:11px;font-weight:700;
    flex-shrink:0;line-height:1;
}
.tp-empty {
    font-size:12px;color:var(--muted);padding:10px 8px;text-align:center;
}
.tp-palette {
    margin-top:10px;padding-top:10px;
    border-top:1px solid var(--border);
}
.tp-swatch {
    width:22px;height:22px;border-radius:50%;
    border:2px solid transparent;
    cursor:pointer;flex-shrink:0;padding:0;
    box-sizing:border-box;
    transition:transform 0.08s;
}
.tp-swatch:active { transform:scale(0.9); }

/* Mobile → bottom sheet */
@media (max-width: 640px) {
    .tp-root .tp-drop {
        position:fixed;
        left:0;
        right:0;
        top:auto;
        bottom:0;
        width:100%;
        max-width:100%;
        border-radius:18px 18px 0 0;
        padding:8px 16px 24px 16px;
        box-shadow:0 -8px 40px rgba(0,0,0,0.6);
        z-index:1001;
        max-height:80vh;
        display:none;
        flex-direction:column;
        animation:tpSlideUp 0.18s ease-out;
    }
    .tp-root .tp-drop.tp-open { display:flex; }
    .tp-root .tp-backdrop.tp-open {
        display:block;
        position:fixed;inset:0;
        background:rgba(0,0,0,0.55);
        z-index:1000;
        animation:tpFadeIn 0.18s ease-out;
    }
    .tp-sheet-header {
        display:block !important;
        padding-bottom:8px;
        margin-bottom:4px;
    }
    .tp-sheet-grip {
        width:36px;height:4px;border-radius:2px;
        background:var(--border);
        margin:4px auto 12px auto;
    }
    .tp-sheet-close {
        display:inline-flex !important;
        align-items:center;justify-content:center;
        width:28px;height:28px;
        background:none;border:none;
        color:var(--muted);font-size:22px;line-height:1;
        cursor:pointer;padding:0;
    }
    .tp-search { font-size:16px; padding:12px 14px; }
    .tp-list { max-height:min(50vh, 400px); }
    .tp-row { padding:12px 10px; font-size:14px; }
    .tp-row .tp-dot { width:13px; height:13px; }
    .tp-swatch { width:28px; height:28px; }
}

@keyframes tpSlideUp {
    from { transform:translateY(100%); }
    to   { transform:translateY(0); }
}
@keyframes tpFadeIn {
    from { opacity:0; }
    to   { opacity:1; }
}

/* Pill remove target — bigger on mobile */
.tp-pill-x {
    font-size:15px;
    opacity:0.55;
    cursor:pointer;
    line-height:1;
    padding:2px 4px;
    margin-left:2px;
    border-radius:50%;
}
.tp-pill-x:hover { opacity:1; }
@media (max-width: 640px) {
    .tp-pill-x { font-size:18px; padding:4px 6px; }
}
</style>
@endonce

<script>
(function(){
    const CID  = '{{ $cid }}';
    const root = document.getElementById(CID);
    const all  = JSON.parse(root.dataset.all);
    const sel  = new Set(JSON.parse(root.dataset.selected));
    let open   = false;
    let highlight = 0;   // index in the rendered list
    let currentQuery = '';

    function esc(s) {
        return String(s).replace(/[&<>"']/g, m =>
            ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[m]);
    }

    function renderPills() {
        const pillsEl = document.getElementById(CID + '-pills');
        pillsEl.innerHTML = '';
        sel.forEach(id => {
            const t = all.find(x => x.id === id);
            if (!t) return;
            const s = document.createElement('span');
            s.style.cssText = `display:inline-flex;align-items:center;gap:4px;padding:3px 6px 3px 10px;
                border-radius:20px;font-size:11px;font-weight:700;
                background:${t.color}22;color:${t.color};border:1px solid ${t.color}55;`;
            s.innerHTML = `${esc(t.name)}<span class="tp-pill-x"
                onclick="tpAction('${CID}','remove',${id})"
                role="button" aria-label="Quitar ${esc(t.name)}">×</span>`;
            pillsEl.appendChild(s);
        });

        const inputsEl = document.getElementById(CID + '-inputs');
        inputsEl.innerHTML = '';
        sel.forEach(id => {
            const inp = document.createElement('input');
            inp.type = 'hidden';
            inp.name = root.dataset.field + '[]';
            inp.value = id;
            inputsEl.appendChild(inp);
        });
    }

    // Compute rendered items in list order (used for highlight/enter)
    function computeItems(q) {
        const lq = q.trim().toLowerCase();
        const filtered = all
            .filter(t => t.name.toLowerCase().includes(lq));
        // Sort: unselected first, then selected; alphabetical inside each
        filtered.sort((a, b) => {
            const aSel = sel.has(a.id) ? 1 : 0;
            const bSel = sel.has(b.id) ? 1 : 0;
            if (aSel !== bSel) return aSel - bSel;
            return a.name.localeCompare(b.name);
        });
        const items = filtered.map(t => ({ kind: 'tag', tag: t }));
        // Add "Crear «X»" if query non-empty AND no exact match
        if (lq) {
            const exact = all.find(t => t.name.toLowerCase() === lq);
            if (!exact) items.push({ kind: 'create', name: q.trim() });
        }
        return items;
    }

    function renderList() {
        const listEl = document.getElementById(CID + '-list');
        const items = computeItems(currentQuery);
        listEl.innerHTML = '';
        if (!items.length) {
            listEl.innerHTML = '<div class="tp-empty">Escribí para buscar o crear una etiqueta</div>';
            document.getElementById(CID + '-palette').style.display = 'none';
            return;
        }
        // Clamp highlight
        if (highlight < 0) highlight = 0;
        if (highlight >= items.length) highlight = items.length - 1;

        items.forEach((it, i) => {
            const d = document.createElement('div');
            d.className = 'tp-row' + (i === highlight ? ' tp-active' : '');
            d.dataset.index = i;
            if (it.kind === 'tag') {
                const isSel = sel.has(it.tag.id);
                d.innerHTML = `<span class="tp-dot" style="background:${it.tag.color};"></span>
                    <span class="tp-name">${esc(it.tag.name)}</span>
                    ${isSel
                        ? '<svg class="tp-check" fill="none" stroke="var(--income)" stroke-width="2.8" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>'
                        : '<span class="tp-check"></span>'}`;
                d.onclick = () => activateItem(i);
            } else {
                d.classList.add('tp-row-create');
                d.innerHTML = `<span class="tp-plus">+</span>
                    <span class="tp-name">Crear «${esc(it.name)}»</span>
                    <span class="tp-check"></span>`;
                d.onclick = () => activateItem(i);
            }
            d.onmouseenter = () => { highlight = i; updateActiveClass(); showPaletteIfCreate(); };
            listEl.appendChild(d);
        });

        // Scroll active into view
        const activeEl = listEl.querySelector('.tp-active');
        if (activeEl) activeEl.scrollIntoView({ block: 'nearest' });

        showPaletteIfCreate();
    }

    function updateActiveClass() {
        const listEl = document.getElementById(CID + '-list');
        [...listEl.children].forEach((el, i) => {
            el.classList.toggle('tp-active', i === highlight);
        });
        const activeEl = listEl.querySelector('.tp-active');
        if (activeEl) activeEl.scrollIntoView({ block: 'nearest' });
    }

    function showPaletteIfCreate() {
        const items = computeItems(currentQuery);
        const active = items[highlight];
        const pal = document.getElementById(CID + '-palette');
        pal.style.display = (active && active.kind === 'create') ? 'block' : 'none';
    }

    function activateItem(i) {
        const items = computeItems(currentQuery);
        const it = items[i];
        if (!it) return;
        if (it.kind === 'tag') {
            if (sel.has(it.tag.id)) sel.delete(it.tag.id);
            else sel.add(it.tag.id);
            renderPills();
            resetSearch();
        } else {
            // create
            createNew(it.name);
        }
    }

    function resetSearch() {
        currentQuery = '';
        const s = document.getElementById(CID + '-search');
        s.value = '';
        highlight = 0;
        renderList();
        s.focus();
    }

    async function createNew(name) {
        const color = document.getElementById(CID + '-color').value;
        const searchEl = document.getElementById(CID + '-search');
        searchEl.disabled = true;
        try {
            const resp = await fetch('/etiquetas', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ name, color }),
            });
            if (!resp.ok) throw 0;
            const tag = await resp.json();
            all.push(tag);
            sel.add(tag.id);
            renderPills();
            resetSearch();
        } catch {
            alert('No se pudo crear la etiqueta. Intentá de nuevo.');
        } finally {
            searchEl.disabled = false;
            searchEl.focus();
        }
    }

    const actions = {
        toggle() {
            if (open) actions.close(); else actions.open();
        },
        open() {
            open = true;
            const drop = document.getElementById(CID + '-drop');
            const backdrop = document.getElementById(CID + '-backdrop');
            drop.style.display = 'block';
            drop.classList.add('tp-open');
            backdrop.classList.add('tp-open');
            currentQuery = '';
            highlight = 0;
            renderList();
            setTimeout(() => {
                const s = document.getElementById(CID + '-search');
                s.value = '';
                s.focus();
            }, 60);
        },
        close() {
            open = false;
            const drop = document.getElementById(CID + '-drop');
            const backdrop = document.getElementById(CID + '-backdrop');
            drop.style.display = 'none';
            drop.classList.remove('tp-open');
            backdrop.classList.remove('tp-open');
        },
        filter(q) {
            currentQuery = q;
            highlight = 0;
            renderList();
        },
        remove(id) { sel.delete(Number(id)); renderPills(); },
        color(c, el) {
            document.getElementById(CID + '-color').value = c;
            document.querySelectorAll('.' + CID + '-dot').forEach(d => d.style.borderColor = 'transparent');
            if (el) el.style.borderColor = '#fff';
        },
        enter() {
            const items = computeItems(currentQuery);
            if (!items.length) return;
            activateItem(highlight);
        },
        moveHighlight(delta) {
            const items = computeItems(currentQuery);
            if (!items.length) return;
            highlight = (highlight + delta + items.length) % items.length;
            updateActiveClass();
            showPaletteIfCreate();
        },
        backspacePopLast() {
            if (currentQuery) return false;
            // Remove the last added pill
            const arr = [...sel];
            if (!arr.length) return false;
            sel.delete(arr[arr.length - 1]);
            renderPills();
            renderList();
            return true;
        },
    };

    // Global dispatchers (defined once per page)
    window.tpAction = window.tpAction || function(cid, action, ...args) {
        window._tpActions[cid][action](...args);
    };
    window._tpActions = window._tpActions || {};
    window._tpActions[CID] = actions;

    window.tpKey = window.tpKey || function(event, cid) {
        const a = window._tpActions[cid];
        if (event.key === 'Enter') {
            event.preventDefault();
            a.enter();
        } else if (event.key === 'ArrowDown') {
            event.preventDefault();
            a.moveHighlight(1);
        } else if (event.key === 'ArrowUp') {
            event.preventDefault();
            a.moveHighlight(-1);
        } else if (event.key === 'Escape') {
            event.preventDefault();
            a.close();
        } else if (event.key === 'Backspace') {
            if (a.backspacePopLast()) event.preventDefault();
        }
    };

    // Close on outside click (desktop). Mobile uses backdrop.
    document.addEventListener('click', e => {
        if (!open) return;
        if (window.innerWidth <= 640) return; // handled by backdrop
        if (!root.contains(e.target)) actions.close();
    });

    renderPills();
})();
</script>
