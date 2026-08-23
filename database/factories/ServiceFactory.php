<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Service>
 */
class ServiceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Cuci AC', 'Servis Rutin', 'Isi Freon', 'Bongkar Pasang', 'Perbaikan']),
            'description' => fake()->sentence(),
            'price' => fake()->numberBetween(50, 500) * 1000,
            'estimated_duration' => fake()->randomElement([30, 45, 60, 90, 120]),
        ];
    }
}
