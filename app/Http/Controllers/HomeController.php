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

        $baseCategoriesQuery = Category::query()
            ->whereNotNull('parent_id')
            ->with(['parent', 'images'])
            ->withCount('products')
            ->orderByDesc('products_count')
            ->orderByDesc('is_featured')
            ->orderBy('name')
            ->get();

        $featuredCategory = $baseCategoriesQuery
            ->first(fn (Category $category): bool => (bool) $category->is_featured);

        $topCategories = $baseCategoriesQuery
            ->when(
                $featuredCategory !== null,
                fn (Collection $collection): Collection => $collection->reject(
                    fn (Category $category): bool => $category->id === $featuredCategory->id
                )
            )
            ->take($featuredCategory !== null ? 4 : 5)
            ->values();

        $categories = collect();
        if ($featuredCategory !== null) {
            $categories->push($featuredCategory);
        }
        $categories = $categories
            ->merge($topCategories)
            ->take(5)
            ->values();

        $categoriesMobile = $baseCategoriesQuery
            ->take(6)
            ->values();
        $cartProductIds = $this->cart->getItems()->pluck('product_id')->filter()->values()->all();

        return view('home', [
            'banners' => Banner::getCachedActive(),
            'categories' => $categories,
            'categoriesMobile' => $categoriesMobile,
            'newProducts' => Product::with('images')->new()
                ->inStock()
                ->latest()
                ->take($newProductsCount)
                ->get(),
            'recommendedProducts' => $this->getRecommendedProducts($recommendedProductsCount),
            'services' => Service::with(['images', 'category'])->latest()->take($servicesCount)->get(),
            'news' => News::with(['author', 'images'])
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
        $pool = Product::with('images')->inStock()->recommended()->latest()->take(50)->get();
        if ($pool->count() <= $count) {
            return $pool;
        }
        return $pool->random(min($count, $pool->count()));
    }
}
