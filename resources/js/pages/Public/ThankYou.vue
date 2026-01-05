<script setup lang="ts">
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import PublicLayout from '@/layouts/public/PublicLayout.vue';
import { Business } from '@/types/business';
import { Head } from '@inertiajs/vue3';
import { CheckCircle, Star } from 'lucide-vue-next';

interface Props {
    business: Business;
    rating: number;
}

const props = defineProps<Props>();

const primaryColor = props.business.brand_color_primary || '#3b82f6';
const thankYouMessage =
    props.business.custom_thank_you_message ||
    'Thank you for your feedback! We truly appreciate you taking the time to share your experience with us.';
</script>

<template>
    <Head :title="`Thank You - ${business.name}`" />

    <PublicLayout>
        <div class="mx-auto mt-4 max-w-2xl">
            <!-- Success Card -->
            <Card class="text-center">
                <CardHeader>
                    <div class="mb-4 flex justify-center">
                        <div
                            class="flex h-20 w-20 items-center justify-center rounded-full text-white"
                            :style="{ backgroundColor: primaryColor }"
                        >
                            <CheckCircle class="h-12 w-12" />
                        </div>
                    </div>
                    <h1 class="text-3xl font-bold">Thank You!</h1>
                </CardHeader>
                <CardContent class="space-y-6">
                    <!-- Rating Display -->
                    <div v-if="rating > 0" class="flex justify-center gap-1">
                        <Star
                            v-for="star in 5"
                            :key="star"
                            :class="
                                star <= rating
                                    ? 'fill-yellow-400 text-yellow-400'
                                    : 'text-gray-300'
                            "
                            class="h-8 w-8"
                        />
                    </div>

                    <!-- Custom Message -->
                    <div class="prose prose-sm dark:prose-invert mx-auto">
                        <p class="text-lg">{{ thankYouMessage }}</p>
                    </div>

                    <!-- Business Info -->
                    <div class="rounded-lg border bg-muted/50 p-6">
                        <h2 class="mb-2 text-xl font-semibold">
                            {{ business.name }}
                        </h2>
                        <p
                            v-if="business.description"
                            class="text-sm text-muted-foreground"
                        >
                            {{ business.description }}
                        </p>
                        <div v-if="business.website" class="mt-4">
                            <a
                                :href="business.website"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="text-sm font-medium hover:underline"
                                :style="{ color: primaryColor }"
                            >
                                Visit our website →
                            </a>
                        </div>
                    </div>

                    <!-- Additional Info -->
                    <div class="space-y-2 text-sm text-muted-foreground">
                        <p v-if="business.auto_approve_feedback">
                            Your feedback has been published and is now visible
                            to others.
                        </p>
                        <p v-else>
                            Your feedback will be reviewed shortly before being
                            published.
                        </p>
                    </div>
                </CardContent>
            </Card>

            <!-- Footer -->
            <div class="mt-8 text-center text-sm text-muted-foreground">
                <p>Powered by <span class="font-semibold">{{ $page.props.name }}</span></p>
            </div>
        </div>
    </PublicLayout>
</template>
