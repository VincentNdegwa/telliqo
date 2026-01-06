<script setup lang="ts">
import SmartFeedbackForm from '@/components/feedback/SmartFeedbackForm.vue';
import PublicLayout from '@/layouts/public/PublicLayout.vue';
import feedback from '@/routes/feedback';
import { Business } from '@/types/business';
import { Head } from '@inertiajs/vue3';
import { Building2 } from 'lucide-vue-next';

interface Props {
    business: Business;
    feedbackSettings: {
        require_customer_name?: boolean;
        require_customer_email?: boolean;
        allow_anonymous_feedback?: boolean;
    };
    externalReviewSettings: {
        enabled?: boolean;
        google_review_url?: string;
        rating_threshold?: number;
    };
    acceptingFeedbackSubmissions: boolean;
}

const props = defineProps<Props>();

const externalReviewSettingsWithDefaults = {
    enabled: props.externalReviewSettings?.enabled ?? false,
    google_review_url: props.externalReviewSettings?.google_review_url ?? '',
    rating_threshold: props.externalReviewSettings?.rating_threshold ?? 0,
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

            <!-- Smart Feedback Form -->
            <SmartFeedbackForm
                :submit-url="feedback.store.url(business)"
                :business-name="business.name"
                :feedback-settings="feedbackSettings"
                :external-review-settings="externalReviewSettingsWithDefaults"
                :accepting-submissions="acceptingFeedbackSubmissions"
            />

            <!-- Footer -->
            <div class="mt-8 text-center text-sm text-muted-foreground">
                <p>Powered by <span class="font-semibold">{{ $page.props.name }}</span></p>
            </div>
        </div>
    </PublicLayout>
</template>
