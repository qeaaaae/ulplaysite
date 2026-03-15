<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    private const IMAGE = 'https://avatars.mds.yandex.net/get-mpic/5347553/2a00000192cd09d4b4cbb9bb28497c637e4a/optimize';

    private const FIRST_NAMES = [
        'PlayStation 4', 'PlayStation 3', 'Xbox ONE', 'Xbox 360', 'Аксессуары', 'Игры',
    ];

    private const FIRST_SLUGS = [
        'playstation-4', 'playstation-3', 'xbox-one', 'xbox-360', 'accessories', 'games',
    ];

    public function run(): void
    {
        for ($i = 0; $i < 6; $i++) {
            Category::updateOrCreate(
                ['slug' => self::FIRST_SLUGS[$i]],
                [
                    'name' => self::FIRST_NAMES[$i],
                    'image_path' => self::IMAGE,
                    'sort_order' => $i + 1,
                    'is_featured' => $i === 0,
                ]
            );
        }
    }
}
