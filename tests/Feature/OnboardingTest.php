<?php

use App\Models\Business;
use App\Models\BusinessCategory;
use App\Models\User;

test('onboarding page can be rendered for authenticated users', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('onboarding.show'));

    $response->assertStatus(200);
});

test('guests cannot access onboarding page', function () {
    $response = $this->get(route('onboarding.show'));

    $response->assertRedirect(route('login'));
});

test('onboarding page displays categories', function () {
    $user = User::factory()->create();
    $category = BusinessCategory::factory()->create(['name' => 'Test Category']);
    $this->actingAs($user);

    $response = $this->get(route('onboarding.show'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Onboarding/Index')
        ->has('categories', 1)
    );
});

test('user with completed onboarding is redirected to dashboard', function () {
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

    $response = $this->get(route('onboarding.show'));

    $response->assertRedirect(route('dashboard'));
});

test('new business can be created through onboarding', function () {
    $user = User::factory()->create();
    $category = BusinessCategory::factory()->create();
    $this->actingAs($user);

    $response = $this->post(route('onboarding.store'), [
        'name' => 'Test Business',
        'description' => 'A test business description',
        'category_id' => $category->id,
        'email' => 'business@example.com',
        'phone' => '+1234567890',
        'address' => '123 Test St',
        'city' => 'Test City',
        'state' => 'Test State',
        'country' => 'Test Country',
        'postal_code' => '12345',
        'website' => 'https://test.com',
    ]);

    $response->assertRedirect(route('dashboard'));
    
    // Check business was created
    $this->assertDatabaseHas('businesses', [
        'name' => 'Test Business',
        'email' => 'business@example.com',
    ]);
    
    // Check user was attached as owner
    $business = Business::where('name', 'Test Business')->first();
    $this->assertTrue($user->businesses()->where('business_id', $business->id)->exists());
    $this->assertEquals('owner', $user->businesses()->first()->pivot->role);
    
    // Check onboarding was marked as completed
    $this->assertNotNull($business->onboarding_completed_at);
});

test('business creation requires name and email', function () {
    $user = User::factory()->create();
    $category = BusinessCategory::factory()->create();
    $this->actingAs($user);

    $response = $this->post(route('onboarding.store'), [
        'category_id' => $category->id,
    ]);

    $response->assertSessionHasErrors(['name', 'email']);
});

test('business creation requires valid category', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->post(route('onboarding.store'), [
        'name' => 'Test Business',
        'email' => 'business@example.com',
        'category_id' => 999, // Non-existent category
    ]);

    $response->assertSessionHasErrors(['category_id']);
});

test('business creation requires valid email', function () {
    $user = User::factory()->create();
    $category = BusinessCategory::factory()->create();
    $this->actingAs($user);

    $response = $this->post(route('onboarding.store'), [
        'name' => 'Test Business',
        'email' => 'invalid-email',
        'category_id' => $category->id,
    ]);

    $response->assertSessionHasErrors(['email']);
});

test('business can be created with minimal required fields', function () {
    $user = User::factory()->create();
    $category = BusinessCategory::factory()->create();
    $this->actingAs($user);

    $response = $this->post(route('onboarding.store'), [
        'name' => 'Minimal Business',
        'email' => 'minimal@example.com',
        'category_id' => $category->id,
    ]);

    $response->assertRedirect(route('dashboard'));
    
    $this->assertDatabaseHas('businesses', [
        'name' => 'Minimal Business',
        'email' => 'minimal@example.com',
    ]);
});

test('business slug is automatically generated from name', function () {
    $user = User::factory()->create();
    $category = BusinessCategory::factory()->create();
    $this->actingAs($user);

    $this->post(route('onboarding.store'), [
        'name' => 'My Test Business',
        'email' => 'test@example.com',
        'category_id' => $category->id,
    ]);

    $this->assertDatabaseHas('businesses', [
        'name' => 'My Test Business',
        'slug' => 'my-test-business',
    ]);
});

test('guest users cannot create business through onboarding', function () {
    $category = BusinessCategory::factory()->create();

    $response = $this->post(route('onboarding.store'), [
        'name' => 'Test Business',
        'email' => 'business@example.com',
        'category_id' => $category->id,
    ]);

    $response->assertRedirect(route('login'));
});
