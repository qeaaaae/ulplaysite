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

    public function show(Request $request, News $news): View
    {
        $news->load(['author', 'images']);

        $sort = $request->get('comments_sort', 'newest');
        $commentsQuery = $news->comments()->with(['user', 'helpfulVotes'])->reorder();
        if ($sort === 'popular') {
            $commentsQuery->withCount('helpfulVotes')->orderByDesc('helpful_votes_count')->orderByDesc('created_at');
        } else {
            $sortDir = $sort === 'oldest' ? 'asc' : 'desc';
            $commentsQuery->orderBy('created_at', $sortDir);
        }
        $comments = $commentsQuery->paginate(10, ['*'], 'comments_page')->withQueryString();

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
            'comments' => $comments,
            'similarNews' => $similarNews,
        ]);
    }
}
