<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'method' => fake()->randomElement(['bank_transfer', 'gopay', 'qris', 'credit_card']),
            'gateway_transaction_id' => (string) Str::uuid(),
            'amount' => fake()->numberBetween(50_000, 500_000),
            'raw_response' => ['transaction_status' => 'settlement', 'fraud_status' => 'accept'],
            'paid_at' => now(),
        ];
    }
}
