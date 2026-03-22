<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Banner;
use Illuminate\Database\Seeder;

class BannerSeeder extends Seeder
{
    private const IMAGES = [
        'https://avatars.mds.yandex.net/get-mpic/5347553/2a00000192cd09d4b4cbb9bb28497c637e4a/optimize',
        'https://avatars.mds.yandex.net/get-mpic/5347553/2a00000192cd09d4b4cbb9bb28497c637e4a/optimize',
        'https://avatars.mds.yandex.net/get-mpic/5347553/2a00000192cd09d4b4cbb9bb28497c637e4a/optimize',
    ];

    public function run(): void
    {
        for ($i = 1; $i <= 100; $i++) {
            $banner = Banner::firstOrCreate(
                ['title' => "Баннер {$i}"],
                [
                    'description' => "Описание баннера {$i}",
                    'link' => $i % 2 === 0 ? '/products' : '/services/repair',
                    'active' => $i <= 10,
                ]
            );

            // У баннера только одно фото
            $banner->images()->delete();
            $banner->images()->create([
                'path' => self::IMAGES[($i - 1) % count(self::IMAGES)],
                'is_cover' => true,
                'position' => 0,
            ]);
        }
    }
}
