<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ingresar — FamFinance</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400;12..96,600;12..96,700;12..96,800&family=Nunito:wght@400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            background: #09090b;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Nunito', sans-serif;
            position: relative;
            overflow: hidden;
            -webkit-font-smoothing: antialiased;
        }

        /* Aurora blobs */
        .aurora {
            position: absolute;
            inset: 0;
            pointer-events: none;
            overflow: hidden;
        }

        .blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(90px);
        }

        .blob-1 {
            width: 550px; height: 550px;
            background: #f0a030;
            opacity: 0.18;
            top: -160px; left: -180px;
            animation: drift 10s ease-in-out infinite;
        }

        .blob-2 {
            width: 420px; height: 420px;
            background: #4e9bff;
            opacity: 0.14;
            bottom: -120px; right: -120px;
            animation: drift 13s ease-in-out infinite reverse;
        }

        .blob-3 {
            width: 280px; height: 280px;
            background: #2dd870;
            opacity: 0.08;
            top: 55%; left: 58%;
            animation: drift 16s ease-in-out infinite 3s;
        }

        @keyframes drift {
            0%,100% { transform: translate(0, 0) scale(1); }
            33%      { transform: translate(28px, -18px) scale(1.06); }
            66%      { transform: translate(-18px, 14px) scale(0.96); }
        }

        /* Subtle grid */
        .grid-bg {
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(240,160,48,0.025) 1px, transparent 1px),
                linear-gradient(90deg, rgba(240,160,48,0.025) 1px, transparent 1px);
            background-size: 48px 48px;
            pointer-events: none;
        }

        /* Card */
        .login-box {
            position: relative;
            z-index: 10;
            width: 400px;
            background: rgba(17, 17, 21, 0.82);
            border: 1px solid rgba(40, 40, 52, 0.9);
            border-radius: 20px;
            padding: 50px 44px;
            text-align: center;
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            box-shadow:
                0 32px 80px rgba(0,0,0,0.55),
                0 0 0 1px rgba(255,255,255,0.04) inset;
            animation: appear 0.4s cubic-bezier(0.4, 0, 0.2, 1) both;
        }

        @keyframes appear {
            from { opacity: 0; transform: translateY(16px) scale(0.98); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }

        .logo {
            font-family: 'Bricolage Grotesque', sans-serif;
            font-size: 38px;
            font-weight: 800;
            letter-spacing: -0.04em;
            color: #eeebe4;
            margin-bottom: 6px;
            line-height: 1;
        }

        .logo .accent { color: #f0a030; }

        .tagline {
            font-size: 11px;
            color: #6a6676;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            font-weight: 700;
            margin-bottom: 36px;
        }

        .divider {
            width: 48px; height: 2px;
            background: linear-gradient(90deg, transparent, #f0a030, transparent);
            margin: 0 auto 36px;
            border-radius: 1px;
        }

        .btn-google {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            width: 100%;
            padding: 14px 22px;
            background: #18181e;
            border: 1px solid #282834;
            border-radius: 11px;
            color: #eeebe4;
            font-family: 'Nunito', sans-serif;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-google:hover {
            border-color: #f0a030;
            background: rgba(240,160,48,0.07);
            color: #f0a030;
            transform: translateY(-2px);
            box-shadow: 0 10px 28px rgba(240,160,48,0.16);
        }

        .btn-google:active { transform: translateY(0); }

        .footer-note {
            margin-top: 28px;
            font-size: 12px;
            color: #6a6676;
            line-height: 1.7;
        }

        .error-msg {
            background: rgba(240,64,96,0.1);
            border: 1px solid rgba(240,64,96,0.25);
            color: #f04060;
            border-radius: 9px;
            padding: 12px 16px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 20px;
            text-align: left;
        }

        .info-msg {
            background: rgba(78,155,255,0.1);
            border: 1px solid rgba(78,155,255,0.25);
            color: #4e9bff;
            border-radius: 9px;
            padding: 12px 16px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 20px;
            text-align: left;
        }

        @media (max-width: 480px) {
            .login-box { width: calc(100vw - 28px); padding: 40px 28px; }
            .logo { font-size: 32px; }
        }
    </style>
</head>
<body>
    <div class="aurora">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
        <div class="blob blob-3"></div>
    </div>
    <div class="grid-bg"></div>

    <div class="login-box">
        <div class="logo"><span class="accent">fam</span>finance</div>
        <div class="tagline">Control financiero familiar</div>
        <div class="divider"></div>

        @if($errors->any())
            <div class="error-msg">{{ $errors->first() }}</div>
        @endif

        @if(session('info'))
            <div class="info-msg">{{ session('info') }}</div>
        @endif

        <a href="{{ route('auth.google') }}" class="btn-google">
            <svg width="18" height="18" viewBox="0 0 18 18">
                <path d="M17.64 9.2c0-.637-.057-1.251-.164-1.84H9v3.481h4.844c-.209 1.125-.843 2.078-1.796 2.717v2.258h2.908c1.702-1.567 2.684-3.875 2.684-6.615z" fill="#4285F4"/>
                <path d="M9 18c2.43 0 4.467-.806 5.956-2.18l-2.908-2.259c-.806.54-1.837.86-3.048.86-2.344 0-4.328-1.584-5.036-3.711H.957v2.332A8.997 8.997 0 0 0 9 18z" fill="#34A853"/>
                <path d="M3.964 10.71A5.41 5.41 0 0 1 3.682 9c0-.593.102-1.17.282-1.71V4.958H.957A8.996 8.996 0 0 0 0 9c0 1.452.348 2.827.957 4.042l3.007-2.332z" fill="#FBBC05"/>
                <path d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.891 11.426 0 9 0A8.997 8.997 0 0 0 .957 4.958L3.964 7.29C4.672 5.163 6.656 3.58 9 3.58z" fill="#EA4335"/>
            </svg>
            Continuar con Google
        </a>

        <a href="{{ route('auth.google.switch') }}" class="btn-google" style="margin-top: 10px; opacity: 0.65; font-size: 13px;">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
            Usar otra cuenta de Google
        </a>

        <p class="footer-note">
            Al ingresar aceptás el uso de la aplicación<br>para gestión de gastos familiares.
        </p>
    </div>
</body>
</html>
