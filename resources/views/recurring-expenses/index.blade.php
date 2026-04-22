@extends('layouts.app')

@section('title', 'Gastos recurrentes')

@section('content')

{{-- Header --}}
<div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 28px; flex-wrap: wrap; gap: 16px;">
    <div>
        <h1 class="font-display" style="font-size: 28px; font-weight: 800; letter-spacing: -0.03em; margin-bottom: 4px;">Gastos recurrentes</h1>
        <div style="font-size: 13px; color: var(--muted); font-weight: 500;">
            Servicios, suscripciones y pagos que se repiten todos los meses
        </div>
    </div>
    <a href="{{ route('recurring-expenses.create') }}" class="btn btn-primary">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
        Agregar gasto recurrente
    </a>
</div>

{{-- Resumen total --}}
@if($recurring->where('is_active', true)->isNotEmpty())
<div style="background: var(--accent-dim); border: 1px solid rgba(240,160,48,0.25); border-radius: 14px; padding: 16px 22px; margin-bottom: 24px; display: flex; align-items: center; gap: 20px; flex-wrap: wrap;">
    <div style="display:flex; align-items:center; gap:10px; flex:1; min-width:0;">
        <div style="width:38px; height:38px; background:var(--accent); border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
            <svg width="18" height="18" fill="none" stroke="#0c0804" stroke-width="2.5" viewBox="0 0 24 24">
                <path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
            </svg>
        </div>
        <div>
            <div style="font-size:11px; letter-spacing:0.08em; text-transform:uppercase; color:var(--muted); font-weight:700;">Total mensual activo</div>
            <div class="font-display" style="font-size:22px; font-weight:800; letter-spacing:-0.03em; color:var(--accent);">
                $ {{ number_format($totalActive, 2, ',', '.') }} ARS
            </div>
        </div>
    </div>
    <div style="font-size:12px; color:var(--muted); font-weight:500;">
        {{ $recurring->where('is_active', true)->count() }} débito{{ $recurring->where('is_active', true)->count() !== 1 ? 's' : '' }} activo{{ $recurring->where('is_active', true)->count() !== 1 ? 's' : '' }}
    </div>
</div>
@endif

{{-- Tabla --}}
@if($recurring->isEmpty())
    <div class="card" style="text-align: center; padding: 60px 24px;">
        <div style="width:56px; height:56px; background:var(--surface2); border-radius:14px; display:flex; align-items:center; justify-content:center; margin:0 auto 16px;">
            <svg width="24" height="24" fill="none" stroke="var(--muted)" stroke-width="1.5" viewBox="0 0 24 24">
                <path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
            </svg>
        </div>
        <div class="font-display" style="font-size:16px; font-weight:700; margin-bottom:8px;">Sin débitos fijos</div>
        <div style="font-size:13px; color:var(--muted); margin-bottom:20px;">
            Agregá tus gastos recurrentes para verlos en el dashboard cada mes.
        </div>
        <a href="{{ route('recurring-expenses.create') }}" class="btn btn-primary" style="display:inline-flex;">
            Crear primer débito fijo
        </a>
    </div>
@else
    <div class="card" style="padding: 0; overflow: hidden;">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 56px; text-align:center;">Día</th>
                    <th>Descripción</th>
                    <th>Cuenta</th>
                    <th>Categoría</th>
                    <th style="text-align:right;">Monto</th>
                    <th style="text-align:center;">Estado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($recurring as $re)
                <tr style="{{ !$re->is_active ? 'opacity: 0.5;' : '' }}">
                    {{-- Día del mes --}}
                    <td style="text-align:center; padding: 12px 8px;">
                        <div style="
                            width: 36px; height: 36px;
                            background: {{ $re->is_active ? 'var(--accent-dim)' : 'var(--surface2)' }};
                            border: 1px solid {{ $re->is_active ? 'rgba(240,160,48,0.3)' : 'var(--border)' }};
                            border-radius: 9px;
                            display: flex; align-items: center; justify-content: center;
                            margin: 0 auto;
                        ">
                            <span class="font-display" style="font-size:13px; font-weight:800; color: {{ $re->is_active ? 'var(--accent)' : 'var(--muted)' }};">
                                {{ $re->day_of_month }}
                            </span>
                        </div>
                    </td>

                    {{-- Descripción --}}
                    <td>
                        <div style="font-size:14px; font-weight:600; color:var(--text);">{{ $re->description }}</div>
                        @if($re->notes)
                            <div style="font-size:11px; color:var(--muted); margin-top:2px;">{{ Str::limit($re->notes, 60) }}</div>
                        @endif
                    </td>

                    {{-- Cuenta --}}
                    <td>
                        <span class="badge badge-{{ $re->account->type }}">{{ $re->account->name }}</span>
                    </td>

                    {{-- Categoría --}}
                    <td style="font-size:13px; color:var(--muted);">
                        {{ $re->category?->name ?? '—' }}
                    </td>

                    {{-- Monto --}}
                    <td style="text-align:right; font-weight:700; font-size:14px; white-space:nowrap;" class="amount-expense">
                        {{ $re->currency === 'USD' ? 'US$' : '$' }} {{ number_format($re->amount, 2, ',', '.') }}
                    </td>

                    {{-- Estado --}}
                    <td style="text-align:center;">
                        @if($re->is_active)
                            <span class="badge badge-income">Activo</span>
                        @else
                            <span class="badge" style="background:var(--surface2); color:var(--muted);">Pausado</span>
                        @endif
                    </td>

                    {{-- Acciones --}}
                    <td style="white-space:nowrap; text-align:right;">
                        <div style="display:flex; gap:4px; justify-content:flex-end; align-items:center;">
                            <a href="{{ route('recurring-expenses.edit', $re) }}"
                               class="btn btn-ghost" style="padding:6px 12px; font-size:12px;">
                                Editar
                            </a>

                            {{-- Toggle activo/pausado --}}
                            <form method="POST" action="{{ route('recurring-expenses.toggle', $re) }}" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn btn-ghost"
                                        style="padding:6px 12px; font-size:12px; color: {{ $re->is_active ? 'var(--warn)' : 'var(--income)' }}; border-color: {{ $re->is_active ? 'rgba(232,184,64,0.3)' : 'rgba(45,216,112,0.3)' }};">
                                    {{ $re->is_active ? 'Pausar' : 'Activar' }}
                                </button>
                            </form>

                            {{-- Eliminar --}}
                            <form method="POST" action="{{ route('recurring-expenses.destroy', $re) }}" style="display:inline;"
                                  onsubmit="return confirm('¿Eliminar este débito fijo?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger" style="padding:6px 12px; font-size:12px;">
                                    Eliminar
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

@endsection
