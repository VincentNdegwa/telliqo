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
import { hasFeature } from '@/plugins/feature';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { router } from '@inertiajs/vue3';
import { formatDistanceToNow } from 'date-fns';
import {
    CheckCircle2,
    Clock,
    Copy,
    Key,
    Plus,
    Trash2,
    XCircle,
} from 'lucide-vue-next';
import Checkbox from 'primevue/checkbox';
import Chip from 'primevue/chip';
import ConfirmDialog from 'primevue/confirmdialog';
import Dialog from 'primevue/dialog';
import Tag from 'primevue/tag';
import { useConfirm } from 'primevue/useconfirm';
import { useToast } from 'primevue/usetoast';
import { nextTick, ref, watch } from 'vue';

interface ApiKey {
    id: number;
    business_id: number;
    name: string;
    key_preview: string;
    permissions: string[];
    is_active: boolean;
    last_used_at: string | null;
    last_used_ip: string | null;
    expires_at: string | null;
    created_at: string;
    updated_at: string;
}

interface Props {
    apiKeys: ApiKey[];
    stats: {
        total: number;
        active: number;
        revoked: number;
        expired: number;
    };
    newApiKey?: string;
}

const props = defineProps<Props>();
const toast = useToast();
const confirm = useConfirm();

const currentNewApiKey = ref<string | null>(null);
const showCopyDialog = ref(false);

watch(
    () => props.newApiKey,
    (value) => {
        if (value) {
            currentNewApiKey.value = value;
            nextTick(() => {
                showCopyDialog.value = true;
            });
        }
    },
    { immediate: true },
);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Settings',
        href: '/business/settings',
    },
    {
        title: 'API Keys',
        href: '/settings/api-keys',
    },
];

// Create API Key Modal
const showCreateModal = ref(false);
const createForm = ref({
    name: '',
    permissions: [] as string[],
    expires_in_days: '' as string | number,
});

const availablePermissions = [
    { value: 'review-requests.create', label: 'Create Review Requests' },
    { value: 'review-requests.read', label: 'Read Review Requests' },
    { value: 'review-requests.update', label: 'Update Review Requests' },
    { value: 'review-requests.delete', label: 'Delete Review Requests' },
    { value: 'customers.create', label: 'Create Customers' },
    { value: 'customers.read', label: 'Read Customers' },
];

const processing = ref(false);

const createApiKey = () => {
    if (!createForm.value.name.trim()) {
        toast.add({
            severity: 'error',
            summary: 'Validation Error',
            detail: 'API key name is required',
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

    router.post('/settings/api-keys', createForm.value, {
        preserveScroll: true,
        onSuccess: () => {
            processing.value = false;
            showCreateModal.value = false;
            createForm.value = {
                name: '',
                permissions: [],
                expires_in_days: '',
            };
            toast.add({
                severity: 'success',
                summary: 'Success',
                detail: 'API key created successfully. Make sure to copy it now!',
                life: 5000,
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

const copyToClipboard = async (text: string) => {
    try {
        await navigator.clipboard.writeText(text);
        toast.add({
            severity: 'success',
            summary: 'Copied!',
            detail: 'API key copied to clipboard',
            life: 3000,
        });
    } catch (err) {
        console.log(err);

        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'Failed to copy to clipboard',
            life: 3000,
        });
    }
};

const revokeApiKey = (apiKey: ApiKey) => {
    confirm.require({
        message: `Are you sure you want to revoke "${apiKey.name}"? This will immediately stop all requests using this key.`,
        header: 'Revoke API Key',
        icon: 'pi pi-exclamation-triangle',
        rejectClass: 'p-button-secondary p-button-outlined',
        acceptClass: 'p-button-danger',
        accept: () => {
            router.post(
                `/settings/api-keys/${apiKey.id}/revoke`,
                {},
                {
                    preserveScroll: true,
                    onSuccess: () => {
                        toast.add({
                            severity: 'success',
                            summary: 'Success',
                            detail: 'API key revoked successfully',
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
                },
            );
        },
    });
};

const deleteApiKey = (apiKey: ApiKey) => {
    confirm.require({
        message: `Are you sure you want to delete "${apiKey.name}"? This action cannot be undone.`,
        header: 'Delete API Key',
        icon: 'pi pi-exclamation-triangle',
        rejectClass: 'p-button-secondary p-button-outlined',
        acceptClass: 'p-button-danger',
        accept: () => {
            router.delete(`/settings/api-keys/${apiKey.id}`, {
                preserveScroll: true,
                onSuccess: () => {
                    toast.add({
                        severity: 'success',
                        summary: 'Success',
                        detail: 'API key deleted successfully',
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

const getStatusSeverity = (apiKey: ApiKey) => {
    if (!apiKey.is_active) {
        return 'danger';
    }
    if (apiKey.expires_at && new Date(apiKey.expires_at) < new Date()) {
        return 'warning';
    }
    return 'success';
};

const getStatusLabel = (apiKey: ApiKey) => {
    if (!apiKey.is_active) {
        return 'Revoked';
    }
    if (apiKey.expires_at && new Date(apiKey.expires_at) < new Date()) {
        return 'Expired';
    }
    return 'Active';
};

const hasApiFeature = hasFeature('api_intergration');
</script>

<template>
    <AppLayout title="API Keys" :breadcrumbs="breadcrumbs">
        <ConfirmDialog />

        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <!-- Header -->
            <!-- Header -->
            <div
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">API Keys</h1>
                    <p class="text-muted-foreground">
                        Manage API keys for programmatic access to your business
                    </p>
                </div>
                <div v-tooltip="!hasApiFeature? 'Upgrade your plan to unlock API integration' : ''" >
                    <Button
                        :disabled="!hasApiFeature"
                        v-permission="'api-integration.create-key'"
                        @click="showCreateModal = true"
                    >
                    <Plus class="mr-2 h-4 w-4" />
                    Create API Key
                </Button>
                </div>
            </div>

            <!-- Stats Cards -->
            <div
                v-permission="'api-integration.stats'"
                class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4"
            >
                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between space-y-0 pb-2"
                    >
                        <CardTitle class="text-sm font-medium"
                            >Total Keys</CardTitle
                        >
                        <Key class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.total }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between space-y-0 pb-2"
                    >
                        <CardTitle class="text-sm font-medium"
                            >Active</CardTitle
                        >
                        <CheckCircle2 class="h-4 w-4 text-green-600" />
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
                        <CardTitle class="text-sm font-medium"
                            >Revoked</CardTitle
                        >
                        <XCircle class="h-4 w-4 text-red-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-red-600">
                            {{ stats.revoked }}
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between space-y-0 pb-2"
                    >
                        <CardTitle class="text-sm font-medium"
                            >Expired</CardTitle
                        >
                        <Clock class="h-4 w-4 text-yellow-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-yellow-600">
                            {{ stats.expired }}
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- API Keys List -->
            <Card>
                <CardHeader>
                    <CardTitle>Your API Keys</CardTitle>
                    <CardDescription>
                        Use these keys to integrate with your POS, appointment
                        system, or other third-party applications.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div
                        v-if="apiKeys.length === 0"
                        class="flex flex-col items-center justify-center py-12"
                    >
                        <Key class="mb-4 h-12 w-12 text-muted-foreground" />
                        <h3 class="mb-2 text-lg font-semibold">
                            No API keys yet
                        </h3>
                        <p class="mb-4 text-sm text-muted-foreground">
                            Create your first API key to get started
                        </p>
                        <div v-tooltip="!hasApiFeature? 'Upgrade your plan to unlock API integration' : ''" >
                            <Button :disabled="!hasApiFeature" @click="showCreateModal = true">
                                <Plus class="mr-2 h-4 w-4" />
                                Create API Key
                            </Button>
                        </div>
                    </div>

                    <div v-else class="space-y-4">
                        <div
                            v-for="apiKey in apiKeys"
                            :key="apiKey.id"
                            class="flex flex-col gap-3 rounded-lg border p-4 md:flex-row md:items-center md:justify-between"
                            :class="{
                                'border-green-500 bg-green-50 dark:bg-green-950/20':
                                    apiKey.id === apiKeys[0].id &&
                                    !!currentNewApiKey,
                            }"
                        >
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <h3 class="font-semibold">
                                        {{ apiKey.name }}
                                    </h3>
                                    <Tag
                                        :severity="getStatusSeverity(apiKey)"
                                        :value="getStatusLabel(apiKey)"
                                    />
                                    <Tag
                                        v-if="
                                            apiKey.id === apiKeys[0].id &&
                                            !!currentNewApiKey
                                        "
                                        severity="success"
                                        value="New"
                                    />
                                </div>
                                <div
                                    class="mt-2 flex flex-col gap-2 text-sm text-muted-foreground md:flex-row md:items-center md:gap-4"
                                >
                                    <div class="flex items-center gap-1">
                                        <Key class="h-3 w-3" />
                                        <div
                                            v-if="
                                                apiKey.id === apiKeys[0].id &&
                                                !!currentNewApiKey
                                            "
                                            class="flex items-center gap-2"
                                        >
                                            <code
                                                class="rounded bg-muted px-1.5 py-0.5 font-mono text-xs break-all"
                                            >
                                                {{ currentNewApiKey }}
                                            </code>
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                @click="
                                                    copyToClipboard(
                                                        currentNewApiKey!,
                                                    )
                                                "
                                            >
                                                <Copy class="h-4 w-4" />
                                            </Button>
                                        </div>
                                        <code
                                            v-else
                                            class="rounded bg-muted px-1.5 py-0.5 font-mono text-xs"
                                        >
                                            {{ apiKey.key_preview }}
                                        </code>
                                    </div>
                                    <div v-if="apiKey.last_used_at">
                                        Last used
                                        {{ formatDate(apiKey.last_used_at) }}
                                        <span
                                            v-if="apiKey.last_used_ip"
                                            class="text-xs"
                                            >({{ apiKey.last_used_ip }})</span
                                        >
                                    </div>
                                    <div v-else>Never used</div>
                                    <div v-if="apiKey.expires_at">
                                        Expires
                                        {{ formatDate(apiKey.expires_at) }}
                                    </div>
                                </div>
                                <div
                                    v-if="
                                        apiKey.id === apiKeys[0].id &&
                                        !!currentNewApiKey
                                    "
                                    class="mt-2"
                                >
                                    <p
                                        class="text-sm font-medium text-yellow-600 dark:text-yellow-500"
                                    >
                                        ⚠️ Copy this key and save it!.
                                    </p>
                                </div>
                                <div class="mt-2 flex flex-wrap gap-1">
                                    <Chip
                                        v-for="permission in apiKey.permissions"
                                        :key="permission"
                                        :label="permission"
                                        class="text-xs"
                                    />
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <Button
                                    v-permission="'api-integration.revoke-key'"
                                    v-if="apiKey.is_active"
                                    size="icon"
                                    variant="ghost"
                                    @click="revokeApiKey(apiKey)"
                                    v-tooltip.top="'Revoke'"
                                >
                                    <XCircle class="h-4 w-4" />
                                </Button>
                                <Button
                                    v-permission="'api-integration.delete-key'"
                                    size="icon"
                                    variant="ghost"
                                    @click="deleteApiKey(apiKey)"
                                    v-tooltip.top="'Delete'"
                                >
                                    <Trash2 class="h-4 w-4 text-destructive" />
                                </Button>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Create API Key Modal -->
            <Dialog
                v-model:visible="showCreateModal"
                header="Create API Key"
                :style="{ width: '50rem' }"
                :modal="true"
            >
                <template #header>
                    <div>
                        <h3 class="text-lg font-semibold">Create API Key</h3>
                        <p class="text-sm text-muted-foreground">
                            Create a new API key for third-party integrations.
                            You'll only see the key once, so make sure to copy
                            it.
                        </p>
                    </div>
                </template>

                <div class="space-y-4 py-4">
                    <div class="space-y-2">
                        <Label for="name">Name</Label>
                        <Input
                            id="name"
                            v-model="createForm.name"
                            placeholder="e.g., POS System, Appointment Scheduler"
                        />
                    </div>

                    <div class="space-y-2">
                        <Label>Permissions</Label>
                        <div class="space-y-3 rounded-lg border p-4">
                            <div
                                v-for="permission in availablePermissions"
                                :key="permission.value"
                                class="flex items-center gap-2"
                            >
                                <Checkbox
                                    :inputId="permission.value"
                                    v-model="createForm.permissions"
                                    :value="permission.value"
                                />
                                <label
                                    :for="permission.value"
                                    class="cursor-pointer text-sm leading-none font-medium"
                                >
                                    {{ permission.label }}
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label for="expires_in_days">Expires In (days)</Label>
                        <Input
                            id="expires_in_days"
                            v-model="createForm.expires_in_days"
                            type="number"
                            placeholder="Leave empty for no expiration"
                            min="1"
                        />
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
                        <Button @click="createApiKey" :disabled="processing">
                            Create API Key
                        </Button>
                    </div>
                </template>
            </Dialog>

            <Dialog
                v-model:visible="showCopyDialog"
                header="API Key Created"
                :style="{ width: '35rem' }"
                :modal="true"
                :closable="false"
            >
                <template #header>
                    <div>
                        <h3 class="text-lg font-semibold">
                            API Key Created Successfully!
                        </h3>
                        <p class="text-sm text-muted-foreground">
                            Copy your API key now. You won't be able to see it
                            again!
                        </p>
                    </div>
                </template>

                <div class="space-y-4 py-4">
                    <div
                        class="rounded-lg border border-yellow-500 bg-yellow-50 p-4 dark:bg-yellow-950/20"
                    >
                        <p
                            class="mb-3 text-sm font-medium text-yellow-800 dark:text-yellow-500"
                        >
                            ⚠️ Important: This is the only time you'll see this
                            key!
                        </p>
                        <div class="mb-2 flex items-center justify-between">
                            <Label class="text-xs font-medium"
                                >Your API Key</Label
                            >
                            <Button
                                variant="ghost"
                                size="sm"
                                @click="copyToClipboard(currentNewApiKey!)"
                            >
                                <Copy class="mr-2 h-4 w-4" />
                                Copy
                            </Button>
                        </div>
                        <code
                            class="block rounded bg-muted p-2 font-mono text-sm break-all"
                            >{{ currentNewApiKey }}</code
                        >
                    </div>
                    <p class="text-sm text-muted-foreground">
                        Store this key securely. It provides access to your
                        account and cannot be retrieved later.
                    </p>
                </div>

                <template #footer>
                    <div class="flex justify-end">
                        <Button @click="showCopyDialog = false">
                            I've Copied the Key
                        </Button>
                    </div>
                </template>
            </Dialog>
        </div>
    </AppLayout>
</template>
