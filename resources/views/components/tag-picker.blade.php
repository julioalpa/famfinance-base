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
    <div id="{{ $cid }}" style="position:relative;"
         data-field="{{ $name }}"
         data-all='@json($allTags->map(fn($t)=>["id"=>$t->id,"name"=>$t->name,"color"=>$t->color])->values())'
         data-selected='@json(array_map("intval",(array)$selectedIds))'>

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

        {{-- Dropdown --}}
        <div id="{{ $cid }}-drop"
             style="display:none;position:absolute;z-index:200;left:0;top:calc(100% + 6px);
                    background:var(--surface);border:1px solid var(--border);border-radius:12px;
                    width:270px;padding:10px;box-shadow:0 8px 32px rgba(0,0,0,0.4);">

            <input type="text"
                   id="{{ $cid }}-search"
                   class="form-input"
                   placeholder="Buscar etiqueta…"
                   style="margin-bottom:8px;font-size:12px;padding:7px 10px;"
                   oninput="tpAction('{{ $cid }}','filter',this.value)"
                   onkeydown="if(event.key==='Escape')tpAction('{{ $cid }}','close')">

            <div id="{{ $cid }}-list"
                 style="max-height:150px;overflow-y:auto;display:flex;flex-direction:column;gap:2px;margin-bottom:8px;">
            </div>

            <div style="border-top:1px solid var(--border);padding-top:8px;">
                <div style="font-size:11px;color:var(--muted);margin-bottom:6px;font-weight:600;">Crear nueva</div>
                <input type="text"
                       id="{{ $cid }}-new"
                       class="form-input"
                       placeholder="Nombre"
                       style="font-size:12px;padding:7px 10px;margin-bottom:7px;"
                       onkeydown="if(event.key==='Enter'){event.preventDefault();tpAction('{{ $cid }}','create');}">
                <div style="display:flex;flex-wrap:wrap;gap:4px;margin-bottom:7px;">
                    @foreach($presetColors as $i => $c)
                    <button type="button"
                            class="{{ $cid }}-dot"
                            data-color="{{ $c }}"
                            style="width:20px;height:20px;border-radius:50%;background:{{ $c }};
                                   border:2px solid {{ $i === 0 ? '#fff' : 'transparent' }};
                                   cursor:pointer;flex-shrink:0;"
                            onclick="tpAction('{{ $cid }}','color','{{ $c }}',this)">
                    </button>
                    @endforeach
                </div>
                <input type="hidden" id="{{ $cid }}-color" value="#f0a030">
                <button type="button"
                        id="{{ $cid }}-create-btn"
                        class="btn btn-primary"
                        style="font-size:11px;padding:6px 12px;width:100%;justify-content:center;"
                        onclick="tpAction('{{ $cid }}','create')">
                    Crear
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function(){
    const CID  = '{{ $cid }}';
    const root = document.getElementById(CID);
    const all  = JSON.parse(root.dataset.all);
    const sel  = new Set(JSON.parse(root.dataset.selected));
    let open   = false;

    function esc(s) {
        return String(s).replace(/[&<>"']/g, m =>
            ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[m]);
    }

    function render() {
        const pillsEl = document.getElementById(CID + '-pills');
        pillsEl.innerHTML = '';
        sel.forEach(id => {
            const t = all.find(x => x.id === id);
            if (!t) return;
            const s = document.createElement('span');
            s.style.cssText = `display:inline-flex;align-items:center;gap:4px;padding:3px 8px 3px 10px;
                border-radius:20px;font-size:11px;font-weight:700;
                background:${t.color}22;color:${t.color};border:1px solid ${t.color}55;`;
            s.innerHTML = `${esc(t.name)}<span
                onclick="tpAction('${CID}','remove',${id})"
                style="font-size:14px;opacity:0.6;cursor:pointer;line-height:1;margin-left:2px;padding:0 1px;">×</span>`;
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

    function filterList(q) {
        const listEl = document.getElementById(CID + '-list');
        listEl.innerHTML = '';
        const lq = q.toLowerCase();
        const filtered = all.filter(t => t.name.toLowerCase().includes(lq));
        if (!filtered.length) {
            listEl.innerHTML = '<div style="font-size:11px;color:var(--muted);padding:6px 8px;">Sin resultados</div>';
            return;
        }
        filtered.forEach(t => {
            const isSel = sel.has(t.id);
            const d = document.createElement('div');
            d.style.cssText = 'display:flex;align-items:center;gap:8px;padding:6px 8px;border-radius:7px;cursor:pointer;font-size:12px;transition:background 0.1s;';
            d.innerHTML = `<span style="width:10px;height:10px;border-radius:50%;background:${t.color};flex-shrink:0;"></span>
                <span style="flex:1;">${esc(t.name)}</span>
                ${isSel ? '<svg width="13" height="13" fill="none" stroke="var(--income)" stroke-width="2.8" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>' : ''}`;
            d.onmouseenter = () => d.style.background = 'var(--surface2)';
            d.onmouseleave = () => d.style.background = '';
            d.onclick = () => {
                if (isSel) sel.delete(t.id); else sel.add(t.id);
                render();
                actions.close();
            };
            listEl.appendChild(d);
        });
    }

    const actions = {
        toggle() {
            open = !open;
            document.getElementById(CID + '-drop').style.display = open ? 'block' : 'none';
            if (open) {
                filterList('');
                setTimeout(() => document.getElementById(CID + '-search').focus(), 50);
            }
        },
        close() {
            open = false;
            document.getElementById(CID + '-drop').style.display = 'none';
        },
        filter(q) { filterList(q); },
        remove(id) { sel.delete(Number(id)); render(); },
        color(c, el) {
            document.getElementById(CID + '-color').value = c;
            document.querySelectorAll('.' + CID + '-dot').forEach(d => d.style.borderColor = 'transparent');
            if (el) el.style.borderColor = '#fff';
        },
        async create() {
            const nameEl = document.getElementById(CID + '-new');
            const n = nameEl.value.trim();
            if (!n) { nameEl.focus(); return; }
            const c   = document.getElementById(CID + '-color').value;
            const btn = document.getElementById(CID + '-create-btn');
            btn.disabled = true; btn.textContent = '…';
            try {
                const resp = await fetch('/etiquetas', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ name: n, color: c }),
                });
                if (!resp.ok) throw 0;
                const tag = await resp.json();
                all.push(tag);
                sel.add(tag.id);
                nameEl.value = '';
                render();
                actions.close();
            } catch {
                alert('No se pudo crear la etiqueta. Intentá de nuevo.');
            } finally {
                btn.disabled = false; btn.textContent = 'Crear';
            }
        },
    };

    // Global dispatcher (defined once; each picker registers under its CID)
    window.tpAction = window.tpAction || function(cid, action, ...args) {
        window._tpActions[cid][action](...args);
    };
    window._tpActions = window._tpActions || {};
    window._tpActions[CID] = actions;

    // Close on outside click
    document.addEventListener('click', e => { if (!root.contains(e.target)) actions.close(); });

    render();
})();
</script>
