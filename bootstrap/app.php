<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')->group(base_path('routes/admin.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Если приложение разворачивается за одним nginx без внешних балансировщиков,
        // доверять всем прокси не нужно — используем стандартное поведение Laravel.
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
            'verified_if_auth' => \App\Http\Middleware\VerifiedIfAuthenticated::class,
        ]);
        $middleware->appendToGroup('web', \App\Http\Middleware\SecurityHeadersMiddleware::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (PostTooLargeException $e, Request $request) {
            $postMax = (string) ini_get('post_max_size');
            $uploadMax = (string) ini_get('upload_max_filesize');
            $message = "Размер запроса превышает лимит PHP (post_max_size={$postMax}, upload_max_filesize={$uploadMax}). "
                . 'Остановите сервер и запустите: composer serve (не artisan serve — у него лимит 8 МБ).';

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => $message,
                    'result' => false,
                ], Response::HTTP_REQUEST_ENTITY_TOO_LARGE);
            }

            $field = $request->is('admin/about') ? 'images' : 'images';

            return redirect()
                ->back()
                ->withErrors([$field => $message]);
        });

        $exceptions->render(function (ThrottleRequestsException $e, Request $request) {
            $message = __('http-statuses.throttle');
            $headers = $e->getHeaders();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => $message,
                    'result' => false,
                ], 429, $headers);
            }

            return response()->view('errors.layout', ['exception' => $e], 429, $headers);
        });
    })->create();
