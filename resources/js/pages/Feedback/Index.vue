<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import Select from 'primevue/select';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import { Feedback } from '@/types/business';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { dashboard } from '@/routes';
import feedbackRoutes from '@/routes/feedback';
import { MessageSquare, Star, Calendar, User, Mail, CheckCircle, XCircle, Clock } from 'lucide-vue-next';
import { ref, computed } from 'vue';

interface Props {
    feedback: Feedback[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Feedback',
        href: feedbackRoutes.index().url,
    },
];

const selectedFilter = ref<'all' | 'pending' | 'approved' | 'rejected'>('all');
const selectedSentiment = ref<'all' | 'positive' | 'neutral' | 'negative'>('all');

const statusOptions = [
    { label: 'All statuses', value: 'all' },
    { label: 'Pending', value: 'pending' },
    { label: 'Approved', value: 'approved' },
    { label: 'Rejected', value: 'rejected' },
];

const sentimentOptions = [
    { label: 'All sentiments', value: 'all' },
    { label: 'Positive', value: 'positive' },
    { label: 'Neutral', value: 'neutral' },
    { label: 'Negative', value: 'negative' },
];

const filteredFeedback = computed(() => {
    return props.feedback.filter((item) => {
        const matchesStatus = selectedFilter.value === 'all' || item.moderation_status === selectedFilter.value;
        const matchesSentiment = selectedSentiment.value === 'all' || item.sentiment === selectedSentiment.value;
        return matchesStatus && matchesSentiment;
    });
});

const getSentimentBadge = (sentiment: string | null) => {
    switch (sentiment) {
        case 'positive':
            return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
        case 'negative':
            return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200';
    }
};

const getStatusBadge = (status: string) => {
    switch (status) {
        case 'approved':
            return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
        case 'rejected':
            return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200';
        default:
            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200';
    }
};

const getStatusIcon = (status: string) => {
    switch (status) {
        case 'approved':
            return CheckCircle;
        case 'rejected':
            return XCircle;
        default:
            return Clock;
    }
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const renderStars = (rating: number) => {
    return Array.from({ length: 5 }, (_, i) => i < rating);
};
</script>

<template>
    <Head title="Customer Feedback" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <!-- Header -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Customer Feedback</h1>
                    <p class="text-muted-foreground">
                        View and manage customer reviews
                    </p>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid gap-4 md:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Feedback</CardTitle>
                        <MessageSquare class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ feedback.length }}</div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Pending Review</CardTitle>
                        <Clock class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">
                            {{ feedback.filter(f => f.moderation_status === 'pending').length }}
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Approved</CardTitle>
                        <CheckCircle class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">
                            {{ feedback.filter(f => f.moderation_status === 'approved').length }}
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Avg Rating</CardTitle>
                        <Star class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">
                            {{ feedback.length > 0 
                                ? (feedback.reduce((sum, f) => sum + f.rating, 0) / feedback.length).toFixed(1)
                                : '0.0'
                            }}
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Filters -->
            <Card>
                <CardHeader>
                    <CardTitle>Filters</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="flex flex-col gap-4 md:flex-row md:items-center">
                        <div class="flex-1">
                            <label class="mb-2 block text-sm font-medium">Status</label>
                            <Select
                                v-model="selectedFilter"
                                :options="statusOptions"
                                option-label="label"
                                option-value="value"
                                placeholder="All statuses"
                                class="w-full"
                            />
                        </div>
                        <div class="flex-1">
                            <label class="mb-2 block text-sm font-medium">Sentiment</label>
                            <Select
                                v-model="selectedSentiment"
                                :options="sentimentOptions"
                                option-label="label"
                                option-value="value"
                                placeholder="All sentiments"
                                class="w-full"
                            />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Feedback List -->
            <div class="space-y-4">
                <Card v-for="item in filteredFeedback" :key="item.id">
                    <CardHeader>
                        <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                            <div class="flex-1 space-y-2">
                                <div class="flex items-center gap-2">
                                    <component
                                        :is="getStatusIcon(item.moderation_status)"
                                        class="h-4 w-4"
                                    />
                                    <Badge :class="getStatusBadge(item.moderation_status)">
                                        {{ item.moderation_status }}
                                    </Badge>
                                    <Badge :class="getSentimentBadge(item.sentiment)">
                                        {{ item.sentiment }}
                                    </Badge>
                                    <Badge v-if="item.is_public" variant="outline">
                                        Public
                                    </Badge>
                                </div>
                                <div class="flex items-center gap-1">
                                    <Star
                                        v-for="(filled, index) in renderStars(item.rating)"
                                        :key="index"
                                        :class="filled ? 'fill-yellow-400 text-yellow-400' : 'text-gray-300'"
                                        class="h-5 w-5"
                                    />
                                    <span class="ml-2 text-sm text-muted-foreground">
                                        {{ item.rating }}/5
                                    </span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 text-sm text-muted-foreground">
                                <Calendar class="h-4 w-4" />
                                {{ formatDate(item.submitted_at) }}
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div v-if="item.customer_name || item.customer_email" class="flex flex-wrap gap-4 text-sm">
                            <div v-if="item.customer_name" class="flex items-center gap-2">
                                <User class="h-4 w-4 text-muted-foreground" />
                                <span>{{ item.customer_name }}</span>
                            </div>
                            <div v-if="item.customer_email" class="flex items-center gap-2">
                                <Mail class="h-4 w-4 text-muted-foreground" />
                                <span>{{ item.customer_email }}</span>
                            </div>
                        </div>
                        <Separator />
                        <div class="prose prose-sm dark:prose-invert max-w-none">
                            <p>{{ item.comment }}</p>
                        </div>
                        <div v-if="item.reply_text" class="rounded-lg bg-muted p-4">
                            <p class="mb-1 text-sm font-medium">Your Reply:</p>
                            <p class="text-sm">{{ item.reply_text }}</p>
                            <p class="mt-2 text-xs text-muted-foreground">
                                Replied on {{ formatDate(item.replied_at!) }}
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <Card v-if="filteredFeedback.length === 0">
                    <CardContent class="py-10 text-center">
                        <MessageSquare class="mx-auto h-12 w-12 text-muted-foreground" />
                        <h3 class="mt-4 text-lg font-semibold">No feedback found</h3>
                        <p class="mt-2 text-sm text-muted-foreground">
                            {{ selectedFilter !== 'all' || selectedSentiment !== 'all'
                                ? 'Try adjusting your filters'
                                : 'Share your QR code to start collecting feedback'
                            }}
                        </p>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
