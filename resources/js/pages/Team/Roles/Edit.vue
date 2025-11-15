<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import team from '@/routes/team';
import { type BreadcrumbItem } from '@/types';
import { router, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Save } from 'lucide-vue-next';
import Checkbox from 'primevue/checkbox';
import Textarea from 'primevue/textarea';
import { useToast } from 'primevue/usetoast';

interface Permission {
    id: number;
    name: string;
    display_name: string;
    description: string | null;
}

interface Role {
    id: number;
    name: string;
    display_name: string;
    description: string | null;
    permissions: number[];
}

interface Props {
    role: Role;
    permissions: Record<string, Permission[]>;
}

const props = defineProps<Props>();
const toast = useToast();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Team',
        href: team.users.index().url,
    },
    {
        title: 'Roles',
        href: team.roles.index().url,
    },
    {
        title: 'Edit Role',
        href: team.roles.edit(props.role.id).url,
    },
];

const form = useForm({
    name: props.role.name,
    display_name: props.role.display_name,
    description: props.role.description || '',
    permissions: props.role.permissions,
});

const updateRole = () => {
    if (!form.name.trim()) {
        toast.add({
            severity: 'error',
            summary: 'Validation Error',
            detail: 'Name is required',
            life: 3000,
        });
        return;
    }

    if (!form.display_name.trim()) {
        toast.add({
            severity: 'error',
            summary: 'Validation Error',
            detail: 'Display name is required',
            life: 3000,
        });
        return;
    }

    if (form.permissions.length === 0) {
        toast.add({
            severity: 'error',
            summary: 'Validation Error',
            detail: 'Please select at least one permission',
            life: 3000,
        });
        return;
    }

    form.put(team.roles.update(props.role.id).url, {
        preserveScroll: true,
        onSuccess: () => {
            toast.add({
                severity: 'success',
                summary: 'Success',
                detail: 'Role updated successfully',
                life: 3000,
            });
        },
        onError: (errors) => {
            toast.add({
                severity: 'error',
                summary: 'Error',
                detail: Object.values(errors)[0] as string,
                life: 3000,
            });
        },
    });
};

const capitalizeModule = (module: string) => {
    return module
        .split('-')
        .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
};

const getAllPermissionIds = () => {
    return Object.values(props.permissions)
        .flat()
        .map((p) => p.id);
};

const isAllSelected = () => {
    const allIds = getAllPermissionIds();
    return allIds.every(id => form.permissions.includes(id));
};

const toggleSelectAll = () => {
    if (isAllSelected()) {
        form.permissions = [];
    } else {
        form.permissions = getAllPermissionIds();
    }
};

const getModulePermissionIds = (permissions: Permission[]) => {
    return permissions.map(p => p.id);
};

const isModuleAllSelected = (permissions: Permission[]) => {
    const moduleIds = getModulePermissionIds(permissions);
    return moduleIds.every(id => form.permissions.includes(id));
};

const toggleModuleSelectAll = (permissions: Permission[]) => {
    const moduleIds = getModulePermissionIds(permissions);
    if (isModuleAllSelected(permissions)) {
        form.permissions = form.permissions.filter(
            id => !moduleIds.includes(id)
        );
    } else {
        const newIds = moduleIds.filter(id => !form.permissions.includes(id));
        form.permissions = [...form.permissions, ...newIds];
    }
};

const goBack = () => {
    router.get(team.roles.index().url);
};
</script>

<template>
    <AppLayout title="Edit Role" :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <div class="flex items-center justify-between">
                <div>
                    <div class="mb-2 flex items-center gap-2">
                        <Button
                            variant="ghost"
                            size="icon"
                            @click="goBack"
                        >
                            <ArrowLeft class="h-4 w-4" />
                        </Button>
                        <h1 class="text-3xl font-bold tracking-tight">
                            Edit Role
                        </h1>
                    </div>
                    <p class="text-muted-foreground">
                        Update role details and permissions
                    </p>
                </div>
                <Button @click="updateRole" :disabled="form.processing">
                    <Save class="mr-2 h-4 w-4" />
                    Save Changes
                </Button>
            </div>

            <div class="grid gap-4 md:grid-cols-3">
                <Card class="md:col-span-2">
                    <CardHeader>
                        <CardTitle>Role Information</CardTitle>
                        <CardDescription>
                            Update the basic information for this role
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-2">
                            <Label for="name">Name (slug)</Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                placeholder="e.g., manager, staff, viewer"
                            />
                            <p class="text-xs text-muted-foreground">
                                Use lowercase letters, numbers, and hyphens only
                            </p>
                        </div>

                        <div class="space-y-2">
                            <Label for="display_name">Display Name</Label>
                            <Input
                                id="display_name"
                                v-model="form.display_name"
                                placeholder="e.g., Manager, Staff Member, Viewer"
                            />
                        </div>

                        <div class="space-y-2">
                            <Label for="description">Description</Label>
                            <Textarea
                                id="description"
                                v-model="form.description"
                                placeholder="Describe what this role can do..."
                                rows="3"
                                class="w-full"
                            />
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Summary</CardTitle>
                        <CardDescription>
                            Role configuration overview
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div>
                            <p class="text-sm font-medium">Role Name</p>
                            <p class="text-sm text-muted-foreground">
                                {{ form.name || 'Not set' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm font-medium">Display Name</p>
                            <p class="text-sm text-muted-foreground">
                                {{ form.display_name || 'Not set' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm font-medium">Permissions</p>
                            <p class="text-sm text-muted-foreground">
                                {{ form.permissions.length }} selected
                            </p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Permissions</CardTitle>
                    <CardDescription>
                        Select the permissions for this role
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="mb-4 flex items-center justify-between rounded-lg border p-3">
                        <span class="font-medium">All Permissions</span>
                        <div class="flex items-center gap-2">
                            <Checkbox
                                id="select-all-permissions"
                                :binary="true"
                                :modelValue="isAllSelected()"
                                @update:modelValue="toggleSelectAll"
                            />
                            <label
                                for="select-all-permissions"
                                class="cursor-pointer text-sm font-medium leading-none"
                            >
                                Select All
                            </label>
                        </div>
                    </div>
                    <div class="space-y-6">
                        <div
                            v-for="(modulePermissions, module) in permissions"
                            :key="module"
                            class="space-y-3"
                        >
                            <div
                                class="flex items-center justify-between rounded-lg bg-muted p-3"
                            >
                                <h3 class="font-semibold">
                                    {{ capitalizeModule(module) }}
                                </h3>
                                <div class="flex items-center gap-2">
                                    <span class="text-sm text-muted-foreground">
                                        {{ modulePermissions.length }} permissions
                                    </span>
                                    <Checkbox
                                        :inputId="`module-${module}`"
                                        :binary="true"
                                        :modelValue="isModuleAllSelected(modulePermissions)"
                                        @update:modelValue="toggleModuleSelectAll(modulePermissions)"
                                    />
                                    <label
                                        :for="`module-${module}`"
                                        class="cursor-pointer text-xs font-medium leading-none"
                                    >
                                        Select All
                                    </label>
                                </div>
                            </div>
                            <div class="grid gap-3 pl-4 md:grid-cols-2">
                                <div
                                    v-for="permission in modulePermissions"
                                    :key="permission.id"
                                    class="flex items-start gap-2 rounded-lg border p-3 transition-colors hover:bg-muted/50"
                                >
                                    <Checkbox
                                        :inputId="`perm-${permission.id}`"
                                        v-model="form.permissions"
                                        :value="permission.id"
                                    />
                                    <div class="flex-1">
                                        <label
                                            :for="`perm-${permission.id}`"
                                            class="cursor-pointer text-sm font-medium leading-none"
                                        >
                                            {{ permission.display_name }}
                                        </label>
                                        <p
                                            v-if="permission.description"
                                            class="mt-1 text-xs text-muted-foreground"
                                        >
                                            {{ permission.description }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <div class="flex justify-end gap-2">
                <Button variant="outline" @click="goBack">Cancel</Button>
                <Button @click="updateRole" :disabled="form.processing">
                    <Save class="mr-2 h-4 w-4" />
                    Save Changes
                </Button>
            </div>
        </div>
    </AppLayout>
</template>
