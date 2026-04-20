<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invitación a FamFinance</title>
</head>
<body style="margin:0;padding:0;background:#0d0f14;font-family:'Courier New',monospace;color:#e8eaf2;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#0d0f14;padding:40px 20px;">
    <tr>
        <td align="center">
            <table width="520" cellpadding="0" cellspacing="0" style="background:#13161e;border:1px solid #252a38;border-radius:12px;overflow:hidden;">

                {{-- Header --}}
                <tr>
                    <td style="background:#13161e;padding:32px 40px 24px;border-bottom:1px solid #252a38;">
                        <div style="font-family:Georgia,serif;font-size:26px;font-weight:700;color:#4fffb0;letter-spacing:-0.02em;">
                            fam<span style="color:#e8eaf2;">finance</span>
                        </div>
                    </td>
                </tr>

                {{-- Body --}}
                <tr>
                    <td style="padding:32px 40px;">
                        <p style="font-size:13px;color:#6b7394;letter-spacing:0.08em;text-transform:uppercase;margin:0 0 8px;">INVITACIÓN AL GRUPO</p>
                        <h1 style="font-family:Georgia,serif;font-size:22px;font-weight:700;margin:0 0 20px;color:#e8eaf2;">
                            {{ $groupName }}
                        </h1>

                        <p style="font-size:14px;line-height:1.7;color:#9ca3b0;margin:0 0 24px;">
                            <strong style="color:#e8eaf2;">{{ $invitedBy }}</strong> te invitó a unirte al grupo
                            <strong style="color:#4fffb0;">{{ $groupName }}</strong> en FamFinance para
                            gestionar los gastos familiares en conjunto.
                        </p>

                        <div style="text-align:center;margin:32px 0;">
                            <a href="{{ $acceptUrl }}"
                               style="display:inline-block;background:#4fffb0;color:#0d0f14;font-family:'Courier New',monospace;font-size:13px;font-weight:700;text-decoration:none;padding:14px 36px;border-radius:7px;letter-spacing:0.04em;">
                                ACEPTAR INVITACIÓN
                            </a>
                        </div>

                        <p style="font-size:12px;color:#6b7394;margin:0 0 8px;">
                            O copiá este link en tu navegador:
                        </p>
                        <div style="background:#1a1e28;border:1px solid #252a38;border-radius:6px;padding:10px 14px;font-size:11px;color:#4fffb0;word-break:break-all;">
                            {{ $acceptUrl }}
                        </div>
                    </td>
                </tr>

                {{-- Footer --}}
                <tr>
                    <td style="padding:20px 40px;border-top:1px solid #252a38;">
                        <p style="font-size:11px;color:#6b7394;margin:0;line-height:1.6;">
                            Esta invitación expira el <strong style="color:#e8eaf2;">{{ $expiresAt }}</strong>.
                            Si no esperabas este email, podés ignorarlo.
                        </p>
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>
</body>
</html>
