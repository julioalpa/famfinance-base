<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'FamFinance') — FamFinance</title>

    {{-- Google Fonts: DM Mono + Syne --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700;800&family=DM+Mono:wght@300;400;500&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --bg:        #0d0f14;
            --surface:   #13161e;
            --surface2:  #1a1e28;
            --border:    #252a38;
            --accent:    #4fffb0;
            --accent2:   #3de8ff;
            --danger:    #ff4d6d;
            --warn:      #ffd166;
            --text:      #e8eaf2;
            --muted:     #6b7394;
            --income:    #4fffb0;
            --expense:   #ff4d6d;
        }

        * { box-sizing: border-box; }

        body {
            background: var(--bg);
            color: var(--text);
            font-family: 'DM Mono', monospace;
            min-height: 100vh;
        }

        .font-display { font-family: 'Syne', sans-serif; }

        /* Sidebar */
        .sidebar {
            width: 240px;
            background: var(--surface);
            border-right: 1px solid var(--border);
            min-height: 100vh;
            position: fixed;
            top: 0; left: 0;
            display: flex;
            flex-direction: column;
            z-index: 50;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 20px;
            color: var(--muted);
            font-size: 13px;
            letter-spacing: 0.04em;
            text-decoration: none;
            transition: all 0.15s;
            border-left: 2px solid transparent;
        }

        .nav-link:hover {
            color: var(--text);
            background: var(--surface2);
        }

        .nav-link.active {
            color: var(--accent);
            border-left-color: var(--accent);
            background: rgba(79,255,176,0.06);
        }

        /* Main content */
        .main-content {
            margin-left: 240px;
            min-height: 100vh;
            padding: 32px 36px;
        }

        /* Cards */
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 24px;
        }

        /* Stat cards */
        .stat-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 20px 24px;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 2px;
        }

        .stat-card.income::before  { background: var(--income); }
        .stat-card.expense::before { background: var(--expense); }
        .stat-card.balance::before { background: var(--accent2); }
        .stat-card.neutral::before { background: var(--warn); }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 18px;
            border-radius: 6px;
            font-family: 'DM Mono', monospace;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.15s;
            border: none;
            text-decoration: none;
        }

        .btn-primary {
            background: var(--accent);
            color: #0d0f14;
        }
        .btn-primary:hover { background: #3de89a; }

        .btn-ghost {
            background: transparent;
            color: var(--muted);
            border: 1px solid var(--border);
        }
        .btn-ghost:hover { color: var(--text); border-color: var(--muted); }

        .btn-danger {
            background: rgba(255,77,109,0.15);
            color: var(--danger);
            border: 1px solid rgba(255,77,109,0.3);
        }
        .btn-danger:hover { background: rgba(255,77,109,0.25); }

        /* Inputs */
        .form-input, .form-select {
            width: 100%;
            background: var(--surface2);
            border: 1px solid var(--border);
            border-radius: 6px;
            padding: 9px 14px;
            color: var(--text);
            font-family: 'DM Mono', monospace;
            font-size: 13px;
            transition: border-color 0.15s;
        }

        .form-input:focus, .form-select:focus {
            outline: none;
            border-color: var(--accent);
        }

        .form-input::placeholder { color: var(--muted); }

        .form-label {
            display: block;
            font-size: 11px;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 6px;
        }

        /* Table */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        .data-table th {
            text-align: left;
            padding: 10px 16px;
            font-size: 11px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--muted);
            border-bottom: 1px solid var(--border);
            font-weight: 500;
        }

        .data-table td {
            padding: 13px 16px;
            border-bottom: 1px solid rgba(37,42,56,0.5);
            vertical-align: middle;
        }

        .data-table tr:hover td { background: rgba(255,255,255,0.02); }

        /* Badges */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 500;
            letter-spacing: 0.04em;
        }

        .badge-income  { background: rgba(79,255,176,0.12); color: var(--income); }
        .badge-expense { background: rgba(255,77,109,0.12); color: var(--expense); }
        .badge-transfer{ background: rgba(61,232,255,0.12); color: var(--accent2); }
        .badge-credit  { background: rgba(255,209,102,0.12); color: var(--warn); }
        .badge-cash    { background: rgba(79,255,176,0.10); color: var(--income); }
        .badge-digital { background: rgba(61,232,255,0.10); color: var(--accent2); }

        /* Alerts */
        .alert {
            padding: 12px 16px;
            border-radius: 6px;
            font-size: 13px;
            margin-bottom: 20px;
        }
        .alert-success { background: rgba(79,255,176,0.1); border: 1px solid rgba(79,255,176,0.25); color: var(--income); }
        .alert-error   { background: rgba(255,77,109,0.1); border: 1px solid rgba(255,77,109,0.25); color: var(--danger); }
        .alert-info    { background: rgba(61,232,255,0.1); border: 1px solid rgba(61,232,255,0.25); color: var(--accent2); }

        /* Amount colors */
        .amount-income  { color: var(--income); }
        .amount-expense { color: var(--expense); }
        .amount-neutral { color: var(--accent2); }

        /* Separator */
        .section-title {
            font-family: 'Syne', sans-serif;
            font-size: 11px;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--muted);
            padding: 16px 20px 8px;
        }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 4px; }
        ::-webkit-scrollbar-track { background: var(--bg); }
        ::-webkit-scrollbar-thumb { background: var(--border); border-radius: 2px; }
    </style>
</head>
<body>

{{-- ── Sidebar ────────────────────────────────────────────────────────────── --}}
<aside class="sidebar">
    {{-- Logo --}}
    <div style="padding: 28px 20px 20px; border-bottom: 1px solid var(--border);">
        <span class="font-display" style="font-size: 20px; font-weight: 800; color: var(--accent); letter-spacing: -0.02em;">
            fam<span style="color: var(--text);">finance</span>
        </span>
        @if(session('active_family_group_id'))
        <div style="margin-top: 6px; font-size: 11px; color: var(--muted); letter-spacing: 0.06em; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
            {{ auth()->user()->familyGroups()->find(session('active_family_group_id'))?->name ?? '' }}
        </div>
        @endif
    </div>

    {{-- Nav --}}
    <nav style="flex: 1; padding: 12px 0;">
        <div class="section-title">Principal</div>

        <a href="{{ route('dashboard') }}"
           class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
            Dashboard
        </a>

        <a href="{{ route('transactions.index') }}"
           class="nav-link {{ request()->routeIs('transactions.*') ? 'active' : '' }}">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M3 7h18M3 12h18M3 17h18"/></svg>
            Movimientos
        </a>

        <a href="{{ route('accounts.index') }}"
           class="nav-link {{ request()->routeIs('accounts.*') ? 'active' : '' }}">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>
            Cuentas
        </a>

        <a href="{{ route('categories.index') }}"
           class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
            Categorías
        </a>

        <div class="section-title" style="margin-top: 8px;">Configuración</div>

        <a href="{{ route('import.index') }}"
           class="nav-link {{ request()->routeIs('import.*') ? 'active' : '' }}">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
            Importar CSV
        </a>

        <a href="{{ route('exchange-rates.index') }}"
           class="nav-link {{ request()->routeIs('exchange-rates.*') ? 'active' : '' }}">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M12 2v4m0 12v4M4.93 4.93l2.83 2.83m8.48 8.48 2.83 2.83M2 12h4m12 0h4M4.93 19.07l2.83-2.83m8.48-8.48 2.83-2.83"/></svg>
            Tipo de cambio
        </a>

        <a href="{{ route('family-groups.show') }}"
           class="nav-link {{ request()->routeIs('family-groups.*') ? 'active' : '' }}">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            Grupo familiar
        </a>
    </nav>

    {{-- User / Logout --}}
    <div style="padding: 16px 20px; border-top: 1px solid var(--border);">
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px;">
            @if(auth()->user()->avatar)
                <img src="{{ auth()->user()->avatar }}" alt="" style="width: 30px; height: 30px; border-radius: 50%; border: 1px solid var(--border);">
            @else
                <div style="width: 30px; height: 30px; border-radius: 50%; background: var(--surface2); display: flex; align-items: center; justify-content: center; font-size: 12px; color: var(--accent);">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
            @endif
            <div style="overflow: hidden;">
                <div style="font-size: 12px; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ auth()->user()->name }}</div>
                <div style="font-size: 10px; color: var(--muted);">{{ auth()->user()->email }}</div>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-ghost" style="width: 100%; justify-content: center; font-size: 12px;">
                Cerrar sesión
            </button>
        </form>
    </div>
</aside>

{{-- ── Main ────────────────────────────────────────────────────────────────── --}}
<main class="main-content">

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="alert alert-success">✓ {{ session('success') }}</div>
    @endif
    @if(session('error') || $errors->has('oauth'))
        <div class="alert alert-error">✗ {{ session('error') ?? $errors->first('oauth') }}</div>
    @endif
    @if(session('info'))
        <div class="alert alert-info">ℹ {{ session('info') }}</div>
    @endif

    @yield('content')
</main>

</body>
</html>
