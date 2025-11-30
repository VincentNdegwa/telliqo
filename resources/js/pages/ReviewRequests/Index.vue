<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import { hasFeature } from '@/plugins/feature';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { router } from '@inertiajs/vue3';
import { format } from 'date-fns';
import {
    CheckCircle2,
    Clock,
    Eye,
    Filter,
    Mail,
    Search,
    Send,
    Trash2,
    XCircle,
} from 'lucide-vue-next';
import Column from 'primevue/column';
import ConfirmDialog from 'primevue/confirmdialog';
import DataTable from 'primevue/datatable';
import Tag from 'primevue/tag';
import { useConfirm } from 'primevue/useconfirm';
import { ref } from 'vue';

interface ReviewRequest {
    id: number;
    customer: {
        id: number;
        name: string;
        email: string;
    };
    subject: string;
    status: string;
    sent_at: string | null;
    opened_at: string | null;
    completed_at: string | null;
    expires_at: string;
    reminder_sent_count: number;
}

interface Props {
    reviewRequests: {
        data: ReviewRequest[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    filters: {
        search: string | null;
        status: string | null;
    };
    stats: {
        total: number;
        pending: number;
        opened: number;
        completed: number;
        expired: number;
    };
}

const props = defineProps<Props>();
const confirm = useConfirm();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Review Requests',
        href: '/review-requests',
    },
];

const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || 'all');

const applyFilters = () => {
    router.get(
        '/review-requests',
        {
            search: search.value || undefined,
            status:
                statusFilter.value !== 'all' ? statusFilter.value : undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
};

const onPage = (event: any) => {
    router.get(
        `/review-requests?page=${event.page + 1}`,
        {
            search: search.value || undefined,
            status:
                statusFilter.value !== 'all' ? statusFilter.value : undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
};

const deleteRequest = (request: ReviewRequest) => {
    confirm.require({
        message: `Are you sure you want to delete this review request for ${request.customer.name}?`,
        header: 'Confirm Delete',
        icon: 'pi pi-exclamation-triangle',
        rejectClass: 'p-button-secondary p-button-outlined',
        acceptClass: 'p-button-danger',
        accept: () => {
            router.delete(`/review-requests/${request.id}`);
        },
    });
};

const sendReminder = (request: ReviewRequest) => {
    confirm.require({
        message: `Send a reminder to ${request.customer.name}?`,
        header: 'Send Reminder',
        icon: 'pi pi-send',
        rejectClass: 'p-button-secondary p-button-outlined',
        acceptClass: 'p-button-primary',
        accept: () => {
            router.post(`/review-requests/${request.id}/remind`);
        },
    });
};

const formatDate = (date: string | null) => {
    if (!date) return '—';
    return format(new Date(date), 'MMM d, yyyy');
};

const getStatusSeverity = (status: string) => {
    const map: Record<string, any> = {
        pending: 'info',
        opened: 'warning',
        completed: 'success',
        expired: 'danger',
    };
    return map[status] || 'info';
};

const hasRequestFeature = hasFeature('review_request_emails');
</script>

<template>
    <AppLayout title="Review Requests" :breadcrumbs="breadcrumbs">
        <ConfirmDialog />

        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">
                        Review Requests
                    </h1>
                    <p class="text-muted-foreground">
                        Manage and track all review requests sent to customers
                    </p>
                </div>
                <div
                    v-tooltip="
                        !hasRequestFeature
                            ? 'Upgrade your plan to unlock review requests'
                            : ''
                    "
                >
                    <Button
                        :disabled="!hasRequestFeature"
                        v-permission="'review-request.create'"
                        @click="router.visit('/review-requests/create')"
                    >
                        <Send class="mr-2 h-4 w-4" />
                        Send Review Request
                    </Button>
                </div>
            </div>

            <!-- Stats Cards -->
            <div
                v-permission="'review-request.stats'"
                class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5"
            >
                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between space-y-0 pb-2"
                    >
                        <CardTitle class="text-sm font-medium"
                            >Total Requests</CardTitle
                        >
                        <Mail class="h-4 w-4 text-muted-foreground" />
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
                            >Pending</CardTitle
                        >
                        <Clock class="h-4 w-4 text-blue-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-blue-600">
                            {{ stats.pending }}
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between space-y-0 pb-2"
                    >
                        <CardTitle class="text-sm font-medium"
                            >Opened</CardTitle
                        >
                        <Mail class="h-4 w-4 text-yellow-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-yellow-600">
                            {{ stats.opened }}
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between space-y-0 pb-2"
                    >
                        <CardTitle class="text-sm font-medium"
                            >Completed</CardTitle
                        >
                        <CheckCircle2 class="h-4 w-4 text-green-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-green-600">
                            {{ stats.completed }}
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
                        <XCircle class="h-4 w-4 text-red-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-red-600">
                            {{ stats.expired }}
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Filters and Table -->
            <Card>
                <CardHeader>
                    <div
                        class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div class="flex flex-1 items-center gap-2">
                            <Search class="h-4 w-4 text-muted-foreground" />
                            <Input
                                v-model="search"
                                placeholder="Search by customer name or email..."
                                class="max-w-sm"
                                @keyup.enter="applyFilters"
                            />
                        </div>
                        <div class="flex items-center gap-2">
                            <Button
                                variant="outline"
                                @click="
                                    () => {
                                        const statuses = [
                                            'all',
                                            'pending',
                                            'opened',
                                            'completed',
                                            'expired',
                                        ];
                                        const currentIndex =
                                            statuses.indexOf(statusFilter);
                                        statusFilter =
                                            statuses[
                                                (currentIndex + 1) %
                                                    statuses.length
                                            ];
                                        applyFilters();
                                    }
                                "
                            >
                                <Filter class="mr-2 h-4 w-4" />
                                {{
                                    statusFilter === 'all'
                                        ? 'All Status'
                                        : statusFilter.charAt(0).toUpperCase() +
                                          statusFilter.slice(1)
                                }}
                            </Button>
                            <Button @click="applyFilters">
                                <Search class="mr-2 h-4 w-4" />
                                Search
                            </Button>
                        </div>
                    </div>
                </CardHeader>
                <CardContent>
                    <DataTable
                        :value="reviewRequests.data"
                        :lazy="true"
                        :paginator="true"
                        :rows="reviewRequests.per_page"
                        :totalRecords="reviewRequests.total"
                        :rowsPerPageOptions="[10, 25, 50]"
                        @page="onPage"
                        :loading="false"
                        class="p-datatable-sm"
                    >
                        <Column field="customer.name" header="Customer">
                            <template #body="{ data }">
                                <div class="font-medium">
                                    {{ data.customer.name }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    {{ data.customer.email }}
                                </div>
                            </template>
                        </Column>

                        <Column field="subject" header="Subject">
                            <template #body="{ data }">
                                <div class="max-w-xs truncate">
                                    {{ data.subject }}
                                </div>
                            </template>
                        </Column>

                        <Column
                            field="status"
                            header="Status"
                            class="w-[120px]"
                        >
                            <template #body="{ data }">
                                <Tag
                                    :severity="getStatusSeverity(data.status)"
                                    :value="data.status"
                                    class="capitalize"
                                />
                            </template>
                        </Column>

                        <Column
                            header="Sent"
                            class="hidden w-[120px] md:table-cell"
                        >
                            <template #body="{ data }">
                                {{ formatDate(data.sent_at) }}
                            </template>
                        </Column>

                        <Column
                            header="Opened"
                            class="hidden w-[120px] lg:table-cell"
                        >
                            <template #body="{ data }">
                                {{ formatDate(data.opened_at) }}
                            </template>
                        </Column>

                        <Column
                            header="Reminders"
                            class="hidden w-[100px] lg:table-cell"
                        >
                            <template #body="{ data }">
                                <span class="text-sm">{{
                                    data.reminder_sent_count
                                }}</span>
                            </template>
                        </Column>

                        <Column header="Actions" class="w-[150px]">
                            <template #body="{ data }">
                                <div class="flex items-center gap-1">
                                    <Button
                                        v-permission="'review-request.view'"
                                        size="icon"
                                        variant="ghost"
                                        @click="
                                            router.visit(
                                                `/review-requests/${data.id}`,
                                            )
                                        "
                                        v-tooltip.top="'View Details'"
                                    >
                                        <Eye class="h-4 w-4" />
                                    </Button>
                                    <Button
                                        v-permission="'review-request.send'"
                                        v-if="
                                            data.status === 'pending' ||
                                            data.status === 'opened'
                                        "
                                        size="icon"
                                        variant="ghost"
                                        @click="sendReminder(data)"
                                        v-tooltip.top="'Send Reminder'"
                                    >
                                        <Mail class="h-4 w-4" />
                                    </Button>
                                    <Button
                                        v-permission="'review-request.delete'"
                                        size="icon"
                                        variant="ghost"
                                        @click="deleteRequest(data)"
                                        v-tooltip.top="'Delete'"
                                    >
                                        <Trash2
                                            class="h-4 w-4 text-destructive"
                                        />
                                    </Button>
                                </div>
                            </template>
                        </Column>

                        <template #empty>
                            <div class="py-8 text-center text-muted-foreground">
                                No review requests found.
                                <a
                                    href="/review-requests/create"
                                    class="ml-1 text-primary hover:underline"
                                >
                                    Send your first request
                                </a>
                            </div>
                        </template>
                    </DataTable>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
