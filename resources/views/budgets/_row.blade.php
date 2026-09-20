{{-- Fila de un presupuesto (mensual o anual). Espera $row. --}}
@php $cat = $row['category']; @endphp
<div class="bdg-row">
    <div style="min-width: 0;">
        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px; flex-wrap: wrap;">
            @include('categories._icon', ['icon' => $cat->icon, 'color' => $cat->color, 'type' => $cat->type ?? 'expense', 'size' => 'xs'])
            <span style="font-size: 14px; font-weight: 600; color: var(--text);">{{ $cat->name }}</span>
            @if($row['is_annual'])
                <span class="bdg-badge" style="background: rgba(100,210,255,0.14); color: var(--accent2);">Anual {{ $currentYear }}</span>
            @endif
            @if($row['pct'] !== null)
                <span class="bdg-badge bdg-badge-{{ $row['status'] }}">
                    @if($row['status'] === 'over') Excedido @else {{ $row['pct'] }}% @endif
                </span>
            @endif
        </div>
        <div class="bdg-bar-track" style="position: relative;">
            <div class="bdg-bar-fill bdg-fill-{{ $row['status'] }}" style="width: {{ $row['pct'] === null ? 0 : min($row['pct'], 100) }}%;"></div>
            {{-- Marcador de ritmo esperado para anuales (dónde deberías ir hoy) --}}
            @if($row['is_annual'] && $row['expected_pct'] !== null)
                <div title="Ritmo esperado a esta altura del año: {{ $row['expected_pct'] }}%"
                     style="position:absolute; top:-2px; bottom:-2px; width:2px; background:var(--text); opacity:0.5; left: {{ min($row['expected_pct'], 100) }}%;"></div>
            @endif
        </div>
        <div style="display: flex; justify-content: space-between; font-size: 12px; color: var(--muted); margin-top: 6px; gap: 10px; flex-wrap: wrap;">
            <span>Gastado{{ $row['is_annual'] ? ' en ' . $currentYear : ' este mes' }}: <strong style="color: var(--text);">$ {{ number_format($row['spent'], 0, ',', '.') }}</strong></span>
            <span>
                @if($row['remaining'] !== null && $row['remaining'] >= 0)
                    Queda $ {{ number_format($row['remaining'], 0, ',', '.') }}
                @else
                    Excedido $ {{ number_format(abs($row['remaining']), 0, ',', '.') }}
                @endif
                · Límite {{ $row['is_annual'] ? 'anual' : 'mensual' }} $ {{ number_format($row['limit_ars'], 0, ',', '.') }}
                @if($row['currency'] === 'USD') <span title="Presupuesto cargado en USD">(USD {{ number_format($row['amount'], 0, ',', '.') }})</span> @endif
            </span>
        </div>
    </div>
    <div class="bdg-inline-form">
        <form method="POST" action="{{ route('budgets.upsert') }}" class="bdg-inline-form">
            @csrf
            <input type="hidden" name="category_id" value="{{ $cat->id }}">
            <input type="number" name="amount" class="form-input" inputmode="decimal"
                   step="0.01" min="0" value="{{ $row['amount'] }}" aria-label="Monto del presupuesto para {{ $cat->name }}">
            <select name="currency" class="form-select" style="width: auto; padding: 6px 26px 6px 8px;">
                <option value="ARS" @selected($row['currency'] === 'ARS')>ARS</option>
                <option value="USD" @selected($row['currency'] === 'USD')>USD</option>
            </select>
            <select name="period" class="form-select" style="width: auto; padding: 6px 26px 6px 8px;">
                <option value="monthly" @selected($row['period'] === 'monthly')>Mensual</option>
                <option value="annual"  @selected($row['period'] === 'annual')>Anual</option>
            </select>
            <button type="submit" class="btn btn-ghost" style="padding: 6px 12px; font-size: 12px;">Guardar</button>
        </form>
        <form method="POST" action="{{ route('budgets.destroy', $row['budget_id']) }}"
              onsubmit="return confirm('¿Eliminar el presupuesto de {{ $cat->name }}?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-ghost" style="padding: 6px 10px; font-size: 12px; color: var(--expense);" title="Eliminar presupuesto">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
            </button>
        </form>
    </div>
</div>
