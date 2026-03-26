<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Services\CartService;
use App\Support\StrHelper;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function __construct(
        private CartService $cart
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        $q = (string) ($request->input('q') ?? '');

        $query = Product::with(['category', 'images'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->where('in_stock', true);

        if ($request->filled('category')) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $request->category));
        }

        if ($request->filled('q')) {
            $tokens = preg_split('/\s+/u', trim($q), -1, PREG_SPLIT_NO_EMPTY) ?: [];
            $query->where(function ($builder) use ($tokens) {
                foreach ($tokens as $token) {
                    $escaped = StrHelper::escapeForLike($token);
                    $builder->where(function ($b) use ($escaped) {
                        $b->where('title', 'like', "%{$escaped}%")
                            ->orWhere('description', 'like', "%{$escaped}%")
                            ->orWhereHas('category', function ($q2) use ($escaped) {
                                $q2->where('name', 'like', "%{$escaped}%")
                                    ->orWhere('slug', 'like', "%{$escaped}%");
                            });
                    });
                }
            });
        }

        $sort = $request->input('sort');
        if ($sort === null || $sort === '') {
            $sort = $request->filled('q') ? 'relevance' : 'newest';
        }
        match ($sort) {
            'price_asc' => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            'rating' => $query->withAvg('reviews', 'rating')
                ->orderByDesc('reviews_avg_rating')
                ->orderByDesc('id'),
            'popular' => $query->orderByDesc('reviews_count')
                ->orderByDesc('reviews_avg_rating')
                ->orderByDesc('id'),
            'relevance' => $query->orderByRaw(
                'CASE WHEN title LIKE ? THEN 3 WHEN description LIKE ? THEN 2 ELSE 0 END DESC',
                ['%' . StrHelper::escapeForLike($q) . '%', '%' . StrHelper::escapeForLike($q) . '%']
            )->orderByDesc('reviews_avg_rating')
                ->orderByDesc('reviews_count')
                ->orderByDesc('id'),
            default => $query->latest(),
        };

        $products = $query->paginate(12)->withQueryString();
        $cartProductIds = $this->cart->getItems()->pluck('product_id')->filter()->values()->all();

        if ($request->wantsJson()) {
            return response()->json([
                'result' => true,
                'html' => view('products._results', [
                    'products' => $products,
                    'cartProductIds' => $cartProductIds,
                ])->render(),
            ]);
        }

        $currentCategory = $request->filled('category')
            ? Category::where('slug', (string) $request->category)->first()
            : null;

        $categoryTree = Category::getTreeForProductFilter();

        $expandParentIds = [];
        if ($currentCategory !== null) {
            if ($currentCategory->parent_id !== null) {
                $expandParentIds[] = $currentCategory->parent_id;
            } else {
                $root = $categoryTree->firstWhere('id', $currentCategory->id);
                if ($root && $root->children->isNotEmpty()) {
                    $expandParentIds[] = $currentCategory->id;
                }
            }
        }

        return view('products.index', [
            'products' => $products,
            'categoryTree' => $categoryTree,
            'expandParentIds' => $expandParentIds,
            'currentCategory' => $currentCategory,
            'cartProductIds' => $cartProductIds,
            'currentSort' => $sort,
        ]);
    }

    public function show(Product $product): View
    {
        $product->load(['category', 'images']);
        $product->loadCount('reviews');
        $product->loadAvg('reviews', 'rating');
        $reviews = $product->reviews()->with('user')->latest()->paginate(10, ['*'], 'reviews_page');
        $reviews->setPath(route('reviews.index.product', $product));
        $reviews = $reviews->withQueryString();
        $cartProductIds = $this->cart->getItems()->pluck('product_id')->filter()->values()->all();
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        $canReview = $user
            && $user->hasPurchasedProduct($product)
            && ! $product->reviews()->where('user_id', $user->id)->exists();

        $categoryIds = collect();
        if ($product->category_id) {
            $categoryIds->push($product->category_id);
        }

        // "Умное пересечение": берём товары из той же категории и из соседних категорий
        // (если у категории есть родитель), а дальше сортируем по рейтингу/кол-ву отзывов
        // и близости цены.
        if ($product->category?->parent_id) {
            $siblings = Category::where('parent_id', $product->category->parent_id)->pluck('id');
            $categoryIds = $categoryIds->merge($siblings);
        }

        $categoryIds = $categoryIds->unique()->filter()->values()->all();

        $similarProducts = Product::with('images')->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->where('in_stock', true)
            ->where('id', '!=', $product->id)
            ->when(!empty($categoryIds), fn ($q) => $q->whereIn('category_id', $categoryIds))
            ->orderByDesc('reviews_avg_rating')
            ->orderByDesc('reviews_count')
            ->orderByRaw('ABS(price - ?) asc', [$product->price])
            ->limit(3)
            ->get();

        return view('products.show', [
            'product' => $product,
            'cartProductIds' => $cartProductIds,
            'reviews' => $reviews,
            'canReview' => $canReview,
            'similarProducts' => $similarProducts,
        ]);
    }
}
