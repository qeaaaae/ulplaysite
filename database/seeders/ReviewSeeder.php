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
    public function run(): void
    {
        $users = User::where('is_admin', false)->limit(25)->get();
        $productIds = Product::pluck('id')->unique()->values()->all();

        if (count($productIds) < 1) {
            return;
        }

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

        foreach ($users as $user) {
            $chosen = collect($productIds)->random(min(4, count($productIds)))->unique()->values()->all();
            foreach ($chosen as $productId) {
                $daysAgo = random_int(0, 45);
                $createdAt = Carbon::now()->subDays($daysAgo)->subHours(random_int(0, 23));
                Review::create([
                    'reviewable_type' => Product::class,
                    'reviewable_id' => $productId,
                    'user_id' => $user->id,
                    'rating' => (int) fake()->numberBetween(1, 5),
                    'body' => fake()->optional(0.8)->randomElement($texts),
                    'images' => null,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            }
        }
    }
}
