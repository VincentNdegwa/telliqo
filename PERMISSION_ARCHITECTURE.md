# Permission Architecture

## Overview
This application uses Laratrust for role-based access control with a multi-tenant (business) architecture.

## Architecture Design

### Permissions (Global)
- Permissions are **global** and shared across all businesses
- No `team_id` column - permissions are defined once and reused
- Examples: `feedback.manage`, `dashboard.view`, `team.role-create`

### Roles (Business-Scoped + Global Owner)
- Most roles are **business-scoped** with `team_id` column
- Each business can create custom roles with any name
- **Exception**: The `owner` role is **global** (`team_id = null`)
  - Special role that exists across all businesses
  - Automatically granted to business owners
  - Cannot be edited or deleted through UI

### Role-Permission Assignment
- Business-scoped roles can be assigned any global permissions
- Each business decides which permissions their roles have
- Permission assignments are stored in `permission_role` table

### User-Role Assignment
- Users are assigned roles within a specific business context
- Stored in `role_user` table with `team_id` column
- A user can have different roles in different businesses

## Database Schema

```sql
-- Global permissions (no team_id)
permissions
├── id
├── name (unique)
├── display_name
└── description

-- Roles (business-scoped, except owner)
roles
├── id
├── team_id (nullable, null for 'owner' role)
├── name
├── display_name
└── description
└── UNIQUE(name, team_id) -- Same name allowed across businesses

-- Permission-Role assignments
permission_role
├── permission_id
└── role_id

-- User-Role assignments (business context)
role_user
├── user_id
├── role_id
├── team_id (business_id)
└── user_type
```

## Flow Examples

### Creating a Custom Role
1. Business A creates role "Manager" with `team_id = business_a_id`
2. Assigns permissions: `feedback.manage`, `dashboard.view`
3. Business B can also create role "Manager" with `team_id = business_b_id`
4. Both roles have same name but different permissions and scope

### Assigning Role to User
1. User invited to Business A
2. Assigned role "Manager" from Business A's roles
3. `role_user` entry created with `team_id = business_a_id`
4. User gets all permissions assigned to that specific "Manager" role

### Checking Permissions
```php
// In controller
$business = $user->getCurrentBusiness();
$permissions = $user->allPermissions($business); // Gets permissions through roles in this business

// In Vue template
<Button v-permission="'feedback.manage'">Manage Feedback</Button>

// In Vue component
import { usePermissions } from '@/composables/usePermissions';
const { can } = usePermissions();
if (can('feedback.manage')) { /* ... */ }
```

## Key Files

### Controllers
- `app/Http/Controllers/TeamController.php` - Team member management
- `app/Http/Controllers/RoleController.php` - Role and permission management

### Models
- `app/Models/Role.php` - Role model (extends Laratrust)
- `app/Models/Permission.php` - Permission model (extends Laratrust)

### Middleware
- `app/Http/Middleware/HandleInertiaRequests.php` - Shares user permissions with frontend

### Frontend
- `resources/js/plugins/permission.ts` - Vue permission directive
- `resources/js/composables/usePermissions.ts` - Permission checking composable
- `resources/js/pages/Team/Users/Index.vue` - Team member management UI
- `resources/js/pages/Team/Roles/Index.vue` - Role listing and creation
- `resources/js/pages/Team/Roles/Edit.vue` - Role editing

### Database
- `database/migrations/2025_11_15_121734_laratrust_setup_tables.php` - Base Laratrust tables
- `database/migrations/2025_11_15_135703_add_team_id_to_roles_and_permissions_tables.php` - Adds team_id to roles
- `database/seeders/LaratrustSeeder.php` - Seeds global permissions and owner role

### Commands
- `app/Console/Commands/SyncBusinessOwnerPermissions.php` - Syncs owner role to business owners

## Migration Guide

### To Apply Changes
1. Run migration to add `team_id` to roles table:
   ```bash
   php artisan migrate
   ```

2. Update existing roles to be business-scoped (if needed):
   ```bash
   # Custom script or manual SQL to assign team_id to existing non-owner roles
   ```

3. Verify owner role has `team_id = null`:
   ```bash
   php artisan tinker
   >>> Role::where('name', 'owner')->first()->team_id
   # Should return null
   ```

## Best Practices

1. **Always filter roles by business**
   ```php
   $roles = Role::where('team_id', $business->id)->get();
   ```

2. **Exclude owner role from user operations**
   ```php
   $roles = Role::where('team_id', $business->id)
       ->where('name', '!=', 'owner')
       ->get();
   ```

3. **Validate role belongs to business**
   ```php
   if ($role->team_id !== $business->id) {
       abort(403, 'Role does not belong to this business');
   }
   ```

4. **Check permissions in business context**
   ```php
   $business = $user->getCurrentBusiness();
   $permissions = $user->allPermissions($business);
   ```

5. **Use permission directive in Vue**
   ```vue
   <Button v-permission="'feedback.manage'">...</Button>
   <Button v-permission.all="['feedback.manage', 'feedback.reply']">...</Button>
   ```

## Security Considerations

- Owner role cannot be edited or deleted
- Users can only manage roles within their current business
- Role assignments are validated against business context
- Permission checks always include business context
- Team members cannot be assigned owner role through UI
