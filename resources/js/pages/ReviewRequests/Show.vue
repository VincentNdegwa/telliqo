<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { router } from '@inertiajs/vue3';
import { format } from 'date-fns';
import {
    ArrowLeft,
    Clock,
    Copy,
    Eye,
    Info,
    Mail,
    Star,
    User,
} from 'lucide-vue-next';
import PrimeCard from 'primevue/card';
import ConfirmDialog from 'primevue/confirmdialog';
import Tag from 'primevue/tag';
import { useConfirm } from 'primevue/useconfirm';
import { useToast } from 'primevue/usetoast';

interface ReviewRequest {
    id: number;
    customer: {
        id: number;
        name: string;
        email: string;
        phone: string | null;
        company_name: string | null;
    };
    subject: string;
    message: string;
    status: string;
    unique_token: string;
    sent_at: string | null;
    opened_at: string | null;
    completed_at: string | null;
    expires_at: string;
    reminder_sent_count: number;
    last_reminder_at: string | null;
    created_at: string;
    feedback: {
        id: number;
        rating: number;
        comment: string | null;
        created_at: string;
    } | null;
}

interface Props {
    reviewRequest: ReviewRequest;
    canSendReminder: boolean;
}

const toast = useToast();

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
    {
        title: props.reviewRequest.subject,
        href: `/review-requests/${props.reviewRequest.id}`,
    },
];

const formatDate = (date: string | null) => {
    if (!date) return 'Not yet';
    return format(new Date(date), 'MMM d, yyyy h:mm a');
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

const copyLink = () => {
    const link = `${window.location.origin}/r/${props.reviewRequest.unique_token}`;
    navigator.clipboard.writeText(link);

    toast.add({
        severity: 'success',
        summary: 'Link Copied',
        detail: 'The review link has been copied to your clipboard.',
        life: 3000,
    });
};

const reviewLink = `${window.location.origin}/r/${props.reviewRequest.unique_token}`;

const sendReminder = () => {
    confirm.require({
        message: `Send a reminder to ${props.reviewRequest.customer.name}?`,
        header: 'Send Reminder',
        icon: 'pi pi-send',
        rejectClass: 'p-button-secondary p-button-outlined',
        acceptClass: 'p-button-primary',
        accept: () => {
            router.post(`/review-requests/${props.reviewRequest.id}/remind`);
        },
    });
};
</script>

<template>
    <AppLayout title="Review Request Details" :breadcrumbs="breadcrumbs">
        <ConfirmDialog />

        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">
                        Review Request Details
                    </h1>
                    <p class="text-muted-foreground">
                        {{ reviewRequest.subject }}
                    </p>
                </div>
                <div class="flex gap-2">
                    <Button
                        v-permission="'review-request.send'"
                        v-if="
                            canSendReminder &&
                            (reviewRequest.status === 'pending' ||
                                reviewRequest.status === 'opened')
                        "
                        variant="outline"
                        @click="sendReminder"
                    >
                        <Mail class="mr-2 h-4 w-4" />
                        Send Reminder
                    </Button>
                    <Button
                        variant="outline"
                        @click="router.visit('/review-requests')"
                    >
                        <ArrowLeft class="mr-2 h-4 w-4" />
                        Back
                    </Button>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between space-y-0 pb-2"
                    >
                        <CardTitle class="text-sm font-medium"
                            >Status</CardTitle
                        >
                    </CardHeader>
                    <CardContent>
                        <Tag
                            :severity="getStatusSeverity(reviewRequest.status)"
                            :value="reviewRequest.status"
                            class="capitalize"
                        />
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between space-y-0 pb-2"
                    >
                        <CardTitle class="text-sm font-medium">Sent</CardTitle>
                        <Mail class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-sm font-medium">
                            {{ formatDate(reviewRequest.sent_at) }}
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
                        <Clock class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-sm font-medium">
                            {{ formatDate(reviewRequest.opened_at) }}
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between space-y-0 pb-2"
                    >
                        <CardTitle class="text-sm font-medium"
                            >Reminders Sent</CardTitle
                        >
                        <Mail class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">
                            {{ reviewRequest.reminder_sent_count }}
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Customer and Timeline Cards -->
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <PrimeCard>
                    <template #title>
                        <div class="text-lg">Customer Information</div>
                    </template>
                    <template #content>
                        <div class="space-y-3">
                            <div>
                                <div class="mb-1 text-sm text-gray-500">
                                    Name
                                </div>
                                <div class="font-medium">
                                    {{ reviewRequest.customer.name }}
                                </div>
                            </div>
                            <div>
                                <div class="mb-1 text-sm text-gray-500">
                                    Email
                                </div>
                                <div class="font-medium">
                                    {{ reviewRequest.customer.email }}
                                </div>
                            </div>
                            <div v-if="reviewRequest.customer.phone">
                                <div class="mb-1 text-sm text-gray-500">
                                    Phone
                                </div>
                                <div class="font-medium">
                                    {{ reviewRequest.customer.phone }}
                                </div>
                            </div>
                            <div v-if="reviewRequest.customer.company_name">
                                <div class="mb-1 text-sm text-gray-500">
                                    Company
                                </div>
                                <div class="font-medium">
                                    {{ reviewRequest.customer.company_name }}
                                </div>
                            </div>
                            <Button
                                variant="outline"
                                size="sm"
                                @click="
                                    router.visit(
                                        `/customers/${reviewRequest.customer.id}`,
                                    )
                                "
                            >
                                <User class="mr-2 h-4 w-4" />
                                View Customer
                            </Button>
                        </div>
                    </template>
                </PrimeCard>

                <PrimeCard>
                    <template #title>
                        <div class="text-lg">Request Timeline</div>
                    </template>
                    <template #content>
                        <div class="space-y-3">
                            <div>
                                <div class="mb-1 text-sm text-gray-500">
                                    Created
                                </div>
                                <div class="font-medium">
                                    {{ formatDate(reviewRequest.created_at) }}
                                </div>
                            </div>
                            <div>
                                <div class="mb-1 text-sm text-gray-500">
                                    Sent
                                </div>
                                <div class="font-medium">
                                    {{ formatDate(reviewRequest.sent_at) }}
                                </div>
                            </div>
                            <div>
                                <div class="mb-1 text-sm text-gray-500">
                                    Last Reminder
                                </div>
                                <div class="font-medium">
                                    {{
                                        formatDate(
                                            reviewRequest.last_reminder_at,
                                        )
                                    }}
                                </div>
                            </div>
                            <div>
                                <div class="mb-1 text-sm text-gray-500">
                                    Expires
                                </div>
                                <div class="font-medium">
                                    {{ formatDate(reviewRequest.expires_at) }}
                                </div>
                            </div>
                            <div v-if="reviewRequest.completed_at">
                                <div class="mb-1 text-sm text-gray-500">
                                    Completed
                                </div>
                                <div class="font-medium">
                                    {{ formatDate(reviewRequest.completed_at) }}
                                </div>
                            </div>
                        </div>
                    </template>
                </PrimeCard>
            </div>

            <!-- Email Content Card -->
            <PrimeCard>
                <template #title>
                    <div class="text-lg">Email Content</div>
                </template>
                <template #content>
                    <div class="space-y-4">
                        <div>
                            <div class="mb-1 text-sm text-gray-500">
                                Subject
                            </div>
                            <div class="font-medium">
                                {{ reviewRequest.subject }}
                            </div>
                        </div>
                        <div>
                            <div class="mb-1 text-sm text-gray-500">
                                Message
                            </div>
                            <div class="whitespace-pre-wrap text-gray-700">
                                {{ reviewRequest.message }}
                            </div>
                        </div>
                        <div>
                            <div class="mb-2 text-sm text-gray-500">
                                Review Link
                            </div>
                            <div class="flex gap-2">
                                <Input
                                    :model-value="reviewLink"
                                    readonly
                                    class="flex-1 text-sm"
                                />
                                <Button
                                    variant="outline"
                                    size="icon"
                                    @click="copyLink"
                                    v-tooltip.top="'Copy link'"
                                >
                                    <Copy class="h-4 w-4" />
                                </Button>
                            </div>
                        </div>
                    </div>
                </template>
            </PrimeCard>

            <!-- Feedback Card (if exists) -->
            <PrimeCard v-if="reviewRequest.feedback">
                <template #title>
                    <div class="text-lg">Submitted Feedback</div>
                </template>
                <template #content>
                    <div class="space-y-4">
                        <div class="flex items-center gap-2">
                            <div class="text-sm text-gray-500">Rating:</div>
                            <div class="flex items-center gap-1">
                                <Star
                                    class="h-5 w-5 fill-yellow-500 text-yellow-500"
                                />
                                <span class="text-lg font-bold">{{
                                    reviewRequest.feedback.rating
                                }}</span>
                            </div>
                        </div>
                        <div v-if="reviewRequest.feedback.comment">
                            <div class="mb-1 text-sm text-gray-500">
                                Comment:
                            </div>
                            <div class="rounded bg-gray-50 p-3 text-gray-700">
                                {{ reviewRequest.feedback.comment }}
                            </div>
                        </div>
                        <div>
                            <div class="mb-1 text-sm text-gray-500">
                                Submitted:
                            </div>
                            <div class="font-medium">
                                {{
                                    formatDate(
                                        reviewRequest.feedback.created_at,
                                    )
                                }}
                            </div>
                        </div>
                        <Button
                            variant="outline"
                            size="sm"
                            @click="
                                router.visit(
                                    `/feedback/${reviewRequest.feedback.id}`,
                                )
                            "
                        >
                            <Eye class="mr-2 h-4 w-4" />
                            View Full Feedback
                        </Button>
                    </div>
                </template>
            </PrimeCard>

            <!-- Info Alert -->
            <Card
                v-if="
                    !canSendReminder &&
                    (reviewRequest.status === 'pending' ||
                        reviewRequest.status === 'opened')
                "
                class="border-yellow-200 bg-yellow-50"
            >
                <CardHeader>
                    <CardTitle class="flex items-center gap-2 text-yellow-900">
                        <Info class="h-5 w-5" />
                        Reminder Not Available
                    </CardTitle>
                </CardHeader>
                <CardContent class="text-sm text-yellow-900">
                    You can send a reminder after 3 days from the last email
                    sent.
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
