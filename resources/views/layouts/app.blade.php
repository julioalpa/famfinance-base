<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'FamFinance') — FamFinance</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400;12..96,500;12..96,600;12..96,700;12..96,800&family=Nunito:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --bg:          #09090b;
            --surface:     #111115;
            --surface2:    #17171d;
            --surface3:    #1e1e26;
            --border:      #282834;
            --accent:      #f0a030;
            --accent-dim:  rgba(240, 160, 48, 0.12);
            --accent-glow: rgba(240, 160, 48, 0.18);
            --accent2:     #4e9bff;
            --danger:      #f04060;
            --warn:        #e8b840;
            --text:        #eeebe4;
            --muted:       #6a6676;
            --income:      #2dd870;
            --expense:     #f04060;
            --sidebar-w:   260px;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            background: var(--bg);
            color: var(--text);
            font-family: 'Nunito', sans-serif;
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .font-display { font-family: 'Bricolage Grotesque', sans-serif; }

        /* ── Sidebar ─────────────────────────────────────────────────────────── */
        .sidebar {
            width: var(--sidebar-w);
            background: var(--surface);
            border-right: 1px solid var(--border);
            min-height: 100vh;
            position: fixed;
            top: 0; left: 0;
            display: flex;
            flex-direction: column;
            z-index: 200;
            transition: transform 0.28s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .sidebar-logo {
            padding: 22px 18px 16px;
            border-bottom: 1px solid var(--border);
            flex-shrink: 0;
        }

        .logo-text {
            font-family: 'Bricolage Grotesque', sans-serif;
            font-size: 21px;
            font-weight: 800;
            letter-spacing: -0.04em;
            color: var(--text);
            line-height: 1;
        }

        .logo-text .accent { color: var(--accent); }

        .sidebar-group-name {
            margin-top: 6px;
            font-size: 11px;
            color: var(--muted);
            letter-spacing: 0.06em;
            text-transform: uppercase;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-weight: 600;
        }

        nav { flex: 1; padding: 8px 0; overflow-y: auto; }

        .nav-section {
            padding: 14px 18px 6px;
            font-size: 10px;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--muted);
            font-family: 'Bricolage Grotesque', sans-serif;
            font-weight: 700;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 2px 8px;
            padding: 9px 12px;
            color: var(--muted);
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            border-radius: 9px;
            transition: all 0.15s;
        }

        .nav-link:hover {
            color: var(--text);
            background: var(--surface2);
        }

        .nav-link.active {
            color: var(--accent);
            background: var(--accent-dim);
        }

        .nav-link svg { flex-shrink: 0; opacity: 0.75; }
        .nav-link:hover svg, .nav-link.active svg { opacity: 1; }

        .sidebar-user {
            padding: 12px;
            border-top: 1px solid var(--border);
            flex-shrink: 0;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 10px;
            border-radius: 10px;
            margin-bottom: 8px;
            background: var(--surface2);
        }

        .user-avatar {
            width: 32px; height: 32px;
            border-radius: 50%;
            flex-shrink: 0;
            object-fit: cover;
        }

        .user-avatar-placeholder {
            width: 32px; height: 32px;
            border-radius: 50%;
            background: var(--accent-dim);
            border: 1.5px solid rgba(240,160,48,0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 800;
            color: var(--accent);
            flex-shrink: 0;
            font-family: 'Bricolage Grotesque', sans-serif;
        }

        .user-details { overflow: hidden; flex: 1; min-width: 0; }
        .user-name  { font-size: 13px; font-weight: 700; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .user-email { font-size: 11px; color: var(--muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

        /* ── Main content ─────────────────────────────────────────────────────── */
        .main-content {
            margin-left: var(--sidebar-w);
            min-height: 100vh;
            padding: 36px 40px;
            animation: fadeUp 0.3s ease both;
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(10px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ── Cards ───────────────────────────────────────────────────────────── */
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 24px;
        }

        /* ── Stat cards ──────────────────────────────────────────────────────── */
        .stat-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 22px 24px;
            position: relative;
            overflow: hidden;
            transition: transform 0.15s, border-color 0.2s, box-shadow 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
        }

        .stat-card::after {
            content: '';
            position: absolute;
            bottom: -20px; right: -20px;
            width: 110px; height: 110px;
            border-radius: 50%;
            opacity: 0.07;
            pointer-events: none;
            transition: opacity 0.2s;
        }

        .stat-card:hover::after { opacity: 0.12; }

        .stat-card.income::after  { background: var(--income); }
        .stat-card.expense::after { background: var(--expense); }
        .stat-card.balance::after { background: var(--accent2); }
        .stat-card.neutral::after { background: var(--warn); }

        .stat-card.income  { border-color: rgba(45,216,112,0.2); }
        .stat-card.expense { border-color: rgba(240,64,96,0.2); }
        .stat-card.balance { border-color: rgba(78,155,255,0.2); }
        .stat-card.neutral { border-color: rgba(232,184,64,0.2); }

        .stat-card.income:hover  { border-color: rgba(45,216,112,0.4); box-shadow: 0 8px 32px rgba(45,216,112,0.06); }
        .stat-card.expense:hover { border-color: rgba(240,64,96,0.4);  box-shadow: 0 8px 32px rgba(240,64,96,0.06); }
        .stat-card.balance:hover { border-color: rgba(78,155,255,0.4); box-shadow: 0 8px 32px rgba(78,155,255,0.06); }
        .stat-card.neutral:hover { border-color: rgba(232,184,64,0.4); box-shadow: 0 8px 32px rgba(232,184,64,0.06); }

        /* ── Buttons ─────────────────────────────────────────────────────────── */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 9px 18px;
            border-radius: 9px;
            font-family: 'Nunito', sans-serif;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.15s;
            border: none;
            text-decoration: none;
            white-space: nowrap;
        }

        .btn-primary {
            background: var(--accent);
            color: #0c0804;
        }
        .btn-primary:hover {
            background: #f5b040;
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(240,160,48,0.28);
        }
        .btn-primary:active { transform: translateY(0); box-shadow: none; }

        .btn-ghost {
            background: transparent;
            color: var(--muted);
            border: 1px solid var(--border);
        }
        .btn-ghost:hover {
            color: var(--text);
            border-color: var(--surface3);
            background: var(--surface2);
        }

        .btn-danger {
            background: rgba(240,64,96,0.1);
            color: var(--danger);
            border: 1px solid rgba(240,64,96,0.22);
        }
        .btn-danger:hover {
            background: rgba(240,64,96,0.18);
            transform: translateY(-1px);
        }

        /* ── Forms ───────────────────────────────────────────────────────────── */
        .form-input, .form-select {
            width: 100%;
            background: var(--surface2);
            border: 1px solid var(--border);
            border-radius: 9px;
            padding: 10px 14px;
            color: var(--text);
            font-family: 'Nunito', sans-serif;
            font-size: 14px;
            font-weight: 500;
            transition: border-color 0.15s, box-shadow 0.15s;
        }

        .form-input:focus, .form-select:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(240,160,48,0.1);
        }

        .form-input::placeholder { color: var(--muted); }

        .form-label {
            display: block;
            font-size: 11px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 7px;
            font-weight: 700;
        }

        /* ── Table ───────────────────────────────────────────────────────────── */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        .data-table th {
            text-align: left;
            padding: 11px 16px;
            font-size: 11px;
            letter-spacing: 0.07em;
            text-transform: uppercase;
            color: var(--muted);
            border-bottom: 1px solid var(--border);
            font-weight: 700;
            font-family: 'Bricolage Grotesque', sans-serif;
        }

        .data-table td {
            padding: 13px 16px;
            border-bottom: 1px solid rgba(40,40,52,0.7);
            vertical-align: middle;
        }

        .data-table tr:last-child td { border-bottom: none; }

        .data-table tbody tr { transition: background 0.1s; }
        .data-table tbody tr:hover td { background: rgba(255,255,255,0.022); }

        /* ── Badges ──────────────────────────────────────────────────────────── */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.02em;
        }

        .badge-income   { background: rgba(45,216,112,0.12);  color: var(--income); }
        .badge-expense  { background: rgba(240,64,96,0.12);   color: var(--expense); }
        .badge-transfer { background: rgba(78,155,255,0.12);  color: var(--accent2); }
        .badge-credit   { background: rgba(232,184,64,0.12);  color: var(--warn); }
        .badge-cash     { background: rgba(45,216,112,0.10);  color: var(--income); }
        .badge-digital  { background: rgba(78,155,255,0.10);  color: var(--accent2); }

        /* ── Alerts ──────────────────────────────────────────────────────────── */
        .alert {
            padding: 12px 18px;
            border-radius: 10px;
            font-size: 14px;
            margin-bottom: 20px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .alert-success { background: rgba(45,216,112,0.09);  border: 1px solid rgba(45,216,112,0.22);  color: var(--income); }
        .alert-error   { background: rgba(240,64,96,0.09);   border: 1px solid rgba(240,64,96,0.22);   color: var(--danger); }
        .alert-info    { background: rgba(78,155,255,0.09);  border: 1px solid rgba(78,155,255,0.22);  color: var(--accent2); }

        /* ── Amount colors ───────────────────────────────────────────────────── */
        .amount-income  { color: var(--income); }
        .amount-expense { color: var(--expense); }
        .amount-neutral { color: var(--accent2); }

        /* ── Mobile toggle ───────────────────────────────────────────────────── */
        .mobile-toggle {
            display: none;
            position: fixed;
            top: 14px;
            left: 14px;
            z-index: 300;
            width: 40px; height: 40px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 10px;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: var(--text);
            transition: background 0.15s;
        }
        .mobile-toggle:hover { background: var(--surface2); }

        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.55);
            z-index: 150;
            backdrop-filter: blur(3px);
            -webkit-backdrop-filter: blur(3px);
        }

        /* ── Scrollbar ───────────────────────────────────────────────────────── */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--border); border-radius: 3px; }

        /* ── Responsive ──────────────────────────────────────────────────────── */
        @media (max-width: 900px) {
            .main-content { padding: 36px 28px; }
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                box-shadow: none;
            }
            .sidebar.open {
                transform: translateX(0);
                box-shadow: 24px 0 60px rgba(0,0,0,0.5);
            }
            .sidebar-overlay.visible { display: block; }
            .mobile-toggle { display: flex; }
            .main-content {
                margin-left: 0;
                padding: 70px 18px 32px;
            }
        }

        @media (max-width: 480px) {
            .main-content { padding: 70px 14px 24px; }
        }
    </style>
</head>
<body>

{{-- Mobile toggle --}}
<button class="mobile-toggle" id="mobile-toggle" onclick="toggleSidebar()" aria-label="Abrir menú">
    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
        <path d="M3 6h18M3 12h18M3 18h18"/>
    </svg>
</button>

{{-- Sidebar overlay --}}
<div class="sidebar-overlay" id="sidebar-overlay" onclick="toggleSidebar()"></div>

{{-- ── Sidebar ──────────────────────────────────────────────────────────────── --}}
<aside class="sidebar" id="sidebar">

    <div class="sidebar-logo">
        <div class="logo-text"><span class="accent">fam</span>finance</div>
        @if(session('active_family_group_id'))
        <div class="sidebar-group-name">
            {{ auth()->user()->familyGroups()->find(session('active_family_group_id'))?->name ?? '' }}
        </div>
        @endif
    </div>

    <nav>
        <div class="nav-section">Principal</div>

        <a href="{{ route('dashboard') }}"
           class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <rect x="3" y="3" width="7" height="7" rx="1.5"/>
                <rect x="14" y="3" width="7" height="7" rx="1.5"/>
                <rect x="3" y="14" width="7" height="7" rx="1.5"/>
                <rect x="14" y="14" width="7" height="7" rx="1.5"/>
            </svg>
            Dashboard
        </a>

        <a href="{{ route('reports.index') }}"
           class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path d="M18 20V10M12 20V4M6 20v-6"/>
            </svg>
            Reportes
        </a>

        <a href="{{ route('transactions.index') }}"
           class="nav-link {{ request()->routeIs('transactions.*') ? 'active' : '' }}">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/>
            </svg>
            Movimientos
        </a>

        <a href="{{ route('accounts.index') }}"
           class="nav-link {{ request()->routeIs('accounts.*') ? 'active' : '' }}">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <rect x="2" y="5" width="20" height="14" rx="2.5"/>
                <path d="M2 10h20"/>
            </svg>
            Cuentas
        </a>

        <a href="{{ route('categories.index') }}"
           class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/>
                <circle cx="7" cy="7" r="1" fill="currentColor"/>
            </svg>
            Categorías
        </a>

        <a href="{{ route('recurring-expenses.index') }}"
           class="nav-link {{ request()->routeIs('recurring-expenses.*') ? 'active' : '' }}">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
            </svg>
            Débitos fijos
        </a>

        <div class="nav-section">Configuración</div>

        <a href="{{ route('import.index') }}"
           class="nav-link {{ request()->routeIs('import.*') ? 'active' : '' }}">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                <polyline points="17 8 12 3 7 8"/>
                <line x1="12" y1="3" x2="12" y2="15"/>
            </svg>
            Importar CSV
        </a>

        <a href="{{ route('exchange-rates.index') }}"
           class="nav-link {{ request()->routeIs('exchange-rates.*') ? 'active' : '' }}">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
            </svg>
            Tipo de cambio
        </a>

        <a href="{{ route('family-groups.show') }}"
           class="nav-link {{ request()->routeIs('family-groups.*') ? 'active' : '' }}">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
            Grupo familiar
        </a>
    </nav>

    {{-- User & logout --}}
    <div class="sidebar-user">
        <div class="user-info">
            @if(auth()->user()->avatar)
                <img src="{{ auth()->user()->avatar }}" alt="" class="user-avatar">
            @else
                <div class="user-avatar-placeholder">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
            @endif
            <div class="user-details">
                <div class="user-name">{{ auth()->user()->name }}</div>
                <div class="user-email">{{ auth()->user()->email }}</div>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-ghost" style="width: 100%; justify-content: center; font-size: 13px; padding: 8px 14px;">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/>
                </svg>
                Cerrar sesión
            </button>
        </form>
    </div>
</aside>

{{-- ── Main ─────────────────────────────────────────────────────────────────── --}}
<main class="main-content">

    @if(session('success'))
        <div class="alert alert-success">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error') || $errors->has('oauth'))
        <div class="alert alert-error">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            {{ session('error') ?? $errors->first('oauth') }}
        </div>
    @endif
    @if(session('info'))
        <div class="alert alert-info">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
            {{ session('info') }}
        </div>
    @endif

    @yield('content')
</main>

<script>
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('open');
        document.getElementById('sidebar-overlay').classList.toggle('visible');
    }
</script>

</body>
</html>
