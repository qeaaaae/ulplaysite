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
    private function resolvePostAuthRedirect(Request $request): string
    {
        $fallback = route('home');
        $referer = (string) $request->headers->get('referer', '');
        if ($referer === '') {
            return $fallback;
        }

        $parts = parse_url($referer);
        if (!is_array($parts)) {
            return $fallback;
        }

        $host = $parts['host'] ?? '';
        if ($host !== '' && $host !== (string) $request->getHost()) {
            return $fallback;
        }

        $path = (string) ($parts['path'] ?? '/');
        if (in_array($path, ['/login', '/register'], true)) {
            return $fallback;
        }

        $query = isset($parts['query']) && $parts['query'] !== '' ? ('?' . $parts['query']) : '';

        return $path . $query;
    }

    public function login(Request $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $guestSessionId = session()->getId();

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
            $cart = app(CartService::class);
            $mergedFromSession = $cart->mergeSessionToUser($user->id, $guestSessionId);
            if ($mergedFromSession === 0) {
                $guestCart = $request->input('_guest_cart');
                if (is_string($guestCart)) {
                    $data = json_decode($guestCart, true);
                    if (is_array($data)) {
                        $cart->restoreGuestCartToUser(
                            $user->id,
                            $data['products'] ?? [],
                            $data['services'] ?? []
                        );
                    }
                }
            }
            $request->session()->regenerate();
            $redirectFallback = $this->resolvePostAuthRedirect($request);
            if ($request->expectsJson()) {
                return response()->json(['redirect' => redirect()->intended($redirectFallback)->getTargetUrl()]);
            }
            return redirect()->intended($redirectFallback);
        }

        if ($request->expectsJson()) {
            return response()->json(['errors' => ['email' => ['Неверный email или пароль.']]], 422);
        }
        return back()->withErrors(['email' => 'Неверный email или пароль.']);
    }

    public function register(Request $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $guestSessionId = session()->getId();

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

        Auth::login($user);
        $cart = app(CartService::class);
        $mergedFromSession = $cart->mergeSessionToUser($user->id, $guestSessionId);
        if ($mergedFromSession === 0) {
            $guestCart = $request->input('_guest_cart');
            if (is_string($guestCart)) {
                $data = json_decode($guestCart, true);
                if (is_array($data)) {
                    $cart->restoreGuestCartToUser(
                        $user->id,
                        $data['products'] ?? [],
                        $data['services'] ?? []
                    );
                }
            }
        }
        $request->session()->regenerate();

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
