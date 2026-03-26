<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Service;
use Illuminate\Database\Seeder;

/**
 * По 10 услуг на каждую корневую категорию (parent_id = null).
 */
class ServiceSeeder extends Seeder
{
    private const IMAGE = 'https://avatars.mds.yandex.net/get-mpic/5347553/2a00000192cd09d4b4cbb9bb28497c637e4a/optimize';

    private const SERVICES_PER_ROOT_CATEGORY = 10;

    public function run(): void
    {
        $roots = Category::query()
            ->whereNull('parent_id')
            ->orderBy('id')
            ->get();

        if ($roots->isEmpty()) {
            return;
        }

        $n = 0;
        foreach ($roots as $category) {
            for ($i = 1; $i <= self::SERVICES_PER_ROOT_CATEGORY; $i++) {
                $n++;
                $slug = 'service-' . $category->slug . '-' . $i;
                $title = 'Услуга: ' . $category->name . ' — вариант ' . $i;
                $service = Service::updateOrCreate(
                    ['slug' => $slug],
                    [
                        'category_id' => $category->id,
                        'title' => $title,
                        'description' => 'Диагностика, консультация и работы по категории «' . $category->name . '». Срок и стоимость — после осмотра или по фото.',
                        'content' => $i % 2 === 0
                            ? "## Как проходит\n\n1. Оставляете заявку или звоните.\n2. Согласовываем время и объём работ.\n3. Выполняем услугу и выдаём результат.\n\nПодробности — в магазине или по телефону."
                            : null,
                    ]
                );

                $service->images()->delete();
                $service->images()->create([
                    'path' => self::IMAGE,
                    'is_cover' => true,
                    'position' => 0,
                ]);
            }
        }
    }
}
