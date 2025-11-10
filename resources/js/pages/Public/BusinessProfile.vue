<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import PublicLayout from '@/layouts/public/PublicLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import {
    CheckCircle2,
    Clock,
    Globe,
    Loader2,
    Mail,
    MapPin,
    MessageSquare,
    Minus,
    Phone,
    QrCode,
    Reply,
    Share2,
    Star,
    ThumbsDown,
    ThumbsUp,
} from 'lucide-vue-next';
import Dialog from 'primevue/dialog';
import Rating from 'primevue/rating';
import Textarea from 'primevue/textarea';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';
import { computed, ref } from 'vue';

interface Business {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    category: {
        id: number;
        name: string;
    };
    email: string | null;
    phone: string | null;
    address: string | null;
    city: string | null;
    state: string | null;
    country: string | null;
    postal_code: string | null;
    website: string | null;
    logo: string | null;
    brand_color_primary: string | null;
    qr_code_path: string | null;
}

interface Feedback {
    id: number;
    customer_name: string | null;
    customer_email: string | null;
    rating: number;
    comment: string | null;
    sentiment: string | null;
    reply_text: string | null;
    replied_at: string | null;
    replied_at_human: string | null;
    submitted_at: string;
    submitted_at_human: string | null;
}

interface Stats {
    total: number;
    average_rating: number;
    rating_distribution: {
        [key: number]: {
            count: number;
            percentage: number;
        };
    };
    sentiment_distribution: {
        positive: { count: number; percentage: number };
        neutral: { count: number; percentage: number };
        negative: { count: number; percentage: number };
    };
}

interface Props {
    business: Business;
    stats: Stats;
    feedbackFeed: {
        data: Feedback[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    displaySettings: {
        show_business_profile?: boolean;
        display_logo?: boolean;
        show_total_reviews?: boolean;
        show_average_rating?: boolean;
        show_verified_badge?: boolean;
    };
    feedbackSettings: {
        require_customer_name?: boolean;
        require_customer_email?: boolean;
        allow_anonymous_feedback?: boolean;
    };
}

const props = defineProps<Props>();

const toast = useToast();
const feedbackDialogVisible = ref(false);
const shareDialogVisible = ref(false);
const qrDialogVisible = ref(false);
const currentUrl = ref('');
const loadingMore = ref(false);

const allFeedback = ref<Feedback[]>([...props.feedbackFeed.data]);
const currentPage = ref(props.feedbackFeed.current_page);
const hasMorePages = ref(
    props.feedbackFeed.current_page < props.feedbackFeed.last_page,
);

// Feedback form
const feedbackForm = ref({
    rating: 0,
    comment: '',
    customer_name: '',
    customer_email: '',
});

const hasNativeShare = ref(false);

const appName = (import.meta.env.VITE_APP_NAME as string) ?? 'Laravel';

// Check for native share support on mount
if (typeof window !== 'undefined') {
    currentUrl.value = window.location.href;
    hasNativeShare.value = !!navigator.share;
}

const openFeedbackDialog = () => {
    feedbackDialogVisible.value = true;
};

const isFormValid = computed(() => {
    if (feedbackForm.value.rating === 0) {
        return false;
    }

    if (props.feedbackSettings?.require_customer_name) {
        if (!feedbackForm.value.customer_name || feedbackForm.value.customer_name.trim() === '') {
            return false;
        }
    }

    if (props.feedbackSettings?.require_customer_email) {
        if (!feedbackForm.value.customer_email || feedbackForm.value.customer_email.trim() === '') {
            return false;
        }
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(feedbackForm.value.customer_email)) {
            return false;
        }
    }

    return true;
});

const submitFeedback = () => {
    if (!isFormValid.value) {
        toast.add({
            severity: 'warn',
            summary: 'Missing Required Fields',
            detail: 'Please fill in all required fields before submitting',
            life: 3000,
        });
        return;
    }

    router.post(`/review/${props.business.slug}`, feedbackForm.value, {
        preserveScroll: true,
        onSuccess: () => {
            toast.add({
                severity: 'success',
                summary: 'Thank You!',
                detail: 'Your feedback has been submitted successfully',
                life: 3000,
            });
            feedbackDialogVisible.value = false;
            feedbackForm.value = {
                rating: 0,
                comment: '',
                customer_name: '',
                customer_email: '',
            };
        },
        onError: () => {
            toast.add({
                severity: 'error',
                summary: 'Error',
                detail: 'Failed to submit feedback. Please try again.',
                life: 3000,
            });
        },
    });
};

const shareLink = () => {
    shareDialogVisible.value = true;
};

const copyLink = () => {
    if (typeof window !== 'undefined') {
        const url = window.location.href;
        navigator.clipboard.writeText(url);
        toast.add({
            severity: 'success',
            summary: 'Link Copied',
            detail: 'Link copied to clipboard!',
            life: 3000,
        });
    }
};

const shareWhatsApp = () => {
    if (typeof window !== 'undefined') {
        const url = window.location.href;
        const text = `Check out ${props.business.name} on Telliqo!`;
        window.open(
            `https://wa.me/?text=${encodeURIComponent(text + ' ' + url)}`,
            '_blank',
        );
    }
};

const shareNative = () => {
    if (typeof window !== 'undefined' && navigator.share) {
        const url = window.location.href;
        navigator.share({
            title: props.business.name,
            text: `Check out ${props.business.name} on Telliqo!`,
            url: url,
        });
    }
};

const formatDate = (date: string) => {
    const now = new Date();
    const submitted = new Date(date);
    const diffTime = Math.abs(now.getTime() - submitted.getTime());
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

    if (diffDays === 0) return 'Today';
    if (diffDays === 1) return 'Yesterday';
    if (diffDays < 7) return `${diffDays} days ago`;
    if (diffDays < 30) return `${Math.floor(diffDays / 7)} weeks ago`;
    if (diffDays < 365) return `${Math.floor(diffDays / 30)} months ago`;
    return `${Math.floor(diffDays / 365)} years ago`;
};

const getInitials = (name: string | null) => {
    if (!name) return 'A';
    return name
        .split(' ')
        .map((n) => n[0])
        .join('')
        .toUpperCase()
        .substring(0, 2);
};

const getSentimentIcon = (sentiment: string | null) => {
    switch (sentiment) {
        case 'positive':
            return ThumbsUp;
        case 'negative':
            return ThumbsDown;
        default:
            return Minus;
    }
};

const getSentimentColor = (sentiment: string | null) => {
    switch (sentiment) {
        case 'positive':
            return 'text-green-600 dark:text-green-400';
        case 'negative':
            return 'text-red-600 dark:text-red-400';
        default:
            return 'text-muted-foreground';
    }
};

const loadMoreReviews = () => {
    if (loadingMore.value || !hasMorePages.value) return;

    loadingMore.value = true;
    const nextPage = currentPage.value + 1;

    router.get(
        `/b/${props.business.slug}`,
        {
            page: nextPage,
        },
        {
            preserveScroll: true,
            preserveState: true,
            only: ['feedbackFeed'],
            onSuccess: (page: any) => {
                const newFeedback = page.props.feedbackFeed.data;
                allFeedback.value = [...allFeedback.value, ...newFeedback];
                currentPage.value = page.props.feedbackFeed.current_page;
                hasMorePages.value =
                    page.props.feedbackFeed.current_page <
                    page.props.feedbackFeed.last_page;
                loadingMore.value = false;
            },
            onError: () => {
                loadingMore.value = false;
                toast.add({
                    severity: 'error',
                    summary: 'Error',
                    detail: 'Failed to load more reviews',
                    life: 3000,
                });
            },
        },
    );
};
</script>

<template>
    <Head :title="`${business.name} - Customer Feedback`" />

    <Toast />

    <PublicLayout >
        <!-- Header -->
        <div class="border-b bg-card">
            <div class="mx-auto max-w-5xl px-4 py-8">
                <div
                    class="flex flex-col items-start gap-6 md:flex-row md:items-center"
                >
                    <!-- Logo -->
                    <div
                        v-if="props.displaySettings.display_logo !== false"
                        class="flex-shrink-0"
                    >
                        <div
                            v-if="business.logo"
                            class="h-24 w-24 overflow-hidden rounded-xl border-2 border-border"
                        >
                            <img
                                :src="`/storage/${business.logo}`"
                                :alt="business.name"
                                class="h-full w-full object-cover"
                            />
                        </div>
                        <div
                            v-else
                            class="flex h-24 w-24 items-center justify-center rounded-xl bg-primary"
                        >
                            <span
                                class="text-3xl font-bold text-primary-foreground"
                                >{{ getInitials(business.name) }}</span
                            >
                        </div>
                    </div>

                    <!-- Business Info -->
                    <div class="flex-1">
                        <div class="mb-2 flex items-center gap-2">
                            <h1 class="text-3xl font-bold">
                                {{ business.name }}
                            </h1>
                            <Badge
                                v-if="
                                    props.displaySettings
                                        .show_verified_badge !== false
                                "
                                variant="outline"
                                class="gap-1"
                            >
                                <CheckCircle2 class="h-3 w-3 text-primary" />
                                Verified
                            </Badge>
                        </div>

                        <div
                            v-if="
                                props.displaySettings.show_average_rating !==
                                    false ||
                                props.displaySettings.show_total_reviews !==
                                    false
                            "
                            class="mb-3 flex items-center gap-3"
                        >
                            <div
                                v-if="
                                    props.displaySettings
                                        .show_average_rating !== false
                                "
                                class="flex items-center gap-1"
                            >
                                <Star
                                    v-for="i in 5"
                                    :key="i"
                                    :class="
                                        i <= Math.round(stats.average_rating)
                                            ? 'fill-yellow-400 text-yellow-400'
                                            : 'text-muted'
                                    "
                                    class="h-5 w-5"
                                />
                            </div>
                            <span
                                v-if="
                                    props.displaySettings
                                        .show_average_rating !== false
                                "
                                class="text-2xl font-bold"
                            >
                                {{ stats.average_rating }}
                            </span>
                            <span
                                v-if="
                                    props.displaySettings.show_total_reviews !==
                                    false
                                "
                                class="text-muted-foreground"
                            >
                                ({{ stats.total }}
                                {{ stats.total === 1 ? 'Review' : 'Reviews' }})
                            </span>
                        </div>

                        <div
                            class="flex items-center gap-2 text-sm text-muted-foreground"
                        >
                            <Badge variant="secondary">{{
                                business.category.name
                            }}</Badge>
                            <span
                                v-if="business.city"
                                class="flex items-center gap-1"
                            >
                                <MapPin class="h-3 w-3" />
                                {{ business.city
                                }}{{
                                    business.state ? `, ${business.state}` : ''
                                }}
                            </span>
                        </div>
                    </div>

                    <!-- CTAs -->
                    <div class="flex w-full flex-col gap-2 md:w-auto">
                        <Button
                            @click="openFeedbackDialog"
                            size="lg"
                            class="w-full gap-2 md:w-auto"
                        >
                            <MessageSquare class="h-4 w-4" />
                            Leave Feedback
                        </Button>
                        <div class="flex gap-2">
                            <Button
                                @click="shareLink"
                                variant="outline"
                                size="sm"
                                class="flex-1 gap-2"
                            >
                                <Share2 class="h-4 w-4" />
                                Share
                            </Button>
                            <Button
                                @click="qrDialogVisible = true"
                                variant="outline"
                                size="sm"
                                class="flex-1 gap-2"
                            >
                                <QrCode class="h-4 w-4" />
                                QR Code
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="mx-auto max-w-5xl px-4 py-8">
            <div class="grid gap-8 lg:grid-cols-3">
                <!-- Left Column (2/3) -->
                <div class="space-y-6 lg:col-span-2">
                    <!-- About Section -->
                    <Card
                        v-if="
                            props.displaySettings.show_business_profile !==
                                false &&
                            (business.description ||
                                business.phone ||
                                business.email ||
                                business.website ||
                                business.address)
                        "
                    >
                        <CardHeader>
                            <CardTitle>About {{ business.name }}</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <p
                                v-if="business.description"
                                class="text-foreground/80"
                            >
                                {{ business.description }}
                            </p>

                            <Separator
                                v-if="
                                    business.description &&
                                    (business.phone ||
                                        business.email ||
                                        business.website ||
                                        business.address)
                                "
                            />

                            <div class="space-y-3">
                                <div
                                    v-if="business.phone"
                                    class="flex items-center gap-3 text-sm"
                                >
                                    <Phone
                                        class="h-4 w-4 text-muted-foreground"
                                    />
                                    <a
                                        :href="`tel:${business.phone}`"
                                        class="text-primary hover:underline"
                                        >{{ business.phone }}</a
                                    >
                                </div>
                                <div
                                    v-if="business.email"
                                    class="flex items-center gap-3 text-sm"
                                >
                                    <Mail
                                        class="h-4 w-4 text-muted-foreground"
                                    />
                                    <a
                                        :href="`mailto:${business.email}`"
                                        class="text-primary hover:underline"
                                        >{{ business.email }}</a
                                    >
                                </div>
                                <div
                                    v-if="business.website"
                                    class="flex items-center gap-3 text-sm"
                                >
                                    <Globe
                                        class="h-4 w-4 text-muted-foreground"
                                    />
                                    <a
                                        :href="business.website"
                                        target="_blank"
                                        class="text-primary hover:underline"
                                        >Visit Website</a
                                    >
                                </div>
                                <div
                                    v-if="business.address"
                                    class="flex items-start gap-3 text-sm"
                                >
                                    <MapPin
                                        class="mt-0.5 h-4 w-4 text-muted-foreground"
                                    />
                                    <span class="text-foreground/80">
                                        {{ business.address }}
                                        <span v-if="business.city"
                                            >, {{ business.city }}</span
                                        >
                                        <span v-if="business.state"
                                            >, {{ business.state }}</span
                                        >
                                        <span v-if="business.postal_code">
                                            {{ business.postal_code }}</span
                                        >
                                    </span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Feedback Feed -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Customer Reviews</CardTitle>
                            <CardDescription
                                >{{ stats.total }} verified
                                reviews</CardDescription
                            >
                        </CardHeader>
                        <CardContent>
                            <div
                                v-if="allFeedback.length > 0"
                                class="space-y-6"
                            >
                                <div
                                    v-for="feedback in allFeedback"
                                    :key="feedback.id"
                                    class="border-b pb-6 last:border-0 last:pb-0"
                                >
                                    <!-- Feedback Header -->
                                    <div
                                        class="mb-3 flex items-start justify-between"
                                    >
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="flex h-10 w-10 items-center justify-center rounded-full bg-primary"
                                            >
                                                <span
                                                    class="text-sm font-bold text-primary-foreground"
                                                    >{{
                                                        getInitials(
                                                            feedback.customer_name,
                                                        )
                                                    }}</span
                                                >
                                            </div>
                                            <div>
                                                <div class="font-medium">
                                                    {{
                                                        feedback.customer_name ||
                                                        'Anonymous'
                                                    }}
                                                </div>
                                                <div
                                                    class="flex items-center gap-2 text-xs text-muted-foreground"
                                                >
                                                    <Clock class="h-3 w-3" />
                                                    {{
                                                        feedback.submitted_at_human ||
                                                        formatDate(
                                                            feedback.submitted_at,
                                                        )
                                                    }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <div
                                                class="flex items-center gap-1"
                                            >
                                                <Star
                                                    v-for="i in feedback.rating"
                                                    :key="i"
                                                    class="h-4 w-4 fill-yellow-400 text-yellow-400"
                                                />
                                            </div>
                                            <component
                                                v-if="feedback.sentiment"
                                                :is="
                                                    getSentimentIcon(
                                                        feedback.sentiment,
                                                    )
                                                "
                                                :class="[
                                                    'h-4 w-4',
                                                    getSentimentColor(
                                                        feedback.sentiment,
                                                    ),
                                                ]"
                                            />
                                        </div>
                                    </div>

                                    <!-- Feedback Comment -->
                                    <p
                                        v-if="feedback.comment"
                                        class="mb-3 leading-relaxed text-foreground/80"
                                    >
                                        {{ feedback.comment }}
                                    </p>

                                    <!-- Business Reply -->
                                    <div
                                        v-if="feedback.reply_text"
                                        class="mt-4 ml-13 rounded-lg border-l-4 border-primary bg-muted p-4"
                                    >
                                        <div
                                            class="mb-2 flex items-center gap-2 text-sm font-medium"
                                        >
                                            <Reply class="h-4 w-4" />
                                            Response from {{ business.name }}
                                        </div>
                                        <p class="text-sm text-foreground/80">
                                            {{ feedback.reply_text }}
                                        </p>
                                        <div
                                            class="mt-2 text-xs text-muted-foreground"
                                        >
                                            {{
                                                feedback.replied_at_human ||
                                                formatDate(feedback.replied_at!)
                                            }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Load More Button -->
                                <div
                                    v-if="hasMorePages"
                                    class="flex justify-center pt-4"
                                >
                                    <Button
                                        @click="loadMoreReviews"
                                        variant="outline"
                                        :disabled="loadingMore"
                                        class="gap-2"
                                    >
                                        <Loader2
                                            v-if="loadingMore"
                                            class="h-4 w-4 animate-spin"
                                        />
                                        {{
                                            loadingMore
                                                ? 'Loading...'
                                                : 'Load More Reviews'
                                        }}
                                    </Button>
                                </div>
                            </div>
                            <div v-else class="py-12 text-center">
                                <MessageSquare
                                    class="mx-auto mb-4 h-12 w-12 text-muted-foreground"
                                />
                                <p class="text-muted-foreground">
                                    No reviews yet. Be the first to leave
                                    feedback!
                                </p>
                                <Button
                                    @click="openFeedbackDialog"
                                    class="mt-4"
                                >
                                    Leave First Review
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Right Column (1/3) -->
                <div class="space-y-6">
                    <!-- Rating Distribution -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Rating Breakdown</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <div
                                v-for="rating in [5, 4, 3, 2, 1]"
                                :key="rating"
                                class="flex items-center gap-3"
                            >
                                <div class="flex w-12 items-center gap-1">
                                    <span class="text-sm font-medium">{{
                                        rating
                                    }}</span>
                                    <Star
                                        class="h-3 w-3 fill-yellow-400 text-yellow-400"
                                    />
                                </div>
                                <div
                                    class="h-2 flex-1 overflow-hidden rounded-full bg-muted"
                                >
                                    <div
                                        class="h-full rounded-full bg-yellow-400 transition-all"
                                        :style="{
                                            width: `${stats.rating_distribution[rating].percentage}%`,
                                        }"
                                    ></div>
                                </div>
                                <span
                                    class="w-12 text-right text-sm text-muted-foreground"
                                >
                                    {{
                                        stats.rating_distribution[rating]
                                            .percentage
                                    }}%
                                </span>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Sentiment Overview -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Sentiment Analysis</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <ThumbsUp
                                        class="h-4 w-4 text-green-600 dark:text-green-400"
                                    />
                                    <span class="text-sm">Positive</span>
                                </div>
                                <span
                                    class="text-sm font-medium text-green-600 dark:text-green-400"
                                >
                                    {{
                                        stats.sentiment_distribution.positive
                                            .percentage
                                    }}%
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <Minus
                                        class="h-4 w-4 text-muted-foreground"
                                    />
                                    <span class="text-sm">Neutral</span>
                                </div>
                                <span
                                    class="text-sm font-medium text-muted-foreground"
                                >
                                    {{
                                        stats.sentiment_distribution.neutral
                                            .percentage
                                    }}%
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <ThumbsDown
                                        class="h-4 w-4 text-red-600 dark:text-red-400"
                                    />
                                    <span class="text-sm">Negative</span>
                                </div>
                                <span
                                    class="text-sm font-medium text-red-600 dark:text-red-400"
                                >
                                    {{
                                        stats.sentiment_distribution.negative
                                            .percentage
                                    }}%
                                </span>
                            </div>
                        </CardContent>
                    </Card>

                    <Card class="border-primary/20">
                        <CardContent class="text-center">
                            <div class="flex items-center justify-start gap-0">
                                <AppLogoIcon class="size-25" />
                                <div class="text-3xl font-bold text-primary">
                                    {{ appName }}
                                </div>
                            </div>
                            <div>
                                <p class="text-xs text-muted-foreground">
                                    Powering authentic customer feedback
                                </p>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>

        <!-- Feedback Dialog -->
        <Dialog
            v-model:visible="feedbackDialogVisible"
            modal
            header="Share Your Experience"
            :style="{ width: '32rem' }"
        >
            <div class="space-y-6">
                <div class="text-center">
                    <p class="text-muted-foreground">
                        How was your experience at {{ business.name }}?
                    </p>
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-medium">Your Rating *</label>
                    <div class="flex justify-center">
                        <Rating
                            v-model="feedbackForm.rating"
                            :cancel="false"
                            class="text-3xl"
                        />
                    </div>
                </div>

                <div class="space-y-2">
                    <label for="comment" class="text-sm font-medium"
                        >Your Review</label
                    >
                    <Textarea
                        id="comment"
                        v-model="feedbackForm.comment"
                        rows="4"
                        placeholder="Share details of your experience..."
                        class="w-full"
                    />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label for="name" class="text-sm font-medium"
                            >Your Name
                            {{
                                props.feedbackSettings
                                    ?.require_customer_name
                                    ? '*'
                                    : '(Optional)'
                            }}
                        </label>
                        <input
                            id="name"
                            v-model="feedbackForm.customer_name"
                            type="text"
                            :required="
                                props.feedbackSettings
                                    ?.require_customer_name ?? false
                            "
                            placeholder="John Doe"
                            class="w-full rounded-md border bg-background px-3 py-2"
                        />
                    </div>
                    <div class="space-y-2">
                        <label for="email" class="text-sm font-medium"
                            >Email
                            {{
                                props.feedbackSettings
                                    ?.require_customer_email
                                    ? '*'
                                    : '(Optional)'
                            }}
                        </label>
                        <input
                            id="email"
                            v-model="feedbackForm.customer_email"
                            type="email"
                            :required="
                                props.feedbackSettings
                                    ?.require_customer_email ?? false
                            "
                            placeholder="john@example.com"
                            class="w-full rounded-md border bg-background px-3 py-2"
                        />
                    </div>
                </div>

                <div class="text-center text-xs text-muted-foreground">
                    Your feedback will be published immediately and will be
                    visible to everyone.
                </div>
            </div>

            <template #footer>
                <div class="flex justify-end gap-2">
                    <Button
                        variant="outline"
                        @click="feedbackDialogVisible = false"
                        >Cancel</Button
                    >
                    <Button
                        @click="submitFeedback"
                        :disabled="!isFormValid"
                    >
                        Submit Feedback
                    </Button>
                </div>
            </template>
        </Dialog>

        <!-- QR Code Dialog -->
        <Dialog
            v-model:visible="qrDialogVisible"
            modal
            header="Scan QR Code"
            :style="{ width: '24rem' }"
        >
            <div class="space-y-4 text-center">
                <div class="inline-block rounded-lg bg-white p-6">
                    <img
                        v-if="business.qr_code_path"
                        :src="`/storage/${business.qr_code_path}`"
                        :alt="`${business.name} QR Code`"
                        class="mx-auto h-48 w-48"
                    />
                    <div
                        v-else
                        class="flex h-48 w-48 items-center justify-center text-gray-400"
                    >
                        <QrCode class="h-24 w-24" />
                    </div>
                </div>
                <p class="text-sm text-gray-600">
                    Scan this QR code to leave feedback or share with customers
                </p>
            </div>
        </Dialog>

        <!-- Share Dialog -->
        <Dialog
            v-model:visible="shareDialogVisible"
            modal
            header="Share This Business"
            :style="{ width: '24rem' }"
        >
            <div class="space-y-4">
                <div class="flex gap-2">
                    <input
                        :value="currentUrl"
                        readonly
                        class="flex-1 rounded-md border bg-muted px-3 py-2 text-sm"
                    />
                    <Button @click="copyLink" variant="outline" size="sm">
                        Copy
                    </Button>
                </div>

                <Separator />

                <div class="grid grid-cols-2 gap-3">
                    <Button
                        @click="shareWhatsApp"
                        variant="outline"
                        class="gap-2"
                        size="sm"
                    >
                        <svg
                            class="h-5 w-5"
                            fill="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"
                            />
                        </svg>
                        WhatsApp
                    </Button>
                    <Button
                        v-if="hasNativeShare"
                        @click="shareNative"
                        variant="outline"
                        class="gap-2"
                        size="sm"
                    >
                        <Share2 class="h-4 w-4" />
                        More Options
                    </Button>
                </div>
            </div>
        </Dialog>
        
    </PublicLayout>
</template>
