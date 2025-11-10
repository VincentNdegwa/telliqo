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
import AppLayout from '@/layouts/AppLayout.vue';
import BusinessLayout from '@/layouts/settings/BusinessLayout.vue';
import { dashboard } from '@/routes';
import businessRoutes from '@/routes/business';
import { type BreadcrumbItem } from '@/types';
import { Business } from '@/types/business';
import { Head, useForm } from '@inertiajs/vue3';
import { Eye, Save } from 'lucide-vue-next';
import InputSwitch from 'primevue/inputswitch';
import Select from 'primevue/select';

interface Props {
    business: Business;
    settings: {
        show_business_profile?: boolean;
        display_logo?: boolean;
        show_total_reviews?: boolean;
        show_average_rating?: boolean;
        show_response_rate?: boolean;
        show_response_time?: boolean;
        reviews_per_page?: number;
        default_sort?: string;
        show_verified_badge?: boolean;
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

const sortOptions = [
    { label: 'Newest First', value: 'newest' },
    { label: 'Oldest First', value: 'oldest' },
    { label: 'Highest Rating', value: 'highest' },
    { label: 'Lowest Rating', value: 'lowest' },
];

const form = useForm({
    show_business_profile: props.settings.show_business_profile ?? true,
    display_logo: props.settings.display_logo ?? true,
    show_total_reviews: props.settings.show_total_reviews ?? true,
    show_average_rating: props.settings.show_average_rating ?? true,
    show_response_rate: props.settings.show_response_rate ?? false,
    show_response_time: props.settings.show_response_time ?? false,
    reviews_per_page: props.settings.reviews_per_page ?? 10,
    default_sort: props.settings.default_sort ?? 'newest',
    show_verified_badge: props.settings.show_verified_badge ?? true,
});

const submit = () => {
    form.post(businessRoutes.settings.display().url, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Display Settings" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <BusinessLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall
                    title="Display Settings"
                    description="Customize how your business and reviews are displayed publicly"
                />

                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Public Profile Display -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Eye class="h-5 w-5" />
                                Public Profile Display
                            </CardTitle>
                            <CardDescription>
                                Control what information is visible on your public profile
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-6">
                            <div class="flex items-center justify-between">
                                <div class="space-y-0.5">
                                    <Label>Show business profile</Label>
                                    <p class="text-sm text-muted-foreground">
                                        Display your business information on the public page
                                    </p>
                                </div>
                                <InputSwitch v-model="form.show_business_profile" />
                            </div>

                            <div class="flex items-center justify-between">
                                <div class="space-y-0.5">
                                    <Label>Display logo</Label>
                                    <p class="text-sm text-muted-foreground">
                                        Show your business logo on the public page
                                    </p>
                                </div>
                                <InputSwitch v-model="form.display_logo" />
                            </div>

                            <div class="flex items-center justify-between">
                                <div class="space-y-0.5">
                                    <Label>Show total reviews</Label>
                                    <p class="text-sm text-muted-foreground">
                                        Display the total number of reviews received
                                    </p>
                                </div>
                                <InputSwitch v-model="form.show_total_reviews" />
                            </div>

                            <div class="flex items-center justify-between">
                                <div class="space-y-0.5">
                                    <Label>Show average rating</Label>
                                    <p class="text-sm text-muted-foreground">
                                        Display your overall average star rating
                                    </p>
                                </div>
                                <InputSwitch v-model="form.show_average_rating" />
                            </div>

                            <div class="flex items-center justify-between">
                                <div class="space-y-0.5">
                                    <Label>Show verified badge</Label>
                                    <p class="text-sm text-muted-foreground">
                                        Display a verified business badge
                                    </p>
                                </div>
                                <InputSwitch v-model="form.show_verified_badge" />
                            </div>
                        </CardContent>
                    </Card>



                    <!-- Submit -->
                    <div class="flex justify-end">
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
