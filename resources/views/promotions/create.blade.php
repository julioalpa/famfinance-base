@extends('layouts.app')

@section('title', 'Nueva promoción')

@section('content')

<div style="max-width: 680px;">

    <div style="margin-bottom: 28px;">
        <a href="{{ route('promotions.index') }}" style="font-size: 13px; color: var(--muted); text-decoration: none; display: inline-flex; align-items: center; gap: 6px; margin-bottom: 16px;">
            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
            Volver a promociones
        </a>
        <h1 class="font-display" style="font-size: 26px; font-weight: 800; letter-spacing: -0.03em; color: var(--text);">
            Nueva promoción
        </h1>
    </div>

    <div class="card">
        <form method="POST" action="{{ route('promotions.store') }}">
            @csrf

            @include('promotions._form')

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 8px;">
                <a href="{{ route('promotions.index') }}" class="btn btn-ghost">Cancelar</a>
                <button type="submit" class="btn btn-primary">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    Guardar promoción
                </button>
            </div>
        </form>
    </div>

</div>

@endsection
