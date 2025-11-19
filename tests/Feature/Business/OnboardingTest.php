<?php

namespace Tests\Feature\Business;

use App\Models\Business;
use App\Models\BusinessCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnboardingTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed permissions and roles
        $this->seed(\Database\Seeders\LaratrustSeeder::class);

        $this->user = User::factory()->create();
    }

    public function test_user_without_business_can_view_onboarding(): void
    {
        $response = $this->actingAs($this->user)->get(route('onboarding.show'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Onboarding/Index')
                ->has('categories')
        );
    }

    public function test_user_with_completed_onboarding_is_redirected_to_dashboard(): void
    {
        $business = Business::factory()->create([
            'onboarding_completed_at' => now(),
        ]);
        
        $business->users()->attach($this->user->id, [
            'role' => 'owner',
            'is_active' => true,
            'joined_at' => now(),
        ]);

        $response = $this->actingAs($this->user)->get(route('onboarding.show'));

        $response->assertRedirect(route('dashboard'));
    }

    public function test_user_can_complete_onboarding(): void
    {
        $category = BusinessCategory::factory()->create();

        $onboardingData = [
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
            'website' => 'https://test.example.com',
        ];

        $response = $this->actingAs($this->user)
            ->post(route('onboarding.store'), $onboardingData);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('businesses', [
            'name' => 'Test Business',
            'email' => 'business@example.com',
        ]);

        // Check that user is attached to business
        $business = Business::where('name', 'Test Business')->first();
        $this->assertTrue($business->users()->where('user_id', $this->user->id)->exists());
        
        // Check that user has owner role
        $pivot = $business->users()->where('user_id', $this->user->id)->first()->pivot;
        $this->assertEquals('owner', $pivot->role);
    }

    public function test_onboarding_validation_works(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('onboarding.store'), [
                'name' => '',
                'email' => 'invalid-email',
                'website' => 'not-a-url',
            ]);

        $response->assertSessionHasErrors([
            'name',
            'category_id',
            'email',
            'website',
        ]);
    }

    public function test_onboarding_creates_default_settings(): void
    {
        $category = BusinessCategory::factory()->create();

        $this->actingAs($this->user)
            ->post(route('onboarding.store'), [
                'name' => 'Test Business',
                'category_id' => $category->id,
                'email' => 'business@example.com',
            ]);

        $business = Business::where('name', 'Test Business')->first();
        
        $this->assertNotNull($business->onboarding_completed_at);
    }

    public function test_unauthenticated_user_cannot_access_onboarding(): void
    {
        $response = $this->get(route('onboarding.show'));

        $response->assertRedirect(route('login'));
    }

    public function test_onboarding_creates_business_with_slug(): void
    {
        $category = BusinessCategory::factory()->create();

        $this->actingAs($this->user)
            ->post(route('onboarding.store'), [
                'name' => 'Test Business Name',
                'category_id' => $category->id,
                'email' => 'business@example.com',
            ]);

        $this->assertDatabaseHas('businesses', [
            'name' => 'Test Business Name',
            'slug' => 'test-business-name',
        ]);
    }
}
