<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurar grupo — FamFinance</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;700;800&family=DM+Mono:wght@300;400;500&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background: #0d0f14;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'DM Mono', monospace;
            color: #e8eaf2;
        }
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(rgba(79,255,176,0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(79,255,176,0.03) 1px, transparent 1px);
            background-size: 40px 40px;
            pointer-events: none;
        }
        .container {
            position: relative;
            z-index: 10;
            width: 440px;
        }
        .header {
            text-align: center;
            margin-bottom: 32px;
        }
        .logo {
            font-family: 'Syne', sans-serif;
            font-size: 24px;
            font-weight: 800;
            color: #4fffb0;
            margin-bottom: 16px;
        }
        .logo span { color: #e8eaf2; }
        h1 {
            font-family: 'Syne', sans-serif;
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 6px;
        }
        .subtitle { font-size: 12px; color: #6b7394; }

        .card {
            background: #13161e;
            border: 1px solid #252a38;
            border-radius: 12px;
            padding: 28px 32px;
            margin-bottom: 16px;
        }
        .card-title {
            font-family: 'Syne', sans-serif;
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .card-title .dot {
            width: 6px; height: 6px;
            border-radius: 50%;
            background: #4fffb0;
        }

        .form-group { margin-bottom: 18px; }
        .form-label {
            display: block;
            font-size: 11px;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: #6b7394;
            margin-bottom: 6px;
        }
        .form-input {
            width: 100%;
            background: #1a1e28;
            border: 1px solid #252a38;
            border-radius: 6px;
            padding: 10px 14px;
            color: #e8eaf2;
            font-family: 'DM Mono', monospace;
            font-size: 13px;
            transition: border-color 0.15s;
        }
        .form-input:focus { outline: none; border-color: #4fffb0; }
        .form-input::placeholder { color: #6b7394; }

        .btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            width: 100%;
            padding: 11px 20px;
            border-radius: 7px;
            font-family: 'DM Mono', monospace;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.15s;
            border: none;
        }
        .btn-primary { background: #4fffb0; color: #0d0f14; }
        .btn-primary:hover { background: #3de89a; }
        .btn-ghost { background: transparent; color: #6b7394; border: 1px solid #252a38; text-decoration: none; }
        .btn-ghost:hover { color: #e8eaf2; border-color: #6b7394; }

        .or-divider {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #6b7394;
            font-size: 11px;
            margin: 4px 0;
        }
        .or-divider::before, .or-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #252a38;
        }

        .error { font-size: 12px; color: #ff4d6d; margin-top: 6px; }

        .user-hint {
            text-align: center;
            font-size: 11px;
            color: #6b7394;
            margin-top: 20px;
        }
        .user-hint a { color: #4fffb0; text-decoration: none; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <div class="logo">fam<span>finance</span></div>
        <h1>Configurá tu grupo</h1>
        <p class="subtitle">Creá un grupo nuevo o unite a uno existente</p>
    </div>

    {{-- Crear nuevo grupo --}}
    <div class="card">
        <div class="card-title">
            <span class="dot"></span>
            Crear grupo nuevo
        </div>
        <form method="POST" action="{{ route('family-groups.store') }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Nombre del grupo</label>
                <input type="text"
                       name="name"
                       class="form-input"
                       placeholder="Ej: Familia García"
                       value="{{ old('name') }}"
                       autofocus>
                @error('name')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>
            <button type="submit" class="btn btn-primary">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
                Crear grupo
            </button>
        </form>
    </div>

    <div class="or-divider">o</div>

    {{-- Ingresar link de invitación --}}
    <div class="card">
        <div class="card-title">
            <span class="dot" style="background: #3de8ff;"></span>
            Unirte con invitación
        </div>
        <p style="font-size: 12px; color: #6b7394; margin-bottom: 16px;">
            Si alguien de tu familia te compartió un link, ingresalo acá.
        </p>
        <div class="form-group">
            <label class="form-label">Link de invitación</label>
            <input type="text"
                   id="invite-link"
                   class="form-input"
                   placeholder="https://famfinance.app/invitacion/...">
        </div>
        <button
            onclick="window.location.href = document.getElementById('invite-link').value"
            class="btn btn-ghost"
            style="border-color: #3de8ff; color: #3de8ff;">
            Unirme al grupo →
        </button>
    </div>

    <div class="user-hint">
        Sesión iniciada como <strong style="color: #e8eaf2;">{{ auth()->user()->email }}</strong>
        — <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Cerrar sesión</a>
    </div>
    <form id="logout-form" method="POST" action="{{ route('logout') }}" style="display:none">@csrf</form>
</div>
</body>
</html>
