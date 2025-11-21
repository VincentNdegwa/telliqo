<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import PublicLayout from '@/layouts/public/PublicLayout.vue';
import feedback from '@/routes/feedback';
import { Business } from '@/types/business';
import { Head, useForm } from '@inertiajs/vue3';
import { Building2, Send, Star } from 'lucide-vue-next';
import Textarea from 'primevue/textarea';
import { computed, ref } from 'vue';

interface Props {
    business: Business;
    feedbackSettings: {
        require_customer_name?: boolean;
        require_customer_email?: boolean;
        allow_anonymous_feedback?: boolean;
    };
    acceptingFeedbackSubmissions: boolean;
}

const props = defineProps<Props>();

const form = useForm({
    customer_name: '',
    customer_email: '',
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
    // Rating is always required
    if (form.rating === 0) {
        return false;
    }

    // Comment is always required
    if (!form.comment || form.comment.trim() === '') {
        return false;
    }

    // Check if customer name is required and provided
    if (props.feedbackSettings?.require_customer_name) {
        if (!form.customer_name || form.customer_name.trim() === '') {
            return false;
        }
    }

    // Check if customer email is required and provided (with basic validation)
    if (props.feedbackSettings?.require_customer_email) {
        if (!form.customer_email || form.customer_email.trim() === '') {
            return false;
        }
        // Basic email validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(form.customer_email)) {
            return false;
        }
    }

    return true;
});

const submit = () => {
    if (!isFormValid.value) {
        return;
    }
    form.post(feedback.store.url(props.business));
};
</script>

<template>
    <Head :title="`Leave Feedback - ${business.name}`" />

    <PublicLayout>
        <div class="mx-auto mt-4 max-w-2xl">
            <!-- Business Header -->
            <div class="mb-8 text-center">
                <div class="mb-4 flex justify-center">
                    <div
                        class="flex h-16 w-16 items-center justify-center rounded-full bg-primary text-primary-foreground"
                    >
                        <Building2 class="h-8 w-8" />
                    </div>
                </div>
                <h1 class="text-3xl font-bold">{{ business.name }}</h1>
                <p
                    v-if="business.description"
                    class="mt-2 text-muted-foreground"
                >
                    {{ business.description }}
                </p>
            </div>

            <!-- Feedback Form -->
            <Card>
                <CardHeader>
                    <CardTitle>Share Your Experience</CardTitle>
                    <CardDescription>
                        We'd love to hear your feedback!
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Rating -->
                        <div class="space-y-2">
                            <Label class="block text-center"
                                >How would you rate your experience?</Label
                            >
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
                                :rows="4"
                                placeholder="Tell us about your experience..."
                                :invalid="!!form.errors.comment"
                                class="w-full"
                                required
                            />
                            <p
                                v-if="form.errors.comment"
                                class="text-sm text-destructive"
                            >
                                {{ form.errors.comment }}
                            </p>
                        </div>

                        <!-- Customer Name -->
                        <div class="space-y-2">
                            <Label for="customer_name">
                                Your Name
                                {{
                                    props.feedbackSettings
                                        ?.require_customer_name
                                        ? '*'
                                        : '(Optional)'
                                }}
                            </Label>
                            <Input
                                id="customer_name"
                                v-model="form.customer_name"
                                :class="{
                                    'border-destructive':
                                        form.errors.customer_name,
                                }"
                                :required="
                                    props.feedbackSettings
                                        ?.require_customer_name ?? false
                                "
                                placeholder="John Doe"
                            />
                            <p
                                v-if="form.errors.customer_name"
                                class="text-sm text-destructive"
                            >
                                {{ form.errors.customer_name }}
                            </p>
                        </div>

                        <!-- Customer Email -->
                        <div class="space-y-2">
                            <Label for="customer_email">
                                Your Email
                                {{
                                    props.feedbackSettings
                                        ?.require_customer_email
                                        ? '*'
                                        : '(Optional)'
                                }}
                            </Label>
                            <Input
                                id="customer_email"
                                v-model="form.customer_email"
                                type="email"
                                :class="{
                                    'border-destructive':
                                        form.errors.customer_email,
                                }"
                                :required="
                                    props.feedbackSettings
                                        ?.require_customer_email ?? false
                                "
                                placeholder="john@example.com"
                            />
                            <p class="text-xs text-muted-foreground">
                                We'll only use this to respond to your feedback
                            </p>
                            <p
                                v-if="form.errors.customer_email"
                                class="text-sm text-destructive"
                            >
                                {{ form.errors.customer_email }}
                            </p>
                        </div>

                        <!-- Submit Button -->
                         <div v-tooltip="!acceptingFeedbackSubmissions? 'This business is not currently accepting feedback submissions.' : ''" >

                             <Button
                             type="submit"
                             :disabled="!isFormValid || form.processing || !acceptingFeedbackSubmissions"
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

            <!-- Footer -->
            <div class="mt-8 text-center text-sm text-muted-foreground">
                <p>Powered by <span class="font-semibold">Telliqo</span></p>
            </div>
        </div>
    </PublicLayout>
</template>
