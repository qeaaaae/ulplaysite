<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
            ServiceSeeder::class,
            NewsSeeder::class,
            BannerSeeder::class,
            OrderSeeder::class,
            ReviewSeeder::class,
            CommentSeeder::class,
            NewsViewSeeder::class,
        ]);
    }
}
