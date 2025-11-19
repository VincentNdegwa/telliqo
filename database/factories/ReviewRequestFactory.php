<?php

namespace Database\Factories;

use App\Models\Business;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ReviewRequest>
 */
class ReviewRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'business_id' => Business::factory(),
            'customer_id' => Customer::factory(),
            'unique_token' => Str::random(64),
            'subject' => fake()->sentence(),
            'message' => fake()->paragraph(),
            'status' => fake()->randomElement(['pending', 'opened', 'completed', 'expired']),
            'sent_at' => fake()->dateTimeBetween('-30 days', 'now'),
            'expires_at' => fake()->dateTimeBetween('now', '+30 days'),
            'opened_at' => null,
            'completed_at' => null,
        ];
    }

    /**
     * Indicate that the review request is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'opened_at' => null,
            'completed_at' => null,
        ]);
    }

    /**
     * Indicate that the review request has been opened.
     */
    public function opened(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'opened',
            'opened_at' => fake()->dateTimeBetween($attributes['sent_at'], 'now'),
            'completed_at' => null,
        ]);
    }

    /**
     * Indicate that the review request has been completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'opened_at' => fake()->dateTimeBetween($attributes['sent_at'], '-1 day'),
            'completed_at' => fake()->dateTimeBetween($attributes['sent_at'], 'now'),
        ]);
    }

    /**
     * Indicate that the review request has expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'expired',
            'expires_at' => fake()->dateTimeBetween('-30 days', '-1 day'),
        ]);
    }
}
