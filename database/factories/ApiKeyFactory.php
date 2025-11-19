<?php

namespace Database\Factories;

use App\Models\Business;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ApiKey>
 */
class ApiKeyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $key = bin2hex(random_bytes(32));
        
        return [
            'business_id' => Business::factory(),
            'name' => fake()->words(3, true),
            'key_hash' => hash('sha256', $key),
            'key_preview' => substr($key, 0, 8) . '...',
            'permissions' => fake()->randomElement([['read'], ['write'], ['read', 'write']]),
            'is_active' => true,
            'last_used_at' => null,
            'expires_at' => fake()->dateTimeBetween('now', '+1 year'),
        ];
    }

    /**
     * Indicate that the API key is revoked.
     */
    public function revoked(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the API key is expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => fake()->dateTimeBetween('-30 days', '-1 day'),
        ]);
    }
}
