<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\News;
use App\Models\User;
use Illuminate\Database\Seeder;

class NewsSeeder extends Seeder
{
    private const IMAGE = 'https://avatars.mds.yandex.net/get-mpic/5347553/2a00000192cd09d4b4cbb9bb28497c637e4a/optimize';

    private const VIDEO_URLS = [
        'https://www.youtube.com/watch?v=_3cinfs0wQc',
        'https://rutube.ru/video/ed2b836f13b534207634a433c4d33eb7/?playlist=288871',
    ];

    public function run(): void
    {
        $author = User::where('is_admin', true)->first();
        $authorId = $author?->id ?? 1;

        for ($i = 1; $i <= 100; $i++) {
            $daysAgo = rand(0, 365);
            $publishedAt = now()->subDays($daysAgo)->format('Y-m-d H:i:s');
            $videoUrl = $i <= 2 ? self::VIDEO_URLS[$i - 1] : null;

            $news = News::updateOrCreate(
                ['slug' => "news-{$i}"],
                [
                    'title' => "Новость {$i}: обновления и акции",
                    'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.',
                    'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.',
                    'video_url' => $videoUrl,
                    'author_id' => $authorId,
                    'published_at' => $publishedAt,
                ]
            );

            $news->images()->delete();
            for ($pos = 0; $pos < 5; $pos++) {
                $news->images()->create([
                    'path' => self::IMAGE,
                    'is_cover' => $pos === 0,
                    'position' => $pos,
                ]);
            }
        }
    }
}
