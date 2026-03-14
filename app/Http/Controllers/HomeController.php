<?php

declare(strict_types=1);

namespace App\Http\Controllers;

class HomeController extends Controller
{
    private const IMAGE_PRODUCTS = 'https://avatars.mds.yandex.net/get-mpic/5347553/2a00000192cd09d4b4cbb9bb28497c637e4a/optimize';
    private const IMAGE_NEWS = 'https://images.unsplash.com/photo-1511512578047-dfb367046420?w=600&h=400&fit=crop';
    private const IMAGE_SERVICES = 'https://images.unsplash.com/photo-1504328345606-18bbc8c9d7d1?w=600&h=400&fit=crop';

    public function index()
    {
        $categories = $this->getMockCategories();
        usort($categories, fn (array $a, array $b) => ($a['sort_order'] ?? 0) <=> ($b['sort_order'] ?? 0));

        return view('home', [
            'banners' => $this->getMockBanners(),
            'categories' => array_slice($categories, 0, 6),
            'newProducts' => $this->getMockNewProducts(),
            'recommendedProducts' => $this->getMockRecommendedProducts(),
            'services' => $this->getMockServices(),
            'news' => $this->getMockNews(),
            'cartCount' => 3,
            'isAuthenticated' => false,
            'footerData' => $this->getFooterData(),
        ]);
    }

    private function getMockBanners(): array
    {
        return [
            [
                'id' => 1,
                'title' => 'Новые поступления PlayStation 4',
                'description' => 'Широкий выбор консолей и аксессуаров',
                'image' => 'https://images.unsplash.com/photo-1550745165-9bc0b252726f?w=1920&h=600&fit=crop',
                'link' => '/products?category=playstation-4',
            ],
            [
                'id' => 2,
                'title' => 'Ремонт игровых приставок',
                'description' => 'Профессиональный ремонт любой сложности',
                'image' => 'https://images.unsplash.com/photo-1504328345606-18bbc8c9d7d1?w=1920&h=600&fit=crop',
                'link' => '/services/repair',
            ],
            [
                'id' => 3,
                'title' => 'Купим ваше устройство',
                'description' => 'Выгодная сдача игровых консолей',
                'image' => 'https://images.unsplash.com/photo-1554224155-6726b3ff858f?w=1920&h=600&fit=crop',
                'link' => '/services/buy',
            ],
        ];
    }

    private function getMockCategories(): array
    {
        return [
            ['id' => 1, 'name' => 'PlayStation 4', 'slug' => 'playstation-4', 'image' => self::IMAGE_PRODUCTS, 'count' => 14, 'sort_order' => 1, 'is_featured' => true],
            ['id' => 2, 'name' => 'PlayStation 3', 'slug' => 'playstation-3', 'image' => self::IMAGE_PRODUCTS, 'count' => 13, 'sort_order' => 2, 'is_featured' => false],
            ['id' => 3, 'name' => 'Xbox ONE', 'slug' => 'xbox-one', 'image' => self::IMAGE_PRODUCTS, 'count' => 8, 'sort_order' => 3, 'is_featured' => false],
            ['id' => 4, 'name' => 'Xbox 360', 'slug' => 'xbox-360', 'image' => self::IMAGE_PRODUCTS, 'count' => 11, 'sort_order' => 4, 'is_featured' => false],
            ['id' => 5, 'name' => 'Аксессуары', 'slug' => 'accessories', 'image' => self::IMAGE_PRODUCTS, 'count' => 15, 'sort_order' => 5, 'is_featured' => false],
            ['id' => 6, 'name' => 'Игры', 'slug' => 'games', 'image' => self::IMAGE_PRODUCTS, 'count' => 27, 'sort_order' => 6, 'is_featured' => false],
        ];
    }

    private function getMockNewProducts(): array
    {
        return [
            ['id' => 1, 'title' => 'Dobe Зарядная станция для геймпадов PS4 Dualshock 4', 'slug' => 'dobe-charging-station-ps4', 'price' => 899, 'image' => self::IMAGE_PRODUCTS, 'in_stock' => true, 'is_new' => true, 'discount_percent' => 10, 'description' => 'Одновременная зарядка двух геймпадов. LED-индикация. Совместима с DualShock 4.'],
            ['id' => 2, 'title' => 'Белые беспроводные геймпады DualShock 3 для PS3', 'slug' => 'white-dualshock-3-ps3', 'price' => 1499, 'image' => self::IMAGE_PRODUCTS, 'in_stock' => true, 'is_new' => true, 'discount_percent' => 15, 'description' => 'Парные беспроводные контроллеры. Вибрация, motion-сенсор. В комплекте USB-кабель.'],
            ['id' => 3, 'title' => 'Dobe Подставка для PlayStation 4 Slim/Pro', 'slug' => 'dobe-stand-ps4', 'price' => 899, 'image' => self::IMAGE_PRODUCTS, 'in_stock' => true, 'is_new' => true, 'description' => 'Вертикальная подставка с охлаждением. Подходит для PS4 Slim и Pro. Компактная установка.'],
            ['id' => 4, 'title' => 'Геймпад Microsoft Xbox 360 черный', 'slug' => 'xbox-360-controller-black', 'price' => 1999, 'image' => self::IMAGE_PRODUCTS, 'in_stock' => true, 'is_new' => false, 'discount_percent' => 10, 'description' => 'Оригинальный беспроводной геймпад. Эргономичный хват. Для Xbox 360 и ПК.'],
            ['id' => 5, 'title' => 'Кабель HDMI 2.0 для PlayStation', 'slug' => 'hdmi-cable-ps', 'price' => 499, 'image' => self::IMAGE_PRODUCTS, 'in_stock' => true, 'is_new' => true, 'description' => 'Высокоскоростной HDMI 2.0. Поддержка 4K@60Hz. Длина 1.5 м. Совместим с PS4, PS5.'],
        ];
    }

    private function getMockRecommendedProducts(): array
    {
        return [
            ['id' => 6, 'title' => 'Геймпад черный MICROSOFT TF5-01708, для Xbox One', 'slug' => 'xbox-one-controller', 'price' => 3499, 'image' => self::IMAGE_PRODUCTS, 'in_stock' => true, 'discount_percent' => 5, 'description' => 'Оригинальный беспроводной геймпад. Импульсные триггеры. Bluetooth для ПК и Xbox.'],
            ['id' => 7, 'title' => 'DualSense для PlayStation 5', 'slug' => 'dualsense-ps5', 'price' => 5999, 'image' => self::IMAGE_PRODUCTS, 'in_stock' => true, 'description' => 'Контроллер PS5 с адаптивными триггерами и тактильной отдачей. 3D-звук, встроенный микрофон.'],
            ['id' => 8, 'title' => 'Чехол для Nintendo Switch', 'slug' => 'switch-case', 'price' => 1299, 'image' => self::IMAGE_PRODUCTS, 'in_stock' => true, 'discount_percent' => 20, 'description' => 'Жёсткий кейс для консоли и Joy-Con. Защита от ударов. Кармашки для игр и карт.'],
            ['id' => 9, 'title' => 'Карта памяти 256GB для PS Vita', 'slug' => 'psvita-memory', 'price' => 2499, 'image' => self::IMAGE_PRODUCTS, 'in_stock' => true, 'description' => 'Официальная карта памяти Sony. Увеличенный объём для игр и сохранений PS Vita.'],
        ];
    }

    private function getMockServices(): array
    {
        return [
            [
                'id' => 1,
                'title' => 'Ремонт игровых приставок',
                'slug' => 'repair',
                'description' => 'Профессиональный ремонт PlayStation и Xbox любой сложности. Гарантия на работы.',
                'price' => null,
                'type' => 'repair',
                'image' => self::IMAGE_SERVICES,
            ],
            [
                'id' => 2,
                'title' => 'Купим ваше устройство',
                'slug' => 'buy',
                'description' => 'Выгодная сдача игровых консолей, аксессуаров и игр. Быстрая оценка и оплата.',
                'price' => null,
                'type' => 'buy',
                'image' => self::IMAGE_SERVICES,
            ],
        ];
    }

    private function getMockNews(): array
    {
        return [
            ['id' => 1, 'title' => 'Новые поступления PlayStation 4', 'slug' => 'new-ps4-arrivals', 'description' => 'В нашем магазине появились новые модели PlayStation 4 и аксессуары к ним.', 'image' => self::IMAGE_NEWS, 'published_at' => '2024-01-15', 'author' => 'Администратор'],
            ['id' => 2, 'title' => 'Обновление прайс-листа на ремонт', 'slug' => 'repair-price-update', 'description' => 'Мы обновили прайс-лист на ремонт игровых приставок. Теперь еще выгоднее!', 'image' => self::IMAGE_NEWS, 'published_at' => '2024-01-10', 'author' => 'Администратор'],
            ['id' => 3, 'title' => 'Акция: скидка 10% на все аксессуары', 'slug' => 'accessories-sale', 'description' => 'Специальное предложение на все аксессуары для игровых консолей до конца месяца.', 'image' => self::IMAGE_NEWS, 'published_at' => '2024-01-05', 'author' => 'Администратор'],
        ];
    }

    private function getFooterData(): array
    {
        return [
            'company' => [
                'name' => 'UlPlay',
                'description' => 'Интернет-магазин игровых консолей и аксессуаров в Ульяновске',
                'phone' => '+7 (927) 988-88-70',
                'email' => 'info@ulplay.com',
            ],
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
        ];
    }
}
