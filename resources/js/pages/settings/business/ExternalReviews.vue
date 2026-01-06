<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import BusinessLayout from '@/layouts/settings/BusinessLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { AlertCircle, CheckCircle2, ExternalLink, HelpCircle, Info } from 'lucide-vue-next';
import InputSwitch from 'primevue/inputswitch';
import { computed, watch } from 'vue';

interface Props {
    business: {
        id: number;
        name: string;
    };
    settings: {
        enabled?: boolean;
        google_review_url?: string;
        rating_threshold?: number;
    };
}

const props = defineProps<Props>();

const breadcrumbs = [
    { title: 'Settings', href: '/business/settings' },
    { title: 'External Reviews', href: '/business/settings/external-reviews' },
];

const form = useForm({
    enabled: props.settings?.enabled ?? false,
    google_review_url: props.settings?.google_review_url ?? '',
    rating_threshold: props.settings?.rating_threshold ?? 4,
});

const isValidUrl = computed(() => {
    if (!form.google_review_url) return false;
    try {
        const url = new URL(form.google_review_url);
        return url.hostname.includes('google.com') || url.hostname.includes('g.page');
    } catch {
        return false;
    }
});

const canEnable = computed(() => {
    return form.google_review_url && isValidUrl.value;
});

// Auto-disable if URL is removed
watch(() => form.google_review_url, (newValue) => {
    if (!newValue && form.enabled) {
        form.enabled = false;
    }
});

const submit = () => {
    form.post('/business/settings/external-reviews', {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="External Review Integration" />

        <BusinessLayout>
            <div class="flex flex-col gap-6">
                <HeadingSmall
                    title="External Review Integration"
                    description="Automatically redirect happy customers to leave public reviews on Google"
                />

                <!-- Info Banner -->
                <div class="rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-950/30">
                    <div class="flex gap-3">
                        <Info class="h-5 w-5 shrink-0 text-blue-600 dark:text-blue-400" />
                        <div class="text-sm text-blue-800 dark:text-blue-300">
                            <p class="font-medium mb-1">How Smart Intercept Works</p>
                            <ul class="list-disc list-inside space-y-1 text-blue-700 dark:text-blue-400">
                                <li><strong>Positive ratings (4-5 stars):</strong> Customer is prompted to share their review on Google</li>
                                <li><strong>Negative ratings (1-3 stars):</strong> Feedback is captured privately for your team to resolve</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Enable Toggle -->
                    <div class="flex items-center justify-between rounded-lg border p-4">
                        <div class="space-y-0.5">
                            <Label class="text-base font-medium">Enable Smart Intercept</Label>
                            <p class="text-sm text-muted-foreground">
                                Route happy customers to Google Reviews automatically
                            </p>
                        </div>
                        <InputSwitch 
                            v-model="form.enabled"
                            :disabled="!canEnable && !form.enabled"
                        />
                    </div>
                    
                    <!-- Warning if enabled without URL -->
                    <div 
                        v-if="form.enabled && !isValidUrl" 
                        class="flex items-start gap-3 rounded-lg border border-amber-200 bg-amber-50 p-4 dark:border-amber-800 dark:bg-amber-950/30"
                    >
                        <AlertCircle class="h-5 w-5 shrink-0 text-amber-600 dark:text-amber-400" />
                        <p class="text-sm text-amber-800 dark:text-amber-300">
                            Please add a valid Google Review URL below to enable this feature.
                        </p>
                    </div>

                    <!-- Google Review URL -->
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <Label for="google_review_url" class="text-base font-medium">
                                Google Review Link
                            </Label>
                            <a 
                                href="https://support.google.com/business/answer/7035772" 
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center gap-1 text-xs text-muted-foreground hover:text-foreground"
                            >
                                <HelpCircle class="h-3 w-3" />
                                How to get this link
                            </a>
                        </div>
                        <Input
                            id="google_review_url"
                            v-model="form.google_review_url"
                            type="url"
                            placeholder="https://g.page/r/your-business/review"
                            :class="{ 'border-green-500': isValidUrl, 'border-destructive': form.google_review_url && !isValidUrl }"
                        />
                        <div class="flex items-center gap-2 text-sm">
                            <template v-if="form.google_review_url">
                                <template v-if="isValidUrl">
                                    <CheckCircle2 class="h-4 w-4 text-green-500" />
                                    <span class="text-green-600 dark:text-green-400">Valid Google Review URL</span>
                                </template>
                                <template v-else>
                                    <AlertCircle class="h-4 w-4 text-destructive" />
                                    <span class="text-destructive">Please enter a valid Google Review URL</span>
                                </template>
                            </template>
                            <template v-else>
                                <span class="text-muted-foreground">
                                    Paste your Google Business "Get more reviews" link
                                </span>
                            </template>
                        </div>
                        <InputError :message="form.errors.google_review_url" />
                    </div>

                    <!-- Instructions -->
                    <div class="rounded-lg border bg-muted/50 p-4">
                        <h4 class="mb-3 font-medium">How to Get Your Google Review Link</h4>
                        <ol class="list-decimal list-inside space-y-2 text-sm text-muted-foreground">
                            <li>Go to <a href="https://business.google.com" target="_blank" class="text-primary hover:underline">Google Business Profile</a></li>
                            <li>Select your business</li>
                            <li>Click "Get more reviews" or go to "Read reviews"</li>
                            <li>Click "Share review form" and copy the link</li>
                            <li>Paste the link above</li>
                        </ol>
                    </div>

                    <!-- Test Link -->
                    <div v-if="isValidUrl" class="flex items-center gap-3">
                        <a
                            :href="form.google_review_url"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center gap-2 text-sm text-primary hover:underline"
                        >
                            <ExternalLink class="h-4 w-4" />
                            Test your review link
                        </a>
                    </div>

                    <!-- Submit -->
                    <div class="flex items-center gap-4">
                        <Button 
                            type="submit" 
                            :disabled="form.processing"
                        >
                            {{ form.processing ? 'Saving...' : 'Save Changes' }}
                        </Button>
                        <p v-if="form.recentlySuccessful" class="text-sm text-green-600">
                            Saved successfully!
                        </p>
                    </div>
                </form>
            </div>
        </BusinessLayout>
    </AppLayout>
</template>
