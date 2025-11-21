<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Services\FeatureService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class TeamController extends Controller
{
    public function __construct(
        protected FeatureService $features,
    ) {}

    public function index(Request $request)
    {
        $business = $request->user()->getCurrentBusiness();

        if (!user_can('team.user-manage', $business)) {
            abort(403, 'You do not have permission to manage team users.');
        }
        
        $teamMembers = $business->users()
            ->with(['roles' => function($query) use ($business) {
                $query->where('role_user.team_id', $business->id);
            }])
            ->withPivot(['role', 'is_active', 'invited_at', 'joined_at'])
            ->get()
            ->map(function($user) use ($business) {
                $laratrustRoles = $user->roles->where('pivot.team_id', $business->id);
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'pivot_role' => $user->pivot->role,
                    'is_active' => $user->pivot->is_active,
                    'invited_at' => $user->pivot->invited_at,
                    'joined_at' => $user->pivot->joined_at,
                    'laratrust_roles' => $laratrustRoles->map(fn($role) => [
                        'id' => $role->id,
                        'name' => $role->name,
                        'display_name' => $role->display_name,
                    ]),
                ];
            });

        $roles = Role::where('team_id', $business->id)
            ->where('name', '!=', 'owner')
            ->get();

        $stats = [
            'total' => $teamMembers->count(),
            'active' => $teamMembers->where('is_active', true)->count(),
            'inactive' => $teamMembers->where('is_active', false)->count(),
            'owners' => $teamMembers->where('pivot_role', 'owner')->count(),
        ];

        return Inertia::render('Team/Users/Index', [
            'teamMembers' => $teamMembers,
            'roles' => $roles,
            'stats' => $stats,
        ]);
    }

    public function store(Request $request)
    {
        $business = $request->user()->getCurrentBusiness();

        if (!user_can('team.user-create', $business)) {
            return redirect()->back()->with('error', 'You do not have permission to create team users.');
        }

        if (! $this->features->canUseFeature($business, 'team_users', 1)) {
            return redirect()->back()->with('error', 'You have exceeded the maximum number of team users for your plan. Please upgrade your plan to add more team members.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => ['required', Password::default()],
            'role_id' => [
                'required', 
                'exists:roles,id', 
                function($attribute, $value, $fail) use ($business) {
                    $role = Role::find($value);
                    if (!$role) {
                        $fail('Role not found.');
                        return;
                    }
                    if ($role->name === 'owner') {
                        $fail('Cannot assign owner role to new team members.');
                        return;
                    }
                    if ($role->team_id !== $business->id) {
                        $fail('Role does not belong to this business.');
                    }
                }
            ],
        ]);

        DB::beginTransaction();
        try {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            $business->users()->attach($user->id, [
                'role' => 'member',
                'is_active' => true,
                'invited_at' => now(),
            ]);

            $role = Role::find($validated['role_id']);
            $user->addRole($role, $business);

            $this->features->recordUsage($business, 'team_users');

            DB::commit();

            return redirect()->back()->with('success', 'Team member invited successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with("error", 'Failed to invite team member.');
        }
    }

    public function update(Request $request, User $user)
    {
        $business = $request->user()->getCurrentBusiness();

        if (!user_can('team.user-edit', $business)) {
            return redirect()->back()->with('error', 'You do not have permission to edit team users.');
        }

        if (!$business->users()->where('users.id', $user->id)->exists()) {
            abort(403, 'User is not a member of this business.');
        }

        $pivotRole = $business->users()
            ->where('users.id', $user->id)
            ->first()
            ->pivot
            ->role;

        if ($pivotRole === 'owner') {
            abort(403, 'Cannot modify owner role.');
        }

        $validated = $request->validate([
            'role_id' => [
                'required', 
                'exists:roles,id', 
                function($attribute, $value, $fail) use ($business) {
                    $role = Role::find($value);
                    if (!$role) {
                        $fail('Role not found.');
                        return;
                    }
                    if ($role->name === 'owner') {
                        $fail('Cannot assign owner role.');
                        return;
                    }
                    if ($role->team_id !== $business->id) {
                        $fail('Role does not belong to this business.');
                    }
                }
            ],
        ]);

        DB::beginTransaction();
        try {
            $currentRoles = $user->roles()
                ->where('role_user.team_id', $business->id)
                ->where('name', '!=', 'owner')
                ->get();

            foreach ($currentRoles as $role) {
                $user->removeRole($role, $business);
            }

            $newRole = Role::find($validated['role_id']);
            $user->addRole($newRole, $business);

            DB::commit();

            return redirect()->back()->with('success', 'Team member role updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to update this role.');
        }
    }

    public function destroy(Request $request, User $user)
    {
        $business = $request->user()->getCurrentBusiness();

        if (!user_can('team.user-delete', $business)) {
            return redirect()->back()->with('error', 'You do not have permission to delete team users.');
        }

        if (!$business->users()->where('users.id', $user->id)->exists()) {
            abort(403, 'User is not a member of this business.');
        }

        $pivotRole = $business->users()
            ->where('users.id', $user->id)
            ->first()
            ->pivot
            ->role;

        if ($pivotRole === 'owner') {
            abort(403, 'Cannot remove owner from business.');
        }

        DB::beginTransaction();
        try {
            $userRoles = $user->roles()
                ->where('role_user.team_id', $business->id)
                ->get();

            foreach ($userRoles as $role) {
                $user->removeRole($role, $business);
            }

            $business->users()->detach($user->id);

            DB::commit();

            return redirect()->back()->with('success', 'Team member removed successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to remove team member.');
        }
    }
}
