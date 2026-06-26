<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#09090b">
    <title>@yield('title', 'Admin') — FamFinance Admin</title>

    <link rel="icon" type="image/svg+xml" href="/app-icons/icon.svg">

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
            --admin:       #a078ff;
            --admin-dim:   rgba(160, 120, 255, 0.12);
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
            touch-action: manipulation;
        }

        .font-display { font-family: 'Bricolage Grotesque', sans-serif; }

        .sidebar {
            width: var(--sidebar-w);
            background: var(--surface);
            border-right: 1px solid var(--border);
            height: 100vh;
            height: 100dvh;
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
            line-height: 1;
            color: var(--text);
        }
        .logo-text .accent { color: var(--admin); }
        .sidebar-group-name {
            margin-top: 6px;
            font-size: 11px;
            color: var(--muted);
            letter-spacing: 0.06em;
            text-transform: uppercase;
            font-weight: 600;
        }

        .sidebar > nav { flex: 1; padding: 8px 0; overflow-y: auto; -webkit-overflow-scrolling: touch; min-height: 0; overscroll-behavior: contain; }

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
        .nav-link:hover { color: var(--text); background: var(--surface2); }
        .nav-link.active { color: var(--admin); background: var(--admin-dim); }
        .nav-link svg { flex-shrink: 0; opacity: 0.75; }
        .nav-link:hover svg, .nav-link.active svg { opacity: 1; }

        .sidebar-user {
            padding: 12px;
            border-top: 1px solid var(--border);
            flex-shrink: 0;
        }
        .user-info {
            display: flex; align-items: center; gap: 10px;
            padding: 8px 10px;
            border-radius: 10px;
            margin-bottom: 8px;
            background: var(--surface2);
        }
        .user-avatar { width: 32px; height: 32px; border-radius: 50%; flex-shrink: 0; object-fit: cover; }
        .user-avatar-placeholder {
            width: 32px; height: 32px;
            border-radius: 50%;
            background: var(--admin-dim);
            border: 1.5px solid rgba(160,120,255,0.3);
            display: flex; align-items: center; justify-content: center;
            font-size: 13px; font-weight: 800; color: var(--admin);
            flex-shrink: 0;
            font-family: 'Bricolage Grotesque', sans-serif;
        }
        .user-details { overflow: hidden; flex: 1; min-width: 0; }
        .user-name  { font-size: 13px; font-weight: 700; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .user-email { font-size: 11px; color: var(--muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

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

        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 24px;
        }

        .stat-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 22px 24px;
            position: relative;
            overflow: hidden;
            transition: transform 0.15s, border-color 0.2s, box-shadow 0.2s;
        }
        .stat-card:hover { transform: translateY(-2px); }
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
        .stat-card.admin::after  { background: var(--admin); }
        .stat-card.accent::after { background: var(--accent); }
        .stat-card.info::after   { background: var(--accent2); }
        .stat-card.warn::after   { background: var(--warn); }

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
        .btn-primary { background: var(--admin); color: #0c0418; }
        .btn-primary:hover { background: #b290ff; transform: translateY(-1px); box-shadow: 0 6px 20px rgba(160,120,255,0.28); }
        .btn-primary:active { transform: translateY(0); box-shadow: none; }
        .btn-ghost { background: transparent; color: var(--muted); border: 1px solid var(--border); }
        .btn-ghost:hover { color: var(--text); border-color: var(--surface3); background: var(--surface2); }
        .btn-danger { background: rgba(240,64,96,0.1); color: var(--danger); border: 1px solid rgba(240,64,96,0.22); }
        .btn-danger:hover { background: rgba(240,64,96,0.18); transform: translateY(-1px); }

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
            border-color: var(--admin);
            box-shadow: 0 0 0 3px rgba(160,120,255,0.1);
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

        .data-table { width: 100%; border-collapse: collapse; font-size: 14px; }
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

        .badge {
            display: inline-flex;
            align-items: center;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.02em;
        }
        .badge-admin   { background: var(--admin-dim);          color: var(--admin); }
        .badge-owner   { background: rgba(232,184,64,0.12);     color: var(--warn); }
        .badge-member  { background: rgba(106,102,118,0.18);    color: var(--muted); }
        .badge-info    { background: rgba(78,155,255,0.12);     color: var(--accent2); }

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
        .alert-success { background: rgba(45,216,112,0.09); border: 1px solid rgba(45,216,112,0.22); color: var(--income); }
        .alert-error   { background: rgba(240,64,96,0.09);  border: 1px solid rgba(240,64,96,0.22);  color: var(--danger); }
        .alert-info    { background: rgba(78,155,255,0.09); border: 1px solid rgba(78,155,255,0.22); color: var(--accent2); }

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
            align-items: center; justify-content: center;
            cursor: pointer;
            color: var(--text);
        }
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.55);
            z-index: 150;
            backdrop-filter: blur(3px);
            -webkit-backdrop-filter: blur(3px);
        }

        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--border); border-radius: 3px; }

        .table-wrap {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            border-radius: 14px;
        }

        .admin-banner {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--admin);
            background: var(--admin-dim);
            padding: 3px 9px;
            border-radius: 6px;
            margin-bottom: 8px;
        }

        @media (max-width: 900px) { .main-content { padding: 36px 28px; } }

        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); box-shadow: 24px 0 60px rgba(0,0,0,0.5); }
            .sidebar-overlay.visible { display: block; }
            .mobile-toggle { display: flex; }
            .main-content { margin-left: 0; padding: 70px 18px 32px; }
            .btn { min-height: 44px; }
            .form-input, .form-select { min-height: 44px; font-size: 16px; }
            .data-table td, .data-table th { padding: 10px 12px; }
            .nav-link { min-height: 44px; }
        }

        @media (max-width: 480px) { .main-content { padding: 70px 14px 32px; } }
    </style>
</head>
<body>

<button class="mobile-toggle" id="mobile-toggle" onclick="toggleSidebar()" aria-label="Abrir menú">
    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
</button>

<div class="sidebar-overlay" id="sidebar-overlay" onclick="toggleSidebar()"></div>

<aside class="sidebar" id="sidebar">

    <div class="sidebar-logo">
        <div class="logo-text"><span class="accent">admin</span> · famfinance</div>
        <div class="sidebar-group-name">Panel de administración</div>
    </div>

    <nav>
        <div class="nav-section">Administración</div>

        <a href="{{ route('admin.dashboard') }}"
           class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <rect x="3" y="3" width="7" height="7" rx="1.5"/>
                <rect x="14" y="3" width="7" height="7" rx="1.5"/>
                <rect x="3" y="14" width="7" height="7" rx="1.5"/>
                <rect x="14" y="14" width="7" height="7" rx="1.5"/>
            </svg>
            Inicio
        </a>

        <a href="{{ route('admin.users.index') }}"
           class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
            Usuarios
        </a>

        <a href="{{ route('admin.family-groups.index') }}"
           class="nav-link {{ request()->routeIs('admin.family-groups.*') ? 'active' : '' }}">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                <polyline points="9 22 9 12 15 12 15 22"/>
            </svg>
            Grupos familiares
        </a>

        <div class="nav-section">Volver</div>

        <a href="{{ route('dashboard') }}" class="nav-link">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path d="M19 12H5M12 19l-7-7 7-7"/>
            </svg>
            Volver a la app
        </a>
    </nav>

    <div class="sidebar-user">
        <div class="user-info">
            @if(auth()->user()->avatar)
                <img src="{{ auth()->user()->avatar }}" alt="" class="user-avatar">
            @else
                <div class="user-avatar-placeholder">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
            @endif
            <div class="user-details">
                <div class="user-name">{{ auth()->user()->name }}</div>
                <div class="user-email">{{ auth()->user()->email }}</div>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-ghost" style="width: 100%; justify-content: center; font-size: 12px; padding: 7px 10px;">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></svg>
                Salir
            </button>
        </form>
    </div>
</aside>

<main class="main-content">

    @if(session('success'))
        <div class="alert alert-success">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-error">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            {{ session('error') }}
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
    document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth <= 768) {
                document.getElementById('sidebar').classList.remove('open');
                document.getElementById('sidebar-overlay').classList.remove('visible');
            }
        });
    });
</script>

</body>
</html>
