<?php

use App\Models\Business;
use App\Models\BusinessCategory;
use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users without business are redirected to onboarding', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    
    $response->assertRedirect(route('onboarding.show'));
});

test('authenticated users with completed onboarding can visit the dashboard', function () {
    $user = User::factory()->create();
    $category = BusinessCategory::factory()->create();
    $business = Business::factory()->create([
        'category_id' => $category->id,
        'onboarding_completed_at' => now(),
    ]);
    
    // Attach user to business as owner
    $business->users()->attach($user->id, [
        'role' => 'owner',
        'is_active' => true,
        'joined_at' => now(),
    ]);
    
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    
    $response->assertStatus(200);
});
