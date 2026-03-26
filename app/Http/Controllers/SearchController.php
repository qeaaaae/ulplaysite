<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\Product;
use App\Models\Service;
use App\Services\CartService;
use App\Support\StrHelper;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function __construct(
        private CartService $cart
    ) {}

    public function index(Request $request): View
    {
        $q = trim((string) $request->input('q', ''));
        $tokens = preg_split('/\s+/u', $q, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        $products = collect();
        $services = collect();
        $news = collect();
        $cartProductIds = [];

        if (!empty($tokens)) {
            $products = Product::with(['images', 'category'])
                ->withAvg('reviews', 'rating')
                ->withCount('reviews')
                ->where('in_stock', true)
                ->where(function ($builder) use ($tokens) {
                    foreach ($tokens as $token) {
                        $escaped = StrHelper::escapeForLike($token);
                        $builder->where(function ($q1) use ($escaped) {
                            $q1->where('title', 'like', "%{$escaped}%")
                                ->orWhere('description', 'like', "%{$escaped}%")
                                ->orWhereHas('category', function ($q2) use ($escaped) {
                                    $q2->where('name', 'like', "%{$escaped}%");
                                });
                        });
                    }
                })
                ->orderByDesc('reviews_avg_rating')
                ->orderByDesc('reviews_count')
                ->latest()
                ->limit(6)
                ->get();

            $services = Service::with(['images', 'category'])
                ->where(function ($builder) use ($tokens) {
                    foreach ($tokens as $token) {
                        $escaped = StrHelper::escapeForLike($token);
                        $builder->where(function ($q1) use ($escaped) {
                            $q1->where('title', 'like', "%{$escaped}%")
                                ->orWhere('description', 'like', "%{$escaped}%")
                                ->orWhere('content', 'like', "%{$escaped}%")
                                ->orWhereHas('category', function ($q2) use ($escaped) {
                                    $q2->where('name', 'like', "%{$escaped}%");
                                });
                        });
                    }
                })
                ->latest()
                ->limit(6)
                ->get();

            $news = News::with(['images', 'author'])
                ->withCount(['comments', 'views'])
                ->whereNotNull('published_at')
                ->where(function ($builder) use ($tokens) {
                    foreach ($tokens as $token) {
                        $escaped = StrHelper::escapeForLike($token);
                        $builder->where(function ($q1) use ($escaped) {
                            $q1->where('title', 'like', "%{$escaped}%")
                                ->orWhere('description', 'like', "%{$escaped}%")
                                ->orWhere('content', 'like', "%{$escaped}%");
                        });
                    }
                })
                ->orderByDesc('published_at')
                ->limit(6)
                ->get();

            $cartProductIds = $this->cart->getItems()->pluck('product_id')->filter()->values()->all();
        }

        return view('search.index', [
            'q' => $q,
            'products' => $products,
            'services' => $services,
            'news' => $news,
            'cartProductIds' => $cartProductIds,
        ]);
    }
}

