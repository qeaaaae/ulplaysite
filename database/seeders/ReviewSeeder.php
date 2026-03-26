<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    /** От 10 до 20 отзывов на каждый товар (разные пользователи). */
    private const MIN_REVIEWS_PER_PRODUCT = 10;

    private const MAX_REVIEWS_PER_PRODUCT = 20;

    public function run(): void
    {
        $users = User::where('is_admin', false)->get();
        $productIds = Product::pluck('id')->unique()->values()->all();

        if (count($productIds) < 1 || $users->isEmpty()) {
            return;
        }

        Review::query()
            ->where('reviewable_type', Product::class)
            ->get()
            ->each(fn (Review $review) => $review->imagesRelation()->delete());

        Review::query()->where('reviewable_type', Product::class)->delete();

        $texts = [
            'Всё понравилось, рекомендую.',
            'Хороший товар, быстрая доставка.',
            'Соответствует описанию, доволен покупкой.',
            'Качество на высоте, буду заказывать ещё.',
            'Всё пришло в срок, упаковано аккуратно.',
            'Отличный товар за свою цену.',
            'Покупкой доволен, спасибо!',
            'Всё супер, как на фото.',
        ];

        foreach ($productIds as $productId) {
            $cap = min(random_int(self::MIN_REVIEWS_PER_PRODUCT, self::MAX_REVIEWS_PER_PRODUCT), $users->count());
            if ($cap < 1) {
                continue;
            }

            $selectedUsers = $users->shuffle()->take($cap);
            foreach ($selectedUsers as $user) {
                $daysAgo = random_int(0, 45);
                $createdAt = Carbon::now()->subDays($daysAgo)->subHours(random_int(0, 23));
                $review = Review::create([
                    'reviewable_type' => Product::class,
                    'reviewable_id' => $productId,
                    'user_id' => $user->id,
                    'rating' => (int) fake()->numberBetween(1, 5),
                    'body' => fake()->optional(0.8)->randomElement($texts),
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

                $imagesCount = random_int(0, 3);
                for ($pos = 0; $pos < $imagesCount; $pos++) {
                    $review->imagesRelation()->create([
                        'path' => 'https://avatars.mds.yandex.net/get-mpic/5347553/2a00000192cd09d4b4cbb9bb28497c637e4a/optimize',
                        'is_cover' => $pos === 0,
                        'position' => $pos,
                    ]);
                }
            }
        }
    }
}
