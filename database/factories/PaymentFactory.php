<?php

namespace Database\Factories;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    public function definition(): array
    {
        $status = fake()->randomElement(PaymentStatus::cases());

        return [
            'booking_id' => Booking::factory(),
            'amount' => fake()->numberBetween(50, 500) * 1000,
            'payment_method' => fake()->randomElement(PaymentMethod::cases()),
            'status' => $status,
            'paid_at' => $status === PaymentStatus::Paid ? now() : null,
        ];
    }
}
