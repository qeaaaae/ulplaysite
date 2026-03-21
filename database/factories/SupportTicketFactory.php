<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\SupportTicketTypeEnum;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SupportTicket>
 */
class SupportTicketFactory extends Factory
{
    protected $model = SupportTicket::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => fake()->randomElement(array_column(SupportTicketTypeEnum::cases(), 'value')),
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'status' => 'new',
            'ip_address' => fake()->optional()->ipv4(),
            'user_agent' => fake()->optional()->userAgent(),
        ];
    }

    public function forGuest(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => null,
        ]);
    }
}
