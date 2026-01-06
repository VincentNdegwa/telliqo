<script setup lang="ts">
import SmartFeedbackForm from '@/components/feedback/SmartFeedbackForm.vue';
import PublicLayout from '@/layouts/public/PublicLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { format } from 'date-fns';
import { Building2, Calendar } from 'lucide-vue-next';

interface ReviewRequest {
    id: number;
    business: {
        name: string;
        logo_url: string | null;
        brand_color_primary?: string;
        custom_thank_you_message?: string;
    };
    customer: {
        name: string;
    };
    subject: string;
    message: string;
    expires_at: string;
}

interface ExternalReviewSettings {
    enabled?: boolean;
    google_review_url?: string;
}

interface Props {
    reviewRequest: ReviewRequest;
    token: string;
    externalReviewSettings?: ExternalReviewSettings;
    acceptingFeedbackSubmissions: boolean;
}

const props = defineProps<Props>();
const appName = usePage().props.name;
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
                    Hello {{ reviewRequest.customer.name }}, share your experience
                </p>
            </div>

            <!-- Smart Feedback Form -->
            <SmartFeedbackForm
                :submit-url="`/r/${token}`"
                :business-name="reviewRequest.business.name"
                :business-logo="reviewRequest.business.logo_url"
                :brand-color="reviewRequest.business.brand_color_primary"
                :custom-thank-you-message="reviewRequest.business.custom_thank_you_message"
                :external-review-settings="externalReviewSettings"
                :customer-name="reviewRequest.customer.name"
                :show-customer-fields="false"
                :accepting-submissions="acceptingFeedbackSubmissions"
                :title="reviewRequest.subject"
                :description="reviewRequest.message"
            />

            <!-- Footer Info -->
            <div class="mt-6 text-center text-xs text-muted-foreground">
                <div class="mb-2 flex items-center justify-center gap-2">
                    <Calendar class="h-3 w-3" />
                    <p>
                        This review request expires on
                        <span class="font-medium">{{
                            format(new Date(reviewRequest.expires_at), 'MMMM d, yyyy')
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
                <p>Powered by <span class="font-semibold">{{ appName }}</span></p>
            </div>
        </div>
    </PublicLayout>
</template>
