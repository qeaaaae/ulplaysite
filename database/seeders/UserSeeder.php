<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Быстрые пользователи для локальной/тестовой БД (Order/Review/Comment и т.д.).
     * Для ~100 «реалистичных» ru-пользователей: php artisan db:seed --class=RealisticUserSeeder
     */
    public function run(): void
    {
        $this->call([AdminUserSeeder::class]);
        User::factory()->count(30)->create();
    }
}
