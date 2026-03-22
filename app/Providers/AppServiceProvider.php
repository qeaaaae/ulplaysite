<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\VerifyEmail;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use App\Models\UserNotification;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Ensure string columns used in indexed constraints (e.g. unique emails) don't exceed
        // MySQL's maximum index length for utf8mb4.
        Schema::defaultStringLength(191);

        // Custom design for "Verify email" mail.
        VerifyEmail::toMailUsing(function ($notifiable, string $verificationUrl) {
            return (new MailMessage)
                ->subject('Подтвердите адрес электронной почты')
                ->view('emails.verify-email', [
                    'verificationUrl' => $verificationUrl,
                    'user' => $notifiable,
                ]);
        });

        Route::bind('user', fn (string $value) => User::withTrashed()->findOrFail($value));
        $this->configureRateLimiting();
        Paginator::useTailwind();

        View::composer(['layouts.app', 'home', 'products.index', 'products.show', 'components.product-card'], function ($view) {
            $cartService = app(CartService::class);
            $cartProductIds = $cartService->getItems()->pluck('product_id')->filter()->values()->all();
            $view->with([
                'cartCount' => $view->getData()['cartCount'] ?? $cartService->count(),
                'cartProductIds' => $view->getData()['cartProductIds'] ?? $cartProductIds,
                'isAuthenticated' => $view->getData()['isAuthenticated'] ?? Auth::check(),
                'notificationsUnreadCount' => $view->getData()['notificationsUnreadCount'] ?? (Auth::check()
                    ? (Schema::hasTable('user_notifications')
                        ? UserNotification::query()
                            ->where('user_id', Auth::id())
                            ->whereNull('read_at')
                            ->count()
                        : 0)
                    : 0),
                'footerData' => $view->getData()['footerData'] ?? config('site.footer', [
                    'company' => ['name' => 'UlPlay', 'description' => 'Интернет-магазин игровых консолей и аксессуаров в Ульяновске', 'phone' => '+7(927)988-88-70', 'email' => 'info@ulplay.com'],
                    'categories' => [
                        ['name' => 'PlayStation', 'url' => '/products?category=playstation'],
                        ['name' => 'Xbox', 'url' => '/products?category=xbox'],
                        ['name' => 'Аксессуары', 'url' => '/products?category=accessories'],
                        ['name' => 'Игры', 'url' => '/products?category=games'],
                    ],
                    'services' => [
                        ['name' => 'Ремонт', 'url' => '/services/repair'],
                        ['name' => 'Купим ваше', 'url' => '/services/buy'],
                    ],
                    'links' => [
                        ['name' => 'О нас', 'url' => '/about'],
                        ['name' => 'Доставка и оплата', 'url' => '/delivery'],
                        ['name' => 'Контакты', 'url' => '/contacts'],
                    ],
                    'social' => [
                        ['name' => 'VK', 'url' => '#', 'icon' => 'vk'],
                        ['name' => 'Telegram', 'url' => '#', 'icon' => 'telegram'],
                    ],
                ]),
            ]);
        });
    }

    private function configureRateLimiting(): void
    {
        $max = fn (string $key, int $default) => config("throttle.{$key}.max_attempts", $default);

        RateLimiter::for('auth', fn (Request $r) => Limit::perMinute($max('auth', 5))->by($r->ip()));
        RateLimiter::for('password', fn (Request $r) => Limit::perMinute($max('password', 3))->by($r->ip()));
        RateLimiter::for('cart', fn (Request $r) => Limit::perMinute($max('cart', 30))->by($r->user()?->id ?? $r->ip()));
        RateLimiter::for('orders', fn (Request $r) => Limit::perMinute($max('orders', 10))->by($r->user()?->id ?? $r->ip()));
        RateLimiter::for('support', fn (Request $r) => Limit::perMinute($max('support', 5))->by($r->user()?->id ?? $r->ip()));
        RateLimiter::for('profile', fn (Request $r) => Limit::perMinute($max('profile', 10))->by($r->user()?->id ?? $r->ip()));
        RateLimiter::for('admin', fn (Request $r) => Limit::perMinute($max('admin', 120))->by($r->user()?->id ?? $r->ip()));
        RateLimiter::for('reviews', fn (Request $r) => Limit::perMinute($max('reviews', 10))->by($r->user()?->id ?? $r->ip()));
    }
}
