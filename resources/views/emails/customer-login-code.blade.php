<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Codigo de acesso</title>
</head>
<body style="margin:0;padding:0;background:#070a0e;font-family:Arial,Helvetica,sans-serif;color:#e8eef4;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#070a0e;padding:32px 14px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:560px;background:#10161d;border:1px solid #243140;border-radius:16px;overflow:hidden;">
                    <tr>
                        <td style="padding:28px 28px 18px;text-align:center;background:linear-gradient(135deg,#0b1016 0%,#111b24 58%,#073717 100%);">
                            <div style="display:inline-block;width:58px;height:58px;line-height:58px;border-radius:50%;background:rgba(0,214,79,.12);border:1px solid rgba(0,214,79,.38);color:#00d64f;font-size:26px;font-weight:700;">M</div>
                            <h1 style="margin:18px 0 8px;font-size:30px;line-height:1;color:#ffffff;text-transform:uppercase;letter-spacing:.02em;">Codigo de acesso</h1>
                            <p style="margin:0;color:#aab7c4;font-size:15px;line-height:1.5;">Use o codigo abaixo para entrar ou criar sua conta.</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px;text-align:center;">
                            <div style="display:inline-block;background:#080c11;border:1px solid #263544;border-radius:14px;padding:18px 24px;color:#00d64f;font-size:38px;font-weight:800;letter-spacing:10px;">
                                {{ $code }}
                            </div>
                            <p style="margin:22px 0 0;color:#aab7c4;font-size:14px;line-height:1.6;">Esse codigo expira em {{ $expiresInMinutes }} minutos. Se voce nao pediu esse acesso, pode ignorar este e-mail.</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:18px 28px 26px;border-top:1px solid #1f2935;text-align:center;color:#6f7d8b;font-size:12px;">
                            Moto Acessorios - compra segura, atendimento rapido e produtos para sua moto.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
