<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeadersMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=(), payment=()');
        $response->headers->set(
            'Content-Security-Policy',
            "default-src 'self'; base-uri 'self'; object-src 'none'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://api-maps.yandex.ru https://res.smartwidgets.ru https://vk.com https://*.vk.com; style-src 'self' 'unsafe-inline'; img-src 'self' data: blob: https: http:; font-src 'self' data: https://res.smartwidgets.ru https://*.smartwidgets.ru; connect-src 'self' https://api-maps.yandex.ru https://*.yandex.ru https://*.yandex.net https://res.smartwidgets.ru https://*.smartwidgets.ru https://vk.com https://*.vk.com; frame-src 'self' https://www.youtube.com https://youtube.com https://rutube.ru https://www.rutube.ru https://yandex.ru https://www.yandex.ru https://vk.com https://*.vk.com https://login.vk.com https://res.smartwidgets.ru https://*.smartwidgets.ru; frame-ancestors 'self';"
        );

        return $response;
    }
}
