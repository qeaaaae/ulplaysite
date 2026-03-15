<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    private const IMAGE = 'https://avatars.mds.yandex.net/get-mpic/5347553/2a00000192cd09d4b4cbb9bb28497c637e4a/optimize';

    private const TITLES = [
        'Геймпад беспроводной',
        'Зарядная станция для геймпадов',
        'Подставка для консоли',
        'Кабель HDMI',
        'Чехол для консоли',
        'Карта памяти',
        'Контроллер оригинальный',
        'Адаптер питания',
        'Гарнитура игровая',
        'Коврик для мыши геймерский',
    ];

    public function run(): void
    {
        $categoryIds = Category::orderBy('sort_order')->pluck('id')->all();
        if ($categoryIds === []) {
            return;
        }

        $count = count($categoryIds);
        for ($i = 1; $i <= 100; $i++) {
            $title = self::TITLES[($i - 1) % count(self::TITLES)] . " #{$i}";
            $slug = 'product-' . $i;
            Product::firstOrCreate(
                ['slug' => $slug],
                [
                    'title' => $title,
                    'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.',
                    'price' => rand(500, 15000),
                    'category_id' => $categoryIds[($i - 1) % $count],
                    'image_path' => self::IMAGE,
                    'in_stock' => $i % 10 !== 0,
                    'is_new' => $i <= 30,
                    'is_recommended' => $i <= 15,
                    'discount_percent' => $i % 4 === 0 ? rand(5, 20) : null,
                ]
            );
        }
    }
}
