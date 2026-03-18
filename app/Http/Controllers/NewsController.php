<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\NewsView;
use Illuminate\View\View;

class NewsController extends Controller
{
    public function index(): View
    {
        $news = News::with('author')
            ->withCount(['comments', 'views'])
            ->whereNotNull('published_at')
            ->orderByDesc('published_at')
            ->paginate(9);

        return view('news.index', ['news' => $news]);
    }

    public function show(News $news): View
    {
        $news->load(['author', 'comments.user']);

        if ($user = auth()->user()) {
            NewsView::firstOrCreate([
                'news_id' => $news->id,
                'user_id' => $user->id,
            ]);
            $news->loadCount('views');
        }

        $similarNews = News::with('author')
            ->whereNotNull('published_at')
            ->where('id', '!=', $news->id)
            ->orderByDesc('published_at')
            ->limit(4)
            ->get();

        return view('news.show', [
            'news' => $news,
            'similarNews' => $similarNews,
        ]);
    }
}
