<?php

namespace Tests\Feature\ApiKeys;

use App\Models\ApiKey;
use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ApiKeyManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Business $business;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\LaratrustSeeder::class);

        $this->user = User::factory()->create();
        $this->business = Business::factory()->create([
            'onboarding_completed_at' => now(),
        ]);
        
        $this->business->users()->attach($this->user->id, [
            'role' => 'owner',
            'is_active' => true,
            'joined_at' => now(),
        ]);

        $ownerRole = \App\Models\Role::where('name', 'owner')->first();
        if ($ownerRole) {
            $this->user->addRole($ownerRole, $this->business);
        }
    }

    public function test_user_can_view_api_keys_list(): void
    {
        ApiKey::factory()->count(3)->create([
            'business_id' => $this->business->id,
        ]);

        $response = $this->actingAs($this->user)->get(route('api-keys.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Settings/ApiKeys/Index')
                ->has('apiKeys', 3)
                ->has('stats')
        );
    }

    public function test_api_keys_stats_are_calculated_correctly(): void
    {
        ApiKey::factory()->create([
            'business_id' => $this->business->id,
            'is_active' => true,
            'expires_at' => now()->addDays(30),
        ]);

        ApiKey::factory()->create([
            'business_id' => $this->business->id,
            'is_active' => false,
            'expires_at' => now()->addDays(30),
        ]);

        ApiKey::factory()->create([
            'business_id' => $this->business->id,
            'is_active' => true,
            'expires_at' => now()->subDays(1),
        ]);

        $response = $this->actingAs($this->user)->get(route('api-keys.index'));

        $response->assertInertia(fn ($page) => 
            $page->where('stats.total', 3)
                ->where('stats.active', 1)
                ->where('stats.expired', 1)
        );
    }

    public function test_user_can_create_api_key(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('api-keys.store'), [
                'name' => 'Test API Key',
                'permissions' => ['read', 'write'],
                'expires_at' => now()->addDays(90)->format('Y-m-d'),
            ]);

        $acceptableStatuses = [200, 201, 302];

        $actualStatus = $response->getStatusCode();

        $this->assertTrue(
            in_array($actualStatus, $acceptableStatuses),
            "Expected one of the following statuses: " . implode(', ', $acceptableStatuses) . ". Got: " . $actualStatus
        );       


        $this->assertDatabaseHas('api_keys', [
            'business_id' => $this->business->id,
            'name' => 'Test API Key',
            'is_active' => true,
        ]);
    }

    public function test_api_key_generation_creates_unique_key(): void
    {
        $this->actingAs($this->user)
            ->post(route('api-keys.store'), [
                'name' => 'Test API Key',
                'permissions' => ['read'],
            ]);

        $apiKey = ApiKey::where('business_id', $this->business->id)->first();
        
        $this->assertNotNull($apiKey->key_hash);
    }

    public function test_user_can_update_api_key(): void
    {
        $apiKey = ApiKey::factory()->create([
            'business_id' => $this->business->id,
            'name' => 'Original Name',
            'permissions' => ['read'],
        ]);

        $response = $this->actingAs($this->user)
            ->put(route('api-keys.update', $apiKey), [
                'name' => 'Updated Name',
                'permissions' => ['read', 'write'],
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('api_keys', [
            'id' => $apiKey->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_user_cannot_update_api_key_from_different_business(): void
    {
        $otherBusiness = Business::factory()->create();
        $apiKey = ApiKey::factory()->create([
            'business_id' => $otherBusiness->id,
        ]);

        $response = $this->actingAs($this->user)
            ->put(route('api-keys.update', $apiKey), [
                'name' => 'Updated Name',
                'permissions' => ['read'],
            ]);

        $response->assertStatus(403);
    }

    public function test_user_can_revoke_api_key(): void
    {
        $apiKey = ApiKey::factory()->create([
            'business_id' => $this->business->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('api-keys.revoke', $apiKey));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $apiKey->refresh();
        $this->assertFalse($apiKey->is_active);
    }

    public function test_user_can_delete_api_key(): void
    {
        $apiKey = ApiKey::factory()->create([
            'business_id' => $this->business->id,
        ]);

        $response = $this->actingAs($this->user)
            ->delete(route('api-keys.destroy', $apiKey));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('api_keys', [
            'id' => $apiKey->id,
        ]);
    }

    public function test_api_key_validation_works(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('api-keys.store'), [
                'name' => '',
                'permissions' => [],
            ]);

        $response->assertSessionHasErrors([
            'name',
            'permissions',
        ]);
    }

    public function test_unauthenticated_user_cannot_access_api_keys(): void
    {
        $response = $this->get(route('api-keys.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_expired_api_keys_are_identified_correctly(): void
    {
        $expiredKey = ApiKey::factory()->create([
            'business_id' => $this->business->id,
            'expires_at' => now()->subDays(1),
            'is_active' => true,
        ]);

        $activeKey = ApiKey::factory()->create([
            'business_id' => $this->business->id,
            'expires_at' => now()->addDays(30),
            'is_active' => true,
        ]);

        $this->assertTrue($expiredKey->fresh()->isExpired());
        $this->assertFalse($activeKey->fresh()->isExpired());
    }
}
