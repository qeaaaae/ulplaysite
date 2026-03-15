<?php

namespace App\Providers;

use App\Models\User;
use App\Services\CartService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
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
        RateLimiter::for('auth', fn (Request $request) => Limit::perMinute(5)->by($request->ip()));
        RateLimiter::for('password', fn (Request $request) => Limit::perMinute(3)->by($request->ip()));
        RateLimiter::for('cart', fn (Request $request) => Limit::perMinute(30)->by($request->user()?->id ?: $request->ip()));
        RateLimiter::for('orders', fn (Request $request) => Limit::perMinute(10)->by($request->user()?->id ?: $request->ip()));
        RateLimiter::for('profile', fn (Request $request) => Limit::perMinute(10)->by($request->user()?->id ?: $request->ip()));
        RateLimiter::for('admin', fn (Request $request) => Limit::perMinute(120)->by($request->user()?->id ?: $request->ip()));
        RateLimiter::for('reviews', fn (Request $request) => Limit::perMinute(10)->by($request->user()?->id ?: $request->ip()));
    }
}
