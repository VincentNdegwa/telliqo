<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import PublicLayout from '@/layouts/public/PublicLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { format } from 'date-fns';
import { Building2, Calendar, Send, Star } from 'lucide-vue-next';
import Textarea from 'primevue/textarea';
import { computed, ref } from 'vue';

interface ReviewRequest {
    id: number;
    business: {
        name: string;
        logo_url: string | null;
    };
    customer: {
        name: string;
    };
    subject: string;
    message: string;
    expires_at: string;
}

interface Props {
    reviewRequest: ReviewRequest;
    token: string;
    acceptingFeedbackSubmissions: boolean;
}

const props = defineProps<Props>();

const form = useForm({
    rating: 0,
    comment: '',
});

const hoveredRating = ref(0);

const setRating = (rating: number) => {
    form.rating = rating;
};

const hoverRating = (rating: number) => {
    hoveredRating.value = rating;
};

const resetHover = () => {
    hoveredRating.value = 0;
};

const displayRating = computed(() => hoveredRating.value || form.rating);

const isFormValid = computed(() => {
    return form.rating > 0 && form.comment.trim() !== '';
});

const submit = () => {
    if (!props.acceptingFeedbackSubmissions) {
        return;
    }
    if (!isFormValid.value) {
        return;
    }
    form.post(`/r/${props.token}`, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="`Share Your Experience - ${reviewRequest.business.name}`" />

    <PublicLayout>
        <div class="mx-auto mt-4 max-w-2xl">
            <!-- Business Header -->
            <div class="mb-8 text-center">
                <div
                    v-if="reviewRequest.business.logo_url"
                    class="mb-4 flex justify-center"
                >
                    <img
                        :src="reviewRequest.business.logo_url"
                        :alt="reviewRequest.business.name"
                        class="h-16 w-16 rounded-full object-cover"
                    />
                </div>
                <div v-else class="mb-4 flex justify-center">
                    <div
                        class="flex h-16 w-16 items-center justify-center rounded-full bg-primary text-primary-foreground"
                    >
                        <Building2 class="h-8 w-8" />
                    </div>
                </div>
                <h1 class="text-3xl font-bold">
                    {{ reviewRequest.business.name }}
                </h1>
                <p class="mt-2 text-muted-foreground">
                    Hello {{ reviewRequest.customer.name }}, share your
                    experience
                </p>
            </div>

            <!-- Feedback Form -->
            <Card>
                <CardHeader>
                    <CardTitle>{{ reviewRequest.subject }}</CardTitle>
                    <CardDescription class="whitespace-pre-wrap">
                        {{ reviewRequest.message }}
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Rating -->
                        <div class="space-y-2">
                            <Label class="block text-center">
                                How would you rate your experience? *
                            </Label>
                            <div class="flex justify-center gap-2">
                                <button
                                    v-for="rating in 5"
                                    :key="rating"
                                    type="button"
                                    @click="setRating(rating)"
                                    @mouseenter="hoverRating(rating)"
                                    @mouseleave="resetHover"
                                    class="transition-transform hover:scale-110"
                                >
                                    <Star
                                        :class="
                                            rating <= displayRating
                                                ? 'fill-yellow-400 text-yellow-400'
                                                : 'text-gray-300'
                                        "
                                        class="h-12 w-12"
                                    />
                                </button>
                            </div>
                            <p
                                v-if="form.errors.rating"
                                class="text-center text-sm text-destructive"
                            >
                                {{ form.errors.rating }}
                            </p>
                        </div>

                        <!-- Comment -->
                        <div class="space-y-2">
                            <Label for="comment">Your Feedback *</Label>
                            <Textarea
                                id="comment"
                                v-model="form.comment"
                                :rows="6"
                                placeholder="Tell us about your experience..."
                                :invalid="!!form.errors.comment"
                                class="w-full"
                                required
                            />
                            <p class="text-xs text-muted-foreground">
                                Share any details about what you liked or what
                                could be improved.
                            </p>
                            <p
                                v-if="form.errors.comment"
                                class="text-sm text-destructive"
                            >
                                {{ form.errors.comment }}
                            </p>
                        </div>

                        <!-- Submit Button -->
                        <div
                            v-tooltip="
                                !acceptingFeedbackSubmissions
                                    ? 'This business is not currently accepting feedback submissions.'
                                    : ''
                            "
                        >
                            <Button
                                type="submit"
                                :disabled="
                                    !isFormValid ||
                                    form.processing ||
                                    !acceptingFeedbackSubmissions
                                "
                                class="w-full"
                            >
                                <Send class="mr-2 h-4 w-4" />
                                {{
                                    form.processing
                                        ? 'Submitting...'
                                        : 'Submit Feedback'
                                }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>

            <!-- Footer Info -->
            <div class="mt-6 text-center text-xs text-muted-foreground">
                <div class="mb-2 flex items-center justify-center gap-2">
                    <Calendar class="h-3 w-3" />
                    <p>
                        This review request expires on
                        <span class="font-medium">{{
                            format(
                                new Date(reviewRequest.expires_at),
                                'MMMM d, yyyy',
                            )
                        }}</span>
                    </p>
                </div>
                <p>
                    <a
                        :href="`/r/${token}/opt-out`"
                        class="text-muted-foreground underline underline-offset-4 hover:text-foreground"
                    >
                        Unsubscribe from future review requests
                    </a>
                </p>
            </div>

            <!-- Powered By -->
            <div class="mt-8 text-center text-sm text-muted-foreground">
                <p>Powered by <span class="font-semibold">Telliqo</span></p>
            </div>
        </div>
    </PublicLayout>
</template>
