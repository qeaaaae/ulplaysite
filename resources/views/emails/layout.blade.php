<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>@yield('title', 'UlPlay')</title>
</head>
<body style="margin:0; padding:0; background:#f1f5f9; -webkit-font-smoothing:antialiased;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:linear-gradient(160deg, #f8fafc 0%, #e2e8f0 50%, #cbd5e1 100%); min-height:100vh;">
        <tr>
            <td align="center" style="padding:48px 20px;">
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center" style="max-width:580px; width:100%;">
                    <tr>
                        <td align="center" style="padding-bottom:32px;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center">
                                <tr>
                                    <td style="width:72px; height:72px; background:linear-gradient(145deg, #0284c7 0%, #0369a1 100%); border-radius:20px; text-align:center; vertical-align:middle; box-shadow:0 8px 24px rgba(2,132,199,0.4);">
                                        <img src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAzMiAzMiIgZmlsbD0ibm9uZSI+PGNpcmNsZSBjeD0iMTYiIGN5PSIxNiIgcj0iMTQiIGZpbGw9IiMwMjg0YzciLz48cGF0aCBmaWxsPSIjZmZmIiBkPSJNMTMgMTB2MTJsOS02LTktNnoiLz48L3N2Zz4=" width="40" height="40" alt="" style="display:block; margin:0 auto;" />
                                    </td>
                                </tr>
                            </table>
                            <div style="font-family:'Segoe UI', system-ui, -apple-system, sans-serif; font-size:26px; font-weight:800; color:#0f172a; margin-top:18px; letter-spacing:-0.03em;">UlPlay</div>
                            <div style="font-family:'Segoe UI', system-ui, -apple-system, sans-serif; font-size:13px; color:#64748b; margin-top:4px;">Интернет-магазин игр и консолей</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="background:#ffffff; border-radius:24px; overflow:hidden; box-shadow:0 8px 40px rgba(15,23,42,0.12), 0 2px 8px rgba(0,0,0,0.04);">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td style="background:linear-gradient(135deg, #0284c7 0%, #0ea5e9 100%); height:8px;"></td>
                                </tr>
                                <tr>
                                    <td style="padding:0;">
                                        @yield('content')
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding-top:28px;">
                            <div style="font-family:'Segoe UI', system-ui, -apple-system, sans-serif; font-size:11px; color:#94a3b8; line-height:1.5;">
                                Письмо отправлено автоматически &middot; UlPlay &copy; {{ date('Y') }}
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
