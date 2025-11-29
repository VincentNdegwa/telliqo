<?php

namespace Database\Factories;

use App\Models\BusinessCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Business>
 */
class BusinessFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->company();
        
        $category = BusinessCategory::inRandomOrder()->first();
        if (!$category) {
            $category = BusinessCategory::create([
                'name' => 'General',
                'slug' => 'general',
                'description' => 'General business category',
                'is_active' => true,
            ]);
        }
        
        return [
            'name' => $name,
            'slug' => Str::slug($name) . '-' . Str::lower(Str::random(5)),
            'description' => fake()->optional()->sentence(),
            'category_id' => $category->id,
            'email' => fake()->companyEmail(),
            'phone' => fake()->optional()->phoneNumber(),
            'address' => fake()->optional()->streetAddress(),
            'city' => fake()->optional()->city(),
            'state' => fake()->optional()->state(),
            'country' => fake()->optional()->country(),
            'postal_code' => fake()->optional()->postcode(),
            'website' => fake()->optional()->url(),
            'logo' => null,
            'is_active' => true,
            'onboarding_completed_at' => null,
        ];
    }

    /**
     * Indicate that the business has completed onboarding.
     */
    public function onboarded(): static
    {
        return $this->state(fn (array $attributes) => [
            'onboarding_completed_at' => now(),
        ]);
    }
}
