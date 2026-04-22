@extends('layouts.app')

@section('title', 'Importar CSV')

@section('content')

<div style="max-width: 640px;">
    <div style="margin-bottom: 24px;">
        <h1 class="font-display" style="font-size: 24px; font-weight: 700; letter-spacing: -0.02em;">Importar movimientos</h1>
        <div style="font-size: 12px; color: var(--muted); margin-top: 3px;">Formato CSV: Categoría, Nota, Importe, Moneda, Tipo, Cuenta, Fecha</div>
    </div>

    {{-- Resultados del import --}}
    @if(session('results'))
    @php $r = session('results'); @endphp
    <div class="card" style="margin-bottom: 20px; border-color: var(--income);">
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 16px;">
            <svg width="18" height="18" fill="none" stroke="var(--income)" stroke-width="2" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            <span style="font-size: 14px; font-weight: 600; color: var(--income);">Importación completada</span>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr {{ ($r['unpairedTransfers'] ?? 0) > 0 ? '1fr' : '' }}; gap: 12px; margin-bottom: 16px;">
            <div style="background: var(--surface2); border-radius: 8px; padding: 14px; text-align: center;">
                <div class="font-display" style="font-size: 28px; font-weight: 700; color: var(--income);">{{ $r['imported'] }}</div>
                <div style="font-size: 11px; color: var(--muted); margin-top: 2px;">movimientos importados</div>
            </div>
            <div style="background: var(--surface2); border-radius: 8px; padding: 14px; text-align: center;">
                <div class="font-display" style="font-size: 28px; font-weight: 700; color: var(--muted);">{{ $r['skipped'] }}</div>
                <div style="font-size: 11px; color: var(--muted); margin-top: 2px;">filas salteadas</div>
            </div>
            @if(($r['unpairedTransfers'] ?? 0) > 0)
            <div style="background: rgba(240,160,48,0.08); border: 1px solid rgba(240,160,48,0.2); border-radius: 8px; padding: 14px; text-align: center;">
                <div class="font-display" style="font-size: 28px; font-weight: 700; color: var(--warn);">{{ $r['unpairedTransfers'] }}</div>
                <div style="font-size: 11px; color: var(--muted); margin-top: 2px;">transferencias sin emparejar</div>
            </div>
            @endif
        </div>

        @if(($r['unpairedTransfers'] ?? 0) > 0)
        <div style="background: rgba(240,160,48,0.06); border: 1px solid rgba(240,160,48,0.18); border-radius: 8px; padding: 12px 14px; margin-bottom: 16px; font-size: 12px; color: var(--warn);">
            <strong>¿Por qué no se emparejaron?</strong> Las transferencias se detectan en pares: dos filas con el mismo día y monto, una con monto negativo (origen) y otra positiva (destino). Si las filas no coinciden exactamente, no se pueden vincular.
        </div>
        @endif

        @if(count($r['createdCategories']) > 0)
        <div style="margin-bottom: 12px;">
            <div style="font-size: 11px; letter-spacing: 0.08em; text-transform: uppercase; color: var(--muted); margin-bottom: 6px;">
                Categorías creadas ({{ count($r['createdCategories']) }})
            </div>
            <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                @foreach($r['createdCategories'] as $cat)
                <span style="font-size: 11px; background: var(--surface2); border: 1px solid var(--border); border-radius: 4px; padding: 2px 8px; color: var(--text);">{{ $cat }}</span>
                @endforeach
            </div>
        </div>
        @endif

        @if(count($r['createdAccounts']) > 0)
        <div>
            <div style="font-size: 11px; letter-spacing: 0.08em; text-transform: uppercase; color: var(--muted); margin-bottom: 6px;">
                Cuentas creadas ({{ count($r['createdAccounts']) }}) — tipo: digital
            </div>
            <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                @foreach($r['createdAccounts'] as $acct)
                <span style="font-size: 11px; background: var(--surface2); border: 1px solid var(--border); border-radius: 4px; padding: 2px 8px; color: var(--text);">{{ $acct }}</span>
                @endforeach
            </div>
        </div>
        @endif
    </div>
    @endif

    {{-- Formulario de carga --}}
    <div class="card">
        <form method="POST" action="{{ route('import.store') }}" enctype="multipart/form-data">
            @csrf

            <div style="margin-bottom: 20px;">
                <label class="form-label">Archivo CSV *</label>
                <input type="file" name="file" accept=".csv,.txt"
                       style="display: block; width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 6px; background: var(--surface); color: var(--text); font-size: 13px; cursor: pointer;">
                @error('file') <div style="font-size:12px;color:var(--danger);margin-top:4px;">{{ $message }}</div> @enderror
            </div>

            <div style="background: var(--surface2); border-radius: 8px; padding: 14px; margin-bottom: 20px; font-size: 12px; color: var(--muted); line-height: 1.8;">
                <strong style="color: var(--text);">Formato esperado del CSV:</strong><br>
                Columnas: <code>Categoría, Nota, Importe, Moneda, Tipo, Cuenta, Fecha</code><br><br>
                <strong style="color: var(--text);">Tipos aceptados:</strong><br>
                · <code>Gastos</code> / <code>Ingresos</code> → se importan directamente<br>
                · <code>Transferencia</code> → se emparejan de a dos filas con igual día y monto; el monto negativo es el origen y el positivo el destino<br><br>
                <strong style="color: var(--text);">Fecha:</strong> <code>YYYY.MM.DD</code> o <code>16 abr 2026</code><br>
                Si una categoría o cuenta no existe, se crea automáticamente.
            </div>

            <div style="display: flex; justify-content: flex-end;">
                <button type="submit" class="btn btn-primary">Importar movimientos</button>
            </div>
        </form>
    </div>
</div>

@endsection
