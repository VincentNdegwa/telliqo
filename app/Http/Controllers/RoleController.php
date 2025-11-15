<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $business = $request->user()->getCurrentBusiness();

        if (!user_can('team.role-manage', $business)) {
            abort(403, 'You do not have permission to manage roles.');
        }
        
        // Get roles for this business only (excluding owner role)
        $roles = Role::with(['permissions'])
            ->where('team_id', $business->id)
            ->where('name', '!=', 'owner')
            ->get()
            ->map(function($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'display_name' => $role->display_name,
                    'description' => $role->description,
                    'permissions_count' => $role->permissions->count(),
                    'created_at' => $role->created_at,
                ];
            });

        // Permissions are global
        $allPermissions = Permission::all()->groupBy(function($permission) {
            return explode('.', $permission->name)[0];
        });

        $stats = [
            'total' => $roles->count(),
            'total_permissions' => Permission::count(),
        ];

        return Inertia::render('Team/Roles/Index', [
            'roles' => $roles,
            'allPermissions' => $allPermissions,
            'stats' => $stats,
        ]);
    }

    public function create()
    {
        $business = request()->user()->getCurrentBusiness();

        if (!user_can('team.role-create', $business)) {
            abort(403, 'You do not have permission to create roles.');
        }

        $permissions = Permission::all()->groupBy(function($permission) {
            return explode('.', $permission->name)[0];
        });

        return Inertia::render('Team/Roles/Create', [
            'permissions' => $permissions,
        ]);
    }

    public function store(Request $request)
    {
        $business = $request->user()->getCurrentBusiness();

        if (!user_can('team.role-create', $business)) {
            return redirect()->back()->with('error', 'You do not have permission to create roles.');
        }
        
        $validated = $request->validate([
            'name' => [
                'required', 
                'string', 
                'max:255', 
                'alpha_dash', 
                Rule::notIn(['owner']),
                Rule::unique('roles', 'name')->where('team_id', $business->id),
            ],
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        DB::beginTransaction();
        try {
            $role = Role::create([
                'name' => $validated['name'],
                'display_name' => $validated['display_name'],
                'description' => $validated['description'] ?? null,
                'team_id' => $business->id,
            ]);

            $permissions = Permission::whereIn('id', $validated['permissions'])->get();
            $role->syncPermissions($permissions);

            DB::commit();

            return redirect()->route('team.roles.index')->with('message', 'Role created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Failed to create role.']);
        }
    }

    public function edit(Role $role)
    {
        $business = request()->user()->getCurrentBusiness();

        if (!user_can('team.role-edit', $business)) {
            abort(403, 'You do not have permission to edit roles.');
        }
        
        // Ensure role belongs to current business
        if ($role->team_id !== $business->id || $role->name === 'owner') {
            abort(403, 'Cannot edit this role.');
        }

        $role->load('permissions');
        
        $permissions = Permission::all()->groupBy(function($permission) {
            return explode('.', $permission->name)[0];
        });

        $rolePermissionIds = $role->permissions->pluck('id')->toArray();

        return Inertia::render('Team/Roles/Edit', [
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'display_name' => $role->display_name,
                'description' => $role->description,
                'permissions' => $rolePermissionIds,
            ],
            'permissions' => $permissions,
        ]);
    }

    public function update(Request $request, Role $role)
    {
        $business = $request->user()->getCurrentBusiness();

        if (!user_can('team.role-edit', $business)) {
            return redirect()->back()->with('error', 'You do not have permission to edit roles.');
        }
        
        // Ensure role belongs to current business
        if ($role->team_id !== $business->id || $role->name === 'owner') {
            abort(403, 'Cannot edit this role.');
        }

        $validated = $request->validate([
            'name' => [
                'required', 
                'string', 
                'max:255', 
                'alpha_dash', 
                Rule::notIn(['owner']),
                Rule::unique('roles', 'name')->where('team_id', $business->id)->ignore($role->id),
            ],
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        DB::beginTransaction();
        try {
            $role->update([
                'name' => $validated['name'],
                'display_name' => $validated['display_name'],
                'description' => $validated['description'] ?? null,
            ]);

            $permissions = Permission::whereIn('id', $validated['permissions'])->get();
            $role->syncPermissions($permissions);

            DB::commit();

            return redirect()->route('team.roles.index')->with('message', 'Role updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Failed to update role.']);
        }
    }

    public function destroy(Role $role)
    {
        $business = request()->user()->getCurrentBusiness();

        if (!user_can('team.role-delete', $business)) {
            return redirect()->back()->with('error', 'You do not have permission to delete roles.');
        }
        
        // Ensure role belongs to current business
        if ($role->team_id !== $business->id || $role->name === 'owner') {
            abort(403, 'Cannot delete this role.');
        }

        $usersCount = DB::table('role_user')
            ->where('role_id', $role->id)
            ->where('team_id', $business->id)
            ->count();

        if ($usersCount > 0) {
            return redirect()->back()->withErrors(['error' => "Cannot delete role. It is assigned to {$usersCount} user(s)."]);
        }

        DB::beginTransaction();
        try {
            $role->permissions()->detach();
            $role->delete();

            DB::commit();

            return redirect()->back()->with('message', 'Role deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Failed to delete role.']);
        }
    }
}
