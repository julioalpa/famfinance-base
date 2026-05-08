@extends('layouts.app')

@section('title', 'Tipo de cambio')

@section('content')

<div style="max-width: 700px;">
    <div style="margin-bottom: 28px;">
        <h1 class="font-display" style="font-size: 24px; font-weight: 700;">Tipo de cambio</h1>
        <div style="font-size: 12px; color: var(--muted); margin-top: 4px;">
            Ingreso manual USD → ARS para unificar reportes
        </div>
    </div>

    {{-- Formulario --}}
    <div class="card" style="margin-bottom: 24px;">
        <h2 class="font-display" style="font-size: 13px; font-weight: 600; margin-bottom: 16px;">Registrar tipo de cambio</h2>
        <form method="POST" action="{{ route('exchange-rates.store') }}" style="display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;">
            @csrf
            <div>
                <label class="form-label">1 USD = ? ARS *</label>
                <input type="number" name="rate" class="form-input"
                       placeholder="Ej: 1250.00"
                       step="0.01" min="0.01"
                       value="{{ old('rate') }}"
                       style="width: 160px;">
                @error('rate') <div style="font-size:11px;color:var(--danger);margin-top:3px;">{{ $message }}</div> @enderror
            </div>
            <div>
                <label class="form-label">Fecha *</label>
                <input type="date" name="date" class="form-input"
                       value="{{ old('date', today()->format('Y-m-d')) }}"
                       style="width: 160px;">
                @error('date') <div style="font-size:11px;color:var(--danger);margin-top:3px;">{{ $message }}</div> @enderror
            </div>
            <div style="flex: 1;">
                <label class="form-label">Notas</label>
                <input type="text" name="notes" class="form-input"
                       placeholder="Ej: Blue, oficial, CCL..."
                       value="{{ old('notes') }}">
            </div>
            <button type="submit" class="btn btn-primary">Guardar</button>
        </form>
    </div>

    {{-- Historial --}}
    <div class="card" style="padding: 0; overflow: hidden;">
        <div style="padding: 18px 20px; border-bottom: 1px solid var(--border);">
            <h2 class="font-display" style="font-size: 13px; font-weight: 600;">Historial</h2>
        </div>

        @if($rates->isEmpty())
            <div style="text-align: center; padding: 40px; color: var(--muted); font-size: 13px;">
                Sin registros todavía
            </div>
        @else
        @php
            $mkSort = function(string $col, string $label) use ($sortBy, $sortDir): string {
                $active = $sortBy === $col;
                $newDir = ($active && $sortDir === 'asc') ? 'desc' : 'asc';
                $url    = request()->fullUrlWithQuery(['sort' => $col, 'dir' => $newDir]);
                $upOp   = !$active ? '0.35' : ($sortDir === 'asc'  ? '1' : '0.2');
                $dnOp   = !$active ? '0.35' : ($sortDir === 'desc' ? '1' : '0.2');
                $aria   = $active ? ($sortDir === 'asc' ? 'ascending' : 'descending') : 'none';
                $cls    = $active ? 'sort-link sort-active' : 'sort-link';
                return "<a href=\"{$url}\" class=\"{$cls}\" aria-sort=\"{$aria}\">{$label}"
                    . "<svg width=\"8\" height=\"11\" viewBox=\"0 0 8 11\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"1.8\" stroke-linecap=\"round\" stroke-linejoin=\"round\" style=\"display:inline-block;vertical-align:middle;margin-left:4px;\">"
                    . "<path d=\"M1 4.5L4 1.5L7 4.5\" style=\"opacity:{$upOp}\"/>"
                    . "<path d=\"M1 6.5L4 9.5L7 6.5\" style=\"opacity:{$dnOp}\"/>"
                    . "</svg></a>";
            };
        @endphp
            <table class="data-table">
                <thead>
                    <tr>
                        <th>{!! $mkSort('date', 'Fecha') !!}</th>
                        <th>{!! $mkSort('rate', 'Tasa') !!}</th>
                        <th>Notas</th>
                        <th>Ingresado por</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rates as $rate)
                    <tr>
                        <td style="font-size: 13px;">{{ $rate->date->format('d/m/Y') }}</td>
                        <td>
                            <span style="font-size: 14px; font-weight: 600; color: var(--warn);">
                                1 USD = $ {{ number_format($rate->rate, 2, ',', '.') }}
                            </span>
                        </td>
                        <td style="font-size: 12px; color: var(--muted);">{{ $rate->notes ?? '—' }}</td>
                        <td style="font-size: 12px; color: var(--muted);">{{ $rate->user->name }}</td>
                        <td>
                            <form method="POST" action="{{ route('exchange-rates.destroy', $rate) }}"
                                  onsubmit="return confirm('¿Eliminar este tipo de cambio?')">
                                @csrf @method('DELETE')
                                <button type="submit" style="background:none; border:none; color:var(--danger); font-size:11px; cursor:pointer; font-family:'DM Mono',monospace;">
                                    Eliminar
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            @if($rates->hasPages())
            <div style="padding: 14px 20px; border-top: 1px solid var(--border);">
                {{ $rates->links() }}
            </div>
            @endif
        @endif
    </div>
</div>

@endsection
