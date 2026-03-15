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
                ->take(5)
                ->get(),
            'recommendedProducts' => $this->getRecommendedProducts(),
            'services' => Service::latest()->take(2)->get(),
            'news' => News::with('author')
                ->whereNotNull('published_at')
                ->orderByDesc('published_at')
                ->take(3)
                ->get(),
            'cartProductIds' => $cartProductIds,
        ]);
    }

    /** @return Collection<int, Product> */
    private function getRecommendedProducts(): Collection
    {
        $recommended = Product::inStock()->recommended()->inRandomOrder()->take(4)->get();
        if ($recommended->count() >= 4) {
            return $recommended;
        }
        $ids = $recommended->pluck('id')->all();
        $extra = Product::inStock()->when($ids !== [], fn ($q) => $q->whereNotIn('id', $ids))->inRandomOrder()->take(4 - $recommended->count())->get();
        return $recommended->merge($extra);
    }
}
