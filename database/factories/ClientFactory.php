<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->optional()->email(),
            'dob' => fake()->optional()->date(),
            'notes' => fake()->optional()->sentence(),
            'status' => fake()->randomElement(['Green', 'Yellow', 'Red']),
        ];
    }
}
