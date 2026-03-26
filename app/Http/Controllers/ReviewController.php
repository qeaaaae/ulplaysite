<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ReviewController extends Controller
{
    public function index(Request $request, Product $product): JsonResponse
    {
        $reviews = $product->reviews()->with('user')->latest()
            ->paginate(10, ['*'], 'reviews_page')
            ->withQueryString();

        return response()->json([
            'result' => true,
            'html' => view('components.reviews-list', [
                'reviews' => $reviews,
            ])->render(),
        ]);
    }

    public function storeProduct(StoreReviewRequest $request, Product $product): RedirectResponse|JsonResponse
    {
        $user = $request->user();
        if (! $user->hasPurchasedProduct($product)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Validation error',
                    'errors' => [
                        'rating' => ['Отзыв можно оставить только на купленный товар.'],
                    ],
                ], 422);
            }
            return redirect()->back()->withErrors(['rating' => 'Отзыв можно оставить только на купленный товар.']);
        }
        if (Review::where('reviewable_type', Product::class)->where('reviewable_id', $product->id)->where('user_id', $user->id)->exists()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Validation error',
                    'errors' => [
                        'rating' => ['Вы уже оставили отзыв на этот товар.'],
                    ],
                ], 422);
            }
            return redirect()->back()->withErrors(['rating' => 'Вы уже оставили отзыв на этот товар.']);
        }

        $data = $request->validated();
        unset($data['images']);
        $data['reviewable_type'] = Product::class;
        $data['reviewable_id'] = $product->id;
        $data['user_id'] = $user->id;

        $review = Review::create($data);
        Cache::forget("user.{$user->id}.purchased_no_review");

        if ($request->hasFile('images')) {
            $files = array_slice($request->file('images'), 0, 3);
            foreach (array_values($files) as $index => $file) {
                $review->imagesRelation()->create([
                    'path' => $file->store('reviews', 'public'),
                    'is_cover' => $index === 0,
                    'position' => $index,
                ]);
            }
        }

        if ($request->wantsJson()) {
            $reviews = $product->reviews()->with('user')->latest()->paginate(10, ['*'], 'reviews_page');
            $reviews->setPath(route('reviews.index.product', $product));
            $reviews = $reviews->withQueryString();
            $canReview = $user
                && $user->hasPurchasedProduct($product)
                && ! $product->reviews()->where('user_id', $user->id)->exists();

            return response()->json([
                'result' => true,
                'message' => 'Спасибо! Ваш отзыв добавлен.',
                'html' => view('components.reviews-block', [
                    'reviewable' => $product,
                    'reviews' => $reviews,
                    'canReview' => (bool) $canReview,
                    'storeRoute' => 'reviews.store.product',
                    'storeRouteParam' => $product,
                ])->render(),
            ]);
        }

        return redirect()
            ->route('products.show', $product)
            ->with('message', 'Спасибо! Ваш отзыв добавлен.');
    }
}
