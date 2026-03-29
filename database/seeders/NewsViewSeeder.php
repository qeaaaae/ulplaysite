<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\News;
use App\Models\NewsView;
use App\Models\User;
use Illuminate\Database\Seeder;

class NewsViewSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::where('is_admin', false)->limit(50)->get();
        $newsItems = News::whereNotNull('published_at')->get();

        if ($users->isEmpty() || $newsItems->isEmpty()) {
            return;
        }

        foreach ($newsItems as $news) {
            $viewersCount = fake()->numberBetween(5, min(40, $users->count()));
            $viewers = $users->shuffle()->take($viewersCount);

            foreach ($viewers as $user) {
                NewsView::updateOrCreate(
                    [
                        'news_id' => $news->id,
                        'user_id' => $user->id,
                    ],
                    []
                );
            }
        }
    }
}
