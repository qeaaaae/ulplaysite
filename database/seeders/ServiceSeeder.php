<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    private const IMAGE = 'https://avatars.mds.yandex.net/get-mpic/5347553/2a00000192cd09d4b4cbb9bb28497c637e4a/optimize';

    public function run(): void
    {
        $types = ['repair', 'buy'];
        $titles = ['Ремонт игровых приставок', 'Купим ваше устройство', 'Диагностика консоли', 'Чистка от пыли', 'Замена термопасты', 'Прошивка и настройка'];
        $lorem = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.';

        for ($i = 1; $i <= 100; $i++) {
            $service = Service::firstOrCreate(
                ['slug' => "service-{$i}"],
                [
                    'title' => $titles[($i - 1) % count($titles)] . " ({$i})",
                    'description' => $lorem,
                    'price' => $i % 3 === 0 ? rand(500, 5000) : null,
                    'type' => $types[($i - 1) % 2],
                ]
            );

            // Максимум 5 фото на услугу
            $service->images()->delete();
            for ($pos = 0; $pos < 5; $pos++) {
                $service->images()->create([
                    'path' => self::IMAGE,
                    'is_cover' => $pos === 0,
                    'position' => $pos,
                ]);
            }
        }
    }
}
