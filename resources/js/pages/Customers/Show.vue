<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { format } from 'date-fns';
import {
    ArrowLeft,
    Edit,
    Eye,
    MessageSquare,
    Send,
    TrendingUp,
} from 'lucide-vue-next';
import Chip from 'primevue/chip';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import TabPanel from 'primevue/tabpanel';
import TabView from 'primevue/tabview';
import Tag from 'primevue/tag';

interface Customer {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    company_name: string | null;
    tags: string[];
    notes: string | null;
    total_requests_sent: number;
    total_feedbacks: number;
    opted_out: boolean;
    created_at: string;
    last_request_sent_at: string | null;
    last_feedback_at: string | null;
}

interface Feedback {
    id: number;
    rating: number;
    comment: string | null;
    status: string;
    created_at: string;
}

interface ReviewRequest {
    id: number;
    status: string;
    subject: string;
    sent_at: string | null;
    opened_at: string | null;
    completed_at: string | null;
    expires_at: string;
}

interface Props {
    customer: Customer;
    recentFeedback: Feedback[];
    recentReviewRequests: ReviewRequest[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Customers',
        href: '/customers',
    },
    {
        title: props.customer.name,
        href: `/customers/${props.customer.id}`,
    },
];

const formatDate = (date: string | null) => {
    if (!date) return '—';
    return format(new Date(date), 'MMM d, yyyy h:mm a');
};

const getStatusSeverity = (status: string) => {
    const map: Record<string, any> = {
        pending: 'info',
        opened: 'warning',
        completed: 'success',
        expired: 'danger',
        published: 'success',
        flagged: 'danger',
    };
    return map[status] || 'info';
};
</script>

<template>
    <Head title="Customer Details" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <!-- Header -->
            <div
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">
                        {{ customer.name }}
                    </h1>
                    <p class="text-muted-foreground">
                        Customer profile and activity history
                    </p>
                </div>
                <div class="flex gap-2">
                    <Button
                        @click="router.visit(`/customers/${customer.id}/edit`)"
                        variant="outline"
                    >
                        <Edit class="mr-2 h-4 w-4" />
                        Edit
                    </Button>
                    <Button
                        @click="router.visit('/customers')"
                        variant="outline"
                    >
                        <ArrowLeft class="mr-2 h-4 w-4" />
                        Back
                    </Button>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid gap-4 md:grid-cols-4">
                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between space-y-0 pb-2"
                    >
                        <CardTitle class="text-sm font-medium"
                            >Total Requests</CardTitle
                        >
                        <Send class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">
                            {{ customer.total_requests_sent }}
                        </div>
                        <p class="mt-1 text-xs text-muted-foreground">
                            {{
                                customer.last_request_sent_at
                                    ? format(
                                          new Date(
                                              customer.last_request_sent_at,
                                          ),
                                          'MMM d, yyyy',
                                      )
                                    : 'Never'
                            }}
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between space-y-0 pb-2"
                    >
                        <CardTitle class="text-sm font-medium"
                            >Total Feedbacks</CardTitle
                        >
                        <MessageSquare class="h-4 w-4 text-green-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-green-600">
                            {{ customer.total_feedbacks }}
                        </div>
                        <p class="mt-1 text-xs text-muted-foreground">
                            {{
                                customer.last_feedback_at
                                    ? format(
                                          new Date(customer.last_feedback_at),
                                          'MMM d, yyyy',
                                      )
                                    : 'Never'
                            }}
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between space-y-0 pb-2"
                    >
                        <CardTitle class="text-sm font-medium"
                            >Status</CardTitle
                        >
                        <TrendingUp class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <Tag
                            v-if="customer.opted_out"
                            severity="danger"
                            value="Opted Out"
                        />
                        <Tag v-else severity="success" value="Active" />
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between space-y-0 pb-2"
                    >
                        <CardTitle class="text-sm font-medium"
                            >Customer Since</CardTitle
                        >
                    </CardHeader>
                    <CardContent>
                        <div class="text-lg font-medium">
                            {{
                                format(
                                    new Date(customer.created_at),
                                    'MMM d, yyyy',
                                )
                            }}
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Contact Information -->
            <Card>
                <CardHeader>
                    <CardTitle>Contact Information</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-4 md:grid-cols-3">
                        <div class="space-y-1">
                            <p class="text-sm text-muted-foreground">Email</p>
                            <p class="font-medium">{{ customer.email }}</p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-sm text-muted-foreground">Phone</p>
                            <p class="font-medium">
                                {{ customer.phone || '—' }}
                            </p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-sm text-muted-foreground">Company</p>
                            <p class="font-medium">
                                {{ customer.company_name || '—' }}
                            </p>
                        </div>
                    </div>

                    <div
                        v-if="customer.tags && customer.tags.length"
                        class="mt-4"
                    >
                        <p class="mb-2 text-sm text-muted-foreground">Tags</p>
                        <div class="flex flex-wrap gap-2">
                            <Chip
                                v-for="tag in customer.tags"
                                :key="tag"
                                :label="tag"
                            />
                        </div>
                    </div>

                    <div v-if="customer.notes" class="mt-4">
                        <p class="mb-2 text-sm text-muted-foreground">Notes</p>
                        <p class="text-sm whitespace-pre-wrap">
                            {{ customer.notes }}
                        </p>
                    </div>
                </CardContent>
            </Card>

            <!-- Activity Tabs -->
            <Card>
                <CardContent class="pt-6">
                    <TabView>
                        <TabPanel
                            header="Recent Feedback"
                            value="recent-feedback"
                        >
                            <DataTable
                                :value="recentFeedback"
                                class="p-datatable-sm"
                            >
                                <Column
                                    field="rating"
                                    header="Rating"
                                    class="w-[100px]"
                                >
                                    <template #body="{ data }">
                                        <div class="flex items-center gap-1">
                                            <i
                                                class="pi pi-star-fill text-yellow-500"
                                            ></i>
                                            <span class="font-medium">{{
                                                data.rating
                                            }}</span>
                                        </div>
                                    </template>
                                </Column>

                                <Column field="comment" header="Comment">
                                    <template #body="{ data }">
                                        <div
                                            v-if="data.comment"
                                            class="max-w-md truncate"
                                        >
                                            {{ data.comment }}
                                        </div>
                                        <span v-else class="text-gray-400"
                                            >No comment</span
                                        >
                                    </template>
                                </Column>

                                <Column
                                    field="status"
                                    header="Status"
                                    class="w-[120px]"
                                >
                                    <template #body="{ data }">
                                        <Tag
                                            :severity="
                                                getStatusSeverity(data.status)
                                            "
                                            :value="data.status"
                                            class="capitalize"
                                        />
                                    </template>
                                </Column>

                                <Column
                                    field="created_at"
                                    header="Date"
                                    class="w-[180px]"
                                >
                                    <template #body="{ data }">
                                        {{ formatDate(data.created_at) }}
                                    </template>
                                </Column>

                                <Column header="Actions" class="w-[80px]">
                                    <template #body="{ data }">
                                        <Button
                                            size="icon"
                                            variant="ghost"
                                            @click="
                                                router.visit(
                                                    `/feedback/${data.id}`,
                                                )
                                            "
                                        >
                                            <Eye class="h-4 w-4" />
                                        </Button>
                                    </template>
                                </Column>

                                <template #empty>
                                    <div class="py-8 text-center text-gray-500">
                                        No feedback submitted yet.
                                    </div>
                                </template>
                            </DataTable>
                        </TabPanel>

                        <TabPanel
                            header="Review Requests"
                            value="review-requests"
                        >
                            <DataTable
                                :value="recentReviewRequests"
                                class="p-datatable-sm"
                            >
                                <Column field="subject" header="Subject">
                                    <template #body="{ data }">
                                        {{ data.subject }}
                                    </template>
                                </Column>

                                <Column
                                    field="status"
                                    header="Status"
                                    class="w-[120px]"
                                >
                                    <template #body="{ data }">
                                        <Tag
                                            :severity="
                                                getStatusSeverity(data.status)
                                            "
                                            :value="data.status"
                                            class="capitalize"
                                        />
                                    </template>
                                </Column>

                                <Column header="Sent" class="w-[180px]">
                                    <template #body="{ data }">
                                        {{ formatDate(data.sent_at) }}
                                    </template>
                                </Column>

                                <Column
                                    header="Opened"
                                    class="hidden w-[180px] md:table-cell"
                                >
                                    <template #body="{ data }">
                                        {{ formatDate(data.opened_at) }}
                                    </template>
                                </Column>

                                <Column header="Actions" class="w-[80px]">
                                    <template #body="{ data }">
                                        <Button
                                            size="icon"
                                            variant="ghost"
                                            @click="
                                                router.visit(
                                                    `/review-requests/${data.id}`,
                                                )
                                            "
                                        >
                                            <Eye class="h-4 w-4" />
                                        </Button>
                                    </template>
                                </Column>

                                <template #empty>
                                    <div class="py-8 text-center text-gray-500">
                                        No review requests sent yet.
                                    </div>
                                </template>
                            </DataTable>
                        </TabPanel>
                    </TabView>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
