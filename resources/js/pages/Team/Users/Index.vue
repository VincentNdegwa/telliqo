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
import {
    Mail,
    Shield,
    Trash2,
    UserCheck,
    UserMinus,
    UserPlus,
    Users,
} from 'lucide-vue-next';
import Chip from 'primevue/chip';
import ConfirmDialog from 'primevue/confirmdialog';
import Dialog from 'primevue/dialog';
import Dropdown from 'primevue/dropdown';
import Tag from 'primevue/tag';
import { useConfirm } from 'primevue/useconfirm';
import { useToast } from 'primevue/usetoast';
import { ref } from 'vue';

interface Role {
    id: number;
    name: string;
    display_name: string;
}

interface LaratrustRole {
    id: number;
    name: string;
    display_name: string;
}

interface TeamMember {
    id: number;
    name: string;
    email: string;
    pivot_role: string;
    is_active: boolean;
    invited_at: string | null;
    joined_at: string | null;
    laratrust_roles: LaratrustRole[];
}

interface Props {
    teamMembers: TeamMember[];
    roles: Role[];
    stats: {
        total: number;
        active: number;
        inactive: number;
        owners: number;
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
        title: 'Users',
        href: team.users.index().url,
    },
];

const showInviteModal = ref(false);
const showEditModal = ref(false);
const selectedMember = ref<TeamMember | null>(null);
const processing = ref(false);

const inviteForm = ref({
    name: '',
    email: '',
    role_id: null as number | null,
    password: '',
});

const editForm = ref({
    role_id: null as number | null,
});

const resetInviteForm = () => {
    inviteForm.value = {
        name: '',
        email: '',
        role_id: null,
        password: '',
    };
};

const resetEditForm = () => {
    editForm.value = {
        role_id: null,
    };
};

const inviteMember = () => {
    if (!inviteForm.value.name.trim()) {
        toast.add({
            severity: 'error',
            summary: 'Validation Error',
            detail: 'Name is required',
            life: 3000,
        });
        return;
    }

    if (!inviteForm.value.email.trim()) {
        toast.add({
            severity: 'error',
            summary: 'Validation Error',
            detail: 'Email is required',
            life: 3000,
        });
        return;
    }

    if (!inviteForm.value.role_id) {
        toast.add({
            severity: 'error',
            summary: 'Validation Error',
            detail: 'Please select a role',
            life: 3000,
        });
        return;
    }

    if (!inviteForm.value.password) {
        toast.add({
            severity: 'error',
            summary: 'Validation Error',
            detail: 'Password is required',
            life: 3000,
        });
        return;
    }

    processing.value = true;

    router.post(team.users.store().url, inviteForm.value, {
        preserveScroll: true,
        onSuccess: () => {
            processing.value = false;
            showInviteModal.value = false;
            resetInviteForm();
            toast.add({
                severity: 'success',
                summary: 'Success',
                detail: 'Team member invited successfully',
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

const openEditModal = (member: TeamMember) => {
    selectedMember.value = member;
    editForm.value.role_id = member.laratrust_roles[0]?.id || null;
    showEditModal.value = true;
};

const updateMemberRole = () => {
    if (!selectedMember.value || !editForm.value.role_id) {
        toast.add({
            severity: 'error',
            summary: 'Validation Error',
            detail: 'Please select a role',
            life: 3000,
        });
        return;
    }

    processing.value = true;

    router.put(team.users.update(selectedMember.value.id).url, editForm.value, {
        preserveScroll: true,
        onSuccess: () => {
            processing.value = false;
            showEditModal.value = false;
            resetEditForm();
            selectedMember.value = null;
            toast.add({
                severity: 'success',
                summary: 'Success',
                detail: 'Team member role updated successfully',
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

const removeMember = (member: TeamMember) => {
    confirm.require({
        message: `Are you sure you want to remove "${member.name}" from your team? This action cannot be undone.`,
        header: 'Remove Team Member',
        icon: 'pi pi-exclamation-triangle',
        rejectClass: 'p-button-secondary p-button-outlined',
        acceptClass: 'p-button-danger',
        accept: () => {
            router.delete(team.users.destroy(member.id).url, {
                preserveScroll: true,
                onSuccess: () => {
                    toast.add({
                        severity: 'success',
                        summary: 'Success',
                        detail: 'Team member removed successfully',
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

const formatDate = (date: string | null) => {
    if (!date) return 'Never';
    return formatDistanceToNow(new Date(date), { addSuffix: true });
};

const getStatusSeverity = (member: TeamMember) => {
    if (!member.is_active) return 'danger';
    if (member.pivot_role === 'owner') return 'success';
    return 'info';
};

const getStatusLabel = (member: TeamMember) => {
    if (!member.is_active) return 'Inactive';
    if (member.pivot_role === 'owner') return 'Owner';
    return 'Active';
};
</script>

<template>
    <AppLayout title="Team Users" :breadcrumbs="breadcrumbs">
        <ConfirmDialog />

        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">
                        Team Members
                    </h1>
                    <p class="text-muted-foreground">
                        Manage your team members and their roles
                    </p>
                </div>
                <div class="flex gap-2">
                    <Button @click="showInviteModal = true">
                        <UserPlus class="mr-2 h-4 w-4" />
                        Invite Member
                    </Button>
                    <Button
                        variant="outline"
                        @click="router.get(team.roles.index().url)"
                    >
                        <Shield class="mr-2 h-4 w-4" />
                        Roles
                    </Button>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between space-y-0 pb-2"
                    >
                        <CardTitle class="text-sm font-medium">
                            Total Members
                        </CardTitle>
                        <Users class="h-4 w-4 text-muted-foreground" />
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
                            Active
                        </CardTitle>
                        <UserCheck class="h-4 w-4 text-green-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-green-600">
                            {{ stats.active }}
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between space-y-0 pb-2"
                    >
                        <CardTitle class="text-sm font-medium">
                            Inactive
                        </CardTitle>
                        <UserMinus class="h-4 w-4 text-red-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-red-600">
                            {{ stats.inactive }}
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between space-y-0 pb-2"
                    >
                        <CardTitle class="text-sm font-medium">
                            Owners
                        </CardTitle>
                        <Shield class="h-4 w-4 text-blue-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-blue-600">
                            {{ stats.owners }}
                        </div>
                    </CardContent>
                </Card>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Team Members</CardTitle>
                    <CardDescription>
                        View and manage your team members and their permissions
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div
                        v-if="teamMembers.length === 0"
                        class="flex flex-col items-center justify-center py-12"
                    >
                        <Users class="mb-4 h-12 w-12 text-muted-foreground" />
                        <h3 class="mb-2 text-lg font-semibold">
                            No team members yet
                        </h3>
                        <p class="mb-4 text-sm text-muted-foreground">
                            Invite your first team member to get started
                        </p>
                        <Button @click="showInviteModal = true">
                            <UserPlus class="mr-2 h-4 w-4" />
                            Invite Member
                        </Button>
                    </div>

                    <div v-else class="space-y-4">
                        <div
                            v-for="member in teamMembers"
                            :key="member.id"
                            class="flex flex-col gap-3 rounded-lg border p-4 md:flex-row md:items-center md:justify-between"
                        >
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <h3 class="font-semibold">
                                        {{ member.name }}
                                    </h3>
                                    <Tag
                                        :severity="getStatusSeverity(member)"
                                        :value="getStatusLabel(member)"
                                    />
                                </div>
                                <div
                                    class="mt-2 flex flex-col gap-2 text-sm text-muted-foreground md:flex-row md:items-center md:gap-4"
                                >
                                    <div class="flex items-center gap-1">
                                        <Mail class="h-3 w-3" />
                                        <span>{{ member.email }}</span>
                                    </div>
                                    <div v-if="member.joined_at">
                                        Joined
                                        {{ formatDate(member.joined_at) }}
                                    </div>
                                    <div v-else-if="member.invited_at">
                                        Invited
                                        {{ formatDate(member.invited_at) }}
                                    </div>
                                </div>
                                <div class="mt-2 flex flex-wrap gap-1">
                                    <Chip
                                        v-for="role in member.laratrust_roles"
                                        :key="role.id"
                                        :label="role.display_name"
                                        class="text-xs"
                                    />
                                </div>
                            </div>
                            <div
                                v-if="member.pivot_role !== 'owner'"
                                class="flex gap-2"
                            >
                                <Button
                                    size="icon"
                                    variant="ghost"
                                    @click="openEditModal(member)"
                                    v-tooltip.top="'Edit Role'"
                                >
                                    <Shield class="h-4 w-4" />
                                </Button>
                                <Button
                                    size="icon"
                                    variant="ghost"
                                    @click="removeMember(member)"
                                    v-tooltip.top="'Remove'"
                                >
                                    <Trash2 class="h-4 w-4 text-destructive" />
                                </Button>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Dialog
                v-model:visible="showInviteModal"
                header="Invite Team Member"
                :style="{ width: '35rem' }"
                :modal="true"
            >
                <template #header>
                    <div>
                        <h3 class="text-lg font-semibold">
                            Invite Team Member
                        </h3>
                        <p class="text-sm text-muted-foreground">
                            Invite a new member to join your team
                        </p>
                    </div>
                </template>

                <div class="space-y-4 py-4">
                    <div class="space-y-2">
                        <Label for="name">Name</Label>
                        <Input
                            id="name"
                            v-model="inviteForm.name"
                            placeholder="John Doe"
                        />
                    </div>

                    <div class="space-y-2">
                        <Label for="email">Email</Label>
                        <Input
                            id="email"
                            v-model="inviteForm.email"
                            type="email"
                            placeholder="john@example.com"
                        />
                    </div>

                    <div class="space-y-2">
                        <Label for="role">Role</Label>
                        <Dropdown
                            v-model="inviteForm.role_id"
                            :options="roles"
                            optionLabel="display_name"
                            optionValue="id"
                            placeholder="Select a role"
                            class="w-full"
                        />
                    </div>

                    <div class="space-y-2">
                        <Label for="password">Password</Label>
                        <Input
                            id="password"
                            v-model="inviteForm.password"
                            type="password"
                            placeholder="Enter password"
                        />
                    </div>
                </div>

                <template #footer>
                    <div class="flex justify-end gap-2">
                        <Button
                            variant="outline"
                            @click="showInviteModal = false"
                            :disabled="processing"
                        >
                            Cancel
                        </Button>
                        <Button @click="inviteMember" :disabled="processing">
                            Invite Member
                        </Button>
                    </div>
                </template>
            </Dialog>

            <Dialog
                v-model:visible="showEditModal"
                header="Edit Member Role"
                :style="{ width: '35rem' }"
                :modal="true"
            >
                <template #header>
                    <div>
                        <h3 class="text-lg font-semibold">Edit Member Role</h3>
                        <p class="text-sm text-muted-foreground">
                            Update the role for {{ selectedMember?.name }}
                        </p>
                    </div>
                </template>

                <div class="space-y-4 py-4">
                    <div class="space-y-2">
                        <Label for="edit-role">Role</Label>
                        <Dropdown
                            v-model="editForm.role_id"
                            :options="roles"
                            optionLabel="display_name"
                            optionValue="id"
                            placeholder="Select a role"
                            class="w-full"
                        />
                    </div>
                </div>

                <template #footer>
                    <div class="flex justify-end gap-2">
                        <Button
                            variant="outline"
                            @click="showEditModal = false"
                            :disabled="processing"
                        >
                            Cancel
                        </Button>
                        <Button
                            @click="updateMemberRole"
                            :disabled="processing"
                        >
                            Update Role
                        </Button>
                    </div>
                </template>
            </Dialog>
        </div>
    </AppLayout>
</template>
