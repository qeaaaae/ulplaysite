@extends('emails.layout')

@section('title', 'Восстановление пароля')

@section('content')
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
    <tr>
        <td style="padding:40px 36px 24px;">
            <div style="font-family:'Segoe UI', system-ui, -apple-system, sans-serif; font-size:24px; font-weight:700; color:#0f172a; line-height:1.25; letter-spacing:-0.02em;">
                Восстановление пароля
            </div>
            <div style="font-family:'Segoe UI', system-ui, -apple-system, sans-serif; font-size:15px; color:#475569; margin-top:14px; line-height:1.65;">
                Вы запросили сброс пароля. Нажмите кнопку ниже, чтобы задать новый пароль. Ссылка действительна <strong>{{ $expireMinutes }} минут</strong>.
            </div>
        </td>
    </tr>
    <tr>
        <td align="center" style="padding:12px 36px 32px;">
            <a href="{{ $resetUrl }}"
               style="display:inline-block; background:linear-gradient(135deg, #0284c7 0%, #0ea5e9 100%); color:#ffffff !important; text-decoration:none; font-family:'Segoe UI', system-ui, -apple-system, sans-serif; font-size:16px; font-weight:600; padding:16px 36px; border-radius:14px; box-shadow:0 4px 14px rgba(2,132,199,0.35);">
                Сбросить пароль
            </a>
        </td>
    </tr>
    <tr>
        <td style="padding:28px 36px 36px; background:#f8fafc; border-top:1px solid #e2e8f0;">
            <div style="font-family:'Segoe UI', system-ui, -apple-system, sans-serif; font-size:12px; color:#64748b; line-height:1.65;">
                <strong style="color:#475569;">Кнопка не работает?</strong> Скопируйте ссылку в браузер:<br>
                <a href="{{ $resetUrl }}" style="color:#0284c7; word-break:break-all; text-decoration:underline; margin-top:6px; display:inline-block;">{{ $resetUrl }}</a>
            </div>
            <div style="font-family:'Segoe UI', system-ui, -apple-system, sans-serif; font-size:12px; color:#94a3b8; margin-top:16px; line-height:1.5;">
                Если вы не запрашивали сброс пароля, просто проигнорируйте это письмо.
            </div>
        </td>
    </tr>
</table>
@endsection
