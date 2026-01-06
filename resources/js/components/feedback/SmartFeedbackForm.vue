<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { AlertCircle, ArrowRight, CheckCircle, Loader2, Star } from 'lucide-vue-next';
import Textarea from 'primevue/textarea';
import { router } from '@inertiajs/vue3';
import { computed, nextTick, ref, watch } from 'vue';

interface ExternalReviewSettings {
    enabled: boolean;
    google_review_url: string | null;
}

interface FeedbackSettings {
    require_customer_name?: boolean;
    require_customer_email?: boolean;
    allow_anonymous_feedback?: boolean;
}

interface Props {
    submitUrl: string;
    businessName: string;
    businessLogo?: string | null;
    brandColor?: string;
    customThankYouMessage?: string;
    feedbackSettings?: FeedbackSettings;
    externalReviewSettings?: ExternalReviewSettings;
    customerName?: string;
    customerEmail?: string;
    showCustomerFields?: boolean;
    title?: string;
    description?: string;
    compact?: boolean;
    acceptingSubmissions?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    feedbackSettings: () => ({}),
    externalReviewSettings: () => ({ enabled: false, google_review_url: null }),
    showCustomerFields: true,
    acceptingSubmissions: true,
    title: 'Share Your Experience',
    description: "We'd love to hear your feedback!",
    compact: false,
});

const emit = defineEmits<{
    (e: 'submitted', data: { rating: number; redirectedToExternal: boolean }): void;
    (e: 'success'): void;
}>();

const form = ref({
    rating: 0,
    comment: '',
    customer_name: props.customerName || '',
    customer_email: props.customerEmail || '',
});

const hoveredRating = ref(0);
const isSubmitting = ref(false);
const hasError = ref(false);
const errorMessage = ref('');
const currentStep = ref<'rating' | 'feedback' | 'success' | 'external-prompt' | 'not-accepting'>(
    props.acceptingSubmissions ? 'rating' : 'not-accepting'
);
const hasSubmitted = ref(false);

const displayRating = computed(() => hoveredRating.value || form.value.rating);

const isExternalReviewEnabled = computed(() => {
    return props.acceptingSubmissions && 
           props.externalReviewSettings?.enabled && 
           props.externalReviewSettings?.google_review_url;
});

const isPerfectRating = computed(() => form.value.rating === 5);
const isFourStarRating = computed(() => form.value.rating === 4);
const isNegativeRating = computed(() => form.value.rating >= 1 && form.value.rating <= 3);

const isFormValid = computed(() => {
    if (form.value.rating === 0) return false;
    
    if (isNegativeRating.value && currentStep.value === 'feedback') {
        if (!form.value.comment?.trim()) return false;
    }
    
    if (props.showCustomerFields) {
        if (props.feedbackSettings?.require_customer_name) {
            if (!form.value.customer_name?.trim()) return false;
        }
        if (props.feedbackSettings?.require_customer_email) {
            if (!form.value.customer_email?.trim()) return false;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(form.value.customer_email)) return false;
        }
    }
    
    return true;
});

const thankYouMessage = computed(() => {
    if (isNegativeRating.value) {
        return 'Your feedback has been sent directly to the management team. We take this very seriously and will look into it immediately.';
    }
    return props.customThankYouMessage || 
           'Thank you for your feedback! We truly appreciate you taking the time to share your experience with us.';
});

const setRating = (rating: number) => {
    form.value.rating = rating;
};

const hoverRating = (rating: number) => {
    hoveredRating.value = rating;
};

const resetHover = () => {
    hoveredRating.value = 0;
};

const handleRatingSelected = () => {
    if (form.value.rating === 0) return;
    
    if (isExternalReviewEnabled.value && isPerfectRating.value) {
        currentStep.value = 'external-prompt';
    } else {
        currentStep.value = 'feedback';
    }
};

const saveRatingForExternalRedirect = () => {
    if (hasSubmitted.value) return;
    
    isSubmitting.value = true;
    hasError.value = false;
    
    const payload: Record<string, any> = {
        rating: form.value.rating,
        comment: null,
        is_external_redirect: true,
    };
    
    if (props.customerName) payload.customer_name = props.customerName;
    if (props.customerEmail) payload.customer_email = props.customerEmail;
    
    router.post(props.submitUrl, payload, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            hasSubmitted.value = true;
            isSubmitting.value = false;
            emit('submitted', { rating: form.value.rating, redirectedToExternal: true });
            currentStep.value = 'success';
            emit('success');
        },
        onError: () => {
            isSubmitting.value = false;
            currentStep.value = 'success';
            emit('success');
        },
    });
};

const submitFeedback = () => {
    if (!isFormValid.value || hasSubmitted.value || !props.acceptingSubmissions) return;
    
    isSubmitting.value = true;
    hasError.value = false;
    
    const payload: Record<string, any> = {
        rating: form.value.rating,
        comment: form.value.comment || null,
    };
    
    if (props.showCustomerFields) {
        if (form.value.customer_name) payload.customer_name = form.value.customer_name;
        if (form.value.customer_email) payload.customer_email = form.value.customer_email;
    }
    
    router.post(props.submitUrl, payload, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            hasSubmitted.value = true;
            isSubmitting.value = false;
            
            emit('submitted', { 
                rating: form.value.rating, 
                redirectedToExternal: !!(isExternalReviewEnabled.value && isFourStarRating.value)
            });
            
            if (isExternalReviewEnabled.value && isFourStarRating.value) {
                currentStep.value = 'external-prompt';
            } else {
                currentStep.value = 'success';
                emit('success');
            }
        },
        onError: (errors) => {
            isSubmitting.value = false;
            hasError.value = true;
            errorMessage.value = Object.values(errors).flat().join(', ') || 'Failed to submit feedback.';
        },
    });
};

const openExternalReview = () => {
    if (props.externalReviewSettings?.google_review_url) {
        window.open(props.externalReviewSettings.google_review_url, '_blank');
    }
    saveRatingForExternalRedirect();
};

const skipExternalReview = () => {
    currentStep.value = 'success';
    emit('success');
};

watch(() => form.value.rating, (newRating, oldRating) => {
    if (oldRating === 0 && newRating > 0 && currentStep.value === 'rating') {
        handleRatingSelected();
    }
});

watch(() => currentStep.value, (newStep) => {
    if (newStep === 'feedback') {
        nextTick(() => {
            const textarea = document.getElementById('comment') as HTMLTextAreaElement;
            if (textarea) {
                textarea.focus();
            }
        });
    }
});
</script>

<template>
    <div class="w-full">
        <component :is="compact ? 'div' : Card" v-if="currentStep === 'rating'" :class="compact ? 'p-4' : ''">
            <component :is="compact ? 'div' : CardHeader" class="text-center" :class="compact ? 'mb-4' : ''">
                <component :is="compact ? 'h3' : CardTitle" :class="compact ? 'text-lg font-semibold' : ''">{{ title }}</component>
                <component :is="compact ? 'p' : CardDescription" :class="compact ? 'text-sm text-muted-foreground mt-1' : ''">{{ description }}</component>
            </component>
            <component :is="compact ? 'div' : CardContent">
                <div class="space-y-6">
                    <div class="space-y-3">
                        <Label class="block text-center text-base">
                            How would you rate your experience?
                        </Label>
                        <div class="flex justify-center gap-2">
                            <button
                                v-for="rating in 5"
                                :key="rating"
                                type="button"
                                @click="setRating(rating)"
                                @mouseenter="hoverRating(rating)"
                                @mouseleave="resetHover"
                                class="transition-transform hover:scale-110 focus:outline-none"
                            >
                                <Star
                                    :class="[
                                        rating <= displayRating ? 'fill-yellow-400 text-yellow-400' : 'text-gray-300',
                                        compact ? 'h-10 w-10 sm:h-12 sm:w-12' : 'h-14 w-14 sm:h-16 sm:w-16'
                                    ]"
                                />
                            </button>
                        </div>
                        <p class="text-center text-sm text-muted-foreground">
                            Tap a star to rate
                        </p>
                    </div>
                    
                    <Button 
                        v-if="form.rating > 0"
                        @click="handleRatingSelected"
                        :disabled="isSubmitting"
                        class="w-full"
                        size="lg"
                    >
                        <Loader2 v-if="isSubmitting" class="mr-2 h-4 w-4 animate-spin" />
                        Continue
                        <ArrowRight class="ml-2 h-4 w-4" />
                    </Button>
                </div>
            </component>
        </component>
        
        <component :is="compact ? 'div' : Card" v-else-if="currentStep === 'feedback'" :class="compact ? 'p-4' : ''">
            <component :is="compact ? 'div' : CardHeader" :class="compact ? 'mb-4' : ''">
                <component :is="compact ? 'h3' : CardTitle" :class="compact ? 'text-lg font-semibold' : ''">
                    <template v-if="isNegativeRating">We're Sorry to Hear That</template>
                    <template v-else>Tell Us More</template>
                </component>
                <component :is="compact ? 'p' : CardDescription" :class="compact ? 'text-sm text-muted-foreground mt-1' : ''">
                    <template v-if="isNegativeRating">Please tell us what went wrong so we can fix it privately.</template>
                    <template v-else>Share any additional details about your experience.</template>
                </component>
            </component>
            <component :is="compact ? 'div' : CardContent">
                <form @submit.prevent="submitFeedback" class="space-y-6">
                    <div class="flex justify-center gap-1">
                        <Star
                            v-for="star in 5"
                            :key="star"
                            :class="star <= form.rating ? 'fill-yellow-400 text-yellow-400' : 'text-gray-300'"
                            class="h-8 w-8"
                        />
                    </div>
                    
                    <div v-if="showCustomerFields && (feedbackSettings?.require_customer_name || feedbackSettings?.allow_anonymous_feedback === false)" class="space-y-2">
                        <Label for="customer_name">
                            Your Name
                            <span v-if="feedbackSettings?.require_customer_name" class="text-destructive">*</span>
                        </Label>
                        <Input
                            id="customer_name"
                            v-model="form.customer_name"
                            placeholder="Enter your name"
                            :required="feedbackSettings?.require_customer_name"
                        />
                    </div>
                    
                    <div v-if="showCustomerFields && feedbackSettings?.require_customer_email" class="space-y-2">
                        <Label for="customer_email">
                            Your Email
                            <span class="text-destructive">*</span>
                        </Label>
                        <Input
                            id="customer_email"
                            v-model="form.customer_email"
                            type="email"
                            placeholder="Enter your email"
                            required
                        />
                    </div>
                    
                    <div class="space-y-2">
                        <Label for="comment">
                            Your Feedback
                            <span v-if="isNegativeRating" class="text-destructive">*</span>
                        </Label>
                        <Textarea
                            id="comment"
                            v-model="form.comment"
                            :rows="compact ? 3 : 5"
                            placeholder="Tell us about your experience..."
                            class="w-full"
                            :required="isNegativeRating"
                        />
                        <p class="text-xs text-muted-foreground">
                            <template v-if="isNegativeRating">Your feedback will be shared privately with our team.</template>
                            <template v-else>Share any details about what you liked or what could be improved.</template>
                        </p>
                    </div>
                    
                    <p v-if="hasError" class="text-sm text-destructive">{{ errorMessage }}</p>
                    
                    <Button 
                        type="submit"
                        :disabled="!isFormValid || isSubmitting || !acceptingSubmissions"
                        class="w-full"
                        size="lg"
                    >
                        <Loader2 v-if="isSubmitting" class="mr-2 h-4 w-4 animate-spin" />
                        {{ isSubmitting ? 'Submitting...' : 'Submit Feedback' }}
                    </Button>
                </form>
            </component>
        </component>
        
        <component :is="compact ? 'div' : Card" v-else-if="currentStep === 'external-prompt'" class="text-center" :class="compact ? 'p-4' : ''">
            <component :is="compact ? 'div' : CardHeader" :class="compact ? 'mb-4' : ''">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/30">
                    <CheckCircle class="h-10 w-10 text-green-600 dark:text-green-400" />
                </div>
                <component :is="compact ? 'h3' : CardTitle" :class="compact ? 'text-xl font-semibold' : 'text-2xl'">Thank You!</component>
                <component :is="compact ? 'p' : CardDescription" :class="compact ? 'text-sm text-muted-foreground mt-1' : 'text-base'">
                    We're glad you had a great experience!
                </component>
            </component>
            <component :is="compact ? 'div' : CardContent" class="space-y-6">
                <div class="rounded-lg border bg-muted/50 p-6">
                    <p class="mb-4 text-sm text-muted-foreground">
                        Would you mind sharing your rating on Google? It really helps us grow!
                    </p>
                    
                    <Button 
                        @click="openExternalReview"
                        size="lg"
                        class="w-full gap-2 bg-[#4285F4] hover:bg-[#3367D6] text-white"
                    >
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#fff"/>
                            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#fff"/>
                            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#fff"/>
                            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#fff"/>
                        </svg>
                        Post My Review on Google
                    </Button>
                </div>
                
                <button
                    @click="skipExternalReview"
                    class="text-sm text-muted-foreground underline-offset-4 hover:underline"
                >
                    No thanks, I'm done
                </button>
            </component>
        </component>
        
        <component :is="compact ? 'div' : Card" v-else-if="currentStep === 'success'" class="text-center" :class="compact ? 'p-4' : ''">
            <component :is="compact ? 'div' : CardHeader" :class="compact ? 'mb-4' : ''">
                <div 
                    class="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-full text-white"
                    :style="{ backgroundColor: brandColor || '#22c55e' }"
                >
                    <CheckCircle class="h-12 w-12" />
                </div>
                <component :is="compact ? 'h3' : CardTitle" :class="compact ? 'text-xl font-semibold' : 'text-2xl'">Thank You!</component>
            </component>
            <component :is="compact ? 'div' : CardContent" class="space-y-6">
                <div class="flex justify-center gap-1">
                    <Star
                        v-for="star in 5"
                        :key="star"
                        :class="star <= form.rating ? 'fill-yellow-400 text-yellow-400' : 'text-gray-300'"
                        class="h-8 w-8"
                    />
                </div>
                
                <p class="text-lg text-muted-foreground">{{ thankYouMessage }}</p>
                
                <div class="rounded-lg border bg-muted/50 p-4">
                    <p class="font-semibold">{{ businessName }}</p>
                </div>
            </component>
        </component>
        
        <component :is="compact ? 'div' : Card" v-else-if="currentStep === 'not-accepting'" class="text-center" :class="compact ? 'p-4' : ''">
            <component :is="compact ? 'div' : CardHeader" :class="compact ? 'mb-4' : ''">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-900/30">
                    <AlertCircle class="h-10 w-10 text-amber-600 dark:text-amber-400" />
                </div>
                <component :is="compact ? 'h3' : CardTitle" :class="compact ? 'text-xl font-semibold' : 'text-2xl'">Feedback Unavailable</component>
                <component :is="compact ? 'p' : CardDescription" :class="compact ? 'text-sm text-muted-foreground mt-1' : 'text-base'">
                    This business is not currently accepting feedback submissions.
                </component>
            </component>
            <component :is="compact ? 'div' : CardContent">
                <div class="rounded-lg border bg-muted/50 p-4">
                    <p class="font-semibold">{{ businessName }}</p>
                </div>
            </component>
        </component>
    </div>
</template>
