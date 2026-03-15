<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\View\View;

class NewsController extends Controller
{
    public function index(): View
    {
        $news = News::with('author')
            ->whereNotNull('published_at')
            ->orderByDesc('published_at')
            ->paginate(9);

        return view('news.index', ['news' => $news]);
    }

    public function show(News $news): View
    {
        $news->load(['author', 'comments.user']);

        return view('news.show', ['news' => $news]);
    }
}
