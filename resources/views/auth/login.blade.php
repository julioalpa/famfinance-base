<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ingresar — FamFinance</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;700;800&family=DM+Mono:wght@300;400&display=swap" rel="stylesheet">
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
            position: relative;
            overflow: hidden;
        }

        /* Grid de fondo decorativo */
        body::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(79,255,176,0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(79,255,176,0.03) 1px, transparent 1px);
            background-size: 40px 40px;
            pointer-events: none;
        }

        /* Glow central */
        body::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(79,255,176,0.06) 0%, transparent 70%);
            pointer-events: none;
        }

        .login-box {
            position: relative;
            z-index: 10;
            width: 380px;
            background: #13161e;
            border: 1px solid #252a38;
            border-radius: 14px;
            padding: 48px 40px;
            text-align: center;
        }

        .logo {
            font-family: 'Syne', sans-serif;
            font-size: 32px;
            font-weight: 800;
            letter-spacing: -0.03em;
            color: #4fffb0;
            margin-bottom: 4px;
        }

        .logo span { color: #e8eaf2; }

        .tagline {
            font-size: 12px;
            color: #6b7394;
            letter-spacing: 0.08em;
            margin-bottom: 40px;
        }

        .divider {
            width: 32px;
            height: 1px;
            background: #252a38;
            margin: 0 auto 40px;
        }

        .btn-google {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            width: 100%;
            padding: 13px 20px;
            background: #1a1e28;
            border: 1px solid #252a38;
            border-radius: 8px;
            color: #e8eaf2;
            font-family: 'DM Mono', monospace;
            font-size: 13px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.15s;
        }

        .btn-google:hover {
            border-color: #4fffb0;
            background: rgba(79,255,176,0.05);
            color: #4fffb0;
        }

        .footer-note {
            margin-top: 28px;
            font-size: 11px;
            color: #6b7394;
            line-height: 1.6;
        }

        .error-msg {
            background: rgba(255,77,109,0.1);
            border: 1px solid rgba(255,77,109,0.25);
            color: #ff4d6d;
            border-radius: 6px;
            padding: 10px 14px;
            font-size: 12px;
            margin-bottom: 20px;
            text-align: left;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <div class="logo">fam<span>finance</span></div>
        <div class="tagline">CONTROL FINANCIERO FAMILIAR</div>
        <div class="divider"></div>

        @if($errors->any())
            <div class="error-msg">{{ $errors->first() }}</div>
        @endif

        @if(session('info'))
            <div style="background: rgba(61,232,255,0.1); border: 1px solid rgba(61,232,255,0.25); color: #3de8ff; border-radius: 6px; padding: 10px 14px; font-size: 12px; margin-bottom: 20px; text-align: left;">
                {{ session('info') }}
            </div>
        @endif

        <a href="{{ route('auth.google') }}" class="btn-google">
            {{-- Google SVG --}}
            <svg width="18" height="18" viewBox="0 0 18 18">
                <path d="M17.64 9.2c0-.637-.057-1.251-.164-1.84H9v3.481h4.844c-.209 1.125-.843 2.078-1.796 2.717v2.258h2.908c1.702-1.567 2.684-3.875 2.684-6.615z" fill="#4285F4"/>
                <path d="M9 18c2.43 0 4.467-.806 5.956-2.18l-2.908-2.259c-.806.54-1.837.86-3.048.86-2.344 0-4.328-1.584-5.036-3.711H.957v2.332A8.997 8.997 0 0 0 9 18z" fill="#34A853"/>
                <path d="M3.964 10.71A5.41 5.41 0 0 1 3.682 9c0-.593.102-1.17.282-1.71V4.958H.957A8.996 8.996 0 0 0 0 9c0 1.452.348 2.827.957 4.042l3.007-2.332z" fill="#FBBC05"/>
                <path d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.891 11.426 0 9 0A8.997 8.997 0 0 0 .957 4.958L3.964 7.29C4.672 5.163 6.656 3.58 9 3.58z" fill="#EA4335"/>
            </svg>
            Continuar con Google
        </a>

        <p class="footer-note">
            Al ingresar aceptás el uso de la aplicación<br>para gestión de gastos familiares.
        </p>
    </div>
</body>
</html>
