<style>
.brand-opt input { display: none; }
.brand-opt-inner {
    cursor: pointer;
    border-radius: 7px;
    border: 2px solid transparent;
    padding: 3px;
    transition: border-color 0.15s, transform 0.12s;
    position: relative;
}
.brand-opt-inner:hover { transform: translateY(-1px); border-color: rgba(255,255,255,0.15); }
.brand-opt input:checked + .brand-opt-inner { border-color: var(--accent); box-shadow: 0 0 0 1px var(--accent); }
.brand-opt-inner::after {
    content: '';
    position: absolute;
    top: -2px; right: -2px;
    width: 8px; height: 8px;
    border-radius: 50%;
    background: var(--accent);
    opacity: 0;
    transition: opacity 0.15s;
}
.brand-opt input:checked + .brand-opt-inner::after { opacity: 1; }

/* "Sin logo" option */
.brand-opt-none-inner {
    cursor: pointer;
    border-radius: 7px;
    border: 2px solid transparent;
    padding: 3px;
    transition: border-color 0.15s;
    display: flex; align-items: center; justify-content: center;
    width: 50px; height: 34px;
    background: var(--surface2);
    border: 1.5px solid var(--border);
    font-size: 10px; color: var(--muted); font-weight: 600; letter-spacing: 0.04em;
}
</style>

<script>
const BRAND_DEFS = {
    digital: [
        { value: 'mercadopago', html: `<div style="width:44px;height:28px;border-radius:6px;background:linear-gradient(135deg,#009ee3,#0072bc);display:flex;align-items:center;justify-content:center;position:relative;overflow:hidden"><div style="position:absolute;top:-4px;right:-4px;width:22px;height:22px;border-radius:50%;background:rgba(255,255,255,0.09)"></div><span style="color:#fff;font-weight:900;font-size:12px;font-family:'Bricolage Grotesque',sans-serif;letter-spacing:-0.5px;position:relative">mp</span></div>`, label: 'MercadoPago' },
        { value: 'bbva',        html: `<div style="width:44px;height:28px;border-radius:6px;background:#004481;display:flex;align-items:center;justify-content:center;overflow:hidden;position:relative"><div style="position:absolute;bottom:-8px;right:-8px;width:28px;height:28px;border-radius:50%;background:rgba(255,255,255,0.05)"></div><span style="color:#fff;font-weight:800;font-size:9px;font-family:'Nunito',sans-serif;letter-spacing:0.8px;position:relative">BBVA</span></div>`, label: 'BBVA' },
        { value: 'provincia',   html: `<div style="width:44px;height:28px;border-radius:6px;background:linear-gradient(135deg,#006633,#004d26);display:flex;flex-direction:column;align-items:center;justify-content:center;gap:1px;overflow:hidden"><span style="color:rgba(255,255,255,0.65);font-weight:700;font-size:7px;font-family:'Nunito',sans-serif;letter-spacing:0.6px;line-height:1;text-transform:uppercase">BANCO</span><span style="color:#fff;font-weight:900;font-size:7.5px;font-family:'Nunito',sans-serif;letter-spacing:0.2px;line-height:1">PROVINCIA</span></div>`, label: 'Banco Provincia' },
    ],
    credit: [
        { value: 'visa',       html: `<div style="width:44px;height:28px;border-radius:6px;background:#1a1f71;display:flex;align-items:center;justify-content:center;position:relative;overflow:hidden"><div style="position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(90deg,#f7a600,#f0c030)"></div><span style="color:#fff;font-weight:900;font-style:italic;font-size:15px;font-family:Georgia,serif;letter-spacing:-1px;line-height:1;margin-top:1px">VISA</span></div>`, label: 'Visa' },
        { value: 'mastercard', html: `<div style="width:44px;height:28px;border-radius:6px;background:#1c1c1c;display:flex;align-items:center;justify-content:center"><div style="position:relative;width:26px;height:18px"><div style="position:absolute;left:0;top:50%;transform:translateY(-50%);width:18px;height:18px;border-radius:50%;background:#eb001b"></div><div style="position:absolute;right:0;top:50%;transform:translateY(-50%);width:18px;height:18px;border-radius:50%;background:#f79e1b;opacity:0.95"></div><div style="position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);width:7px;height:15px;background:#ff5f00;opacity:0.82"></div></div></div>`, label: 'Mastercard' },
    ],
};

function updateBrandPicker(type) {
    const picker  = document.getElementById('brand-picker');
    const options = document.getElementById('brand-options');
    const brands  = BRAND_DEFS[type] || [];

    if (!brands.length) {
        picker.style.display = 'none';
        // Clear hidden brand input
        const existing = document.querySelector('input[name="brand"]');
        if (existing && !existing.closest('.brand-opt')) existing.remove();
        return;
    }

    picker.style.display = '';
    const currentBrand = '{{ $currentBrand ?? '' }}';

    let html = `<label class="brand-opt" title="Sin logo">
        <input type="radio" name="brand" value="" ${!currentBrand || currentBrand === '' ? 'checked' : ''}>
        <div class="brand-opt-inner" style="display:flex;align-items:center;justify-content:center;width:44px;height:28px;background:var(--surface2);border:1.5px solid var(--border);border-radius:6px;">
            <svg width="14" height="14" fill="none" stroke="var(--muted)" stroke-width="1.6" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M4.93 4.93l14.14 14.14"/></svg>
        </div>
    </label>`;

    brands.forEach(b => {
        html += `<label class="brand-opt" title="${b.label}">
            <input type="radio" name="brand" value="${b.value}" ${currentBrand === b.value ? 'checked' : ''}>
            <div class="brand-opt-inner">${b.html}</div>
        </label>`;
    });

    options.innerHTML = html;
}
</script>
