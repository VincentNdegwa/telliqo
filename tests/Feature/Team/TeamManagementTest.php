<?php

namespace Tests\Feature\Team;

use App\Models\Business;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TeamManagementTest extends TestCase
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

    public function test_user_can_view_team_members_list(): void
    {
        $member = User::factory()->create();
        $this->business->users()->attach($member->id, [
            'role' => 'member',
            'is_active' => true,
            'joined_at' => now(),
        ]);

        $response = $this->actingAs($this->user)->get(route('team.users.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) =>
            $page->component('Team/Users/Index')
                ->has('teamMembers', 2)
                ->has('stats')
        );
    }

    public function test_team_members_include_roles(): void
    {
        $role = Role::factory()->create([
            'team_id' => $this->business->id,
            'name' => 'manager',
        ]);

        $member = User::factory()->create();
        $this->business->users()->attach($member->id, [
            'role' => 'member',
            'is_active' => true,
            'joined_at' => now(),
        ]);
        $member->addRole($role, $this->business);

        $response = $this->actingAs($this->user)->get(route('team.users.index'));

        $response->assertInertia(fn ($page) => 
            $page->has('teamMembers.1.pivot_role')
        );
    }

    public function test_user_can_invite_team_member(): void
    {
        $role = Role::factory()->create([
            'team_id' => $this->business->id,
            'name' => 'member',
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('team.users.store'), [
                'name' => 'New Member',
                'email' => 'newmember@example.com',
                'password' => 'password123',
                'role_id' => $role->id,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'email' => 'newmember@example.com',
        ]);

        $newUser = User::where('email', 'newmember@example.com')->first();
        $this->assertTrue($this->business->users()->where('users.id', $newUser->id)->exists());
    }

    public function test_invited_member_has_correct_role(): void
    {
        $role = Role::factory()->create([
            'team_id' => $this->business->id,
            'name' => 'member',
        ]);

        $this->actingAs($this->user)
            ->post(route('team.users.store'), [
                'name' => 'New Member',
                'email' => 'newmember@example.com',
                'password' => 'password123',
                'role_id' => $role->id,
            ]);

        $newUser = User::where('email', 'newmember@example.com')->first();
        $this->assertTrue($newUser->hasRole($role->name, $this->business));
    }

    public function test_cannot_invite_member_with_owner_role(): void
    {
        $ownerRole = Role::factory()->create([
            'team_id' => $this->business->id,
            'name' => 'owner',
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('team.users.store'), [
                'name' => 'New Member',
                'email' => 'newmember@example.com',
                'password' => 'password123',
                'role_id' => $ownerRole->id,
            ]);

        $response->assertSessionHasErrors('role_id');
    }

    public function test_user_can_update_team_member_role(): void
    {
        $oldRole = Role::factory()->create([
            'team_id' => $this->business->id,
            'name' => 'member',
        ]);
        
        $newRole = Role::factory()->create([
            'team_id' => $this->business->id,
            'name' => 'manager',
        ]);

        $member = User::factory()->create();
        $this->business->users()->attach($member->id, [
            'role' => 'member',
            'is_active' => true,
            'joined_at' => now(),
        ]);
        $member->addRole($oldRole, $this->business);

        $response = $this->actingAs($this->user)
            ->put(route('team.users.update', $member), [
                'role_id' => $newRole->id,
            ]);

        $response->assertSessionHas('success');

        $this->assertTrue($member->hasRole('manager', $this->business));
        $this->assertFalse($member->hasRole('member', $this->business));
    }

    public function test_cannot_update_owner_role(): void
    {
        $ownerMember = User::factory()->create();
        $this->business->users()->attach($ownerMember->id, [
            'role' => 'owner',
            'is_active' => true,
            'joined_at' => now(),
        ]);

        $role = Role::factory()->create([
            'team_id' => $this->business->id,
            'name' => 'member',
        ]);

        $response = $this->actingAs($this->user)
            ->put(route('team.users.update', $ownerMember), [
                'role_id' => $role->id,
            ]);

        $response->assertStatus(403);
    }

    public function test_user_can_remove_team_member(): void
    {
        $member = User::factory()->create();
        $this->business->users()->attach($member->id, [
            'role' => 'member',
            'is_active' => true,
            'joined_at' => now(),
        ]);

        $response = $this->actingAs($this->user)
            ->delete(route('team.users.destroy', $member));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertFalse($this->business->users()->where('users.id', $member->id)->exists());
    }

    public function test_cannot_remove_owner_from_team(): void
    {
        $ownerMember = User::factory()->create();
        $this->business->users()->attach($ownerMember->id, [
            'role' => 'owner',
            'is_active' => true,
            'joined_at' => now(),
        ]);

        $response = $this->actingAs($this->user)
            ->delete(route('team.users.destroy', $ownerMember));

        $response->assertStatus(403);
    }

    public function test_team_member_validation_works(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('team.users.store'), [
                'name' => '',
                'email' => 'invalid-email',
                'password' => '123',
            ]);

        $response->assertSessionHasErrors([
            'name',
            'email',
            'password',
            'role_id',
        ]);
    }

    public function test_unauthenticated_user_cannot_access_team_management(): void
    {
        $response = $this->get(route('team.users.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_cannot_add_member_from_different_business_role(): void
    {
        $otherBusiness = Business::factory()->create();
        $roleFromOtherBusiness = Role::factory()->create([
            'team_id' => $otherBusiness->id,
            'name' => 'member',
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('team.users.store'), [
                'name' => 'New Member',
                'email' => 'newmember@example.com',
                'password' => 'password123',
                'role_id' => $roleFromOtherBusiness->id,
            ]);

        $response->assertSessionHasErrors('role_id');
    }
}
