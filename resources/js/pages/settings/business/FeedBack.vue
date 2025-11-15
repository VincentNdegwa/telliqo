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
import { dashboard } from '@/routes';
import businessRoutes from '@/routes/business';
import { type BreadcrumbItem } from '@/types';
import { Business } from '@/types/business';
import { Head, useForm } from '@inertiajs/vue3';
import InputSwitch from 'primevue/inputswitch';

interface Props {
    business: Business;
    settings: {
        require_customer_name?: boolean;
        require_customer_email?: boolean;
        require_customer_phone?: boolean;
        allow_anonymous_feedback?: boolean;
        require_rating?: boolean;
        require_comment?: boolean;
        minimum_comment_length?: number;
        enable_photo_upload?: boolean;
        max_photos?: number;
        collect_visit_date?: boolean;
        show_privacy_notice?: boolean;
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
    require_customer_name: props.settings.require_customer_name ?? false,
    require_customer_email: props.settings.require_customer_email ?? false,
    require_customer_phone: props.settings.require_customer_phone ?? false,
    allow_anonymous_feedback: props.settings.allow_anonymous_feedback ?? true,
    require_rating: props.settings.require_rating ?? true,
    require_comment: props.settings.require_comment ?? false,
    minimum_comment_length: props.settings.minimum_comment_length ?? 0,
    enable_photo_upload: props.settings.enable_photo_upload ?? true,
    max_photos: props.settings.max_photos ?? 5,
    collect_visit_date: props.settings.collect_visit_date ?? false,
    show_privacy_notice: props.settings.show_privacy_notice ?? true,
});

const submit = () => {
    form.post(businessRoutes.settings.feedback().url, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Feedback Collection Settings" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <BusinessLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall
                    title="Feedback Collection Settings"
                    description="Configure what information customers must provide when submitting feedback"
                />

                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Customer Information Requirements -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <User class="h-5 w-5" />
                                Customer Information Requirements
                            </CardTitle>
                            <CardDescription>
                                Control what customer information is required
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-6">
                            <div class="flex items-center justify-between">
                                <div class="space-y-0.5">
                                    <Label>Require customer name</Label>
                                    <p class="text-sm text-muted-foreground">
                                        Make the customer name field mandatory
                                    </p>
                                </div>
                                <InputSwitch
                                    v-model="form.require_customer_name"
                                />
                            </div>

                            <div class="flex items-center justify-between">
                                <div class="space-y-0.5">
                                    <Label>Require customer email</Label>
                                    <p class="text-sm text-muted-foreground">
                                        Make the email address field mandatory
                                    </p>
                                </div>
                                <InputSwitch
                                    v-model="form.require_customer_email"
                                />
                            </div>

                            <div class="flex items-center justify-between">
                                <div class="space-y-0.5">
                                    <Label>Allow anonymous feedback</Label>
                                    <p class="text-sm text-muted-foreground">
                                        Let customers submit feedback without
                                        providing contact details
                                    </p>
                                </div>
                                <InputSwitch
                                    v-model="form.allow_anonymous_feedback"
                                    :disabled="
                                        form.require_customer_name ||
                                        form.require_customer_email ||
                                        form.require_customer_phone
                                    "
                                />
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Submit -->
                    <div
                        v-permission="'business-settings.feedback'"
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
