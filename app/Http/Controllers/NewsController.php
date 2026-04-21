<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\NewsView;
use App\Support\StrHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class NewsController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $q = trim((string) $request->input('q', ''));
        $tokens = preg_split('/\s+/u', $q, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        $newsQuery = News::with(['author', 'images'])
            ->withCount(['comments', 'views'])
            ->whereNotNull('published_at')
            ->orderByDesc('published_at');

        if (!empty($tokens)) {
            $newsQuery->where(function ($builder) use ($tokens) {
                foreach ($tokens as $token) {
                    $escaped = StrHelper::escapeForLike($token);
                    $builder->where(function ($q1) use ($escaped) {
                        $q1->where('title', 'like', "%{$escaped}%")
                            ->orWhere('description', 'like', "%{$escaped}%")
                            ->orWhere('content', 'like', "%{$escaped}%");
                    });
                }
            });
        }

        $news = $newsQuery
            ->paginate(12)
            ->withQueryString();

        if ($request->wantsJson()) {
            return response()->json([
                'result' => true,
                'html' => view('news._results', [
                    'news' => $news,
                ])->render(),
            ]);
        }

        $metaDescription = 'Свежие игровые новости, анонсы и обзоры UlPlay. Оперативные публикации и события для пользователей из Ульяновска и всей России.';

        $structuredData = [
            [
                '@context' => 'https://schema.org',
                '@type' => 'CollectionPage',
                'name' => 'Новости UlPlay',
                'url' => route('news.index'),
                'description' => $metaDescription,
            ],
            [
                '@context' => 'https://schema.org',
                '@type' => 'ItemList',
                'name' => 'Лента новостей UlPlay',
                'itemListOrder' => 'https://schema.org/ItemListOrderDescending',
                'numberOfItems' => $news->count(),
                'itemListElement' => $news->values()->map(fn (News $item, int $index): array => [
                    '@type' => 'ListItem',
                    'position' => $index + 1,
                    'url' => route('news.show', $item->slug),
                    'name' => $item->title,
                ])->all(),
            ],
        ];

        return view('news.index', [
            'news' => $news,
            'metaTitle' => 'Новости',
            'metaDescription' => $metaDescription,
            'canonicalUrl' => route('news.index'),
            'structuredData' => $structuredData,
        ]);
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
        $comments = $commentsQuery->paginate(10, ['*'], 'comments_page');
        $comments->setPath(route('comments.index', $news));
        $comments = $comments->withQueryString();

        if ($user = Auth::user()) {
            NewsView::firstOrCreate([
                'news_id' => $news->id,
                'user_id' => $user->id,
            ]);
        }

        $news->loadCount('views');

        $similarNews = News::with(['author', 'images'])
            ->whereNotNull('published_at')
            ->where('id', '!=', $news->id)
            ->withCount(['comments', 'views'])
            ->orderByDesc('published_at')
            ->limit(4)
            ->get();

        $plainTextContent = trim(
            preg_replace('/\s+/u', ' ', strip_tags((string) app(\App\Services\MarkdownService::class)->render($news->content ?? '')))
            ?: ''
        );
        $metaDescription = $news->description
            ?: Str::limit($plainTextContent, 220, '...');
        $metaImage = $news->image ?: asset('favicon.svg');
        $published = $news->published_at ?? now();
        $canonicalUrl = route('news.show', $news->slug);

        $structuredData = [
            [
                '@context' => 'https://schema.org',
                '@type' => 'NewsArticle',
                'headline' => $news->title,
                'description' => $metaDescription,
                'url' => $canonicalUrl,
                'datePublished' => $published->toIso8601String(),
                'dateModified' => ($news->updated_at ?? $published)->toIso8601String(),
                'inLanguage' => 'ru-RU',
                'image' => [$metaImage],
                'author' => [
                    '@type' => 'Person',
                    'name' => $news->author?->name ?: 'Редакция UlPlay',
                ],
                'publisher' => [
                    '@type' => 'Organization',
                    'name' => config('app.name', 'UlPlay'),
                    'logo' => [
                        '@type' => 'ImageObject',
                        'url' => asset('favicon.svg'),
                    ],
                ],
            ],
            [
                '@context' => 'https://schema.org',
                '@type' => 'BreadcrumbList',
                'itemListElement' => [
                    [
                        '@type' => 'ListItem',
                        'position' => 1,
                        'name' => 'Главная',
                        'item' => route('home'),
                    ],
                    [
                        '@type' => 'ListItem',
                        'position' => 2,
                        'name' => 'Новости',
                        'item' => route('news.index'),
                    ],
                    [
                        '@type' => 'ListItem',
                        'position' => 3,
                        'name' => $news->title,
                        'item' => $canonicalUrl,
                    ],
                ],
            ],
        ];

        return view('news.show', [
            'metaTitle' => $news->title,
            'metaDescription' => $metaDescription,
            'canonicalUrl' => $canonicalUrl,
            'metaImage' => $metaImage,
            'openGraph' => [
                'og:type' => 'article',
                'article:published_time' => $published->toIso8601String(),
                'article:modified_time' => ($news->updated_at ?? $published)->toIso8601String(),
                'article:section' => 'Новости',
            ],
            'twitter' => [
                'twitter:label1' => 'Раздел',
                'twitter:data1' => 'Новости',
            ],
            'structuredData' => $structuredData,
            'news' => $news,
            'comments' => $comments,
            'similarNews' => $similarNews,
        ]);
    }
}
