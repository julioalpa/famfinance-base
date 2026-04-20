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

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px;">
            <div style="background: var(--surface2); border-radius: 8px; padding: 14px; text-align: center;">
                <div class="font-display" style="font-size: 28px; font-weight: 700; color: var(--income);">{{ $r['imported'] }}</div>
                <div style="font-size: 11px; color: var(--muted); margin-top: 2px;">movimientos importados</div>
            </div>
            <div style="background: var(--surface2); border-radius: 8px; padding: 14px; text-align: center;">
                <div class="font-display" style="font-size: 28px; font-weight: 700; color: var(--muted);">{{ $r['skipped'] }}</div>
                <div style="font-size: 11px; color: var(--muted); margin-top: 2px;">filas salteadas</div>
            </div>
        </div>

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

            <div style="background: var(--surface2); border-radius: 8px; padding: 14px; margin-bottom: 20px; font-size: 12px; color: var(--muted); line-height: 1.7;">
                <strong style="color: var(--text);">Formato esperado del CSV:</strong><br>
                Columnas: <code>Categoría, Nota, Importe, Moneda, Tipo, Cuenta, Fecha</code><br>
                Tipo aceptado: <code>Gastos</code> o <code>Ingresos</code> (las <em>Transferencias</em> se saltean)<br>
                Fecha: <code>YYYY.MM.DD</code><br>
                Si una categoría o cuenta no existe, se crea automáticamente.
            </div>

            <div style="display: flex; justify-content: flex-end;">
                <button type="submit" class="btn btn-primary">Importar movimientos</button>
            </div>
        </form>
    </div>
</div>

@endsection
