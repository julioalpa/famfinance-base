@extends('layouts.app')

@section('title', 'Ítems de pago')

@section('content')

<div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 32px; flex-wrap: wrap; gap: 16px;">
    <div>
        <h1 class="font-display" style="font-size: 28px; font-weight: 800; letter-spacing: -0.03em; margin-bottom: 4px; color: var(--text);">
            Ítems de pago
        </h1>
        <div style="font-size: 13px; color: var(--muted); font-weight: 500;">
            Definí los pagos que hacés cada mes para llevarlos como checklist
        </div>
    </div>
    <div style="display: flex; gap: 10px; align-items: center;">
        <a href="{{ route('monthly-payments.index') }}" class="btn btn-ghost">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
            Ver checklist
        </a>
        <a href="{{ route('payment-items.create') }}" class="btn btn-primary">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
            Nuevo ítem
        </a>
    </div>
</div>

@if($items->isEmpty())
    <div class="card" style="text-align: center; padding: 60px 32px;">
        <div style="font-size: 40px; margin-bottom: 16px;">📋</div>
        <div style="font-size: 16px; font-weight: 700; color: var(--text); margin-bottom: 8px;">Sin ítems configurados</div>
        <div style="font-size: 13px; color: var(--muted); margin-bottom: 24px;">
            Agregá los pagos manuales que tenés que hacer cada mes: alquiler, internet, expensas, etc.
        </div>
        <a href="{{ route('payment-items.create') }}" class="btn btn-primary">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
            Agregar primer ítem
        </a>
    </div>
@else
    <div class="card">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Pago</th>
                    <th>Cuenta</th>
                    <th>Categoría</th>
                    <th>Día</th>
                    <th>Moneda</th>
                    <th>Estado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                <tr style="{{ !$item->is_active ? 'opacity: 0.55;' : '' }}">
                    <td style="font-weight: 700; font-size: 14px;">{{ $item->description }}</td>
                    <td>
                        <span class="badge badge-{{ $item->account->type }}">{{ $item->account->name }}</span>
                    </td>
                    <td>
                        @if($item->category)
                            <span style="font-size: 12px; color: var(--muted);">{{ $item->category->name }}</span>
                        @else
                            <span style="color: var(--border);">—</span>
                        @endif
                    </td>
                    <td style="font-size: 13px; color: var(--muted);">
                        {{ $item->day_of_month ? 'día ' . $item->day_of_month : '—' }}
                    </td>
                    <td>
                        <span class="badge" style="background: var(--surface2); color: var(--muted);">{{ $item->currency }}</span>
                    </td>
                    <td>
                        <form method="POST" action="{{ route('payment-items.toggle', $item) }}" style="display:inline;">
                            @csrf
                            <button type="submit"
                                    class="badge {{ $item->is_active ? 'badge-income' : '' }}"
                                    style="border: none; cursor: pointer; background: {{ $item->is_active ? 'rgba(45,216,112,0.12)' : 'var(--surface2)' }}; color: {{ $item->is_active ? 'var(--income)' : 'var(--muted)' }};"
                                    title="{{ $item->is_active ? 'Pausar' : 'Activar' }}">
                                {{ $item->is_active ? 'Activo' : 'Pausado' }}
                            </button>
                        </form>
                    </td>
                    <td>
                        <div style="display: flex; gap: 6px; justify-content: flex-end;">
                            <a href="{{ route('payment-items.edit', $item) }}" class="btn btn-ghost" style="padding: 6px 12px; font-size: 12px;">
                                Editar
                            </a>
                            <form method="POST" action="{{ route('payment-items.destroy', $item) }}"
                                  onsubmit="return confirm('¿Eliminar «{{ addslashes($item->description) }}»? También se eliminarán los registros mensuales.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger" style="padding: 6px 12px; font-size: 12px;">
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
