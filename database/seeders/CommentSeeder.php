<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\News;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class CommentSeeder extends Seeder
{
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
            $commentCount = min(20, $users->count());
            $selectedUsers = $users->shuffle()->take($commentCount);
            foreach ($selectedUsers as $user) {
                $daysAgo = random_int(0, 30);
                $createdAt = Carbon::now()->subDays($daysAgo)->subHours(random_int(0, 23));
                Comment::create([
                    'news_id' => $newsId,
                    'user_id' => $user->id,
                    'body' => fake()->randomElement($texts),
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            }
        }
    }
}
