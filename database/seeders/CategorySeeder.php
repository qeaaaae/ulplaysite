<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    private const IMAGE = 'https://avatars.mds.yandex.net/get-mpic/5347553/2a00000192cd09d4b4cbb9bb28497c637e4a/optimize';

    public function run(): void
    {
        $playstation = Category::updateOrCreate(
            ['slug' => 'playstation'],
            [
                'name' => 'PlayStation',
                'parent_id' => null,
                'is_featured' => true,
            ]
        );

        $playstation->images()->delete();
        $playstation->images()->create([
            'path' => self::IMAGE,
            'is_cover' => true,
            'position' => 0,
        ]);

        $xbox = Category::updateOrCreate(
            ['slug' => 'xbox'],
            [
                'name' => 'Xbox',
                'parent_id' => null,
                'is_featured' => false,
            ]
        );

        $xbox->images()->delete();
        $xbox->images()->create([
            'path' => self::IMAGE,
            'is_cover' => true,
            'position' => 0,
        ]);

        $nintendo = Category::updateOrCreate(
            ['slug' => 'nintendo'],
            [
                'name' => 'Nintendo',
                'parent_id' => null,
                'is_featured' => false,
            ]
        );

        $nintendo->images()->delete();
        $nintendo->images()->create([
            'path' => self::IMAGE,
            'is_cover' => true,
            'position' => 0,
        ]);

        $children = [
            ['slug' => 'playstation-4', 'name' => 'PlayStation 4', 'parent' => $playstation],
            ['slug' => 'playstation-3', 'name' => 'PlayStation 3', 'parent' => $playstation],
            ['slug' => 'xbox-one', 'name' => 'Xbox ONE', 'parent' => $xbox],
            ['slug' => 'xbox-360', 'name' => 'Xbox 360', 'parent' => $xbox],
            ['slug' => 'nintendo-switch', 'name' => 'Nintendo Switch', 'parent' => $nintendo],
            ['slug' => 'accessories', 'name' => 'Аксессуары', 'parent' => null],
            ['slug' => 'games', 'name' => 'Игры', 'parent' => null],
        ];

        foreach ($children as $item) {
            $category = Category::updateOrCreate(
                ['slug' => $item['slug']],
                [
                    'name' => $item['name'],
                    'parent_id' => $item['parent']?->id,
                    'is_featured' => false,
                ]
            );

            $category->images()->delete();
            $category->images()->create([
                'path' => self::IMAGE,
                'is_cover' => true,
                'position' => 0,
            ]);
        }
    }
}
