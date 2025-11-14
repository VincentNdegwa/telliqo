<?php

namespace Database\Factories;

use App\Models\Business;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'business_id' => Business::factory(),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'company_name' => fake()->optional()->company(),
            'tags' => fake()->optional()->randomElements(['vip', 'regular', 'new', 'returning'], rand(1, 2)),
            'notes' => fake()->optional()->paragraph(),
            'total_requests_sent' => 0,
            'total_feedbacks' => 0,
            'opted_out' => false,
        ];
    }

    public function optedOut(): static
    {
        return $this->state(fn (array $attributes) => [
            'opted_out' => true,
        ]);
    }
}
