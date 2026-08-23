<?php

namespace Database\Factories;

use App\Enums\TechnicianStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Technician>
 */
class TechnicianFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->technician(),
            'specialization' => fake()->randomElement(['Servis rutin', 'Bongkar-pasang', 'Kelistrikan', 'Freon']),
            'status' => TechnicianStatus::Available,
        ];
    }
}
