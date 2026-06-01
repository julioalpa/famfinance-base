@extends('layouts.app')

@section('title', 'Plan de cuotas — ' . $account->name)

@section('content')
<style>
.ls-container { max-width: 600px; margin: 0 auto; }

.ls-title {
    font-family: 'Bricolage Grotesque', sans-serif;
    font-size: 22px;
    font-weight: 800;
    letter-spacing: -0.03em;
    color: var(--text);
    margin-bottom: 4px;
}

.ls-sub { font-size: 13px; color: var(--muted); margin-bottom: 28px; }
.ls-sub a { color: var(--muted); }

.ls-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 24px;
    margin-bottom: 16px;
}

.ls-field { margin-bottom: 18px; }
.ls-field:last-child { margin-bottom: 0; }

.ls-label {
    display: block;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: var(--muted);
    margin-bottom: 7px;
    font-family: 'Bricolage Grotesque', sans-serif;
}

.ls-input {
    width: 100%;
    background: var(--surface2);
    border: 1px solid var(--border);
    border-radius: 10px;
    color: var(--text);
    font-family: 'Nunito', sans-serif;
    font-size: 14px;
    font-weight: 600;
    padding: 11px 14px;
    transition: border-color 0.15s;
}

.ls-input:focus {
    outline: none;
    border-color: rgba(78,155,255,0.5);
    background: var(--surface3);
}

.ls-helper { font-size: 11px; color: var(--muted); margin-top: 5px; }

.ls-preview {
    background: var(--surface2);
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 14px 16px;
    margin-top: 16px;
    display: none;
}

.ls-preview.visible { display: block; }

.ls-preview-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 5px 0;
    font-size: 13px;
    border-bottom: 1px solid rgba(40,40,52,0.5);
}

.ls-preview-row:last-child { border-bottom: none; }
.ls-preview-label { color: var(--muted); font-weight: 600; }
.ls-preview-val { font-family: 'Bricolage Grotesque', sans-serif; font-weight: 800; color: var(--text); }

/* Existing installments table */
.cuotas-table { width: 100%; border-collapse: collapse; }
.cuotas-table th {
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--muted);
    padding: 0 8px 10px;
    text-align: left;
    border-bottom: 1px solid var(--border);
}
.cuotas-table td {
    padding: 9px 8px;
    border-bottom: 1px solid rgba(40,40,52,0.4);
    font-size: 13px;
    vertical-align: middle;
}
.cuotas-table tr:last-child td { border-bottom: none; }

.status-dot {
    width: 7px; height: 7px; border-radius: 50%;
    display: inline-block; margin-right: 6px; vertical-align: middle;
}
</style>

<div class="ls-container">
    <div class="ls-sub">
        <a href="{{ route('accounts.show', $account) }}">← {{ $account->name }}</a>
        <span style="margin: 0 6px; color: var(--border);">·</span>
        <span>Plan de cuotas</span>
    </div>

    <div class="ls-title">
        @if($installments->isEmpty()) Configurar plan @else Modificar plan @endif
        <span style="color:var(--accent2);">de cuotas</span>
    </div>
    <p class="ls-sub" style="margin-bottom:20px;">
        {{ $account->name }}
        @if($account->initial_balance)
            · Capital: {{ $account->currency === 'USD' ? 'US$' : '$' }} {{ number_format($account->initial_balance, 0, ',', '.') }}
        @endif
    </p>

    {{-- Paid installments warning --}}
    @php $paidCount = $installments->where('is_paid', true)->count(); @endphp
    @if($paidCount > 0)
    <div style="padding:12px 16px;background:rgba(78,155,255,0.08);border:1px solid rgba(78,155,255,0.2);border-radius:10px;font-size:13px;color:var(--accent2);margin-bottom:20px;">
        <strong>{{ $paidCount }} cuota{{ $paidCount > 1 ? 's' : '' }}</strong> ya pagada{{ $paidCount > 1 ? 's' : '' }} — se conservan. Solo se reemplazarán las pendientes.
    </div>
    @endif

    {{-- Form --}}
    <div class="ls-card">
        <form method="POST" action="{{ route('loans.store-schedule', $account) }}" id="scheduleForm">
            @csrf

            <div class="ls-field">
                <label class="ls-label" for="installments_count">Cantidad de cuotas pendientes a generar</label>
                <input type="number"
                       name="installments_count"
                       id="installments_count"
                       class="ls-input"
                       inputmode="numeric"
                       min="1" max="600"
                       value="{{ old('installments_count') }}"
                       placeholder="Ej: 24"
                       required>
                @if($paidCount > 0)
                <div class="ls-helper">Las cuotas se numerarán desde la #{{ $paidCount + 1 }}</div>
                @endif
            </div>

            <div class="ls-field">
                <label class="ls-label" for="installment_amount">Importe por cuota</label>
                <div style="position:relative;">
                    <span style="position:absolute;left:14px;top:50%;transform:translateY(-50%);font-size:13px;color:var(--muted);font-weight:700;">
                        {{ $account->currency === 'USD' ? 'US$' : '$' }}
                    </span>
                    <input type="number"
                           name="installment_amount"
                           id="installment_amount"
                           class="ls-input"
                           inputmode="decimal"
                           style="padding-left:36px;"
                           min="0.01" step="0.01"
                           value="{{ old('installment_amount', $account->initial_balance && !$installments->isEmpty() ? null : ($account->initial_balance ? round($account->initial_balance, 2) : null)) }}"
                           placeholder="0,00"
                           required>
                </div>
                @if($account->initial_balance)
                <div class="ls-helper">
                    Capital total: {{ $account->currency === 'USD' ? 'US$' : '$' }} {{ number_format($account->initial_balance, 0, ',', '.') }}
                    <button type="button" onclick="autoFillAmount()" style="background:none;border:none;color:var(--accent2);font-size:11px;cursor:pointer;font-family:inherit;padding:0 0 0 6px;text-decoration:underline;">
                        Dividir en cuotas
                    </button>
                </div>
                @endif
            </div>

            <div class="ls-field">
                <label class="ls-label" for="first_due_date">Fecha primera cuota {{ $paidCount > 0 ? 'pendiente' : '' }}</label>
                <input type="date"
                       name="first_due_date"
                       id="first_due_date"
                       class="ls-input"
                       value="{{ old('first_due_date', now()->addMonth()->startOfMonth()->format('Y-m-d')) }}"
                       required>
            </div>

            {{-- Live preview --}}
            <div class="ls-preview" id="preview">
                <div style="font-size:10px;font-weight:800;letter-spacing:0.1em;text-transform:uppercase;color:var(--muted);margin-bottom:10px;font-family:'Bricolage Grotesque',sans-serif;">Vista previa</div>
                <div class="ls-preview-row">
                    <span class="ls-preview-label">Total a pagar</span>
                    <span class="ls-preview-val c-expense" id="previewTotal">—</span>
                </div>
                <div class="ls-preview-row">
                    <span class="ls-preview-label">Primera cuota</span>
                    <span class="ls-preview-val" id="previewFirst">—</span>
                </div>
                <div class="ls-preview-row">
                    <span class="ls-preview-label">Última cuota</span>
                    <span class="ls-preview-val" id="previewLast">—</span>
                </div>
            </div>

            @if($errors->any())
            <div style="margin-top:14px;padding:10px 14px;background:rgba(240,64,96,0.08);border:1px solid rgba(240,64,96,0.2);border-radius:10px;font-size:13px;color:var(--expense);">
                @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
            </div>
            @endif

            <div style="display:flex;justify-content:space-between;align-items:center;margin-top:22px;">
                <a href="{{ route('accounts.show', $account) }}" class="btn btn-ghost" style="color:var(--muted);">Cancelar</a>
                <button type="submit" class="btn btn-primary">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                    Guardar plan
                </button>
            </div>
        </form>
    </div>

    {{-- Existing schedule overview --}}
    @if($installments->isNotEmpty())
    <div class="ls-card" style="border-color:rgba(78,155,255,0.15);">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
            <div style="font-family:'Bricolage Grotesque',sans-serif;font-size:14px;font-weight:800;color:var(--text);">
                Plan actual — {{ $installments->count() }} cuotas
            </div>
            <form method="POST" action="{{ route('loans.destroy-schedule', $account) }}"
                  onsubmit="return confirm('¿Eliminar las cuotas pendientes? Las ya pagadas se conservan.')">
                @csrf @method('DELETE')
                <button type="submit" style="background:none;border:none;font-size:12px;color:var(--danger);cursor:pointer;font-family:inherit;">
                    Eliminar pendientes
                </button>
            </form>
        </div>

        <div style="max-height:340px;overflow-y:auto;">
            <table class="cuotas-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Vencimiento</th>
                        <th style="text-align:right;">Importe</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($installments as $inst)
                    <tr style="{{ $inst->is_paid ? 'opacity:0.5;' : '' }}">
                        <td style="font-weight:800;color:var(--muted);font-size:12px;">{{ $inst->installment_number }}</td>
                        <td style="color:{{ $inst->isOverdue() ? 'var(--expense)' : 'var(--text)' }};">
                            {{ $inst->due_date->format('d/m/Y') }}
                            @if($inst->isOverdue())<span style="font-size:10px;margin-left:4px;color:var(--expense);font-weight:700;">VENCIDA</span>@endif
                        </td>
                        <td style="text-align:right;font-family:'Bricolage Grotesque',sans-serif;font-weight:800;color:{{ $inst->is_paid ? 'var(--muted)' : 'var(--expense)' }};">
                            {{ $account->currency === 'USD' ? 'US$' : '$' }} {{ number_format($inst->amount, 0, ',', '.') }}
                        </td>
                        <td>
                            @if($inst->is_paid)
                                <span style="font-size:10px;font-weight:800;color:var(--income);">✓ Pagada</span>
                                @if($inst->paid_at)
                                    <span style="font-size:10px;color:var(--muted);margin-left:4px;">{{ $inst->paid_at->format('d/m') }}</span>
                                @endif
                            @else
                                <span style="font-size:10px;color:var(--accent2);">Pendiente</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>

<script>
const INITIAL_BALANCE = {{ $account->initial_balance ?? 'null' }};
const CURRENCY = '{{ $account->currency === "USD" ? "US$" : "$" }}';

function fmt(n) {
    return CURRENCY + ' ' + parseFloat(n).toLocaleString('es-AR', {minimumFractionDigits: 0, maximumFractionDigits: 2});
}

function autoFillAmount() {
    const count = parseInt(document.getElementById('installments_count').value);
    if (!INITIAL_BALANCE || !count || count < 1) return;
    document.getElementById('installment_amount').value = (INITIAL_BALANCE / count).toFixed(2);
    updatePreview();
}

function updatePreview() {
    const count  = parseInt(document.getElementById('installments_count').value);
    const amount = parseFloat(document.getElementById('installment_amount').value);
    const date   = document.getElementById('first_due_date').value;

    const preview = document.getElementById('preview');

    if (!count || !amount || !date) {
        preview.classList.remove('visible');
        return;
    }

    preview.classList.add('visible');
    document.getElementById('previewTotal').textContent = fmt(count * amount);

    const first = new Date(date + 'T00:00:00');
    const last  = new Date(date + 'T00:00:00');
    last.setMonth(last.getMonth() + count - 1);

    const opts = {day:'2-digit', month:'2-digit', year:'numeric'};
    document.getElementById('previewFirst').textContent = first.toLocaleDateString('es-AR', opts);
    document.getElementById('previewLast').textContent  = last.toLocaleDateString('es-AR', opts);
}

document.getElementById('installments_count')?.addEventListener('input', updatePreview);
document.getElementById('installment_amount')?.addEventListener('input', updatePreview);
document.getElementById('first_due_date')?.addEventListener('change', updatePreview);

updatePreview();
</script>
@endsection
