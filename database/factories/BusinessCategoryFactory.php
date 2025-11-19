<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BusinessCategory>
 */
class BusinessCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->words(2, true);

        return [
            'name' => ucwords($name),
            'slug' => Str::slug($name) . '-' . Str::lower(Str::random(5)),
            'icon' => 'pi pi-'.fake()->randomElement(['building', 'shopping-cart', 'heart', 'star', 'car', 'briefcase']),
            'description' => fake()->optional()->sentence(),
            'is_active' => true,
        ];
    }
}
