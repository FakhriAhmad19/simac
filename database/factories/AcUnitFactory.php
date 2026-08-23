<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\AcUnit>
 */
class AcUnitFactory extends Factory
{
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'brand' => fake()->randomElement(['Daikin', 'Panasonic', 'LG', 'Sharp', 'Samsung', 'Gree']),
            'type' => fake()->randomElement(['Split', 'Cassette', 'Standing']),
            'capacity_pk' => fake()->randomElement(['0.5', '1', '1.5', '2']),
            'location_note' => fake()->randomElement(['Ruang tamu', 'Kamar utama', 'Kantor lantai 2', 'Ruang rapat']),
        ];
    }
}
