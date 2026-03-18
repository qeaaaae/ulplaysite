<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        private CartService $cart
    ) {}

    public function index(Request $request): View
    {
        $query = Product::with('category')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->where('in_stock', true);

        if ($request->filled('category')) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $request->category));
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($builder) use ($q) {
                $builder->where('title', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            });
        }

        $sort = $request->input('sort', 'newest');
        match ($sort) {
            'price_asc' => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            'rating' => $query->withAvg('reviews', 'rating')
                ->orderByDesc('reviews_avg_rating')
                ->orderByDesc('id'),
            'popular' => $query->orderByDesc('reviews_count')
                ->orderByDesc('reviews_avg_rating')
                ->orderByDesc('id'),
            default => $query->latest(),
        };

        $products = $query->paginate(12)->withQueryString();
        $categories = Category::withCount('products')->orderBy('sort_order')->get();
        $cartProductIds = $this->cart->getItems()->pluck('product_id')->filter()->values()->all();

        return view('products.index', [
            'products' => $products,
            'categories' => $categories,
            'currentCategory' => $request->filled('category')
                ? Category::where('slug', $request->category)->first()
                : null,
            'cartProductIds' => $cartProductIds,
            'currentSort' => $sort,
        ]);
    }

    public function show(Product $product): View
    {
        $product->load(['category', 'reviews' => fn ($q) => $q->with('user')->latest()->limit(50)]);
        $cartProductIds = $this->cart->getItems()->pluck('product_id')->filter()->values()->all();
        $user = auth()->user();
        $canReview = $user
            && $user->hasPurchasedProduct($product)
            && ! $product->reviews->contains('user_id', $user->id);

        $similarProducts = Product::withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->where('in_stock', true)
            ->where('id', '!=', $product->id)
            ->when($product->category_id, fn ($q) => $q->where('category_id', $product->category_id))
            ->latest()
            ->limit(4)
            ->get();

        return view('products.show', [
            'product' => $product,
            'cartProductIds' => $cartProductIds,
            'reviews' => $product->reviews,
            'canReview' => $canReview,
            'similarProducts' => $similarProducts,
        ]);
    }
}
