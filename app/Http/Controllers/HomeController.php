<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Category;
use App\Models\News;
use App\Models\Product;
use App\Models\Service;
use App\Services\CartService;
use Illuminate\Database\Eloquent\Collection;

class HomeController extends Controller
{
    public function __construct(
        private CartService $cart
    ) {}

    public function index()
    {
        $newProductsCount = 5;
        $recommendedProductsCount = 5;
        $servicesCount = 3;
        $newsCount = 4;

        $categories = Category::withCount('products')
            ->orderBy('sort_order')
            ->take(6)
            ->get();
        $cartProductIds = $this->cart->getItems()->pluck('product_id')->filter()->values()->all();

        return view('home', [
            'banners' => Banner::where('active', true)->orderBy('sort_order')->orderBy('id')->get(),
            'categories' => $categories,
            'newProducts' => Product::new()
                ->inStock()
                ->latest()
                ->take($newProductsCount)
                ->get(),
            'recommendedProducts' => $this->getRecommendedProducts($recommendedProductsCount),
            'services' => Service::withAvg('reviews', 'rating')->withCount('reviews')->latest()->take($servicesCount)->get(),
            'news' => News::with('author')
                ->withCount(['comments', 'views'])
                ->whereNotNull('published_at')
                ->orderByDesc('published_at')
                ->take($newsCount)
                ->get(),
            'cartProductIds' => $cartProductIds,
        ]);
    }

    /** @return Collection<int, Product> */
    private function getRecommendedProducts(int $count): Collection
    {
        $recommended = Product::inStock()->recommended()->inRandomOrder()->take($count)->get();
        if ($recommended->count() >= $count) {
            return $recommended;
        }
        $ids = $recommended->pluck('id')->all();
        $extra = Product::inStock()
            ->when($ids !== [], fn ($q) => $q->whereNotIn('id', $ids))
            ->inRandomOrder()
            ->take($count - $recommended->count())
            ->get();
        return $recommended->merge($extra);
    }
}
