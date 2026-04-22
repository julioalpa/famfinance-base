{{--
  Partial: _brand_logo.blade.php
  Variables:
    $brand  — 'mercadopago' | 'bbva' | 'provincia' | 'visa' | 'mastercard' | null
    $type   — 'cash' | 'digital' | 'credit' | 'loan'
    $size   — 'sm' (36×23) | 'md' (44×28, default) | 'lg' (64×40)
--}}
@php
  $size  = $size  ?? 'md';
  $brand = $brand ?? null;
  $w = match($size) { 'sm' => 36, 'lg' => 64, default => 44 };
  $h = match($size) { 'sm' => 23, 'lg' => 40, default => 28 };
  $r = match($size) { 'sm' => 5,  'lg' => 8,  default => 6  };
  $base = "width:{$w}px;height:{$h}px;border-radius:{$r}px;display:flex;align-items:center;justify-content:center;flex-shrink:0;";
@endphp

@if($brand === 'mercadopago')
  {{-- MercadoPago: blue gradient + "mp" lowercase bold --}}
  <div style="{{ $base }}background:linear-gradient(135deg,#009ee3 0%,#0072bc 100%);overflow:hidden;position:relative;">
    <div style="position:absolute;top:-4px;right:-4px;width:{{ round($h*0.8) }}px;height:{{ round($h*0.8) }}px;border-radius:50%;background:rgba(255,255,255,0.09);"></div>
    <span style="color:#fff;font-weight:900;font-size:{{ round($h*0.43) }}px;font-family:'Bricolage Grotesque',sans-serif;letter-spacing:-0.5px;position:relative;z-index:1;">mp</span>
  </div>

@elseif($brand === 'bbva')
  {{-- BBVA: deep navy + "BBVA" spaced caps --}}
  <div style="{{ $base }}background:#004481;overflow:hidden;position:relative;">
    <div style="position:absolute;bottom:-{{ round($h*0.4) }}px;right:-{{ round($h*0.3) }}px;width:{{ round($h*1.1) }}px;height:{{ round($h*1.1) }}px;border-radius:50%;background:rgba(255,255,255,0.05);"></div>
    <span style="color:#fff;font-weight:800;font-size:{{ round($h*0.32) }}px;font-family:'Nunito',sans-serif;letter-spacing:0.8px;position:relative;z-index:1;">BBVA</span>
  </div>

@elseif($brand === 'provincia')
  {{-- Banco Provincia: forest green, stacked text --}}
  <div style="{{ $base }}background:linear-gradient(135deg,#006633 0%,#004d26 100%);overflow:hidden;flex-direction:column;gap:1px;">
    <span style="color:rgba(255,255,255,0.65);font-weight:700;font-size:{{ round($h*0.26) }}px;font-family:'Nunito',sans-serif;letter-spacing:0.6px;line-height:1;text-transform:uppercase;">BANCO</span>
    <span style="color:#fff;font-weight:900;font-size:{{ round($h*0.27) }}px;font-family:'Nunito',sans-serif;letter-spacing:0.2px;line-height:1;text-transform:uppercase;">PROVINCIA</span>
  </div>

@elseif($brand === 'visa')
  {{-- Visa: classic navy, italic serif wordmark --}}
  <div style="{{ $base }}background:#1a1f71;overflow:hidden;position:relative;">
    <div style="position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(90deg,#f7a600,#f0c030);"></div>
    <span style="color:#fff;font-weight:900;font-style:italic;font-size:{{ round($h*0.54) }}px;font-family:Georgia,serif;letter-spacing:-1px;line-height:1;margin-top:1px;">VISA</span>
  </div>

@elseif($brand === 'mastercard')
  {{-- Mastercard: dark bg, two overlapping circles --}}
  <div style="{{ $base }}background:#1c1c1c;overflow:hidden;">
    <div style="position:relative;width:{{ round($h*0.95) }}px;height:{{ round($h*0.65) }}px;">
      <div style="position:absolute;left:0;top:50%;transform:translateY(-50%);width:{{ round($h*0.65) }}px;height:{{ round($h*0.65) }}px;border-radius:50%;background:#eb001b;"></div>
      <div style="position:absolute;right:0;top:50%;transform:translateY(-50%);width:{{ round($h*0.65) }}px;height:{{ round($h*0.65) }}px;border-radius:50%;background:#f79e1b;mix-blend-mode:normal;opacity:0.95;"></div>
      {{-- overlap blending in the middle --}}
      <div style="position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);width:{{ round($h*0.25) }}px;height:{{ round($h*0.55) }}px;background:#ff5f00;opacity:0.85;"></div>
    </div>
  </div>

@elseif($type === 'cash')
  {{-- Efectivo: green tinted with banknote icon --}}
  <div style="{{ $base }}background:rgba(45,216,112,0.1);border:1.5px solid rgba(45,216,112,0.28);">
    <svg width="{{ round($w*0.5) }}" height="{{ round($h*0.65) }}" fill="none" stroke="#2dd870" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 22 14">
      <rect x="1" y="1" width="20" height="12" rx="2"/>
      <circle cx="11" cy="7" r="2.8"/>
      <path d="M1 4.5h2.5M18.5 4.5H21M1 9.5h2.5M18.5 9.5H21"/>
    </svg>
  </div>

@elseif($type === 'loan')
  {{-- Préstamo: red tinted with document + checkmark icon --}}
  <div style="{{ $base }}background:rgba(240,64,96,0.1);border:1.5px solid rgba(240,64,96,0.25);">
    <svg width="{{ round($w*0.4) }}" height="{{ round($h*0.75) }}" fill="none" stroke="#f04060" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 16 20">
      <path d="M9 1H3a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7z"/>
      <path d="M9 1v6h6"/>
      <path d="M5 12l2.5 2.5L11 10"/>
    </svg>
  </div>

@else
  {{-- Generic digital / credit: card outline icon --}}
  @php
    $strokeColor = match($type) {
      'credit'  => '#e8b840',
      'digital' => '#4e9bff',
      default   => '#6a6676',
    };
    $bg = match($type) {
      'credit'  => 'rgba(232,184,64,0.1)',
      'digital' => 'rgba(78,155,255,0.1)',
      default   => 'rgba(106,102,118,0.1)',
    };
    $border = match($type) {
      'credit'  => 'rgba(232,184,64,0.25)',
      'digital' => 'rgba(78,155,255,0.25)',
      default   => 'rgba(106,102,118,0.25)',
    };
  @endphp
  <div style="{{ $base }}background:{{ $bg }};border:1.5px solid {{ $border }};">
    <svg width="{{ round($w*0.52) }}" height="{{ round($h*0.6) }}" fill="none" stroke="{{ $strokeColor }}" stroke-width="1.6" stroke-linecap="round" viewBox="0 0 22 14">
      <rect x="1" y="1" width="20" height="12" rx="2"/>
      <path d="M1 5h20"/>
      <path d="M4 9.5h3M9.5 9.5h7.5"/>
    </svg>
  </div>
@endif
