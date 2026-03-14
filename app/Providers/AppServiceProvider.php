<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.app', function ($view) {
            $view->with([
                'cartCount' => $view->getData()['cartCount'] ?? 0,
                'isAuthenticated' => $view->getData()['isAuthenticated'] ?? false,
                'footerData' => $view->getData()['footerData'] ?? [
                    'company' => ['name' => 'UlPlay', 'description' => 'Интернет-магазин игровых консолей и аксессуаров в Ульяновске', 'phone' => '+7 (927) 988-88-70', 'email' => 'info@ulplay.com'],
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
                ],
            ]);
        });
    }
}
