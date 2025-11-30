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
import AppLayout from '@/layouts/AppLayout.vue';
import BusinessLayout from '@/layouts/settings/BusinessLayout.vue';
import { hasFeature } from '@/plugins/feature';
import { dashboard } from '@/routes';
import businessRoutes from '@/routes/business';
import { type BreadcrumbItem } from '@/types';
import { Business } from '@/types/business';
import { Head, useForm } from '@inertiajs/vue3';
import { Save } from 'lucide-vue-next';
import InputSwitch from 'primevue/inputswitch';

interface Props {
    business: Business;
    settings: {
        enable_ai_moderation?: boolean;
        enable_ai_sentiment?: boolean;
        block_duplicate_reviews?: boolean;
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Business Settings',
        href: businessRoutes.settings().url,
    },
];

const form = useForm({
    enable_ai_moderation: props.settings.enable_ai_moderation ?? false,
    enable_ai_sentiment: props.settings.enable_ai_sentiment ?? false,
    block_duplicate_reviews: props.settings.block_duplicate_reviews ?? true,
});

const submit = () => {
    form.post(businessRoutes.settings.moderation().url, {
        preserveScroll: true,
    });
};

const hasAiModerationFeature = hasFeature('ai_moderation');
const hasAiSentimentFeature = hasFeature('ai_sentiment');
</script>

<template>
    <Head title="Moderation Settings" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <BusinessLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall
                    title="Moderation Settings"
                    description="Configure how reviews are moderated and filtered"
                />

                <form @submit.prevent="submit" class="space-y-6">
                    <!-- AI Moderation -->
                    <Card>
                        <CardHeader>
                            <CardTitle>AI-Powered Moderation</CardTitle>
                            <CardDescription>
                                Use AI to automatically detect and flag
                                inappropriate content
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-6">
                            <div
                                v-tooltip="
                                    !hasAiModerationFeature
                                        ? 'Upgrade your plan to unlock AI moderation'
                                        : ''
                                "
                                class="flex items-center justify-between"
                            >
                                <div class="space-y-0.5">
                                    <Label>Enable AI moderation</Label>
                                    <p class="text-sm text-muted-foreground">
                                        Use AI to detect spam, profanity, and
                                        inappropriate content
                                    </p>
                                </div>
                                <InputSwitch
                                    :disabled="!hasAiModerationFeature"
                                    v-model="form.enable_ai_moderation"
                                />
                            </div>
                            <div
                                v-tooltip="
                                    !hasAiSentimentFeature
                                        ? 'Upgrade your plan to unlock AI sentiment analysis'
                                        : ''
                                "
                                class="flex items-center justify-between"
                            >
                                <div class="space-y-0.5">
                                    <Label>Enable AI sentiment analysis</Label>
                                    <p class="text-sm text-muted-foreground">
                                        Uses AI to classify feedback tone
                                        (Positive/Negative/Neutral) to help
                                        prioritize urgent issues.
                                    </p>
                                </div>
                                <InputSwitch
                                    :disabled="!hasAiSentimentFeature"
                                    v-model="form.enable_ai_sentiment"
                                />
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Quality Controls -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Quality Controls</CardTitle>
                            <CardDescription>
                                Set standards for review quality and
                                authenticity
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-6">
                            <div class="flex items-center justify-between">
                                <div class="space-y-0.5">
                                    <Label>Block duplicate reviews</Label>
                                    <p class="text-sm text-muted-foreground">
                                        Prevent the same customer from
                                        submitting multiple reviews
                                    </p>
                                </div>
                                <InputSwitch
                                    v-model="form.block_duplicate_reviews"
                                />
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Submit -->
                    <div
                        v-permission="'business-settings.moderation'"
                        class="flex justify-end"
                    >
                        <Button type="submit" :disabled="form.processing">
                            <Save class="mr-2 h-4 w-4" />
                            Save Changes
                        </Button>
                    </div>
                </form>
            </div>
        </BusinessLayout>
    </AppLayout>
</template>
