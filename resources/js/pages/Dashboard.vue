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
    Copy,
    ExternalLink,
    Link2,
    MessageSquare,
    QrCode,
    Reply,
    Star,
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
        not_determined: number;
    };
    feedback_trend: Array<{
        date: string;
        count: number;
    }>;
    nps_trend: Array<{
        date: string;
        nps: number;
        promoters: number;
        passives: number;
        detractors: number;
    }>;
    rating_trend: Array<{
        date: string;
        avg_rating: number;
        total_feedback: number;
    }>;
    sentiment_trend: Array<{
        date: string;
        positive: number;
        neutral: number;
        negative: number;
        not_determined: number;
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

interface MetricsSummary {
    total_feedback: number;
    avg_rating: number;
    avg_nps: number;
    rating_trend: number;
    nps_trend: number;
    sentiment_distribution: {
        positive: number;
        neutral: number;
        negative: number;
        not_determined: number;
    };
    top_keywords: Array<{
        word: string;
        count: number;
    }>;
    nps_breakdown: {
        promoters: number;
        passives: number;
        detractors: number;
    };
}

interface CategoryAverage {
    avg_rating: number;
    avg_nps: number;
    total_feedback: number;
    sentiment_distribution: {
        positive: number;
        neutral: number;
        negative: number;
        not_determined: number;
    };
}

interface Props {
    stats: Stats;
    charts: Charts;
    metrics: MetricsSummary;
    daily_metrics: Array<any>;
    category_average: CategoryAverage | null;
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
const npsChartData = ref();
const npsChartOptions = ref();
const ratingTrendChartData = ref();
const ratingTrendChartOptions = ref();
const sentimentTrendChartData = ref();
const sentimentTrendChartOptions = ref();

onMounted(() => {
    setupTrendChart();
    setupRatingChart();
    setupSentimentChart();
    setupNPSChart();
    setupRatingTrendChart();
    setupSentimentTrendChart();
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
        labels: ['Positive', 'Neutral', 'Negative', 'Not Determined'],
        datasets: [
            {
                data: [
                    props.charts.sentiment_distribution.positive,
                    props.charts.sentiment_distribution.neutral,
                    props.charts.sentiment_distribution.negative,
                    props.charts.sentiment_distribution.not_determined,
                ],
                backgroundColor: [
                    'rgba(34, 197, 94, 0.6)', // green
                    'rgba(234, 179, 8, 0.6)', // yellow
                    'rgba(239, 68, 68, 0.6)', // red
                    'rgba(156, 163, 175, 0.6)', // gray
                ],
                borderColor: [
                    'rgb(34, 197, 94)',
                    'rgb(234, 179, 8)',
                    'rgb(239, 68, 68)',
                    'rgb(156, 163, 175)',
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

const setupNPSChart = () => {
    const documentStyle = getComputedStyle(document.documentElement);
    const textColor = documentStyle.getPropertyValue('--color-foreground');
    const textColorSecondary = documentStyle.getPropertyValue(
        '--color-muted-foreground',
    );
    const surfaceBorder = documentStyle.getPropertyValue('--color-border');

    npsChartData.value = {
        labels: props.charts.nps_trend.map((item) => item.date),
        datasets: [
            {
                label: 'NPS Score',
                data: props.charts.nps_trend.map((item) => item.nps),
                fill: true,
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                yAxisID: 'y',
            },
            {
                label: 'Promoters',
                data: props.charts.nps_trend.map((item) => item.promoters),
                borderColor: 'rgb(34, 197, 94)',
                backgroundColor: 'rgba(34, 197, 94, 0.5)',
                tension: 0.3,
                yAxisID: 'y1',
            },
            {
                label: 'Detractors',
                data: props.charts.nps_trend.map((item) => item.detractors),
                borderColor: 'rgb(239, 68, 68)',
                backgroundColor: 'rgba(239, 68, 68, 0.5)',
                tension: 0.3,
                yAxisID: 'y1',
            },
        ],
    };

    npsChartOptions.value = {
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
                type: 'linear',
                display: true,
                position: 'left',
                title: {
                    display: true,
                    text: 'NPS Score',
                    color: textColor,
                },
                ticks: {
                    color: textColorSecondary,
                },
                grid: {
                    color: surfaceBorder,
                    drawBorder: false,
                },
            },
            y1: {
                type: 'linear',
                display: true,
                position: 'right',
                title: {
                    display: true,
                    text: 'Count',
                    color: textColor,
                },
                ticks: {
                    color: textColorSecondary,
                    stepSize: 1,
                },
                grid: {
                    drawOnChartArea: false,
                },
            },
        },
    };
};

const setupRatingTrendChart = () => {
    const documentStyle = getComputedStyle(document.documentElement);
    const textColor = documentStyle.getPropertyValue('--color-foreground');
    const textColorSecondary = documentStyle.getPropertyValue(
        '--color-muted-foreground',
    );
    const surfaceBorder = documentStyle.getPropertyValue('--color-border');

    ratingTrendChartData.value = {
        labels: props.charts.rating_trend.map((item) => item.date),
        datasets: [
            {
                label: 'Average Rating',
                data: props.charts.rating_trend.map((item) => item.avg_rating),
                fill: true,
                borderColor: 'rgb(250, 204, 21)',
                backgroundColor: 'rgba(250, 204, 21, 0.1)',
                tension: 0.4,
            },
        ],
    };

    ratingTrendChartOptions.value = {
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
                max: 5,
                ticks: {
                    color: textColorSecondary,
                    stepSize: 0.5,
                },
                grid: {
                    color: surfaceBorder,
                    drawBorder: false,
                },
            },
        },
    };
};

const setupSentimentTrendChart = () => {
    const documentStyle = getComputedStyle(document.documentElement);
    const textColor = documentStyle.getPropertyValue('--color-foreground');
    const textColorSecondary = documentStyle.getPropertyValue(
        '--color-muted-foreground',
    );
    const surfaceBorder = documentStyle.getPropertyValue('--color-border');

    sentimentTrendChartData.value = {
        labels: props.charts.sentiment_trend.map((item) => item.date),
        datasets: [
            {
                label: 'Positive',
                data: props.charts.sentiment_trend.map((item) => item.positive),
                fill: true,
                borderColor: 'rgb(34, 197, 94)',
                backgroundColor: 'rgba(34, 197, 94, 0.2)',
                tension: 0.4,
            },
            {
                label: 'Neutral',
                data: props.charts.sentiment_trend.map((item) => item.neutral),
                fill: true,
                borderColor: 'rgb(234, 179, 8)',
                backgroundColor: 'rgba(234, 179, 8, 0.2)',
                tension: 0.4,
            },
            {
                label: 'Negative',
                data: props.charts.sentiment_trend.map((item) => item.negative),
                fill: true,
                borderColor: 'rgb(239, 68, 68)',
                backgroundColor: 'rgba(239, 68, 68, 0.2)',
                tension: 0.4,
            },
            {
                label: 'Not Determined',
                data: props.charts.sentiment_trend.map(
                    (item) => item.not_determined,
                ),
                fill: true,
                borderColor: 'rgb(156, 163, 175)',
                backgroundColor: 'rgba(156, 163, 175, 0.2)',
                tension: 0.4,
            },
        ],
    };

    sentimentTrendChartOptions.value = {
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
                stacked: true,
            },
        },
    };
};

const npsColor = computed(() => {
    const nps = props.metrics.avg_nps;
    if (nps >= 50)
        return {
            color: 'text-green-600',
            bg: 'bg-green-600',
            severity: 'success',
        };
    if (nps >= 0)
        return {
            color: 'text-yellow-600',
            bg: 'bg-yellow-600',
            severity: 'warning',
        };
    return { color: 'text-red-600', bg: 'bg-red-600', severity: 'danger' };
});

const npsCategory = computed(() => {
    const nps = props.metrics.avg_nps;
    if (nps >= 70) return 'Excellent';
    if (nps >= 50) return 'Great';
    if (nps >= 30) return 'Good';
    if (nps >= 0) return 'Needs Improvement';
    return 'Poor';
});

const topKeywords = computed(() => {
    return props.metrics.top_keywords.slice(0, 15);
});

const comparisonMetrics = computed(() => {
    if (!props.category_average) return null;

    return {
        rating_diff: (
            props.metrics.avg_rating - props.category_average.avg_rating
        ).toFixed(2),
        nps_diff: Math.round(
            props.metrics.avg_nps - props.category_average.avg_nps,
        ),
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
        <div class="flex h-full flex-1 flex-col gap-4 p-3 md:gap-6 md:p-6">
            <!-- Stats Cards - Simplified -->
            <div
                v-permission="'dashboard.stats'"
                class="grid gap-3 sm:grid-cols-2 md:gap-4 lg:grid-cols-4"
            >
                <!-- Total Feedback -->
                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between space-y-0 pb-2"
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
                        <div class="mt-1 flex items-center gap-1 text-xs">
                            <span class="text-green-600"
                                >{{ stats.total_published }} published</span
                            >
                            <span class="text-muted-foreground">•</span>
                            <span class="text-red-600"
                                >{{ stats.total_flagged }} flagged</span
                            >
                        </div>
                    </CardContent>
                </Card>

                <!-- Average Rating -->
                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between space-y-0 pb-2"
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

                <!-- NPS Score -->
                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between space-y-0 pb-2"
                    >
                        <CardTitle class="text-sm font-medium"
                            >NPS Score</CardTitle
                        >
                        <TrendingUp class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="flex items-baseline gap-2">
                            <div
                                :class="['text-2xl font-bold', npsColor.color]"
                            >
                                {{ Math.round(metrics.avg_nps) }}
                            </div>
                            <span class="text-xs text-muted-foreground">{{
                                npsCategory
                            }}</span>
                        </div>
                        <p class="mt-1 text-xs text-muted-foreground">
                            {{ metrics.nps_breakdown.promoters }} promoters,
                            {{ metrics.nps_breakdown.detractors }} detractors
                        </p>
                    </CardContent>
                </Card>

                <!-- Response Rate -->
                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between space-y-0 pb-2"
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
            </div>

            <!-- NPS Breakdown & Category Comparison -->
            <div class="grid gap-3 md:grid-cols-2 md:gap-4">
                <!-- NPS Breakdown -->
                <Card v-permission="'dashboard.nps-breakdown'">
                    <CardHeader>
                        <CardTitle class="text-base md:text-lg"
                            >NPS Breakdown (Last 30 Days)</CardTitle
                        >
                        <CardDescription
                            >Customer satisfaction distribution</CardDescription
                        >
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <!-- Promoters -->
                        <div class="space-y-2">
                            <div
                                class="flex items-center justify-between text-sm"
                            >
                                <span class="flex items-center gap-2">
                                    <div
                                        class="h-3 w-3 rounded-full bg-green-500"
                                    ></div>
                                    <span class="font-medium"
                                        >Promoters (5★)</span
                                    >
                                </span>
                                <span class="font-bold text-green-600">{{
                                    metrics.nps_breakdown.promoters
                                }}</span>
                            </div>
                            <div class="h-2 w-full rounded-full bg-muted">
                                <div
                                    class="h-2 rounded-full bg-green-500 transition-all"
                                    :style="{
                                        width: `${metrics.total_feedback > 0 ? (metrics.nps_breakdown.promoters / metrics.total_feedback) * 100 : 0}%`,
                                    }"
                                ></div>
                            </div>
                        </div>

                        <!-- Passives -->
                        <div class="space-y-2">
                            <div
                                class="flex items-center justify-between text-sm"
                            >
                                <span class="flex items-center gap-2">
                                    <div
                                        class="h-3 w-3 rounded-full bg-yellow-500"
                                    ></div>
                                    <span class="font-medium"
                                        >Passives (4★)</span
                                    >
                                </span>
                                <span class="font-bold text-yellow-600">{{
                                    metrics.nps_breakdown.passives
                                }}</span>
                            </div>
                            <div class="h-2 w-full rounded-full bg-muted">
                                <div
                                    class="h-2 rounded-full bg-yellow-500 transition-all"
                                    :style="{
                                        width: `${metrics.total_feedback > 0 ? (metrics.nps_breakdown.passives / metrics.total_feedback) * 100 : 0}%`,
                                    }"
                                ></div>
                            </div>
                        </div>

                        <!-- Detractors -->
                        <div class="space-y-2">
                            <div
                                class="flex items-center justify-between text-sm"
                            >
                                <span class="flex items-center gap-2">
                                    <div
                                        class="h-3 w-3 rounded-full bg-red-500"
                                    ></div>
                                    <span class="font-medium"
                                        >Detractors (≤3★)</span
                                    >
                                </span>
                                <span class="font-bold text-red-600">{{
                                    metrics.nps_breakdown.detractors
                                }}</span>
                            </div>
                            <div class="h-2 w-full rounded-full bg-muted">
                                <div
                                    class="h-2 rounded-full bg-red-500 transition-all"
                                    :style="{
                                        width: `${metrics.total_feedback > 0 ? (metrics.nps_breakdown.detractors / metrics.total_feedback) * 100 : 0}%`,
                                    }"
                                ></div>
                            </div>
                        </div>

                        <div class="border-t pt-2">
                            <p class="text-xs text-muted-foreground">
                                NPS = ((Promoters - Detractors) / Total
                                Feedback) × 100
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Category Comparison -->
                <Card
                    v-if="category_average"
                    v-permission="'dashboard.category-comparison'"
                >
                    <CardHeader>
                        <CardTitle class="text-base md:text-lg"
                            >Category Comparison</CardTitle
                        >
                        <CardDescription
                            >Your performance vs category
                            average</CardDescription
                        >
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <!-- Rating Comparison -->
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium"
                                    >Average Rating</span
                                >
                                <div class="flex items-center gap-2">
                                    <span
                                        class="text-sm text-muted-foreground"
                                        >{{
                                            category_average.avg_rating.toFixed(
                                                2,
                                            )
                                        }}</span
                                    >
                                    <span class="text-sm">→</span>
                                    <span class="text-sm font-bold">{{
                                        metrics.avg_rating.toFixed(2)
                                    }}</span>
                                    <span
                                        v-if="comparisonMetrics"
                                        :class="
                                            parseFloat(
                                                comparisonMetrics.rating_diff,
                                            ) >= 0
                                                ? 'text-green-600'
                                                : 'text-red-600'
                                        "
                                        class="text-xs font-medium"
                                    >
                                        {{
                                            parseFloat(
                                                comparisonMetrics.rating_diff,
                                            ) >= 0
                                                ? '+'
                                                : ''
                                        }}{{ comparisonMetrics.rating_diff }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <div class="h-2 flex-1 rounded-full bg-muted">
                                    <div
                                        class="h-2 rounded-full bg-yellow-500 transition-all"
                                        :style="{
                                            width: `${(category_average.avg_rating / 5) * 100}%`,
                                        }"
                                    ></div>
                                </div>
                                <div class="h-2 flex-1 rounded-full bg-muted">
                                    <div
                                        class="h-2 rounded-full bg-primary transition-all"
                                        :style="{
                                            width: `${(metrics.avg_rating / 5) * 100}%`,
                                        }"
                                    ></div>
                                </div>
                            </div>
                        </div>

                        <!-- NPS Comparison -->
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium"
                                    >NPS Score</span
                                >
                                <div class="flex items-center gap-2">
                                    <span
                                        class="text-sm text-muted-foreground"
                                        >{{
                                            Math.round(category_average.avg_nps)
                                        }}</span
                                    >
                                    <span class="text-sm">→</span>
                                    <span class="text-sm font-bold">{{
                                        Math.round(metrics.avg_nps)
                                    }}</span>
                                    <span
                                        v-if="comparisonMetrics"
                                        :class="
                                            comparisonMetrics.nps_diff >= 0
                                                ? 'text-green-600'
                                                : 'text-red-600'
                                        "
                                        class="text-xs font-medium"
                                    >
                                        {{
                                            comparisonMetrics.nps_diff >= 0
                                                ? '+'
                                                : ''
                                        }}{{ comparisonMetrics.nps_diff }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <div class="h-2 flex-1 rounded-full bg-muted">
                                    <div
                                        class="h-2 rounded-full bg-yellow-500 transition-all"
                                        :style="{
                                            width: `${Math.max(0, (category_average.avg_nps + 100) / 2)}%`,
                                        }"
                                    ></div>
                                </div>
                                <div class="h-2 flex-1 rounded-full bg-muted">
                                    <div
                                        :class="npsColor.bg"
                                        class="h-2 rounded-full transition-all"
                                        :style="{
                                            width: `${Math.max(0, (metrics.avg_nps + 100) / 2)}%`,
                                        }"
                                    ></div>
                                </div>
                            </div>
                        </div>

                        <!-- Total Feedback -->
                        <div class="border-t pt-2">
                            <div
                                class="flex items-center justify-between text-sm"
                            >
                                <span class="text-muted-foreground"
                                    >Total Feedback (30 days)</span
                                >
                                <div class="flex items-center gap-2">
                                    <span class="text-muted-foreground">{{
                                        category_average.total_feedback
                                    }}</span>
                                    <span>vs</span>
                                    <span class="font-bold">{{
                                        metrics.total_feedback
                                    }}</span>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Top Keywords (if no category) -->
                <Card v-else>
                    <CardHeader>
                        <CardTitle class="text-base md:text-lg"
                            >Top Keywords</CardTitle
                        >
                        <CardDescription
                            >Most mentioned words in feedback</CardDescription
                        >
                    </CardHeader>
                    <CardContent>
                        <div class="flex flex-wrap gap-2">
                            <Tag
                                v-for="keyword in topKeywords"
                                :key="keyword.word"
                                :value="`${keyword.word} (${keyword.count})`"
                                severity="secondary"
                                class="capitalize"
                            />
                        </div>
                        <p
                            v-if="topKeywords.length === 0"
                            class="py-4 text-center text-sm text-muted-foreground"
                        >
                            No keywords available yet
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Top Keywords Card (Always show if keywords exist) -->
            <Card
                v-if="topKeywords.length > 0 && category_average"
                v-permission="'dashboard.top-keywords'"
            >
                <CardHeader>
                    <CardTitle class="text-base md:text-lg"
                        >Top Keywords</CardTitle
                    >
                    <CardDescription
                        >Most mentioned words in customer feedback (Last 30
                        Days)</CardDescription
                    >
                </CardHeader>
                <CardContent>
                    <div class="flex flex-wrap gap-2">
                        <Tag
                            v-for="keyword in topKeywords"
                            :key="keyword.word"
                            :value="`${keyword.word} (${keyword.count})`"
                            severity="secondary"
                            class="capitalize"
                        />
                    </div>
                </CardContent>
            </Card>

            <!-- Advanced Analytics Section -->
            <div class="grid gap-3 md:gap-4 lg:grid-cols-2">
                <!-- NPS Trend Chart -->
                <Card
                    class="min-w-0 lg:col-span-2"
                    v-permission="'dashboard.nps-trend'"
                >
                    <CardHeader>
                        <CardTitle class="text-base md:text-lg"
                            >NPS Trend Analysis (Last 30 Days)</CardTitle
                        >
                        <CardDescription
                            >Net Promoter Score evolution with promoters and
                            detractors</CardDescription
                        >
                    </CardHeader>
                    <CardContent class="overflow-x-auto">
                        <Chart
                            type="line"
                            :data="npsChartData"
                            :options="npsChartOptions"
                            class="h-[250px] md:h-[350px]"
                        />
                    </CardContent>
                </Card>

                <!-- Rating Trend Chart -->
                <Card class="min-w-0" v-permission="'dashboard.rating-trend'">
                    <CardHeader>
                        <CardTitle class="text-base md:text-lg"
                            >Rating Trend</CardTitle
                        >
                        <CardDescription
                            >Average rating over time</CardDescription
                        >
                    </CardHeader>
                    <CardContent class="overflow-x-auto">
                        <Chart
                            type="line"
                            :data="ratingTrendChartData"
                            :options="ratingTrendChartOptions"
                            class="h-[250px] md:h-[300px]"
                        />
                    </CardContent>
                </Card>

                <!-- Sentiment Trend Chart -->
                <Card
                    class="min-w-0"
                    v-permission="'dashboard.sentiment-trend'"
                >
                    <CardHeader>
                        <CardTitle class="text-base md:text-lg"
                            >Sentiment Trend</CardTitle
                        >
                        <CardDescription
                            >Sentiment distribution over time</CardDescription
                        >
                    </CardHeader>
                    <CardContent class="overflow-x-auto">
                        <Chart
                            type="line"
                            :data="sentimentTrendChartData"
                            :options="sentimentTrendChartOptions"
                            class="h-[250px] md:h-[300px]"
                        />
                    </CardContent>
                </Card>
            </div>

            <!-- Performance Metrics Table -->
            <Card
                v-if="daily_metrics.length > 0"
                v-permission="'dashboard.daily-performance'"
            >
                <CardHeader>
                    <CardTitle class="text-base md:text-lg"
                        >Daily Performance Metrics</CardTitle
                    >
                    <CardDescription
                        >Detailed breakdown of last 10 days</CardDescription
                    >
                </CardHeader>
                <CardContent>
                    <DataTable
                        :value="daily_metrics.slice(0, 10)"
                        :rows="10"
                        stripedRows
                        class="text-sm"
                    >
                        <Column
                            field="metric_date"
                            header="Date"
                            style="min-width: 100px"
                        >
                            <template #body="{ data }">
                                <span class="font-medium">{{
                                    new Date(
                                        data.metric_date,
                                    ).toLocaleDateString('en-US', {
                                        month: 'short',
                                        day: 'numeric',
                                    })
                                }}</span>
                            </template>
                        </Column>
                        <Column
                            field="total_feedback"
                            header="Feedback"
                            style="min-width: 80px"
                        >
                            <template #body="{ data }">
                                <span class="font-semibold">{{
                                    data.total_feedback
                                }}</span>
                            </template>
                        </Column>
                        <Column
                            field="avg_rating"
                            header="Avg Rating"
                            style="min-width: 100px"
                        >
                            <template #body="{ data }">
                                <div class="flex items-center gap-1">
                                    <Star
                                        class="h-3 w-3 fill-yellow-400 text-yellow-400"
                                    />
                                    <span class="font-medium">{{
                                        data.avg_rating
                                    }}</span>
                                </div>
                            </template>
                        </Column>
                        <Column
                            field="nps"
                            header="NPS"
                            style="min-width: 80px"
                        >
                            <template #body="{ data }">
                                <Tag
                                    :value="data.nps"
                                    :severity="
                                        data.nps >= 50
                                            ? 'success'
                                            : data.nps >= 0
                                              ? 'warning'
                                              : 'danger'
                                    "
                                />
                            </template>
                        </Column>
                        <Column header="Promoters" style="min-width: 90px">
                            <template #body="{ data }">
                                <span class="font-medium text-green-600">{{
                                    data.promoters
                                }}</span>
                            </template>
                        </Column>
                        <Column header="Passives" style="min-width: 80px">
                            <template #body="{ data }">
                                <span class="font-medium text-yellow-600">{{
                                    data.passives
                                }}</span>
                            </template>
                        </Column>
                        <Column header="Detractors" style="min-width: 90px">
                            <template #body="{ data }">
                                <span class="font-medium text-red-600">{{
                                    data.detractors
                                }}</span>
                            </template>
                        </Column>
                        <Column header="Sentiment" style="min-width: 120px">
                            <template #body="{ data }">
                                <div class="flex gap-1 text-xs">
                                    <span class="text-green-600"
                                        >+{{ data.positive_count }}</span
                                    >
                                    <span class="text-gray-500"
                                        >~{{ data.neutral_count }}</span
                                    >
                                    <span>
                                        {{
                                            data.not_determined_count
                                                ? `~${data.not_determined_count}`
                                                : ''
                                        }}
                                    </span>
                                    <span class="text-red-600"
                                        >-{{ data.negative_count }}</span
                                    >
                                </div>
                            </template>
                        </Column>
                    </DataTable>
                </CardContent>
            </Card>

            <!-- Quick Links Section -->
            <div class="grid gap-3 sm:grid-cols-2 md:gap-4 lg:grid-cols-3">
                <!-- QR Code -->
                <Card v-permission="'dashboard.qr-code'">
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
                <Card v-permission="'dashboard.review-link'">
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
                <Card v-permission="'dashboard.public-profile'">
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
            <div class="grid gap-3 md:gap-4 lg:grid-cols-3">
                <!-- Feedback Trend -->
                <Card
                    class="min-w-0 lg:col-span-2"
                    v-permission="'dashboard.feedback-trend'"
                >
                    <CardHeader>
                        <CardTitle class="text-base md:text-lg"
                            >Feedback Trend (Last 30 Days)</CardTitle
                        >
                        <CardDescription
                            >Daily feedback submissions</CardDescription
                        >
                    </CardHeader>
                    <CardContent class="overflow-x-auto">
                        <Chart
                            type="line"
                            :data="trendChartData"
                            :options="trendChartOptions"
                            class="h-[250px] md:h-[300px]"
                        />
                    </CardContent>
                </Card>

                <!-- Sentiment Distribution -->
                <Card
                    class="min-w-0"
                    v-permission="'dashboard.sentiment-analysis'"
                >
                    <CardHeader>
                        <CardTitle class="text-base md:text-lg"
                            >Sentiment Analysis</CardTitle
                        >
                        <CardDescription
                            >Customer sentiment breakdown</CardDescription
                        >
                    </CardHeader>
                    <CardContent
                        class="flex items-center justify-center overflow-x-auto"
                    >
                        <Chart
                            type="doughnut"
                            :data="sentimentChartData"
                            :options="sentimentChartOptions"
                            class="h-[250px] w-full md:h-[300px]"
                        />
                    </CardContent>
                </Card>
            </div>

            <!-- Rating Distribution & Recent Feedback -->
            <div class="grid gap-3 md:gap-4 lg:grid-cols-3">
                <!-- Rating Distribution -->
                <Card
                    class="min-w-0"
                    v-permission="'dashboard.rating-distribution'"
                >
                    <CardHeader>
                        <CardTitle class="text-base md:text-lg"
                            >Rating Distribution</CardTitle
                        >
                        <CardDescription
                            >Breakdown by star rating</CardDescription
                        >
                    </CardHeader>
                    <CardContent
                        class="flex items-center justify-center overflow-x-auto"
                    >
                        <Chart
                            type="doughnut"
                            :data="ratingChartData"
                            :options="ratingChartOptions"
                            class="h-[250px] w-full md:h-[300px]"
                        />
                    </CardContent>
                </Card>

                <!-- Recent Feedback Table -->
                <Card
                    class="min-w-0 lg:col-span-2"
                    v-permission="'dashboard.recent-feedback'"
                >
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
