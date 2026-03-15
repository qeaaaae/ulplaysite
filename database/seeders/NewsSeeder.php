<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\News;
use App\Models\User;
use Illuminate\Database\Seeder;

class NewsSeeder extends Seeder
{
    private const IMAGE = 'https://images.unsplash.com/photo-1511512578047-dfb367046420?w=600&h=400&fit=crop';

    public function run(): void
    {
        $author = User::where('is_admin', true)->first();
        $authorId = $author?->id ?? 1;

        for ($i = 1; $i <= 100; $i++) {
            $daysAgo = rand(0, 365);
            $publishedAt = now()->subDays($daysAgo)->format('Y-m-d H:i:s');
            News::firstOrCreate(
                ['slug' => "news-{$i}"],
                [
                    'title' => "Новость {$i}: обновления и акции",
                    'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.',
                    'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.',
                    'image_path' => self::IMAGE,
                    'author_id' => $authorId,
                    'published_at' => $publishedAt,
                ]
            );
        }
    }
}
