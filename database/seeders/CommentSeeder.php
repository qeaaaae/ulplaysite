<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\News;
use App\Models\User;
use Illuminate\Database\Seeder;

class CommentSeeder extends Seeder
{
    private const MIN_COMMENTS_PER_NEWS = 20;

    private const MAX_COMMENTS_PER_NEWS = 30;

    public function run(): void
    {
        $users = User::where('is_admin', false)->get();
        $newsIds = News::pluck('id')->unique()->values()->all();

        if (empty($newsIds) || $users->isEmpty()) {
            return;
        }

        $texts = [
            'Интересно, спасибо.',
            'Полезная новость.',
            'Ждём продолжения.',
            'Всё по делу.',
            'Согласен с автором.',
        ];

        foreach ($newsIds as $newsId) {
            $commentCount = random_int(self::MIN_COMMENTS_PER_NEWS, self::MAX_COMMENTS_PER_NEWS);
            for ($c = 0; $c < $commentCount; $c++) {
                Comment::create([
                    'news_id' => $newsId,
                    'user_id' => $users->random()->id,
                    'body' => fake()->randomElement($texts),
                ]);
            }
        }
    }
}
