<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class ForgotPasswordController extends Controller
{
    public function showLinkRequestForm(): View
    {
        return view('auth.forgot-password', ['metaTitle' => 'Восстановление пароля']);
    }

    public function sendResetLinkEmail(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            if ($request->expectsJson()) {
                return response()->json(['result' => true, 'message' => 'Ссылка для сброса пароля отправлена на указанный email.']);
            }
            return back()->with('status', __($status));
        }

        if ($request->expectsJson()) {
            return response()->json([
                'result' => false,
                'message' => __($status),
                'errors' => ['email' => [__($status)]],
            ], 422);
        }

        return back()->withErrors(['email' => __($status)]);
    }
}
