<?php

namespace Tests\Feature\Customers;

use App\Models\Business;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Business $business;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed permissions and roles
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

        // Assign owner role via Laratrust
        $ownerRole = \App\Models\Role::where('name', 'owner')->first();
        if ($ownerRole) {
            $this->user->addRole($ownerRole, $this->business);
        }
    }

    public function test_authenticated_user_can_view_customers_list(): void
    {
        Customer::factory()->count(5)->create([
            'business_id' => $this->business->id,
        ]);

        $response = $this->actingAs($this->user)->get(route('customers.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Customers/Index')
                ->has('customers.data', 5)
        );
    }

    public function test_unauthenticated_user_cannot_view_customers_list(): void
    {
        $response = $this->get(route('customers.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_user_can_view_create_customer_form(): void
    {
        $response = $this->actingAs($this->user)->get(route('customers.create'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Customers/Create')
        );
    }

    public function test_user_can_create_customer(): void
    {
        $customerData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+1234567890',
            'company_name' => 'ACME Corp',
            'tags' => ['VIP', 'Enterprise'],
            'notes' => 'Important customer',
        ];

        $response = $this->actingAs($this->user)
            ->post(route('customers.store'), $customerData);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('customers', [
            'business_id' => $this->business->id,
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
    }

    public function test_customer_creation_requires_unique_email_per_business(): void
    {
        Customer::factory()->create([
            'business_id' => $this->business->id,
            'email' => 'existing@example.com',
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('customers.store'), [
                'name' => 'Test User',
                'email' => 'existing@example.com',
            ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_customer_creation_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('customers.store'), [
                'name' => '',
                'email' => 'invalid-email',
            ]);

        $response->assertSessionHasErrors(['name', 'email']);
    }

    public function test_user_can_view_customer_details(): void
    {
        $customer = Customer::factory()->create([
            'business_id' => $this->business->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('customers.show', $customer));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Customers/Show')
                ->has('customer')
        );
    }

    public function test_user_can_update_customer(): void
    {
        $customer = Customer::factory()->create([
            'business_id' => $this->business->id,
            'name' => 'Old Name',
        ]);

        $response = $this->actingAs($this->user)
            ->put(route('customers.update', $customer), [
                'name' => 'New Name',
                'email' => $customer->email,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'name' => 'New Name',
        ]);
    }

    public function test_user_can_delete_customer(): void
    {
        $customer = Customer::factory()->create([
            'business_id' => $this->business->id,
        ]);

        $response = $this->actingAs($this->user)
            ->delete(route('customers.destroy', $customer));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseEmpty('customers');
    }

    public function test_user_can_search_customers_by_name(): void
    {
        Customer::factory()->create([
            'business_id' => $this->business->id,
            'name' => 'John Smith',
        ]);

        Customer::factory()->create([
            'business_id' => $this->business->id,
            'name' => 'Jane Doe',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('customers.index', ['search' => 'John']));

        $response->assertStatus(200);
    }

    public function test_user_can_filter_customers_by_opted_out_status(): void
    {
        Customer::factory()->create([
            'business_id' => $this->business->id,
            'opted_out' => true,
        ]);

        Customer::factory()->create([
            'business_id' => $this->business->id,
            'opted_out' => false,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('customers.index', ['opted_out' => true]));

        $response->assertStatus(200);
    }

    public function test_customer_stats_are_calculated_correctly(): void
    {
        Customer::factory()->count(3)->create([
            'business_id' => $this->business->id,
            'opted_out' => false,
        ]);

        Customer::factory()->count(2)->create([
            'business_id' => $this->business->id,
            'opted_out' => true,
        ]);

        $response = $this->actingAs($this->user)->get(route('customers.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->where('stats.total', 5)
                ->where('stats.active', 3)
                ->where('stats.opted_out', 2)
        );
    }

    public function test_user_cannot_access_another_business_customer(): void
    {
        $otherBusiness = Business::factory()->create();
        $otherCustomer = Customer::factory()->create([
            'business_id' => $otherBusiness->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('customers.show', $otherCustomer));

        $response->assertStatus(403);
    }
}
