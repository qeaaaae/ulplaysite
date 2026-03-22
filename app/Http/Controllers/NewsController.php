<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\NewsView;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class NewsController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $news = News::with(['author', 'images'])
            ->withCount(['comments', 'views'])
            ->whereNotNull('published_at')
            ->orderByDesc('published_at')
            ->paginate(10)
            ->withQueryString();

        if ($request->wantsJson()) {
            return response()->json([
                'result' => true,
                'html' => view('news._results', [
                    'news' => $news,
                ])->render(),
            ]);
        }

        return view('news.index', ['news' => $news]);
    }

    public function show(News $news): View
    {
        $news->load(['author', 'images', 'comments.user', 'comments.helpfulVotes']);

        if ($user = Auth::user()) {
            NewsView::firstOrCreate([
                'news_id' => $news->id,
                'user_id' => $user->id,
            ]);
            $news->loadCount('views');
        }

        $similarNews = News::with(['author', 'images'])
            ->whereNotNull('published_at')
            ->where('id', '!=', $news->id)
            ->withCount(['comments', 'views'])
            ->orderByDesc('published_at')
            ->limit(4)
            ->get();

        return view('news.show', [
            'news' => $news,
            'similarNews' => $similarNews,
        ]);
    }
}
