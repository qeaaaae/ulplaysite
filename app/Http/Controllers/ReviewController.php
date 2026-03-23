<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;
use App\Models\Product;
use App\Models\Review;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class ReviewController extends Controller
{
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
            $product->load(['reviews' => fn ($q) => $q->with('user')->latest()->limit(50)]);
            $canReview = $user
                && $user->hasPurchasedProduct($product)
                && ! $product->reviews->contains('user_id', $user->id);

            return response()->json([
                'result' => true,
                'message' => 'Спасибо! Ваш отзыв добавлен.',
                'html' => view('components.reviews-block', [
                    'reviewable' => $product,
                    'reviews' => $product->reviews,
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

    public function storeService(StoreReviewRequest $request, Service $service): RedirectResponse|JsonResponse
    {
        $user = $request->user();
        if (! $user->hasPurchasedService($service)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Validation error',
                    'errors' => [
                        'rating' => ['Отзыв можно оставить только на купленную услугу.'],
                    ],
                ], 422);
            }
            return redirect()->back()->withErrors(['rating' => 'Отзыв можно оставить только на купленную услугу.']);
        }
        if (Review::where('reviewable_type', Service::class)->where('reviewable_id', $service->id)->where('user_id', $user->id)->exists()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Validation error',
                    'errors' => [
                        'rating' => ['Вы уже оставили отзыв на эту услугу.'],
                    ],
                ], 422);
            }
            return redirect()->back()->withErrors(['rating' => 'Вы уже оставили отзыв на эту услугу.']);
        }

        $data = $request->validated();
        unset($data['images']);
        $data['reviewable_type'] = Service::class;
        $data['reviewable_id'] = $service->id;
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
            $service->load(['reviews' => fn ($q) => $q->with('user')->latest()->limit(50)]);
            $canReview = $user
                && $user->hasPurchasedService($service)
                && ! $service->reviews->contains('user_id', $user->id);

            return response()->json([
                'result' => true,
                'message' => 'Спасибо! Ваш отзыв добавлен.',
                'html' => view('components.reviews-block', [
                    'reviewable' => $service,
                    'reviews' => $service->reviews,
                    'canReview' => (bool) $canReview,
                    'storeRoute' => 'reviews.store.service',
                    'storeRouteParam' => $service,
                ])->render(),
            ]);
        }

        return redirect()
            ->route('services.show', $service)
            ->with('message', 'Спасибо! Ваш отзыв добавлен.');
    }
}
