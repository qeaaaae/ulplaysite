<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class VerifiedIfAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return $next($request);
        }

        // Если пользователь не авторизован — ничего не делаем.
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (! $user || $user->hasVerifiedEmail()) {
            return $next($request);
        }

        // Не мешаем маршрутам верификации, но блокируем любые "изменяющие" действия.
        $isVerificationRoute = $request->routeIs('verification.notice')
            || $request->routeIs('verification.verify')
            || $request->routeIs('verification.send')
            || $request->routeIs('logout');

        // Для изменения данных (POST/PATCH/PUT/DELETE) блокируем.
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true) && ! $isVerificationRoute) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Подтвердите адрес электронной почты.'], 403);
            }

            abort(403, 'Подтвердите адрес электронной почты.');
        }

        // Для GET страниц — просто даём отрендерить, а модалка покажется в layout.
        return $next($request);
    }
}

