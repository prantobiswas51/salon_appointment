<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Appointment>
 */
class AppointmentFactory extends Factory
{

    public function definition(): array
    {
        return [
            'client_name' => fake()->name(),
            'client_phone' => fake()->phoneNumber(),
            'service' => fake()->randomElement(['Hair Cut', 'Beard Shaping', 'Other Services']),
            'start_time' => fake()->dateTimeBetween('now', '+1 month'),
            'duration' => fake()->numberBetween(15, 120),
            'attendance_status' => fake()->optional()->randomElement(['attended', 'canceled', 'no_show', 'pending']),
            'status' => fake()->randomElement(['Scheduled', 'Confirmed', 'Canceled']),
            'reminder_sent' => fake()->optional()->dateTime(),
            'notes' => fake()->optional()->sentence(),
            'event_id' => fake()->uuid(),
        ];
    }
}
