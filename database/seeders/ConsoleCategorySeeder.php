<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

/**
 * Продакшен-структура категорий консолей: PlayStation, Xbox, Nintendo, SEGA, «Другие»,
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
            ['slug' => 'playstation-5-pro', 'name' => 'PlayStation 5 Pro'],
            ['slug' => 'playstation-4', 'name' => 'PlayStation 4'],
            ['slug' => 'playstation-4-pro', 'name' => 'PlayStation 4 Pro'],
            ['slug' => 'playstation-3', 'name' => 'PlayStation 3'],
            ['slug' => 'playstation-2', 'name' => 'PlayStation 2'],
            ['slug' => 'playstation-1', 'name' => 'PlayStation (PS one)'],
            ['slug' => 'psp', 'name' => 'PSP (PlayStation Portable)'],
            ['slug' => 'ps-vita', 'name' => 'PlayStation Vita'],
            ['slug' => 'playstation-vr2', 'name' => 'PlayStation VR2'],
            ['slug' => 'playstation-vr', 'name' => 'PlayStation VR (PS4)'],
            ['slug' => 'playstation-classic', 'name' => 'PlayStation Classic'],
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
            ['slug' => 'xbox-one-x', 'name' => 'Xbox One X'],
            ['slug' => 'xbox-one-s', 'name' => 'Xbox One S'],
            ['slug' => 'xbox-360', 'name' => 'Xbox 360'],
            ['slug' => 'xbox-360-slim', 'name' => 'Xbox 360 S / E'],
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
            ['slug' => 'nintendo-switch-lite', 'name' => 'Nintendo Switch Lite'],
            ['slug' => 'wii-u', 'name' => 'Wii U'],
            ['slug' => 'wii', 'name' => 'Wii'],
            ['slug' => 'gamecube', 'name' => 'Nintendo GameCube'],
            ['slug' => 'nintendo-64', 'name' => 'Nintendo 64'],
            ['slug' => 'snes', 'name' => 'Super Nintendo (SNES)'],
            ['slug' => 'nes', 'name' => 'NES'],
            ['slug' => 'virtual-boy', 'name' => 'Virtual Boy'],
            ['slug' => 'nintendo-3ds', 'name' => 'Nintendo 3DS / 2DS'],
            ['slug' => 'nintendo-ds', 'name' => 'Nintendo DS / DSi'],
            ['slug' => 'game-boy-advance', 'name' => 'Game Boy Advance'],
            ['slug' => 'game-boy', 'name' => 'Game Boy / Game Boy Color'],
            ['slug' => 'game-and-watch', 'name' => 'Game & Watch (классика и серия 2020+)'],
            ['slug' => 'nes-snes-classic-mini', 'name' => 'NES / SNES Classic Mini'],
        ] as $row) {
            $this->upsertChild($nintendo, $row['slug'], $row['name']);
        }

        $sega = $this->upsertRoot(
            slug: 'sega',
            name: 'SEGA',
            isFeatured: false,
            description: 'Консоли и железо SEGA: Mega Drive, Saturn, Dreamcast, портативки и аддоны.',
        );

        foreach ([
            ['slug' => 'sega-mega-drive', 'name' => 'SEGA Mega Drive / Genesis'],
            ['slug' => 'sega-master-system', 'name' => 'SEGA Master System'],
            ['slug' => 'sega-saturn', 'name' => 'SEGA Saturn'],
            ['slug' => 'sega-dreamcast', 'name' => 'SEGA Dreamcast'],
            ['slug' => 'sega-game-gear', 'name' => 'SEGA Game Gear'],
            ['slug' => 'sega-mega-cd', 'name' => 'SEGA Mega-CD / Sega CD'],
            ['slug' => 'sega-32x', 'name' => 'SEGA 32X'],
            ['slug' => 'sega-pico', 'name' => 'SEGA Pico'],
        ] as $row) {
            $this->upsertChild($sega, $row['slug'], $row['name']);
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
            ['slug' => 'meta-quest', 'name' => 'Meta Quest (VR-шлемы)'],
            ['slug' => 'pico-vr', 'name' => 'PICO / прочие VR-шлемы'],
            ['slug' => 'playdate', 'name' => 'Playdate'],
            ['slug' => 'analogue-pocket', 'name' => 'Analogue Pocket / FPGA'],
            [
                'slug' => 'dendy-famiclones',
                'name' => 'Dendy / клоны Famicom (8-bit)',
            ],
            ['slug' => 'neo-geo', 'name' => 'Neo Geo (AES / CD)'],
            ['slug' => '3do', 'name' => '3DO Interactive Multiplayer'],
            ['slug' => 'atari-jaguar', 'name' => 'Atari Jaguar / Lynx'],
            ['slug' => 'wonderswan', 'name' => 'WonderSwan / Color'],
            ['slug' => 'pc-engine', 'name' => 'PC Engine / TurboGrafx-16'],
            ['slug' => 'cd-i', 'name' => 'Philips CD-i'],
            [
                'slug' => 'retro-other',
                'name' => 'Прочие ретро и малоформатные системы',
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
