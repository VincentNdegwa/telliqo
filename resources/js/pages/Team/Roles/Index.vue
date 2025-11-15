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
import { router } from '@inertiajs/vue3';
import { formatDistanceToNow } from 'date-fns';
import { Edit, Plus, Shield, ShieldCheck, Trash2 } from 'lucide-vue-next';
import Checkbox from 'primevue/checkbox';
import Chip from 'primevue/chip';
import ConfirmDialog from 'primevue/confirmdialog';
import Dialog from 'primevue/dialog';
import Textarea from 'primevue/textarea';
import { useConfirm } from 'primevue/useconfirm';
import { useToast } from 'primevue/usetoast';
import { ref } from 'vue';

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
    permissions_count: number;
    created_at: string;
}

interface Props {
    roles: Role[];
    allPermissions: Record<string, Permission[]>;
    stats: {
        total: number;
        total_permissions: number;
    };
}

const props = defineProps<Props>();
const toast = useToast();
const confirm = useConfirm();

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
];

const showCreateModal = ref(false);
const processing = ref(false);

const createForm = ref({
    name: '',
    display_name: '',
    description: '',
    permissions: [] as number[],
});

const resetCreateForm = () => {
    createForm.value = {
        name: '',
        display_name: '',
        description: '',
        permissions: [],
    };
};

const createRole = () => {
    if (!createForm.value.name.trim()) {
        toast.add({
            severity: 'error',
            summary: 'Validation Error',
            detail: 'Name is required',
            life: 3000,
        });
        return;
    }

    if (!createForm.value.display_name.trim()) {
        toast.add({
            severity: 'error',
            summary: 'Validation Error',
            detail: 'Display name is required',
            life: 3000,
        });
        return;
    }

    if (createForm.value.permissions.length === 0) {
        toast.add({
            severity: 'error',
            summary: 'Validation Error',
            detail: 'Please select at least one permission',
            life: 3000,
        });
        return;
    }

    processing.value = true;

    router.post(team.roles.store().url, createForm.value, {
        preserveScroll: true,
        onSuccess: () => {
            processing.value = false;
            showCreateModal.value = false;
            resetCreateForm();
            toast.add({
                severity: 'success',
                summary: 'Success',
                detail: 'Role created successfully',
                life: 3000,
            });
        },
        onError: (errors) => {
            processing.value = false;
            toast.add({
                severity: 'error',
                summary: 'Error',
                detail: Object.values(errors)[0] as string,
                life: 3000,
            });
        },
    });
};

const editRole = (role: Role) => {
    router.get(team.roles.edit(role.id).url);
};

const deleteRole = (role: Role) => {
    confirm.require({
        message: `Are you sure you want to delete "${role.display_name}"? This action cannot be undone.`,
        header: 'Delete Role',
        icon: 'pi pi-exclamation-triangle',
        rejectClass: 'p-button-secondary p-button-outlined',
        acceptClass: 'p-button-danger',
        accept: () => {
            router.delete(team.roles.destroy(role.id).url, {
                preserveScroll: true,
                onSuccess: () => {
                    toast.add({
                        severity: 'success',
                        summary: 'Success',
                        detail: 'Role deleted successfully',
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
        },
    });
};

const formatDate = (date: string) => {
    return formatDistanceToNow(new Date(date), { addSuffix: true });
};

const capitalizeModule = (module: string) => {
    return module
        .split('-')
        .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
};

const getAllPermissionIds = () => {
    return Object.values(props.allPermissions)
        .flat()
        .map((p) => p.id);
};

const isAllSelected = () => {
    const allIds = getAllPermissionIds();
    return allIds.every((id) => createForm.value.permissions.includes(id));
};

const toggleSelectAll = () => {
    if (isAllSelected()) {
        createForm.value.permissions = [];
    } else {
        createForm.value.permissions = getAllPermissionIds();
    }
};

const getModulePermissionIds = (permissions: Permission[]) => {
    return permissions.map((p) => p.id);
};

const isModuleAllSelected = (permissions: Permission[]) => {
    const moduleIds = getModulePermissionIds(permissions);
    return moduleIds.every((id) => createForm.value.permissions.includes(id));
};

const toggleModuleSelectAll = (permissions: Permission[]) => {
    const moduleIds = getModulePermissionIds(permissions);
    if (isModuleAllSelected(permissions)) {
        createForm.value.permissions = createForm.value.permissions.filter(
            (id) => !moduleIds.includes(id),
        );
    } else {
        const newIds = moduleIds.filter(
            (id) => !createForm.value.permissions.includes(id),
        );
        createForm.value.permissions = [
            ...createForm.value.permissions,
            ...newIds,
        ];
    }
};
</script>

<template>
    <AppLayout title="Roles & Permissions" :breadcrumbs="breadcrumbs">
        <ConfirmDialog />

        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">
                        Roles & Permissions
                    </h1>
                    <p class="text-muted-foreground">
                        Manage roles and their permissions
                    </p>
                </div>
                <Button
                    v-permission="'team.role-create'"
                    @click="showCreateModal = true"
                >
                    <Plus class="mr-2 h-4 w-4" />
                    Create Role
                </Button>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between space-y-0 pb-2"
                    >
                        <CardTitle class="text-sm font-medium">
                            Total Roles
                        </CardTitle>
                        <Shield class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.total }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between space-y-0 pb-2"
                    >
                        <CardTitle class="text-sm font-medium">
                            Total Permissions
                        </CardTitle>
                        <ShieldCheck class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">
                            {{ stats.total_permissions }}
                        </div>
                    </CardContent>
                </Card>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Roles</CardTitle>
                    <CardDescription>
                        View and manage roles and their permissions
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div
                        v-if="roles.length === 0"
                        class="flex flex-col items-center justify-center py-12"
                    >
                        <Shield class="mb-4 h-12 w-12 text-muted-foreground" />
                        <h3 class="mb-2 text-lg font-semibold">No roles yet</h3>
                        <p class="mb-4 text-sm text-muted-foreground">
                            Create your first role to get started
                        </p>
                        <Button @click="showCreateModal = true">
                            <Plus class="mr-2 h-4 w-4" />
                            Create Role
                        </Button>
                    </div>

                    <div v-else class="space-y-4">
                        <div
                            v-for="role in roles"
                            :key="role.id"
                            class="flex flex-col gap-3 rounded-lg border p-4 md:flex-row md:items-center md:justify-between"
                        >
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <h3 class="font-semibold">
                                        {{ role.display_name }}
                                    </h3>
                                    <Chip
                                        :label="`${role.permissions_count} permissions`"
                                        class="text-xs"
                                    />
                                </div>
                                <div
                                    v-if="role.description"
                                    class="mt-1 text-sm text-muted-foreground"
                                >
                                    {{ role.description }}
                                </div>
                                <div
                                    class="mt-2 flex items-center gap-2 text-xs text-muted-foreground"
                                >
                                    <code
                                        class="rounded bg-muted px-1.5 py-0.5 font-mono"
                                    >
                                        {{ role.name }}
                                    </code>
                                    <span>•</span>
                                    <span
                                        >Created
                                        {{ formatDate(role.created_at) }}</span
                                    >
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <Button
                                    v-permission="'team.role-edit'"
                                    size="icon"
                                    variant="ghost"
                                    @click="editRole(role)"
                                    v-tooltip.top="'Edit'"
                                >
                                    <Edit class="h-4 w-4" />
                                </Button>
                                <Button
                                    v-permission="'team.role-delete'"
                                    size="icon"
                                    variant="ghost"
                                    @click="deleteRole(role)"
                                    v-tooltip.top="'Delete'"
                                >
                                    <Trash2 class="h-4 w-4 text-destructive" />
                                </Button>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Dialog
                v-model:visible="showCreateModal"
                header="Create Role"
                :style="{ width: '50rem' }"
                :modal="true"
            >
                <template #header>
                    <div>
                        <h3 class="text-lg font-semibold">Create Role</h3>
                        <p class="text-sm text-muted-foreground">
                            Create a new role and assign permissions
                        </p>
                    </div>
                </template>

                <div class="space-y-4 py-4">
                    <div class="space-y-2">
                        <Label for="name">Name (slug)</Label>
                        <Input
                            id="name"
                            v-model="createForm.name"
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
                            v-model="createForm.display_name"
                            placeholder="e.g., Manager, Staff Member, Viewer"
                        />
                    </div>

                    <div class="space-y-2">
                        <Label for="description">Description</Label>
                        <Textarea
                            id="description"
                            v-model="createForm.description"
                            placeholder="Describe what this role can do..."
                            rows="2"
                            class="w-full"
                        />
                    </div>

                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <Label>Permissions</Label>
                            <div class="flex items-center gap-2">
                                <Checkbox
                                    id="select-all-permissions"
                                    :binary="true"
                                    :modelValue="isAllSelected()"
                                    @update:modelValue="toggleSelectAll"
                                />
                                <label
                                    for="select-all-permissions"
                                    class="cursor-pointer text-sm leading-none font-medium"
                                >
                                    Select All
                                </label>
                            </div>
                        </div>
                        <div
                            class="max-h-96 space-y-4 overflow-y-auto rounded-lg border p-4"
                        >
                            <div
                                v-for="(permissions, module) in allPermissions"
                                :key="module"
                                class="space-y-2"
                            >
                                <div
                                    class="flex items-center justify-between rounded-lg bg-muted p-2"
                                >
                                    <h4 class="font-semibold">
                                        {{ capitalizeModule(module) }}
                                    </h4>
                                    <div class="flex items-center gap-2">
                                        <Checkbox
                                            :inputId="`module-${module}`"
                                            :binary="true"
                                            :modelValue="
                                                isModuleAllSelected(permissions)
                                            "
                                            @update:modelValue="
                                                toggleModuleSelectAll(
                                                    permissions,
                                                )
                                            "
                                        />
                                        <label
                                            :for="`module-${module}`"
                                            class="cursor-pointer text-xs leading-none font-medium"
                                        >
                                            Select All
                                        </label>
                                    </div>
                                </div>
                                <div class="space-y-2 pl-4">
                                    <div
                                        v-for="permission in permissions"
                                        :key="permission.id"
                                        class="flex items-start gap-2"
                                    >
                                        <Checkbox
                                            :inputId="`perm-${permission.id}`"
                                            v-model="createForm.permissions"
                                            :value="permission.id"
                                        />
                                        <div class="flex-1">
                                            <label
                                                :for="`perm-${permission.id}`"
                                                class="cursor-pointer text-sm leading-none font-medium"
                                            >
                                                {{ permission.display_name }}
                                            </label>
                                            <p
                                                v-if="permission.description"
                                                class="text-xs text-muted-foreground"
                                            >
                                                {{ permission.description }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <template #footer>
                    <div class="flex justify-end gap-2">
                        <Button
                            variant="outline"
                            @click="showCreateModal = false"
                            :disabled="processing"
                        >
                            Cancel
                        </Button>
                        <Button @click="createRole" :disabled="processing">
                            Create Role
                        </Button>
                    </div>
                </template>
            </Dialog>
        </div>
    </AppLayout>
</template>
