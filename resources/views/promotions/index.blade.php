@extends('layouts.app')

@section('title', 'Promociones y descuentos')

@section('content')

{{-- Alertas de vencimiento próximo --}}
@php $alerts = $promotions->filter(fn($p) => $p->needsAlert()); @endphp
@if($alerts->isNotEmpty())
    <div style="margin-bottom: 24px; display: flex; flex-direction: column; gap: 10px;">
        @foreach($alerts as $promo)
            @php $days = $promo->daysUntilExpiry(); @endphp
            <div style="background: rgba(232,184,64,0.08); border: 1px solid rgba(232,184,64,0.35); border-radius: 12px; padding: 14px 18px; display: flex; align-items: center; gap: 14px; flex-wrap: wrap;">
                <svg width="18" height="18" fill="none" stroke="var(--warn)" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink: 0;">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                    <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
                <div style="flex: 1; min-width: 180px;">
                    <span style="font-size: 14px; font-weight: 700; color: var(--warn);">
                        {{ $days === 0 ? 'Hoy vence' : "Vence en {$days} " . ($days === 1 ? 'día' : 'días') }}:
                    </span>
                    <span style="font-size: 14px; color: var(--text); margin-left: 4px;">{{ $promo->name }}</span>
                    @if($promo->provider)
                        <span style="font-size: 12px; color: var(--muted); margin-left: 6px;">({{ $promo->provider }})</span>
                    @endif
                    <div style="font-size: 12px; color: var(--muted); margin-top: 2px;">
                        Vence el {{ $promo->expires_at->format('d/m/Y') }} — descuento de {{ $promo->discountLabel() }}
                    </div>
                </div>
                <form method="POST" action="{{ route('promotions.dismiss-alert', $promo) }}">
                    @csrf
                    <button type="submit" class="btn btn-ghost" style="padding: 6px 14px; font-size: 12px; border-color: rgba(232,184,64,0.3); color: var(--warn);">
                        Marcar como leído
                    </button>
                </form>
            </div>
        @endforeach
    </div>
@endif

<div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 32px; flex-wrap: wrap; gap: 16px;">
    <div>
        <h1 class="font-display" style="font-size: 28px; font-weight: 800; letter-spacing: -0.03em; margin-bottom: 4px; color: var(--text);">
            Promociones
        </h1>
        <div style="font-size: 13px; color: var(--muted); font-weight: 500;">
            Seguí tus descuentos y promociones. Te avisamos antes de que venzan.
        </div>
    </div>
    <a href="{{ route('promotions.create') }}" class="btn btn-primary">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
        Nueva promoción
    </a>
</div>

@if($promotions->isEmpty())
    <div class="card" style="text-align: center; padding: 60px 32px;">
        <div style="font-size: 40px; margin-bottom: 16px;">🏷️</div>
        <div style="font-size: 16px; font-weight: 700; color: var(--text); margin-bottom: 8px;">Sin promociones registradas</div>
        <div style="font-size: 13px; color: var(--muted); margin-bottom: 24px;">
            Registrá tus descuentos y promociones para recibir avisos antes de que venzan.
        </div>
        <a href="{{ route('promotions.create') }}" class="btn btn-primary">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
            Agregar primera promoción
        </a>
    </div>
@else
    <div style="display: flex; flex-direction: column; gap: 12px;">
        @foreach($promotions as $promo)
            @php
                $expired  = $promo->isExpired();
                $soon     = !$expired && $promo->isExpiringSoon();
                $days     = $promo->daysUntilExpiry();
                $inactive = !$promo->is_active;
            @endphp
            <div class="card" style="display: flex; align-items: center; gap: 20px; flex-wrap: wrap; padding: 20px 24px; {{ $expired || $inactive ? 'opacity: 0.6;' : '' }} {{ $soon && !$promo->alerted_at ? 'border-color: rgba(232,184,64,0.4);' : '' }}">

                {{-- Icono / badge de estado --}}
                <div style="flex-shrink: 0; width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center;
                    background: {{ $expired ? 'var(--surface2)' : ($soon ? 'rgba(232,184,64,0.12)' : 'rgba(45,216,112,0.1)') }};">
                    <svg width="20" height="20" fill="none" stroke="{{ $expired ? 'var(--muted)' : ($soon ? 'var(--warn)' : 'var(--income)') }}" stroke-width="1.8" viewBox="0 0 24 24">
                        <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/>
                        <circle cx="7" cy="7" r="1.5" fill="{{ $expired ? 'var(--muted)' : ($soon ? 'var(--warn)' : 'var(--income)') }}" stroke="none"/>
                    </svg>
                </div>

                {{-- Info principal --}}
                <div style="flex: 1; min-width: 200px;">
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px; flex-wrap: wrap;">
                        <span style="font-size: 15px; font-weight: 700; color: var(--text);">{{ $promo->name }}</span>
                        @if($promo->provider)
                            <span style="font-size: 11px; color: var(--muted); font-weight: 600; background: var(--surface2); padding: 2px 8px; border-radius: 6px;">
                                {{ $promo->provider }}
                            </span>
                        @endif
                        @if($expired)
                            <span style="font-size: 11px; font-weight: 700; color: var(--expense); background: rgba(240,64,96,0.1); padding: 2px 8px; border-radius: 6px;">Vencida</span>
                        @elseif($inactive)
                            <span style="font-size: 11px; font-weight: 700; color: var(--muted); background: var(--surface2); padding: 2px 8px; border-radius: 6px;">Inactiva</span>
                        @elseif($soon && !$promo->alerted_at)
                            <span style="font-size: 11px; font-weight: 700; color: var(--warn); background: rgba(232,184,64,0.12); padding: 2px 8px; border-radius: 6px;">
                                ⚠ Vence en {{ $days }} {{ $days === 1 ? 'día' : 'días' }}
                            </span>
                        @elseif($soon)
                            <span style="font-size: 11px; font-weight: 700; color: var(--muted); background: var(--surface2); padding: 2px 8px; border-radius: 6px;">
                                Vence en {{ $days }} {{ $days === 1 ? 'día' : 'días' }}
                            </span>
                        @else
                            <span style="font-size: 11px; font-weight: 700; color: var(--income); background: rgba(45,216,112,0.1); padding: 2px 8px; border-radius: 6px;">Activa</span>
                        @endif
                    </div>
                    <div style="font-size: 12px; color: var(--muted); display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                        @if($promo->paymentItem)
                            <span>
                                <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align: middle; margin-right: 3px;"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                                {{ $promo->paymentItem->description }}
                            </span>
                        @endif
                        <span>
                            <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align: middle; margin-right: 3px;"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                            Vence {{ $promo->expires_at->format('d/m/Y') }}
                        </span>
                        @if($promo->starts_at)
                            <span>Desde {{ $promo->starts_at->format('d/m/Y') }}</span>
                        @endif
                        <span>Recordatorio {{ $promo->reminder_days_before }}d antes</span>
                    </div>
                </div>

                {{-- Descuento --}}
                <div style="text-align: right; flex-shrink: 0;">
                    <div style="font-size: 22px; font-weight: 800; font-family: 'Bricolage Grotesque', sans-serif; color: {{ $expired ? 'var(--muted)' : 'var(--income)' }}; letter-spacing: -0.03em;">
                        {{ $promo->discountLabel() }}
                    </div>
                    @if($promo->original_amount !== null)
                        <div style="font-size: 11px; color: var(--muted);">
                            de ${{ number_format((float)$promo->original_amount, 2, ',', '.') }} {{ $promo->currency }}
                            @if($promo->discountedAmount() !== null)
                                → ${{ $promo->discountedAmount() }}
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Acciones --}}
                <div style="display: flex; gap: 6px; flex-shrink: 0; align-items: center;">
                    @if($promo->needsAlert())
                        <form method="POST" action="{{ route('promotions.dismiss-alert', $promo) }}">
                            @csrf
                            <button type="submit" class="btn btn-ghost" style="padding: 6px 12px; font-size: 12px; border-color: rgba(232,184,64,0.3); color: var(--warn);" title="Marcar alerta como leída">
                                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                                Leído
                            </button>
                        </form>
                    @endif
                    <a href="{{ route('promotions.edit', $promo) }}" class="btn btn-ghost" style="padding: 6px 12px; font-size: 12px;">
                        Editar
                    </a>
                    <form method="POST" action="{{ route('promotions.destroy', $promo) }}"
                          onsubmit="return confirm('¿Eliminar «{{ addslashes($promo->name) }}»?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" style="padding: 6px 12px; font-size: 12px;">
                            Eliminar
                        </button>
                    </form>
                </div>

            </div>
        @endforeach
    </div>
@endif

@endsection
