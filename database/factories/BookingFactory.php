<?php

namespace Database\Factories;

use App\Enums\BookingStatus;
use App\Models\Customer;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Booking>
 */
class BookingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'technician_id' => null,
            'service_id' => Service::factory(),
            'ac_unit_id' => null,
            'scheduled_at' => fake()->dateTimeBetween('-1 month', '+2 weeks'),
            'status' => BookingStatus::Pending,
            'notes' => fake()->optional()->sentence(),
            'created_by' => User::factory()->admin(),
        ];
    }
}
