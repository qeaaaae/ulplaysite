<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

/**
 * Продакшен-структура категорий консолей: родители PlayStation / Xbox / Nintendo / «Другие»,
 * дочерние — поколения и линейки. Плюс корневые «Аксессуары» и «Игры» для товаров вне приставок.
 * Избранная категория (is_featured) — только PlayStation.
 */
class ConsoleCategorySeeder extends Seeder
{
    private const IMAGE = 'https://avatars.mds.yandex.net/get-mpic/5347553/2a00000192cd09d4b4cbb9bb28497c637e4a/optimize';

    public function run(): void
    {
        $playstation = $this->upsertRoot(
            slug: 'playstation',
            name: 'PlayStation',
            isFeatured: true,
            description: 'Консоли Sony: домашние поколения и портативные системы PSP / PS Vita.',
        );

        foreach ([
            ['slug' => 'playstation-5', 'name' => 'PlayStation 5'],
            ['slug' => 'playstation-4', 'name' => 'PlayStation 4'],
            ['slug' => 'playstation-3', 'name' => 'PlayStation 3'],
            ['slug' => 'playstation-2', 'name' => 'PlayStation 2'],
            ['slug' => 'playstation-1', 'name' => 'PlayStation (PS one)'],
            ['slug' => 'psp', 'name' => 'PSP (PlayStation Portable)'],
            ['slug' => 'ps-vita', 'name' => 'PlayStation Vita'],
        ] as $row) {
            $this->upsertChild($playstation, $row['slug'], $row['name']);
        }

        $xbox = $this->upsertRoot(
            slug: 'xbox',
            name: 'Xbox',
            isFeatured: false,
            description: 'Консоли Microsoft: от оригинального Xbox до Series X|S.',
        );

        foreach ([
            ['slug' => 'xbox-series-x', 'name' => 'Xbox Series X'],
            ['slug' => 'xbox-series-s', 'name' => 'Xbox Series S'],
            ['slug' => 'xbox-one', 'name' => 'Xbox One'],
            ['slug' => 'xbox-360', 'name' => 'Xbox 360'],
            ['slug' => 'xbox-original', 'name' => 'Xbox (оригинальная)'],
        ] as $row) {
            $this->upsertChild($xbox, $row['slug'], $row['name']);
        }

        $nintendo = $this->upsertRoot(
            slug: 'nintendo',
            name: 'Nintendo',
            isFeatured: false,
            description: 'Домашние и портативные системы Nintendo: от NES до Switch.',
        );

        foreach ([
            ['slug' => 'nintendo-switch-2', 'name' => 'Nintendo Switch 2'],
            ['slug' => 'nintendo-switch', 'name' => 'Nintendo Switch'],
            ['slug' => 'wii-u', 'name' => 'Wii U'],
            ['slug' => 'wii', 'name' => 'Wii'],
            ['slug' => 'gamecube', 'name' => 'Nintendo GameCube'],
            ['slug' => 'nintendo-64', 'name' => 'Nintendo 64'],
            ['slug' => 'snes', 'name' => 'Super Nintendo (SNES)'],
            ['slug' => 'nes', 'name' => 'NES'],
            ['slug' => 'nintendo-3ds', 'name' => 'Nintendo 3DS / 2DS'],
            ['slug' => 'nintendo-ds', 'name' => 'Nintendo DS / DSi'],
            ['slug' => 'game-boy-advance', 'name' => 'Game Boy Advance'],
            ['slug' => 'game-boy', 'name' => 'Game Boy / Game Boy Color'],
        ] as $row) {
            $this->upsertChild($nintendo, $row['slug'], $row['name']);
        }

        $other = $this->upsertRoot(
            slug: 'other-consoles',
            name: 'Другие консоли',
            isFeatured: false,
            description: 'Портативные PC, Steam Deck, ретро и малоформатные системы.',
        );

        foreach ([
            ['slug' => 'steam-deck', 'name' => 'Steam Deck'],
            [
                'slug' => 'handheld-pc',
                'name' => 'Портативные PC (ROG Ally, Legion Go, MSI Claw и др.)',
            ],
            [
                'slug' => 'dendy-famiclones',
                'name' => 'Dendy / клоны Famicom (8-bit)',
            ],
            [
                'slug' => 'sega-mega-drive',
                'name' => 'SEGA Mega Drive / Genesis',
            ],
            [
                'slug' => 'retro-other',
                'name' => 'Прочие ретро (Atari, 3DO, WonderSwan и др.)',
            ],
        ] as $row) {
            $this->upsertChild($other, $row['slug'], $row['name']);
        }

        $this->upsertRoot(
            slug: 'accessories',
            name: 'Аксессуары',
            isFeatured: false,
            description: 'Геймпады, наушники, зарядки, чехлы и прочее.',
        );

        $this->upsertRoot(
            slug: 'games',
            name: 'Игры',
            isFeatured: false,
            description: 'Физические и цифровые игры по платформам.',
        );
    }

    private function upsertRoot(string $slug, string $name, bool $isFeatured, ?string $description = null): Category
    {
        $category = Category::updateOrCreate(
            ['slug' => $slug],
            [
                'name' => $name,
                'parent_id' => null,
                'is_featured' => $isFeatured,
                'description' => $description,
            ]
        );

        $this->syncCoverImage($category);

        return $category;
    }

    private function upsertChild(Category $parent, string $slug, string $name): void
    {
        $category = Category::updateOrCreate(
            ['slug' => $slug],
            [
                'name' => $name,
                'parent_id' => $parent->id,
                'is_featured' => false,
                'description' => null,
            ]
        );

        $this->syncCoverImage($category);
    }

    private function syncCoverImage(Category $category): void
    {
        $category->images()->delete();
        $category->images()->create([
            'path' => self::IMAGE,
            'is_cover' => true,
            'position' => 0,
        ]);
    }
}
