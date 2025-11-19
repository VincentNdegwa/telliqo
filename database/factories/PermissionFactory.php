<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Permission>
 */
class PermissionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = ['feedback', 'customer', 'team', 'api-integration', 'settings'];
        $actions = ['view', 'create', 'edit', 'delete'];
        
        $category = fake()->randomElement($categories);
        $action = fake()->randomElement($actions);
        
        return [
            'name' => "{$category}.{$action}",
            'display_name' => ucfirst($action) . ' ' . ucfirst($category),
            'description' => fake()->sentence(),
        ];
    }
}
