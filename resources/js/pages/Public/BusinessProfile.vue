<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import AppLogo from '@/components/AppLogo.vue';
import Dialog from 'primevue/dialog';
import Textarea from 'primevue/textarea';
import Rating from 'primevue/rating';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';
import { 
    Star, 
    MapPin, 
    Phone, 
    Mail, 
    Globe, 
    Share2, 
    QrCode, 
    MessageSquare,
    Clock,
    CheckCircle2,
    ThumbsUp,
    ThumbsDown,
    Minus,
    Reply,
    Loader2
} from 'lucide-vue-next';
import AppLogoIcon from '@/components/AppLogoIcon.vue';

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
const hasMorePages = ref(props.feedbackFeed.current_page < props.feedbackFeed.last_page);

// Feedback form
const feedbackForm = ref({
    rating: 0,
    comment: '',
    customer_name: '',
    customer_email: '',
});

const brandColor = computed(() => props.business.brand_color_primary || 'hsl(0 0% 9%)');
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

const submitFeedback = () => {
    if (feedbackForm.value.rating === 0) {
        toast.add({
            severity: 'warn',
            summary: 'Rating Required',
            detail: 'Please select a rating before submitting',
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
    const url = window.location.href;
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
        window.open(`https://wa.me/?text=${encodeURIComponent(text + ' ' + url)}`, '_blank');
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
        .map(n => n[0])
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

    router.get(`/b/${props.business.slug}`, {
        page: nextPage,
    }, {
        preserveScroll: true,
        preserveState: true,
        only: ['feedbackFeed'],
        onSuccess: (page: any) => {
            const newFeedback = page.props.feedbackFeed.data;
            allFeedback.value = [...allFeedback.value, ...newFeedback];
            currentPage.value = page.props.feedbackFeed.current_page;
            hasMorePages.value = page.props.feedbackFeed.current_page < page.props.feedbackFeed.last_page;
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
    });
};
</script>

<template>
    <Head :title="`${business.name} - Customer Feedback`" />
    
    <Toast />
    
    <div class="min-h-screen bg-background">
        <!-- Header -->
        <div class="border-b bg-card">
            <div class="max-w-5xl mx-auto px-4 py-8">
                <div class="flex flex-col md:flex-row items-start md:items-center gap-6">
                    <!-- Logo -->
                    <div class="flex-shrink-0">
                        <div v-if="business.logo" class="w-24 h-24 rounded-xl overflow-hidden border-2 border-border">
                            <img :src="`/storage/${business.logo}`" :alt="business.name" class="w-full h-full object-cover" />
                        </div>
                        <div v-else class="w-24 h-24 rounded-xl bg-primary flex items-center justify-center">
                            <span class="text-3xl font-bold text-primary-foreground">{{ getInitials(business.name) }}</span>
                        </div>
                    </div>

                    <!-- Business Info -->
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-2">
                            <h1 class="text-3xl font-bold">{{ business.name }}</h1>
                            <Badge variant="outline" class="gap-1">
                                <CheckCircle2 class="h-3 w-3 text-primary" />
                                Verified
                            </Badge>
                        </div>
                        
                        <div class="flex items-center gap-3 mb-3">
                            <div class="flex items-center gap-1">
                                <Star v-for="i in 5" :key="i" 
                                    :class="i <= Math.round(stats.average_rating) ? 'fill-yellow-400 text-yellow-400' : 'text-muted'"
                                    class="h-5 w-5"
                                />
                            </div>
                            <span class="text-2xl font-bold">{{ stats.average_rating }}</span>
                            <span class="text-muted-foreground">({{ stats.total }} {{ stats.total === 1 ? 'Review' : 'Reviews' }})</span>
                        </div>

                        <div class="flex items-center gap-2 text-sm text-muted-foreground">
                            <Badge variant="secondary">{{ business.category.name }}</Badge>
                            <span v-if="business.city" class="flex items-center gap-1">
                                <MapPin class="h-3 w-3" />
                                {{ business.city }}{{ business.state ? `, ${business.state}` : '' }}
                            </span>
                        </div>
                    </div>

                    <!-- CTAs -->
                    <div class="flex flex-col gap-2 w-full md:w-auto">
                        <Button 
                            @click="openFeedbackDialog" 
                            size="lg"
                            class="gap-2 w-full md:w-auto"
                        >
                            <MessageSquare class="h-4 w-4" />
                            Leave Feedback
                        </Button>
                        <div class="flex gap-2">
                            <Button @click="shareLink" variant="outline" size="sm" class="gap-2 flex-1">
                                <Share2 class="h-4 w-4" />
                                Share
                            </Button>
                            <Button @click="qrDialogVisible = true" variant="outline" size="sm" class="gap-2 flex-1">
                                <QrCode class="h-4 w-4" />
                                QR Code
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="max-w-5xl mx-auto px-4 py-8">
            <div class="grid lg:grid-cols-3 gap-8">
                <!-- Left Column (2/3) -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- About Section -->
                    <Card v-if="business.description || business.phone || business.email || business.website || business.address">
                        <CardHeader>
                            <CardTitle>About {{ business.name }}</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <p v-if="business.description" class="text-foreground/80">
                                {{ business.description }}
                            </p>
                            
                            <Separator v-if="business.description && (business.phone || business.email || business.website || business.address)" />
                            
                            <div class="space-y-3">
                                <div v-if="business.phone" class="flex items-center gap-3 text-sm">
                                    <Phone class="h-4 w-4 text-muted-foreground" />
                                    <a :href="`tel:${business.phone}`" class="text-primary hover:underline">{{ business.phone }}</a>
                                </div>
                                <div v-if="business.email" class="flex items-center gap-3 text-sm">
                                    <Mail class="h-4 w-4 text-muted-foreground" />
                                    <a :href="`mailto:${business.email}`" class="text-primary hover:underline">{{ business.email }}</a>
                                </div>
                                <div v-if="business.website" class="flex items-center gap-3 text-sm">
                                    <Globe class="h-4 w-4 text-muted-foreground" />
                                    <a :href="business.website" target="_blank" class="text-primary hover:underline">Visit Website</a>
                                </div>
                                <div v-if="business.address" class="flex items-start gap-3 text-sm">
                                    <MapPin class="h-4 w-4 text-muted-foreground mt-0.5" />
                                    <span class="text-foreground/80">
                                        {{ business.address }}
                                        <span v-if="business.city">, {{ business.city }}</span>
                                        <span v-if="business.state">, {{ business.state }}</span>
                                        <span v-if="business.postal_code"> {{ business.postal_code }}</span>
                                    </span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Feedback Feed -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Customer Reviews</CardTitle>
                            <CardDescription>{{ stats.total }} verified reviews</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div v-if="allFeedback.length > 0" class="space-y-6">
                                <div v-for="feedback in allFeedback" :key="feedback.id" class="pb-6 border-b last:border-0 last:pb-0">
                                    <!-- Feedback Header -->
                                    <div class="flex items-start justify-between mb-3">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-full bg-primary flex items-center justify-center">
                                                <span class="text-sm font-bold text-primary-foreground">{{ getInitials(feedback.customer_name) }}</span>
                                            </div>
                                            <div>
                                                <div class="font-medium">
                                                    {{ feedback.customer_name || 'Anonymous' }}
                                                </div>
                                                <div class="flex items-center gap-2 text-xs text-muted-foreground">
                                                    <Clock class="h-3 w-3" />
                                                    {{ feedback.submitted_at_human || formatDate(feedback.submitted_at) }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <div class="flex items-center gap-1">
                                                <Star v-for="i in feedback.rating" :key="i" 
                                                    class="h-4 w-4 fill-yellow-400 text-yellow-400"
                                                />
                                            </div>
                                            <component 
                                                v-if="feedback.sentiment"
                                                :is="getSentimentIcon(feedback.sentiment)" 
                                                :class="['h-4 w-4', getSentimentColor(feedback.sentiment)]"
                                            />
                                        </div>
                                    </div>

                                    <!-- Feedback Comment -->
                                    <p v-if="feedback.comment" class="text-foreground/80 mb-3 leading-relaxed">
                                        {{ feedback.comment }}
                                    </p>

                                    <!-- Business Reply -->
                                    <div v-if="feedback.reply_text" class="mt-4 ml-13 p-4 bg-muted rounded-lg border-l-4 border-primary">
                                        <div class="flex items-center gap-2 mb-2 text-sm font-medium">
                                            <Reply class="h-4 w-4" />
                                            Response from {{ business.name }}
                                        </div>
                                        <p class="text-sm text-foreground/80">{{ feedback.reply_text }}</p>
                                        <div class="text-xs text-muted-foreground mt-2">{{ feedback.replied_at_human || formatDate(feedback.replied_at!) }}</div>
                                    </div>
                                </div>

                                <!-- Load More Button -->
                                <div v-if="hasMorePages" class="flex justify-center pt-4">
                                    <Button 
                                        @click="loadMoreReviews" 
                                        variant="outline"
                                        :disabled="loadingMore"
                                        class="gap-2"
                                    >
                                        <Loader2 v-if="loadingMore" class="h-4 w-4 animate-spin" />
                                        {{ loadingMore ? 'Loading...' : 'Load More Reviews' }}
                                    </Button>
                                </div>
                            </div>
                            <div v-else class="text-center py-12">
                                <MessageSquare class="h-12 w-12 mx-auto text-muted-foreground mb-4" />
                                <p class="text-muted-foreground">No reviews yet. Be the first to leave feedback!</p>
                                <Button @click="openFeedbackDialog" class="mt-4">
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
                            <div v-for="rating in [5, 4, 3, 2, 1]" :key="rating" class="flex items-center gap-3">
                                <div class="flex items-center gap-1 w-12">
                                    <span class="text-sm font-medium">{{ rating }}</span>
                                    <Star class="h-3 w-3 fill-yellow-400 text-yellow-400" />
                                </div>
                                <div class="flex-1 h-2 bg-muted rounded-full overflow-hidden">
                                    <div 
                                        class="h-full bg-yellow-400 rounded-full transition-all"
                                        :style="{ width: `${stats.rating_distribution[rating].percentage}%` }"
                                    ></div>
                                </div>
                                <span class="text-sm text-muted-foreground w-12 text-right">
                                    {{ stats.rating_distribution[rating].percentage }}%
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
                                    <ThumbsUp class="h-4 w-4 text-green-600 dark:text-green-400" />
                                    <span class="text-sm">Positive</span>
                                </div>
                                <span class="text-sm font-medium text-green-600 dark:text-green-400">
                                    {{ stats.sentiment_distribution.positive.percentage }}%
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <Minus class="h-4 w-4 text-muted-foreground" />
                                    <span class="text-sm">Neutral</span>
                                </div>
                                <span class="text-sm font-medium text-muted-foreground">
                                    {{ stats.sentiment_distribution.neutral.percentage }}%
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <ThumbsDown class="h-4 w-4 text-red-600 dark:text-red-400" />
                                    <span class="text-sm">Negative</span>
                                </div>
                                <span class="text-sm font-medium text-red-600 dark:text-red-400">
                                    {{ stats.sentiment_distribution.negative.percentage }}%
                                </span>
                            </div>
                        </CardContent>
                    </Card>

                    <Card class="border-primary/20">
                        <CardContent class="text-center">
                            <div class="flex items-center justify-start gap-0">
                                <AppLogoIcon class="size-25" />
                                <div class="text-3xl font-bold text-primary">{{ appName }}</div>
                            </div>
                            <div>
                                <p class="text-xs text-muted-foreground">Powering authentic customer feedback</p>
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
                        <Rating v-model="feedbackForm.rating" :cancel="false" class="text-3xl" />
                    </div>
                </div>

                <div class="space-y-2">
                    <label for="comment" class="text-sm font-medium">Your Review</label>
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
                        <label for="name" class="text-sm font-medium">Your Name (Optional)</label>
                        <input 
                            id="name"
                            v-model="feedbackForm.customer_name" 
                            type="text"
                            placeholder="John Doe"
                            class="w-full px-3 py-2 border rounded-md bg-background"
                        />
                    </div>
                    <div class="space-y-2">
                        <label for="email" class="text-sm font-medium">Email (Optional)</label>
                        <input 
                            id="email"
                            v-model="feedbackForm.customer_email" 
                            type="email"
                            placeholder="john@example.com"
                            class="w-full px-3 py-2 border rounded-md bg-background"
                        />
                    </div>
                </div>

                <div class="text-xs text-muted-foreground text-center">
                    Your feedback will be published immediately and will be visible to everyone.
                </div>
            </div>

            <template #footer>
                <div class="flex justify-end gap-2">
                    <Button variant="outline" @click="feedbackDialogVisible = false">Cancel</Button>
                    <Button 
                        @click="submitFeedback" 
                        :disabled="feedbackForm.rating === 0"
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
            <div class="text-center space-y-4">
                <div class="bg-white p-6 rounded-lg inline-block">
                    <img 
                        v-if="business.qr_code_path"
                        :src="`/storage/${business.qr_code_path}`" 
                        :alt="`${business.name} QR Code`"
                        class="w-48 h-48 mx-auto"
                    />
                    <div v-else class="w-48 h-48 flex items-center justify-center text-gray-400">
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
                        class="flex-1 px-3 py-2 border rounded-md bg-muted text-sm"
                    />
                    <Button @click="copyLink" variant="outline" size="sm">
                        Copy
                    </Button>
                </div>
                
                <Separator />
                
                <div class="grid grid-cols-2 gap-3">
                    <Button @click="shareWhatsApp" variant="outline" class="gap-2" size="sm">
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                        </svg>
                        WhatsApp
                    </Button>
                    <Button v-if="hasNativeShare" @click="shareNative" variant="outline" class="gap-2" size="sm">
                        <Share2 class="h-4 w-4" />
                        More Options
                    </Button>
                </div>
            </div>
        </Dialog>
    </div>
</template>
