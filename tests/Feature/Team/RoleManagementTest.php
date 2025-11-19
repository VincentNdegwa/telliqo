<?php

namespace Tests\Feature\Team;

use App\Models\Business;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleManagementTest extends TestCase
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
        $ownerRole = Role::where('name', 'owner')->first();
        if ($ownerRole) {
            $this->user->addRole($ownerRole, $this->business);
        }
    }

    public function test_user_can_view_roles_list(): void
    {
        Role::factory()->count(3)->create([
            'team_id' => $this->business->id,
        ]);

        $response = $this->actingAs($this->user)->get(route('team.roles.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Team/Roles/Index')
                ->has('roles', 3)
                ->has('allPermissions')
                ->has('stats')
        );
    }

    public function test_owner_role_is_excluded_from_roles_list(): void
    {
        Role::factory()->create([
            'team_id' => $this->business->id,
            'name' => 'owner',
        ]);

        Role::factory()->create([
            'team_id' => $this->business->id,
            'name' => 'manager',
        ]);

        $response = $this->actingAs($this->user)->get(route('team.roles.index'));

        $response->assertInertia(fn ($page) => 
            $page->has('roles', 1) // Only manager, owner excluded
        );
    }

    public function test_user_can_create_role(): void
    {
        $permissions = Permission::all();

        $response = $this->actingAs($this->user)
            ->post(route('team.roles.store'), [
                'name' => 'custom_role',
                'display_name' => 'Custom Role',
                'description' => 'A custom role for testing',
                'permissions' => $permissions->pluck('id')->toArray(),
            ]);

        $response->assertRedirect(route('team.roles.index'));
        $response->assertSessionHas('message');

        $this->assertDatabaseHas('roles', [
            'name' => 'custom_role',
            'team_id' => $this->business->id,
        ]);
    }

    public function test_created_role_has_assigned_permissions(): void
    {
        $permissions = Permission::take(2)->get();

        $this->actingAs($this->user)
            ->post(route('team.roles.store'), [
                'name' => 'custom_role',
                'display_name' => 'Custom Role',
                'permissions' => $permissions->pluck('id')->toArray(),
            ]);

        $role = Role::where('name', 'custom_role')->first();
        $this->assertEquals(2, $role->permissions()->count());
    }

    public function test_role_name_must_be_unique_per_business(): void
    {
        Role::factory()->create([
            'team_id' => $this->business->id,
            'name' => 'existing_role',
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('team.roles.store'), [
                'name' => 'existing_role',
                'display_name' => 'Existing Role',
                'permissions' => Permission::pluck('id')->toArray(),
            ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_cannot_create_role_with_owner_name(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('team.roles.store'), [
                'name' => 'owner',
                'display_name' => 'Owner',
                'permissions' => Permission::pluck('id')->toArray(),
            ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_user_can_view_role_edit_page(): void
    {
        $role = Role::factory()->create([
            'team_id' => $this->business->id,
            'name' => 'manager',
        ]);

        $response = $this->actingAs($this->user)->get(route('team.roles.edit', $role));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Team/Roles/Edit')
                ->has('role')
                ->has('permissions')
        );
    }

    public function test_cannot_edit_owner_role(): void
    {
        $ownerRole = Role::factory()->create([
            'team_id' => $this->business->id,
            'name' => 'owner',
        ]);

        $response = $this->actingAs($this->user)->get(route('team.roles.edit', $ownerRole));

        $response->assertStatus(403);
    }

    public function test_user_can_update_role(): void
    {
        $role = Role::factory()->create([
            'team_id' => $this->business->id,
            'name' => 'manager',
            'display_name' => 'Manager',
        ]);

        $permissions = Permission::take(2)->get();

        $response = $this->actingAs($this->user)
            ->put(route('team.roles.update', $role), [
                'name' => 'updated_manager',
                'display_name' => 'Updated Manager',
                'description' => 'Updated description',
                'permissions' => $permissions->pluck('id')->toArray(),
            ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('roles', [
            'id' => $role->id,
            'name' => 'updated_manager',
        ]);
    }

    public function test_updating_role_syncs_permissions(): void
    {
        $role = Role::factory()->create([
            'team_id' => $this->business->id,
        ]);

        $initialPermissions = Permission::take(1)->get();
        $role->syncPermissions($initialPermissions);

        $newPermissions = Permission::skip(1)->take(2)->get();

        $this->actingAs($this->user)
            ->put(route('team.roles.update', $role), [
                'name' => $role->name,
                'display_name' => $role->display_name,
                'permissions' => $newPermissions->pluck('id')->toArray(),
            ]);

        $role->refresh();
        $this->assertEquals(2, $role->permissions()->count());
    }

    public function test_user_can_delete_role(): void
    {
        $role = Role::factory()->create([
            'team_id' => $this->business->id,
        ]);

        $response = $this->actingAs($this->user)
            ->delete(route('team.roles.destroy', $role));

        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('roles', [
            'id' => $role->id,
        ]);
    }

    public function test_cannot_delete_owner_role(): void
    {
        $ownerRole = Role::factory()->create([
            'team_id' => $this->business->id,
            'name' => 'owner',
        ]);

        $response = $this->actingAs($this->user)
            ->delete(route('team.roles.destroy', $ownerRole));

        $response->assertStatus(403);
    }

    public function test_cannot_delete_role_from_different_business(): void
    {
        $otherBusiness = Business::factory()->create();
        $role = Role::factory()->create([
            'team_id' => $otherBusiness->id,
        ]);

        $response = $this->actingAs($this->user)
            ->delete(route('team.roles.destroy', $role));

        $response->assertStatus(403);
    }

    public function test_role_validation_works(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('team.roles.store'), [
                'name' => '',
                'display_name' => '',
                'permissions' => [],
            ]);

        $response->assertSessionHasErrors([
            'name',
            'display_name',
            'permissions',
        ]);
    }

    public function test_unauthenticated_user_cannot_access_role_management(): void
    {
        $response = $this->get(route('team.roles.index'));

        $response->assertRedirect(route('login'));
    }
}
