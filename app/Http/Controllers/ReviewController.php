<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;
use App\Models\Product;
use App\Models\Review;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;

class ReviewController extends Controller
{
    public function storeProduct(StoreReviewRequest $request, Product $product): RedirectResponse
    {
        $user = $request->user();
        if (! $user->hasPurchasedProduct($product)) {
            return redirect()->back()->withErrors(['rating' => 'Отзыв можно оставить только на купленный товар.']);
        }
        if (Review::where('reviewable_type', Product::class)->where('reviewable_id', $product->id)->where('user_id', $user->id)->exists()) {
            return redirect()->back()->withErrors(['rating' => 'Вы уже оставили отзыв на этот товар.']);
        }

        $data = $request->validated();
        unset($data['images']);
        $data['reviewable_type'] = Product::class;
        $data['reviewable_id'] = $product->id;
        $data['user_id'] = $user->id;

        $review = Review::create($data);

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

        return redirect()
            ->route('products.show', $product)
            ->with('message', 'Спасибо! Ваш отзыв добавлен.');
    }

    public function storeService(StoreReviewRequest $request, Service $service): RedirectResponse
    {
        $user = $request->user();
        if (! $user->hasPurchasedService($service)) {
            return redirect()->back()->withErrors(['rating' => 'Отзыв можно оставить только на купленную услугу.']);
        }
        if (Review::where('reviewable_type', Service::class)->where('reviewable_id', $service->id)->where('user_id', $user->id)->exists()) {
            return redirect()->back()->withErrors(['rating' => 'Вы уже оставили отзыв на эту услугу.']);
        }

        $data = $request->validated();
        unset($data['images']);
        $data['reviewable_type'] = Service::class;
        $data['reviewable_id'] = $service->id;
        $data['user_id'] = $user->id;

        $review = Review::create($data);

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

        return redirect()
            ->route('services.show', $service)
            ->with('message', 'Спасибо! Ваш отзыв добавлен.');
    }
}
