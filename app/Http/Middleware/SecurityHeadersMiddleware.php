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
        $response->headers->set(
            'Content-Security-Policy',
            "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://api-maps.yandex.ru https://res.smartwidgets.ru; style-src 'self' 'unsafe-inline'; img-src 'self' data: blob: https: http:; font-src 'self' data: https://res.smartwidgets.ru https://*.smartwidgets.ru; connect-src 'self' https://api-maps.yandex.ru https://*.yandex.ru https://*.yandex.net https://res.smartwidgets.ru https://*.smartwidgets.ru; frame-src 'self' https://www.youtube.com https://youtube.com https://rutube.ru https://www.rutube.ru https://yandex.ru https://www.yandex.ru https://res.smartwidgets.ru https://*.smartwidgets.ru; frame-ancestors 'self';"
        );

        return $response;
    }
}
