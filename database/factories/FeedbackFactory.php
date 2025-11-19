<?php

namespace Database\Factories;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Enums\ModerationStatus;
use App\Models\Enums\Sentiments;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Feedback>
 */
class FeedbackFactory extends Factory
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
            'customer_name' => fake()->name(),
            'customer_email' => fake()->safeEmail(),
            'rating' => fake()->numberBetween(1, 5),
            'comment' => fake()->paragraph(),
            'sentiment' => fake()->randomElement([Sentiments::POSITIVE, Sentiments::NEUTRAL, Sentiments::NEGATIVE]),
            'moderation_status' => fake()->randomElement([ModerationStatus::PUBLISHED, ModerationStatus::SOFT_FLAGGED, ModerationStatus::FLAGGED]),
            'is_public' => fake()->boolean(80),
            'submitted_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ];
    }

    /**
     * Indicate that the feedback is public.
     */
    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => true,
        ]);
    }

    /**
     * Indicate that the feedback is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'moderation_status' => ModerationStatus::PUBLISHED,
        ]);
    }

    /**
     * Indicate that the feedback is flagged.
     */
    public function flagged(): static
    {
        return $this->state(fn (array $attributes) => [
            'moderation_status' => ModerationStatus::FLAGGED,
        ]);
    }

    /**
     * Indicate that the feedback has been replied to.
     */
    public function replied(): static
    {
        return $this->state(fn (array $attributes) => [
            'reply_text' => fake()->paragraph(),
            'replied_at' => fake()->dateTimeBetween($attributes['submitted_at'] ?? '-30 days', 'now'),
        ]);
    }
}
