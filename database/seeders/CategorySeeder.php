<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * @deprecated Используйте {@see ConsoleCategorySeeder} напрямую; класс оставлен для совместимости.
 */
class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $this->call(ConsoleCategorySeeder::class);
    }
}
