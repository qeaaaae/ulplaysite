<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    private const IMAGE = 'https://avatars.mds.yandex.net/get-mpic/5347553/2a00000192cd09d4b4cbb9bb28497c637e4a/optimize';

    /** Как у новости /news/news-2 (второй URL из NewsSeeder::VIDEO_URLS). */
    private const VIDEO_URL = 'https://rutube.ru/video/ed2b836f13b534207634a433c4d33eb7/?playlist=288871';

    private const PRODUCTS_PER_CHILD_CATEGORY = 10;

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
        $childCategoryIds = Category::query()
            ->whereNotNull('parent_id')
            ->orderBy('id')
            ->pluck('id')
            ->all();

        if ($childCategoryIds === []) {
            return;
        }

        $titleCount = count(self::TITLES);
        $productIndex = 0;

        foreach ($childCategoryIds as $categoryId) {
            for ($n = 0; $n < self::PRODUCTS_PER_CHILD_CATEGORY; $n++) {
                $productIndex++;
                $title = self::TITLES[($productIndex - 1) % $titleCount] . " #{$productIndex}";
                $slug = 'product-' . $productIndex;
                $product = Product::updateOrCreate(
                    ['slug' => $slug],
                    [
                        'title' => $title,
                        'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.',
                        'price' => rand(500, 15000),
                        'category_id' => $categoryId,
                        'in_stock' => $productIndex % 10 !== 0,
                        'is_new' => $productIndex <= 30,
                        'is_recommended' => $productIndex <= 15,
                        'discount_percent' => $productIndex % 4 === 0 ? rand(5, 20) : null,
                        'video_url' => self::VIDEO_URL,
                    ]
                );

                $product->images()->delete();
                for ($pos = 0; $pos < 5; $pos++) {
                    $product->images()->create([
                        'path' => self::IMAGE,
                        'is_cover' => $pos === 0,
                        'position' => $pos,
                    ]);
                }
            }
        }
    }
}
