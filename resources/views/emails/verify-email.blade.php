<!doctype html>
<html lang="ru">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Подтвердите email</title>
    </head>
    <body style="margin:0; padding:0; background:#f5f5f5;">
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f5f5f5;">
            <tr>
                <td align="center" style="padding:24px 12px;">
                    <table role="presentation" width="600" cellspacing="0" cellpadding="0" style="background:#ffffff; border-radius:16px; overflow:hidden; box-shadow:0 6px 24px rgba(0,0,0,0.06);">
                        <tr>
                            <td style="background:#0ea5e9; padding:22px 24px;">
                                <table role="presentation" cellspacing="0" cellpadding="0" style="width:100%;">
                                    <tr>
                                        <td style="vertical-align:middle;">
                                            <img src="{{ asset('favicon.svg') }}" width="28" height="28" alt="UlPlay" style="display:block; border:0;">
                                        </td>
                                        <td style="vertical-align:middle; padding-left:12px;">
                                            <div style="font-family:Arial, Helvetica, sans-serif; font-size:18px; font-weight:700; color:#ffffff;">
                                                UlPlay
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>

                        <tr>
                            <td style="padding:26px 24px 10px;">
                                <div style="font-family:Arial, Helvetica, sans-serif; color:#0f172a; font-size:22px; font-weight:800; line-height:1.25;">
                                    Подтвердите адрес электронной почты
                                </div>
                                <div style="font-family:Arial, Helvetica, sans-serif; color:#475569; font-size:14px; margin-top:10px; line-height:1.6;">
                                    Мы отправили письмо со ссылкой подтверждения. Перейдите по кнопке ниже, чтобы завершить регистрацию.
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <td align="center" style="padding:18px 24px 10px;">
                                <a href="{{ $verificationUrl }}"
                                   style="display:inline-block; background:#0ea5e9; color:#ffffff; text-decoration:none; font-family:Arial, Helvetica, sans-serif; font-size:15px; font-weight:700; padding:14px 22px; border-radius:12px;">
                                    Подтвердить email
                                </a>
                            </td>
                        </tr>

                        <tr>
                            <td style="padding:0 24px 22px;">
                                <div style="font-family:Arial, Helvetica, sans-serif; color:#64748b; font-size:12px; line-height:1.6;">
                                    Если кнопка не работает, скопируйте ссылку в браузер:<br>
                                    <a href="{{ $verificationUrl }}" style="color:#0ea5e9; word-break:break-all;">{{ $verificationUrl }}</a>
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <td style="background:#f8fafc; padding:18px 24px; border-top:1px solid #e2e8f0;">
                                <div style="font-family:Arial, Helvetica, sans-serif; color:#64748b; font-size:12px; line-height:1.6;">
                                    UlPlay — интернет-магазин игровых консолей и аксессуаров.
                                </div>
                            </td>
                        </tr>
                    </table>

                    <div style="height:14px; line-height:14px;">&nbsp;</div>
                    <div style="font-family:Arial, Helvetica, sans-serif; color:#94a3b8; font-size:11px;">
                        Это письмо отправлено автоматически.
                    </div>
                </td>
            </tr>
        </table>
    </body>
</html>

