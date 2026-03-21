<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Services\CartService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function login(Request $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['boolean'],
        ]);

        if (Auth::attempt(['email' => $validated['email'], 'password' => $validated['password']], $validated['remember'] ?? false)) {
            $user = Auth::user();
            if ($user->is_blocked) {
                Auth::logout();
                if ($request->expectsJson()) {
                    return response()->json(['errors' => ['email' => ['Ваш аккаунт заблокирован.']]], 422);
                }
                return back()->withErrors(['email' => 'Ваш аккаунт заблокирован.']);
            }
            $oldSessionId = session()->getId();
            $request->session()->regenerate();
            app(CartService::class)->mergeSessionToUser($user->id, $oldSessionId);
            if ($request->expectsJson()) {
                return response()->json(['redirect' => redirect()->intended(route('home'))->getTargetUrl()]);
            }
            return redirect()->intended(route('home'));
        }

        if ($request->expectsJson()) {
            return response()->json(['errors' => ['email' => ['Неверный email или пароль.']]], 422);
        }
        return back()->withErrors(['email' => 'Неверный email или пароль.']);
    }

    public function register(Request $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = \App\Models\User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
        ]);

        $oldSessionId = session()->getId();
        Auth::login($user);
        $request->session()->regenerate();
        app(CartService::class)->mergeSessionToUser($user->id, $oldSessionId);

        if (! $user->hasVerifiedEmail()) {
            try {
                $user->sendEmailVerificationNotification();
            } catch (\Throwable) {
                // Не ломаем регистрацию, если письмо не удалось отправить.
                // Пользователь сможет отправить ссылку повторно со страницы верификации.
            }
        }

        if ($request->expectsJson()) {
            return response()->json(['redirect' => route('home')]);
        }
        return redirect()->route('home')->with('message', 'Мы отправили письмо для подтверждения email.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
