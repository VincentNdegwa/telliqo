# Permission System Usage Guide

## Overview

The permission system allows you to control access to features based on user permissions. Permissions are automatically loaded from the backend and shared with all Vue components through Inertia.

## Available Methods

### 1. Using the `v-permission` Directive (Recommended for Templates)

The `v-permission` directive hides elements if the user doesn't have the required permission.

#### Basic Usage - Single Permission
```vue
<template>
    <!-- Only show if user has 'dashboard.manage' permission -->
    <Button v-permission="'dashboard.manage'">
        Manage Dashboard
    </Button>
</template>
```

#### Multiple Permissions (OR logic - any permission matches)
```vue
<template>
    <!-- Show if user has ANY of these permissions -->
    <Button v-permission="['dashboard.manage', 'dashboard.view']">
        View Dashboard
    </Button>
</template>
```

#### Multiple Permissions (AND logic - all permissions required)
```vue
<template>
    <!-- Show only if user has ALL permissions -->
    <Button v-permission.all="['dashboard.manage', 'feedback.manage']">
        Advanced Settings
    </Button>
</template>
```

#### Complete Example
```vue
<template>
    <div>
        <!-- Navigation items with permissions -->
        <nav>
            <a href="/dashboard" v-permission="'dashboard.view'">
                Dashboard
            </a>
            <a href="/feedback" v-permission="'feedback.view'">
                Feedback
            </a>
            <a href="/team" v-permission="['team.user.view', 'team.role.view']">
                Team
            </a>
        </nav>

        <!-- Action buttons -->
        <Button 
            v-permission="'feedback.create'"
            @click="createFeedback"
        >
            Create Feedback
        </Button>

        <!-- Complex permissions -->
        <Card v-permission.all="['dashboard.manage', 'business-settings.manage']">
            <CardContent>
                Advanced Admin Panel
            </CardContent>
        </Card>
    </div>
</template>
```

### 2. Using the `usePermissions()` Composable (Recommended for Script)

The composable provides reactive permission checking in your component logic.

```vue
<script setup lang="ts">
import { usePermissions } from '@/composables/usePermissions';

const { can, canAll, canAny, permissions } = usePermissions();

// Check single permission
if (can('dashboard.manage')) {
    console.log('User can manage dashboard');
}

// Check if user has ANY of these permissions
if (canAny(['dashboard.view', 'dashboard.manage'])) {
    console.log('User can access dashboard');
}

// Check if user has ALL permissions
if (canAll(['feedback.create', 'feedback.manage'])) {
    console.log('User has full feedback access');
}

// Access all permissions array
console.log(permissions.value);
</script>
```

### 3. Using Helper Functions Directly

Import permission helpers for standalone usage:

```typescript
import { hasPermission, hasAllPermissions } from '@/plugins/permission';

// Check single permission
if (hasPermission('dashboard.manage')) {
    // Do something
}

// Check multiple permissions (OR logic)
if (hasPermission(['dashboard.manage', 'dashboard.view'])) {
    // Has at least one permission
}

// Check all permissions (AND logic)
if (hasAllPermissions(['dashboard.manage', 'feedback.manage'])) {
    // Has all permissions
}
```

## Complete Component Example

```vue
<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { usePermissions } from '@/composables/usePermissions';
import { ref } from 'vue';

const { can, canAll } = usePermissions();

const showAdvancedOptions = ref(canAll([
    'dashboard.manage',
    'business-settings.manage'
]));

const handleCreate = () => {
    if (can('feedback.create')) {
        // Create feedback
    }
};

const handleDelete = (id: number) => {
    if (can('feedback.delete')) {
        // Delete feedback
    }
};
</script>

<template>
    <div>
        <h1>Feedback Management</h1>

        <!-- Show button only if user can create -->
        <Button 
            v-permission="'feedback.create'"
            @click="handleCreate"
        >
            Create Feedback
        </Button>

        <!-- List with conditional actions -->
        <div v-for="item in items" :key="item.id">
            <span>{{ item.title }}</span>
            
            <Button 
                v-permission="'feedback.edit'"
                @click="handleEdit(item)"
            >
                Edit
            </Button>
            
            <Button 
                v-permission="'feedback.delete'"
                variant="destructive"
                @click="handleDelete(item.id)"
            >
                Delete
            </Button>
        </div>

        <!-- Advanced section only for admins -->
        <Card v-if="showAdvancedOptions">
            <CardContent>
                Advanced Options (Admin Only)
            </CardContent>
        </Card>
    </div>
</template>
```

## Available Permissions

All permissions are automatically loaded from your backend. Common permissions include:

### Dashboard
- `dashboard.view` - View dashboard
- `dashboard.manage` - Manage dashboard
- `dashboard.export` - Export dashboard data
- `dashboard.stats.view` - View statistics

### Feedback
- `feedback.view` - View feedback
- `feedback.create` - Create feedback
- `feedback.edit` - Edit feedback
- `feedback.delete` - Delete feedback
- `feedback.reply` - Reply to feedback
- `feedback.flag` - Flag feedback

### Team
- `team.user.view` - View team users
- `team.user.create` - Invite team users
- `team.user.edit` - Edit team users
- `team.user.delete` - Remove team users
- `team.role.view` - View roles
- `team.role.create` - Create roles
- `team.role.edit` - Edit roles
- `team.role.delete` - Delete roles

And many more... Check `permissions` array in Inertia props for complete list.

## Best Practices

1. **Use directives for UI elements**: `v-permission` is cleaner for showing/hiding elements
2. **Use composable for logic**: Use `usePermissions()` for conditional logic in script
3. **Prefer specific permissions**: Use granular permissions rather than broad ones
4. **Combine permissions when needed**: Use `.all` modifier for features requiring multiple permissions
5. **Check permissions before actions**: Always verify permissions before executing sensitive operations

## TypeScript Support

All permission functions and composables are fully typed. Your IDE will provide autocomplete and type checking.

```typescript
import type { usePermissions } from '@/composables/usePermissions';

const { can, canAll, canAny } = usePermissions();

// TypeScript knows these return boolean
const hasAccess: boolean = can('dashboard.manage');
const hasFullAccess: boolean = canAll(['dashboard.manage', 'dashboard.view']);
```
