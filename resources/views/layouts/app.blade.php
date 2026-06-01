<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#09090b">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="application-name" content="FamFinance">
    <title>@yield('title', 'FamFinance') — FamFinance</title>

    <link rel="manifest" href="/manifest.webmanifest">
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

        /* ── Sortable table columns ───────────────────────────────────────── */
        .data-table th[data-sort] { cursor:pointer;user-select:none;white-space:nowrap;transition:color 0.15s; }
        .data-table th[data-sort]:hover { color:var(--text);background:rgba(255,255,255,0.025); }
        .data-table th[data-sort].sort-active { color:var(--accent) !important; }
        .sort-link { color:inherit;text-decoration:none;display:inline-flex;align-items:center;gap:4px;white-space:nowrap;transition:color 0.15s; }
        .sort-link:hover { color:var(--text); }
        .sort-link.sort-active { color:var(--accent); }

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

        .badge-income     { background: rgba(45,216,112,0.12);  color: var(--income); }
        .badge-expense    { background: rgba(240,64,96,0.12);   color: var(--expense); }
        .badge-transfer   { background: rgba(78,155,255,0.12);  color: var(--accent2); }
        .badge-adjustment { background: rgba(160,120,255,0.12); color: #a078ff; }
        .badge-credit     { background: rgba(232,184,64,0.12);  color: var(--warn); }
        .badge-cash       { background: rgba(45,216,112,0.10);  color: var(--income); }
        .badge-digital    { background: rgba(78,155,255,0.10);  color: var(--accent2); }
        .badge-loan       { background: rgba(240,64,96,0.10);   color: var(--expense); }

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
            .main-content { padding: 70px 14px 32px; }
        }

        /* ── Mobile helpers ──────────────────────────────────────────────────── */

        /* Tables scroll horizontally on small screens */
        .table-wrap {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            border-radius: 14px;
        }

        /* Stat grids: 4-col → 2-col → 1-col */
        @media (max-width: 640px) {
            [style*="grid-template-columns: repeat(4, 1fr)"],
            [style*="grid-template-columns:repeat(4,1fr)"] {
                grid-template-columns: repeat(2, 1fr) !important;
            }
            [style*="grid-template-columns: repeat(3, 1fr)"],
            [style*="grid-template-columns:repeat(3,1fr)"] {
                grid-template-columns: repeat(2, 1fr) !important;
            }
        }

        @media (max-width: 400px) {
            [style*="grid-template-columns: repeat(4, 1fr)"],
            [style*="grid-template-columns:repeat(4,1fr)"],
            [style*="grid-template-columns: repeat(2, 1fr)"],
            [style*="grid-template-columns:repeat(2,1fr)"] {
                grid-template-columns: 1fr !important;
            }
        }

        /* Filter forms on mobile: stack vertically */
        @media (max-width: 640px) {
            form[style*="display: flex"][style*="flex-wrap: wrap"] > div,
            form[style*="display:flex"][style*="flex-wrap:wrap"] > div {
                width: 100%;
            }
            form[style*="display: flex"][style*="flex-wrap: wrap"] .form-input,
            form[style*="display: flex"][style*="flex-wrap: wrap"] .form-select {
                width: 100% !important;
            }
        }

        /* Ensure touch targets are large enough */
        @media (max-width: 768px) {
            .btn { min-height: 44px; }
            .form-input, .form-select { min-height: 44px; font-size: 16px; }
            .data-table td, .data-table th { padding: 10px 12px; }
            .nav-link { min-height: 44px; }
        }

        /* ── Bottom nav (mobile) ─────────────────────────────────────────────── */
        .bottom-nav {
            display: none;
            position: fixed;
            bottom: 0; left: 0; right: 0;
            background: var(--surface);
            border-top: 1px solid var(--border);
            z-index: 200;
            padding-bottom: env(safe-area-inset-bottom);
        }

        .bottom-nav-inner {
            display: flex;
            height: 56px;
            align-items: stretch;
        }

        .bottom-nav-item {
            flex: 1;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            gap: 3px;
            color: var(--muted);
            text-decoration: none;
            font-size: 10px; font-weight: 700;
            letter-spacing: 0.03em;
            background: none; border: none; cursor: pointer;
            font-family: 'Nunito', sans-serif;
            transition: color 0.15s;
            padding: 0;
        }

        .bottom-nav-item svg { transition: color 0.15s; }
        .bottom-nav-item.active { color: var(--accent); }
        .bottom-nav-item.active svg { opacity: 1; }

        /* ── FAB ─────────────────────────────────────────────────────────────── */
        .fab {
            display: none;
            position: fixed;
            right: 20px;
            bottom: calc(56px + env(safe-area-inset-bottom) + 14px);
            width: 52px; height: 52px;
            border-radius: 50%;
            background: var(--accent);
            color: #0c0804;
            align-items: center; justify-content: center;
            box-shadow: 0 4px 20px rgba(240,160,48,0.45), 0 1px 4px rgba(0,0,0,0.3);
            z-index: 190;
            text-decoration: none;
            transition: transform 0.15s, box-shadow 0.15s;
        }
        .fab:active { transform: scale(0.93); box-shadow: 0 2px 10px rgba(240,160,48,0.3); }

        /* ── PWA install banner ─────────────────────────────────────────────── */
        .pwa-install {
            display: none;
            position: fixed;
            left: 16px;
            right: 16px;
            bottom: calc(56px + env(safe-area-inset-bottom) + 78px);
            z-index: 250;
            background: var(--surface2);
            border: 1px solid var(--accent-dim);
            border-radius: 14px;
            padding: 14px 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.45);
            gap: 12px;
            align-items: center;
        }
        .pwa-install.visible { display: flex; }
        .pwa-install-text { flex: 1; min-width: 0; }
        .pwa-install-title { font-size: 13px; font-weight: 700; color: var(--text); margin-bottom: 2px; }
        .pwa-install-sub { font-size: 11px; color: var(--muted); line-height: 1.35; }
        .pwa-install-actions { display: flex; gap: 6px; flex-shrink: 0; }
        .pwa-install-actions button {
            min-height: 36px;
            padding: 0 12px;
            font-size: 12px;
            font-weight: 700;
            border-radius: 9px;
            border: none;
            cursor: pointer;
        }
        .pwa-install-yes { background: var(--accent); color: #1a1208; }
        .pwa-install-yes:active { transform: scale(0.96); }
        .pwa-install-no { background: transparent; color: var(--muted); }
        @media (min-width: 769px) {
            .pwa-install { left: auto; right: 24px; bottom: 24px; max-width: 360px; }
        }

        /* ── Mobile: show bottom nav, hide hamburger, adjust content ─────────── */
        @media (max-width: 768px) {
            .bottom-nav { display: flex; flex-direction: column; }
            .fab { display: flex; }
            .mobile-toggle { display: none !important; }
            .main-content {
                padding-top: max(20px, env(safe-area-inset-top)) !important;
                padding-bottom: calc(56px + env(safe-area-inset-bottom) + 16px) !important;
            }
        }

        /* Safe area padding for phones with notch/gesture bar */
        @media (min-width: 769px) {
            .main-content {
                padding-bottom: max(24px, env(safe-area-inset-bottom));
            }
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
           class="nav-link {{ request()->routeIs('reports.index') ? 'active' : '' }}">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path d="M18 20V10M12 20V4M6 20v-6"/>
            </svg>
            Reportes
        </a>

        <a href="{{ route('reports.monthly') }}"
           class="nav-link {{ request()->routeIs('reports.monthly*') ? 'active' : '' }}">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>
                <path d="M8 14h.01M12 14h.01M16 14h.01M8 18h.01M12 18h.01"/>
            </svg>
            Balance mensual
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
                <rect x="3" y="3" width="7" height="7" rx="1.5"/>
                <rect x="14" y="3" width="7" height="7" rx="1.5"/>
                <rect x="3" y="14" width="7" height="7" rx="1.5"/>
                <rect x="14" y="14" width="7" height="7" rx="1.5"/>
            </svg>
            Categorías
        </a>

        <a href="{{ route('tags.index') }}"
           class="nav-link {{ request()->routeIs('tags.*') ? 'active' : '' }}">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/>
                <circle cx="7" cy="7" r="1.5" fill="currentColor" stroke="none"/>
            </svg>
            Etiquetas
        </a>

        <a href="{{ route('monthly-payments.index') }}"
           class="nav-link {{ request()->routeIs('monthly-payments.*') || request()->routeIs('payment-items.*') ? 'active' : '' }}"
           title="Checklist de pagos del mes — marcá los que ya pagaste">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
            </svg>
            Pagos del mes
        </a>

        <a href="{{ route('promotions.index') }}"
           class="nav-link {{ request()->routeIs('promotions.*') ? 'active' : '' }}"
           title="Promociones y descuentos con vencimiento">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <circle cx="8.5" cy="8.5" r="2.5"/>
                <circle cx="15.5" cy="15.5" r="2.5"/>
                <line x1="6" y1="18" x2="18" y2="6"/>
            </svg>
            Promociones
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
        <div style="display: flex; gap: 6px; margin-bottom: 6px;">
            <a href="{{ route('profile.password') }}" class="btn btn-ghost" style="flex: 1; justify-content: center; font-size: 12px; padding: 7px 10px;">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
                Contraseña
            </a>
            <form method="POST" action="{{ route('logout') }}" style="flex: 1;">
                @csrf
                <button type="submit" class="btn btn-ghost" style="width: 100%; justify-content: center; font-size: 12px; padding: 7px 10px;">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/>
                    </svg>
                    Salir
                </button>
            </form>
        </div>
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

{{-- ── PWA install banner ────────────────────────────────────────────────── --}}
<div id="pwa-install-banner" class="pwa-install" role="dialog" aria-labelledby="pwa-install-title">
    <div class="pwa-install-text">
        <div class="pwa-install-title" id="pwa-install-title">Instalar FamFinance</div>
        <div class="pwa-install-sub">Acceso directo desde tu pantalla de inicio, sin barra del navegador.</div>
    </div>
    <div class="pwa-install-actions">
        <button type="button" class="pwa-install-no" id="pwa-install-dismiss">Ahora no</button>
        <button type="button" class="pwa-install-yes" id="pwa-install-accept">Instalar</button>
    </div>
</div>

{{-- ── FAB ───────────────────────────────────────────────────────────────────── --}}
<a href="{{ route('transactions.create') }}" class="fab" aria-label="Nuevo movimiento">
    <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.8" viewBox="0 0 24 24">
        <path d="M12 5v14M5 12h14"/>
    </svg>
</a>

{{-- ── Bottom nav ───────────────────────────────────────────────────────────── --}}
<nav class="bottom-nav" aria-label="Navegación principal">
    <div class="bottom-nav-inner">
        <a href="{{ route('dashboard') }}"
           class="bottom-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <rect x="3" y="3" width="7" height="7" rx="1.5"/>
                <rect x="14" y="3" width="7" height="7" rx="1.5"/>
                <rect x="3" y="14" width="7" height="7" rx="1.5"/>
                <rect x="14" y="14" width="7" height="7" rx="1.5"/>
            </svg>
            Inicio
        </a>

        <a href="{{ route('transactions.index') }}"
           class="bottom-nav-item {{ request()->routeIs('transactions.*') || request()->routeIs('card-payment.*') ? 'active' : '' }}">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/>
            </svg>
            Movimientos
        </a>

        <a href="{{ route('accounts.index') }}"
           class="bottom-nav-item {{ request()->routeIs('accounts.*') ? 'active' : '' }}">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <rect x="2" y="5" width="20" height="14" rx="2.5"/>
                <path d="M2 10h20"/>
            </svg>
            Cuentas
        </a>

        <a href="{{ route('monthly-payments.index') }}"
           class="bottom-nav-item {{ request()->routeIs('monthly-payments.*') || request()->routeIs('payment-items.*') ? 'active' : '' }}">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
            </svg>
            Pagos
        </a>

        <button class="bottom-nav-item {{ request()->routeIs('reports.*') || request()->routeIs('categories.*') || request()->routeIs('exchange-rates.*') || request()->routeIs('family-groups.*') || request()->routeIs('import.*') || request()->routeIs('profile.*') ? 'active' : '' }}"
                onclick="toggleSidebar()" aria-label="Menú">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path d="M3 6h18M3 12h18M3 18h18"/>
            </svg>
            Menú
        </button>
    </div>
</nav>

<script>
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('open');
        document.getElementById('sidebar-overlay').classList.toggle('visible');
    }

    // Close sidebar when a nav link is tapped on mobile
    document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth <= 768) {
                document.getElementById('sidebar').classList.remove('open');
                document.getElementById('sidebar-overlay').classList.remove('visible');
            }
        });
    });

    document.addEventListener('click', function(e) {
        if (e.target.matches('input[type="date"], input[type="month"]')) {
            try { e.target.showPicker(); } catch (_) {}
        }
    });

    // ── Client-side table sort ────────────────────────────────────────────
    (function () {
        window.initTableSort = function (tableId) {
            const table = document.getElementById(tableId);
            if (!table) return;
            const allThs = Array.from(table.querySelector('thead tr').children);
            let activeEl = null, dir = 1;

            allThs.forEach(th => {
                if (!th.dataset.sort) return;
                const colIdx = allThs.indexOf(th);
                const type   = th.dataset.sort;

                const ico = document.createElement('span');
                ico.className = 'th-sort-icon';
                ico.innerHTML = '<svg width="8" height="11" viewBox="0 0 8 11" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle;margin-left:4px;opacity:0.3;transition:all 0.15s;"><path class="si-up" d="M1 4.5L4 1.5L7 4.5"/><path class="si-dn" d="M1 6.5L4 9.5L7 6.5"/></svg>';
                th.appendChild(ico);

                th.addEventListener('click', () => {
                    const same = activeEl === th;
                    dir = same ? -dir : 1;

                    allThs.forEach(t => {
                        if (!t.dataset.sort) return;
                        const s = t.querySelector('.th-sort-icon svg');
                        if (!s) return;
                        s.style.opacity = '0.3'; s.style.color = '';
                        s.querySelector('.si-up').style.opacity = '1';
                        s.querySelector('.si-dn').style.opacity = '1';
                        t.classList.remove('sort-active');
                        t.removeAttribute('aria-sort');
                    });

                    activeEl = th;
                    th.classList.add('sort-active');
                    const svg = ico.querySelector('svg');
                    svg.style.opacity = '0.9'; svg.style.color = 'var(--accent)';
                    if (dir === 1) {
                        svg.querySelector('.si-up').style.opacity = '1';
                        svg.querySelector('.si-dn').style.opacity = '0.2';
                        th.setAttribute('aria-sort', 'ascending');
                    } else {
                        svg.querySelector('.si-up').style.opacity = '0.2';
                        svg.querySelector('.si-dn').style.opacity = '1';
                        th.setAttribute('aria-sort', 'descending');
                    }

                    const tbody = table.querySelector('tbody');
                    const rows  = Array.from(tbody.querySelectorAll('tr'));
                    rows.sort((a, b) => {
                        const av = a.cells[colIdx]?.dataset.val ?? a.cells[colIdx]?.textContent.trim() ?? '';
                        const bv = b.cells[colIdx]?.dataset.val ?? b.cells[colIdx]?.textContent.trim() ?? '';
                        const cmp = type === 'number'
                            ? (parseFloat(av) || 0) - (parseFloat(bv) || 0)
                            : av.localeCompare(bv, 'es', { sensitivity: 'base', numeric: true });
                        return cmp * dir;
                    });
                    rows.forEach(r => tbody.appendChild(r));
                });
            });
        };
    })();

    // Register Service Worker (only on HTTPS or localhost)
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js')
                .catch(() => {}); // Silently skip on HTTP non-localhost
        });
    }

    // PWA install prompt
    (function () {
        const DISMISS_KEY = 'pwa-install-dismissed-at';
        const DISMISS_DAYS = 14;
        const banner = document.getElementById('pwa-install-banner');
        if (!banner) return;

        const isStandalone = window.matchMedia('(display-mode: standalone)').matches
            || window.navigator.standalone === true;
        if (isStandalone) return;

        const dismissedAt = parseInt(localStorage.getItem(DISMISS_KEY) || '0', 10);
        if (dismissedAt && (Date.now() - dismissedAt) < DISMISS_DAYS * 86400000) return;

        let deferred = null;

        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferred = e;
            banner.classList.add('visible');
        });

        document.getElementById('pwa-install-accept')?.addEventListener('click', async () => {
            if (!deferred) return;
            banner.classList.remove('visible');
            deferred.prompt();
            const { outcome } = await deferred.userChoice;
            if (outcome !== 'accepted') {
                localStorage.setItem(DISMISS_KEY, Date.now().toString());
            }
            deferred = null;
        });

        document.getElementById('pwa-install-dismiss')?.addEventListener('click', () => {
            banner.classList.remove('visible');
            localStorage.setItem(DISMISS_KEY, Date.now().toString());
        });

        window.addEventListener('appinstalled', () => {
            banner.classList.remove('visible');
            localStorage.removeItem(DISMISS_KEY);
        });
    })();
</script>

</body>
</html>
