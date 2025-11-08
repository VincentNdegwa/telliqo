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
import { Badge } from '@/components/ui/badge';
import { Feedback } from '@/types/business';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { dashboard } from '@/routes';
import feedbackRoutes from '@/routes/feedback';
import { MessageSquare, Star, Eye, Flag, EyeOff, Reply, MailWarning, TrendingUp, TrendingDown, Minus } from 'lucide-vue-next';
import { ref } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Rating from 'primevue/rating';
import Tag from 'primevue/tag';
import ConfirmDialog from 'primevue/confirmdialog';
import { useConfirm } from "primevue/useconfirm";
import Dialog from 'primevue/dialog';
import Textarea from 'primevue/textarea';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';

interface StatValue {
    value: number;
    trend: {
        percentage: number;
        direction: 'up' | 'down' | 'neutral';
    };
}

interface Props {
    feedback: {
        data: Feedback[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number;
        to: number;
    };
    stats: {
        total: StatValue;
        published: StatValue;
        flagged: StatValue;
        avg_rating: StatValue;
    };
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

const confirm = useConfirm();
const toast = useToast();

// Reply Dialog
const replyDialogVisible = ref(false);
const selectedFeedback = ref<Feedback | null>(null);
const replyText = ref('');

const showReplyDialog = (feedback: Feedback) => {
    selectedFeedback.value = feedback;
    replyText.value = feedback.reply_text || '';
    replyDialogVisible.value = true;
};

const submitReply = () => {
    if (!selectedFeedback.value) return;
    
    router.post(`/feedback/${selectedFeedback.value.id}/reply`, {
        reply_text: replyText.value,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            toast.add({ 
                severity: 'success', 
                summary: 'Reply Posted', 
                detail: 'Your reply has been posted successfully', 
                life: 3000 
            });
            replyDialogVisible.value = false;
            replyText.value = '';
        },
        onError: () => {
            toast.add({ 
                severity: 'error', 
                summary: 'Error', 
                detail: 'Failed to post reply', 
                life: 3000 
            });
        }
    });
};

const flagReview = (feedback: Feedback) => {
    confirm.require({
        message: 'Are you sure you want to flag this review for admin moderation? This action will hide the review from public view until reviewed by an admin.',
        header: 'Flag Review',
        icon: 'pi pi-exclamation-triangle',
        rejectProps: {
            label: 'Cancel',
            severity: 'secondary',
            outlined: true
        },
        acceptProps: {
            label: 'Flag Review',
            severity: 'danger'
        },
        accept: () => {
            router.post(`/feedback/${feedback.id}/flag`, {
                reason: 'inappropriate',
                notes: 'Flagged by business user',
            }, {
                preserveScroll: true,
                onSuccess: () => {
                    toast.add({ 
                        severity: 'info', 
                        summary: 'Review Flagged', 
                        detail: 'This review has been flagged for admin review', 
                        life: 3000 
                    });
                }
            });
        }
    });
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

const getSeverity = (status: string) => {
    switch (status.toLocaleLowerCase()) {
        case 'published':
            return 'success';
        case 'flagged':
            return 'warn';
        case 'hidden':
            return 'danger';
        default:
            return 'secondary';
    }
};

const getSentimentSeverity = (sentiment: string | null) => {
    switch (sentiment?.toLocaleLowerCase()) {
        case 'positive':
            return 'success';
        case 'negative':
            return 'danger';
        case 'neutral':
            return 'warn';
        default:
            return 'secondary';
    }
};

const onPage = (event: any) => {
    router.get(feedbackRoutes.index().url, {
        page: event.page + 1,
        per_page: event.rows,
    }, {
        preserveScroll: true,
        preserveState: true,
    });
};

const getTrendIcon = (direction: 'up' | 'down' | 'neutral') => {
    switch (direction) {
        case 'up':
            return TrendingUp;
        case 'down':
            return TrendingDown;
        default:
            return Minus;
    }
};

const getTrendColor = (direction: 'up' | 'down' | 'neutral', isPositive: boolean = true) => {
    if (direction === 'neutral') return 'text-muted-foreground';
    
    // For metrics where up is good (total, published, avg_rating)
    if (isPositive) {
        return direction === 'up' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400';
    }
    // For metrics where up is bad (flagged)
    return direction === 'up' ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400';
};
</script>

<template>
    <Head title="Customer Feedback" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <Toast />
        <ConfirmDialog />
        
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
                        <div class="text-2xl font-bold">{{ stats.total.value }}</div>
                        <div class="flex items-center gap-1 mt-1 text-xs">
                            <component 
                                :is="getTrendIcon(stats.total.trend.direction)" 
                                :class="['h-3 w-3', getTrendColor(stats.total.trend.direction, true)]"
                            />
                            <span :class="getTrendColor(stats.total.trend.direction, true)">
                                {{ stats.total.trend.percentage }}% from last week
                            </span>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Published</CardTitle>
                        <Eye class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.published.value }}</div>
                        <div class="flex items-center gap-1 mt-1 text-xs">
                            <component 
                                :is="getTrendIcon(stats.published.trend.direction)" 
                                :class="['h-3 w-3', getTrendColor(stats.published.trend.direction, true)]"
                            />
                            <span :class="getTrendColor(stats.published.trend.direction, true)">
                                {{ stats.published.trend.percentage }}% from last week
                            </span>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Flagged</CardTitle>
                        <Flag class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.flagged.value }}</div>
                        <div class="flex items-center gap-1 mt-1 text-xs">
                            <component 
                                :is="getTrendIcon(stats.flagged.trend.direction)" 
                                :class="['h-3 w-3', getTrendColor(stats.flagged.trend.direction, false)]"
                            />
                            <span :class="getTrendColor(stats.flagged.trend.direction, false)">
                                {{ stats.flagged.trend.percentage }}% from last week
                            </span>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Avg Rating</CardTitle>
                        <Star class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.avg_rating.value.toFixed(1) }}</div>
                        <div class="flex items-center gap-1 mt-1 text-xs">
                            <component 
                                :is="getTrendIcon(stats.avg_rating.trend.direction)" 
                                :class="['h-3 w-3', getTrendColor(stats.avg_rating.trend.direction, true)]"
                            />
                            <span :class="getTrendColor(stats.avg_rating.trend.direction, true)">
                                {{ stats.avg_rating.trend.percentage }}% from last week
                            </span>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- DataTable -->
            <Card>
                <CardContent class="pt-6">
                    <DataTable 
                        :value="feedback.data" 
                        :lazy="true"
                        :paginator="true"
                        :rows="feedback.per_page"
                        :totalRecords="feedback.total"
                        :first="(feedback.current_page - 1) * feedback.per_page"
                        @page="onPage"
                        stripedRows
                        tableStyle="min-width: 50rem"
                        paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                        :rowsPerPageOptions="[5, 10, 20]"
                        currentPageReportTemplate="Showing {first} to {last} of {totalRecords} reviews"
                    >
                        <template #empty>
                            <div class="py-10 text-center">
                                <MessageSquare class="mx-auto h-12 w-12 text-muted-foreground" />
                                <h3 class="mt-4 text-lg font-semibold">No feedback found</h3>
                                <p class="mt-2 text-sm text-muted-foreground">
                                    Share your QR code to start collecting feedback
                                </p>
                            </div>
                        </template>

                        <!-- Customer Column -->
                        <Column header="Customer" style="min-width: 200px">
                            <template #body="{ data }">
                                <div class="space-y-1">
                                    <div class="font-medium">{{ data.customer_name || 'Anonymous' }}</div>
                                    <div class="text-sm text-muted-foreground">{{ data.customer_email || 'No email' }}</div>
                                </div>
                            </template>
                        </Column>

                        <!-- Rating Column -->
                        <Column header="Rating" style="min-width: 150px">
                            <template #body="{ data }">
                                <Rating :modelValue="data.rating" readonly :cancel="false" />
                            </template>
                        </Column>

                        <!-- Comment Column -->
                        <Column field="comment" header="Comment" style="min-width: 300px">
                            <template #body="{ data }">
                                <div class="max-w-md">
                                    <p class="line-clamp-2">{{ data.comment || 'No comment' }}</p>
                                    <div v-if="data.reply_text" class="mt-2 rounded-lg bg-muted p-2">
                                        <div class="flex items-center gap-1 text-xs font-medium text-muted-foreground mb-1">
                                            <Reply class="h-3 w-3" />
                                            Your Reply
                                        </div>
                                        <p class="text-sm line-clamp-2">{{ data.reply_text }}</p>
                                    </div>
                                </div>
                            </template>
                        </Column>

                        <!-- Status Column -->
                        <Column field="moderation_status" header="Status" style="min-width: 120px">
                            <template #body="{ data }">
                                <div class="space-y-1">
                                    <Tag 
                                        :value="data.moderation_status || 'published'" 
                                        :severity="getSeverity(data.moderation_status || 'published')"
                                        class="capitalize"
                                    />
                                    <div v-if="data.sentiment">
                                        <Tag 
                                            :value="data.sentiment" 
                                            :severity="getSentimentSeverity(data.sentiment)"
                                            class="text-xs capitalize"
                                        />
                                    </div>
                                </div>
                            </template>
                        </Column>

                        <!-- Date Column -->
                        <Column field="submitted_at" header="Submitted" style="min-width: 150px">
                            <template #body="{ data }">
                                <div class="text-sm">{{ formatDate(data.submitted_at) }}</div>
                            </template>
                        </Column>

                        <!-- Actions Column -->
                        <Column header="Actions" style="min-width: 180px">
                            <template #body="{ data }">
                                <div class="flex gap-2">
                                    <Button 
                                        @click="showReplyDialog(data)" 
                                        size="sm" 
                                        variant="outline"
                                        class="gap-1"
                                    >
                                        <Reply class="h-3 w-3" />
                                        {{ data.reply_text ? 'Edit Reply' : 'Reply' }}
                                    </Button>
                                    <Button 
                                        v-if="data.is_public && data.moderation_status !== 'flagged'"
                                        @click="flagReview(data)" 
                                        size="sm" 
                                        variant="outline"
                                        class="gap-1 text-destructive hover:text-destructive"
                                    >
                                        <MailWarning class="h-3 w-3" />
                                        Flag
                                    </Button>
                                </div>
                            </template>
                        </Column>
                    </DataTable>
                </CardContent>
            </Card>
        </div>

        <!-- Reply Dialog -->
        <Dialog 
            v-model:visible="replyDialogVisible" 
            modal 
            header="Reply to Customer" 
            :style="{ width: '30rem' }"
        >
            <div class="space-y-4">
                <div v-if="selectedFeedback" class="rounded-lg bg-muted p-4">
                    <div class="mb-2">
                        <Rating :modelValue="selectedFeedback.rating" readonly :cancel="false" />
                    </div>
                    <p class="text-sm">{{ selectedFeedback.comment }}</p>
                    <p class="mt-2 text-xs text-muted-foreground">
                        - {{ selectedFeedback.customer_name || 'Anonymous' }}
                    </p>
                </div>

                <div class="space-y-2">
                    <label for="reply-text" class="text-sm font-medium">Your Reply</label>
                    <Textarea 
                        id="reply-text"
                        v-model="replyText" 
                        rows="5" 
                        placeholder="Write your reply here..."
                        class="w-full"
                    />
                    <p class="text-xs text-muted-foreground">
                        This reply will be visible to customers viewing this review.
                    </p>
                </div>
            </div>

            <template #footer>
                <div class="flex justify-end gap-2">
                    <Button variant="outline" @click="replyDialogVisible = false">Cancel</Button>
                    <Button @click="submitReply" :disabled="!replyText.trim()">Post Reply</Button>
                </div>
            </template>
        </Dialog>
    </AppLayout>
</template>
