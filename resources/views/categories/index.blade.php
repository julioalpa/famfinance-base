@extends('layouts.app')

@section('title', 'Categorías')

@section('content')

<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 28px;">
    <div>
        <h1 class="font-display" style="font-size: 24px; font-weight: 700; letter-spacing: -0.02em;">Categorías</h1>
        <div style="font-size: 12px; color: var(--muted); margin-top: 3px;">Clasificá tus ingresos y gastos</div>
    </div>
    <a href="{{ route('categories.create') }}" class="btn btn-primary">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
        Nueva categoría
    </a>
</div>

@php
    $system = $categories->where('is_system', true);
    $custom = $categories->where('is_system', false);
    $typeLabels = ['expense' => 'Gasto', 'income' => 'Ingreso', 'both' => 'Ambos'];
@endphp

@if($system->isNotEmpty())
<div style="margin-bottom: 28px;">
    <div style="font-size: 11px; letter-spacing: 0.1em; text-transform: uppercase; color: var(--muted); margin-bottom: 12px;">Del sistema</div>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 10px;">
        @foreach($system as $cat)
        <div class="card" style="padding: 12px 16px; display: flex; align-items: center; gap: 12px; opacity: 0.75;">
            <div style="display:flex;align-items:center;flex-shrink:0;">
                @include('categories._icon', ['icon' => $cat->icon, 'color' => $cat->color, 'type' => $cat->type, 'size' => 'sm'])
            </div>
            <div style="flex: 1; min-width: 0;">
                <div style="font-size: 13px; font-weight: 500; color: var(--text);">{{ $cat->name }}</div>
            </div>
            <span style="font-size: 10px; color: var(--muted); background: var(--surface2); padding: 2px 7px; border-radius: 4px; white-space: nowrap;">{{ $typeLabels[$cat->type] }}</span>
        </div>
        @endforeach
    </div>
</div>
@endif

@if($custom->isNotEmpty())
<div style="margin-bottom: 28px;">
    <div style="font-size: 11px; letter-spacing: 0.1em; text-transform: uppercase; color: var(--muted); margin-bottom: 12px;">Del grupo</div>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 10px;">
        @foreach($custom as $cat)
        <div class="card" style="padding: 12px 16px; display: flex; align-items: center; gap: 12px;">
            <div style="display:flex;align-items:center;flex-shrink:0;">
                @include('categories._icon', ['icon' => $cat->icon, 'color' => $cat->color, 'type' => $cat->type, 'size' => 'sm'])
            </div>
            <div style="flex: 1; min-width: 0;">
                <div style="font-size: 13px; font-weight: 500; color: var(--text);">{{ $cat->name }}</div>
            </div>
            <span style="font-size: 10px; color: var(--muted); background: var(--surface2); padding: 2px 7px; border-radius: 4px; white-space: nowrap;">{{ $typeLabels[$cat->type] }}</span>
            <a href="{{ route('categories.edit', $cat) }}" style="color: var(--muted); text-decoration: none; flex-shrink: 0;" title="Editar">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            </a>
        </div>
        @endforeach
    </div>
</div>
@endif

@if($categories->isEmpty())
<div style="text-align: center; padding: 80px 20px;">
    <div style="font-size: 36px; margin-bottom: 12px;">🏷️</div>
    <div style="font-size: 14px; color: var(--muted); margin-bottom: 20px;">No hay categorías todavía</div>
    <a href="{{ route('categories.create') }}" class="btn btn-primary" style="display: inline-flex;">Crear primera categoría</a>
</div>
@endif

@endsection
