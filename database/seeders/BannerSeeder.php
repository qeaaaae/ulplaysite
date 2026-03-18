<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Banner;
use Illuminate\Database\Seeder;

class BannerSeeder extends Seeder
{
    private const IMAGES = [
        'https://images.unsplash.com/photo-1550745165-9bc0b252726f?w=1920&h=600&fit=crop',
        'https://images.unsplash.com/photo-1504328345606-18bbc8c9d7d1?w=1920&h=600&fit=crop',
        'https://images.unsplash.com/photo-1554224155-6726b3ff858f?w=1920&h=600&fit=crop',
    ];

    public function run(): void
    {
        for ($i = 1; $i <= 100; $i++) {
            $banner = Banner::firstOrCreate(
                ['title' => "Баннер {$i}"],
                [
                    'description' => "Описание баннера {$i}",
                    'image_path' => self::IMAGES[($i - 1) % count(self::IMAGES)],
                    'link' => $i % 2 === 0 ? '/products' : '/services/repair',
                    'sort_order' => $i,
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
