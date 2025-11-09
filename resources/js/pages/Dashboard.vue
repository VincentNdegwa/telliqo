<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import {
    CheckCircle2,
    Copy,
    ExternalLink,
    Link2,
    MessageSquare,
    QrCode,
    Reply,
    Star,
    TrendingDown,
    TrendingUp,
} from 'lucide-vue-next';
import Chart from 'primevue/chart';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import Rating from 'primevue/rating';
import Tag from 'primevue/tag';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';
import { computed, onMounted, ref } from 'vue';

interface Stats {
    total_feedback: number;
    total_published: number;
    total_flagged: number;
    average_rating: number;
    response_rate: number;
    recent_feedback: number;
    previous_week_feedback: number;
    current_month_feedback: number;
    previous_month_feedback: number;
}

interface Charts {
    rating_distribution: Record<number, number>;
    sentiment_distribution: {
        positive: number;
        neutral: number;
        negative: number;
    };
    feedback_trend: Array<{
        date: string;
        count: number;
    }>;
}

interface RecentFeedback {
    id: number;
    customer_name: string | null;
    customer_email: string | null;
    rating: number;
    comment: string | null;
    sentiment: {
        value: string;
        label: string;
        severity: string;
        color: string;
        icon: string;
    } | null;
    moderation_status: {
        value: string;
        label: string;
        severity: string;
        color: string;
    };
    is_public: boolean;
    submitted_at: string;
    replied_at: string | null;
}

interface QuickLinks {
    public_profile_url: string;
    review_url: string;
    qr_code_path: string | null;
    business_slug: string;
}

interface Props {
    stats: Stats;
    charts: Charts;
    recent_feedback_list: RecentFeedback[];
    quick_links: QuickLinks;
}

const props = defineProps<Props>();
const toast = useToast();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

const trendChartData = ref();
const trendChartOptions = ref();
const ratingChartData = ref();
const ratingChartOptions = ref();
const sentimentChartData = ref();
const sentimentChartOptions = ref();

onMounted(() => {
    setupTrendChart();
    setupRatingChart();
    setupSentimentChart();
});

const setupTrendChart = () => {
    const documentStyle = getComputedStyle(document.documentElement);
    const textColor = documentStyle.getPropertyValue('--color-foreground');
    const textColorSecondary = documentStyle.getPropertyValue(
        '--color-muted-foreground',
    );
    const surfaceBorder = documentStyle.getPropertyValue('--color-border');

    trendChartData.value = {
        labels: props.charts.feedback_trend.map((item) => item.date),
        datasets: [
            {
                label: 'Feedback Received',
                data: props.charts.feedback_trend.map((item) => item.count),
                fill: true,
                borderColor: documentStyle.getPropertyValue('--color-primary'),
                backgroundColor: 'rgba(0, 0, 0, 0.05)',
                tension: 0.4,
            },
        ],
    };

    trendChartOptions.value = {
        maintainAspectRatio: false,
        aspectRatio: 0.6,
        plugins: {
            legend: {
                labels: {
                    color: textColor,
                },
            },
        },
        scales: {
            x: {
                ticks: {
                    color: textColorSecondary,
                },
                grid: {
                    color: surfaceBorder,
                    drawBorder: false,
                },
            },
            y: {
                beginAtZero: true,
                ticks: {
                    color: textColorSecondary,
                    stepSize: 1,
                },
                grid: {
                    color: surfaceBorder,
                    drawBorder: false,
                },
            },
        },
    };
};

const setupRatingChart = () => {
    const documentStyle = getComputedStyle(document.documentElement);

    ratingChartData.value = {
        labels: ['5 Stars', '4 Stars', '3 Stars', '2 Stars', '1 Star'],
        datasets: [
            {
                data: [
                    props.charts.rating_distribution[5],
                    props.charts.rating_distribution[4],
                    props.charts.rating_distribution[3],
                    props.charts.rating_distribution[2],
                    props.charts.rating_distribution[1],
                ],
                backgroundColor: [
                    'rgba(34, 197, 94, 0.8)',
                    'rgba(132, 204, 22, 0.8)',
                    'rgba(250, 204, 21, 0.8)',
                    'rgba(251, 146, 60, 0.8)',
                    'rgba(239, 68, 68, 0.8)',
                ],
                borderColor: [
                    'rgb(34, 197, 94)',
                    'rgb(132, 204, 22)',
                    'rgb(250, 204, 21)',
                    'rgb(251, 146, 60)',
                    'rgb(239, 68, 68)',
                ],
                borderWidth: 1,
            },
        ],
    };

    ratingChartOptions.value = {
        plugins: {
            legend: {
                labels: {
                    color: documentStyle.getPropertyValue('--color-foreground'),
                    usePointStyle: true,
                    padding: 15,
                    font: {
                        size: 12,
                    },
                },
                position: 'right',
            },
        },
    };
};

const setupSentimentChart = () => {
    const documentStyle = getComputedStyle(document.documentElement);

    sentimentChartData.value = {
        labels: ['Positive', 'Neutral', 'Negative'],
        datasets: [
            {
                data: [
                    props.charts.sentiment_distribution.positive,
                    props.charts.sentiment_distribution.neutral,
                    props.charts.sentiment_distribution.negative,
                ],
                backgroundColor: [
                    'rgba(34, 197, 94, 0.6)',
                    'rgba(156, 163, 175, 0.6)',
                    'rgba(239, 68, 68, 0.6)',
                ],
                borderColor: [
                    'rgb(34, 197, 94)',
                    'rgb(156, 163, 175)',
                    'rgb(239, 68, 68)',
                ],
                borderWidth: 2,
            },
        ],
    };

    sentimentChartOptions.value = {
        cutout: '60%',
        plugins: {
            legend: {
                labels: {
                    color: documentStyle.getPropertyValue('--color-foreground'),
                    usePointStyle: true,
                    padding: 15,
                    font: {
                        size: 12,
                    },
                },
                position: 'right',
            },
        },
    };
};

const weeklyTrend = computed(() => {
    const diff =
        props.stats.recent_feedback - props.stats.previous_week_feedback;
    if (diff > 0)
        return {
            icon: TrendingUp,
            color: 'text-green-600',
            text: `+${diff} from last week`,
        };
    if (diff < 0)
        return {
            icon: TrendingDown,
            color: 'text-red-600',
            text: `${diff} from last week`,
        };
    return {
        icon: TrendingUp,
        color: 'text-muted-foreground',
        text: 'No change',
    };
});

const monthlyTrend = computed(() => {
    const diff =
        props.stats.current_month_feedback -
        props.stats.previous_month_feedback;
    if (diff > 0)
        return {
            icon: TrendingUp,
            color: 'text-green-600',
            text: `+${diff} from last month`,
        };
    if (diff < 0)
        return {
            icon: TrendingDown,
            color: 'text-red-600',
            text: `${diff} from last month`,
        };
    return {
        icon: TrendingUp,
        color: 'text-muted-foreground',
        text: 'No change',
    };
});

const copyToClipboard = (text: string, label: string) => {
    if (typeof window !== 'undefined' && navigator.clipboard) {
        navigator.clipboard.writeText(text);
        toast.add({
            severity: 'success',
            summary: 'Copied!',
            detail: `${label} copied to clipboard`,
            life: 3000,
        });
    }
};

const openInNewTab = (url: string) => {
    if (typeof window !== 'undefined') {
        window.open(url, '_blank');
    }
};
</script>

<template>
    <Head title="Dashboard" />

    <Toast />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <!-- Stats Cards -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <!-- Total Feedback -->
                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between pb-2"
                    >
                        <CardTitle class="text-sm font-medium"
                            >Total Feedback</CardTitle
                        >
                        <MessageSquare class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">
                            {{ stats.total_feedback }}
                        </div>
                        <div
                            class="mt-1 flex items-center gap-1 text-xs text-muted-foreground"
                        >
                            <component
                                :is="weeklyTrend.icon"
                                :class="['h-3 w-3', weeklyTrend.color]"
                            />
                            <span>{{ weeklyTrend.text }}</span>
                        </div>
                    </CardContent>
                </Card>

                <!-- Average Rating -->
                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between pb-2"
                    >
                        <CardTitle class="text-sm font-medium"
                            >Average Rating</CardTitle
                        >
                        <Star class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">
                            {{ stats.average_rating }}
                        </div>
                        <div class="mt-1 flex items-center gap-1">
                            <Star
                                v-for="i in 5"
                                :key="i"
                                :class="
                                    i <= Math.round(stats.average_rating)
                                        ? 'fill-yellow-400 text-yellow-400'
                                        : 'text-muted'
                                "
                                class="h-3 w-3"
                            />
                        </div>
                    </CardContent>
                </Card>

                <!-- Response Rate -->
                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between pb-2"
                    >
                        <CardTitle class="text-sm font-medium"
                            >Response Rate</CardTitle
                        >
                        <Reply class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">
                            {{ stats.response_rate }}%
                        </div>
                        <p class="mt-1 text-xs text-muted-foreground">
                            Responded to feedback
                        </p>
                    </CardContent>
                </Card>

                <!-- Published Reviews -->
                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between pb-2"
                    >
                        <CardTitle class="text-sm font-medium"
                            >Published</CardTitle
                        >
                        <CheckCircle2 class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">
                            {{ stats.total_published }}
                        </div>
                        <div
                            class="mt-1 flex items-center gap-1 text-xs text-muted-foreground"
                        >
                            <component
                                :is="monthlyTrend.icon"
                                :class="['h-3 w-3', monthlyTrend.color]"
                            />
                            <span>{{ monthlyTrend.text }}</span>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Quick Links Section -->
            <div class="grid gap-4 md:grid-cols-3">
                <!-- QR Code -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2 text-base">
                            <QrCode class="h-5 w-5" />
                            QR Code
                        </CardTitle>
                        <CardDescription
                            >Download or view your QR code</CardDescription
                        >
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div
                            v-if="quick_links.qr_code_path"
                            class="flex justify-center rounded-lg bg-muted p-4"
                        >
                            <img
                                :src="`/storage/${quick_links.qr_code_path}`"
                                alt="Business QR Code"
                                class="h-32 w-32"
                            />
                        </div>
                        <div
                            v-else
                            class="flex justify-center rounded-lg bg-muted p-8"
                        >
                            <QrCode class="h-16 w-16 text-muted-foreground" />
                        </div>
                        <Button
                            variant="outline"
                            class="w-full gap-2"
                            @click="$inertia.visit('/qr-code')"
                        >
                            <ExternalLink class="h-4 w-4" />
                            Manage QR Code
                        </Button>
                    </CardContent>
                </Card>

                <!-- Review Link -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2 text-base">
                            <Link2 class="h-5 w-5" />
                            Review Link
                        </CardTitle>
                        <CardDescription
                            >Share this link for reviews</CardDescription
                        >
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div
                            class="rounded-lg bg-muted p-3 font-mono text-sm break-all"
                        >
                            {{ quick_links.review_url }}
                        </div>
                        <div class="flex gap-2">
                            <Button
                                variant="outline"
                                class="flex-1 gap-2"
                                @click="
                                    copyToClipboard(
                                        quick_links.review_url,
                                        'Review link',
                                    )
                                "
                            >
                                <Copy class="h-4 w-4" />
                                Copy Link
                            </Button>
                            <Button
                                variant="outline"
                                @click="openInNewTab(quick_links.review_url)"
                            >
                                <ExternalLink class="h-4 w-4" />
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                <!-- Public Profile -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2 text-base">
                            <ExternalLink class="h-5 w-5" />
                            Public Profile
                        </CardTitle>
                        <CardDescription
                            >Your public feedback page</CardDescription
                        >
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div
                            class="rounded-lg bg-muted p-3 font-mono text-sm break-all"
                        >
                            {{ quick_links.public_profile_url }}
                        </div>
                        <div class="flex gap-2">
                            <Button
                                variant="outline"
                                class="flex-1 gap-2"
                                @click="
                                    copyToClipboard(
                                        quick_links.public_profile_url,
                                        'Public profile link',
                                    )
                                "
                            >
                                <Copy class="h-4 w-4" />
                                Copy Link
                            </Button>
                            <Button
                                variant="outline"
                                @click="
                                    openInNewTab(quick_links.public_profile_url)
                                "
                            >
                                <ExternalLink class="h-4 w-4" />
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Charts Row -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <!-- Feedback Trend -->
                <Card class="lg:col-span-2">
                    <CardHeader>
                        <CardTitle>Feedback Trend (Last 30 Days)</CardTitle>
                        <CardDescription
                            >Daily feedback submissions</CardDescription
                        >
                    </CardHeader>
                    <CardContent>
                        <Chart
                            type="line"
                            :data="trendChartData"
                            :options="trendChartOptions"
                            class="h-[300px]"
                        />
                    </CardContent>
                </Card>

                <!-- Sentiment Distribution -->
                <Card>
                    <CardHeader>
                        <CardTitle>Sentiment Analysis</CardTitle>
                        <CardDescription
                            >Customer sentiment breakdown</CardDescription
                        >
                    </CardHeader>
                    <CardContent class="flex items-center justify-center">
                        <Chart
                            type="doughnut"
                            :data="sentimentChartData"
                            :options="sentimentChartOptions"
                            class="h-[300px] w-full"
                        />
                    </CardContent>
                </Card>
            </div>

            <!-- Rating Distribution & Recent Feedback -->
            <div class="grid gap-4 lg:grid-cols-3">
                <!-- Rating Distribution -->
                <Card>
                    <CardHeader>
                        <CardTitle>Rating Distribution</CardTitle>
                        <CardDescription
                            >Breakdown by star rating</CardDescription
                        >
                    </CardHeader>
                    <CardContent class="flex items-center justify-center">
                        <Chart
                            type="doughnut"
                            :data="ratingChartData"
                            :options="ratingChartOptions"
                            class="h-[300px] w-full"
                        />
                    </CardContent>
                </Card>

                <!-- Recent Feedback Table -->
                <Card class="lg:col-span-2">
                    <CardHeader>
                        <CardTitle>Recent Feedback</CardTitle>
                        <CardDescription
                            >Latest customer reviews</CardDescription
                        >
                    </CardHeader>
                    <CardContent>
                        <DataTable
                            :value="recent_feedback_list"
                            :rows="10"
                            stripedRows
                            class="text-sm"
                        >
                            <Column
                                field="customer_name"
                                header="Customer"
                                style="min-width: 150px"
                            >
                                <template #body="{ data }">
                                    <div>
                                        <div class="font-medium">
                                            {{
                                                data.customer_name ||
                                                'Anonymous'
                                            }}
                                        </div>
                                        <div
                                            class="text-xs text-muted-foreground"
                                        >
                                            {{ data.customer_email }}
                                        </div>
                                    </div>
                                </template>
                            </Column>
                            <Column
                                field="rating"
                                header="Rating"
                                style="min-width: 120px"
                            >
                                <template #body="{ data }">
                                    <Rating
                                        :modelValue="data.rating"
                                        :cancel="false"
                                        readonly
                                        class="text-sm"
                                    />
                                </template>
                            </Column>
                            <Column
                                field="comment"
                                header="Comment"
                                style="min-width: 200px"
                            >
                                <template #body="{ data }">
                                    <div class="line-clamp-2">
                                        {{ data.comment || 'No comment' }}
                                    </div>
                                </template>
                            </Column>
                            <Column
                                field="sentiment"
                                header="Sentiment"
                                style="min-width: 100px"
                            >
                                <template #body="{ data }">
                                    <Tag
                                        v-if="data.sentiment"
                                        class="capitalize"
                                        :severity="data.sentiment.severity"
                                        :value="data.sentiment.value"
                                    />
                                </template>
                            </Column>
                            <Column
                                field="moderation_status"
                                header="Status"
                                style="min-width: 100px"
                            >
                                <template #body="{ data }">
                                    <Tag
                                        :severity="
                                            data.moderation_status.severity
                                        "
                                        class="capitalize"
                                        :value="data.moderation_status.value"
                                    />
                                </template>
                            </Column>
                            <Column
                                field="submitted_at"
                                header="Submitted"
                                style="min-width: 120px"
                            >
                                <template #body="{ data }">
                                    <span class="text-xs">{{
                                        data.submitted_at
                                    }}</span>
                                </template>
                            </Column>
                        </DataTable>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
