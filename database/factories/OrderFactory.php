<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'course_id' => Course::factory(),
            'order_number' => 'ORD-'.Str::upper(Str::random(10)),
            'amount' => fake()->numberBetween(50_000, 500_000),
            'status' => 'pending',
            'gateway_ref' => null,
            'expired_at' => now()->addDay(),
            'paid_at' => null,
        ];
    }

    public function paid(): static
    {
        return $this->state([
            'status' => 'paid',
            'gateway_ref' => 'PAY-'.Str::upper(Str::random(12)),
            'paid_at' => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state(['status' => 'failed']);
    }

    public function expired(): static
    {
        return $this->state(['status' => 'expired', 'expired_at' => now()->subDay()]);
    }
}
