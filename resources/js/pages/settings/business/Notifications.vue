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
import { hasFeature } from '@/plugins/feature';
import { dashboard } from '@/routes';
import businessRoutes from '@/routes/business';
import { type BreadcrumbItem } from '@/types';
import { Business } from '@/types/business';
import { Head, useForm } from '@inertiajs/vue3';
import { Bell, Save } from 'lucide-vue-next';
import ToggleSwitch from 'primevue/toggleswitch';

interface Props {
    business: Business;
    settings: {
        email_notifications_enabled?: boolean;
        new_feedback_email?: boolean;
        low_rating_alert?: boolean;
        low_rating_threshold?: number;
        weekly_summary?: boolean;
        monthly_report?: boolean;
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
const hasSummaryReportFeature = hasFeature('summary_reports');

const form = useForm({
    email_notifications_enabled:
        props.settings.email_notifications_enabled ?? true,
    new_feedback_email: props.settings.new_feedback_email ?? true,
    low_rating_alert: props.settings.low_rating_alert ?? true,
    low_rating_threshold: props.settings.low_rating_threshold ?? 2,
    weekly_summary: props.settings.weekly_summary ?? false,
    monthly_report: props.settings.monthly_report ?? false,
});

const submit = () => {
    form.post(businessRoutes.settings.notifications().url, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Notification Settings" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <BusinessLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall
                    title="Notification Settings"
                    description="Configure how and when you receive notifications"
                />

                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Master Toggle -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Bell class="h-5 w-5" />
                                Email Notifications
                            </CardTitle>
                            <CardDescription>
                                Manage your email notification preferences
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-6">
                            <div
                                class="flex items-center justify-between rounded-lg border bg-muted/50 p-4"
                            >
                                <div class="space-y-0.5">
                                    <Label class="text-base font-semibold"
                                        >Enable email notifications</Label
                                    >
                                    <p class="text-sm text-muted-foreground">
                                        Turn on/off all email notifications at
                                        once
                                    </p>
                                </div>
                                <ToggleSwitch
                                    v-model="form.email_notifications_enabled"
                                />
                            </div>

                            <div class="space-y-4 pl-4">
                                <p
                                    class="text-sm font-medium text-muted-foreground"
                                >
                                    Individual notification preferences
                                </p>

                                <div class="flex items-center justify-between">
                                    <div class="space-y-0.5">
                                        <Label>New feedback</Label>
                                        <p
                                            class="text-sm text-muted-foreground"
                                        >
                                            Get notified when new feedback is
                                            submitted
                                        </p>
                                    </div>
                                    <ToggleSwitch
                                        v-model="form.new_feedback_email"
                                        :disabled="
                                            !form.email_notifications_enabled
                                        "
                                    />
                                </div>

                                <div class="flex items-center justify-between">
                                    <div class="space-y-0.5">
                                        <Label>Low rating alerts</Label>
                                        <p
                                            class="text-sm text-muted-foreground"
                                        >
                                            Get alerted when you receive ratings
                                            below the threshold
                                        </p>
                                    </div>
                                    <ToggleSwitch
                                        v-model="form.low_rating_alert"
                                        :disabled="
                                            !form.email_notifications_enabled
                                        "
                                    />
                                </div>

                                <div
                                    v-if="
                                        form.low_rating_alert &&
                                        form.email_notifications_enabled
                                    "
                                    class="ml-4 space-y-2 border-l-2 border-muted pl-4"
                                >
                                    <Label for="low_rating_threshold">
                                        Low rating threshold
                                    </Label>
                                    <Input
                                        id="low_rating_threshold"
                                        v-model.number="
                                            form.low_rating_threshold
                                        "
                                        type="number"
                                        min="1"
                                        max="5"
                                        class=""
                                    />
                                    <p class="text-xs text-muted-foreground">
                                        Alert for ratings at or below this value
                                        (1-5 stars)
                                    </p>
                                </div>

                                <div
                                    v-tooltip="
                                        !hasSummaryReportFeature
                                            ? 'Upgrade your plan to access this feature'
                                            : ''
                                    "
                                    class="flex items-center justify-between"
                                >
                                    <div class="space-y-0.5">
                                        <Label>Weekly summary</Label>
                                        <p
                                            class="text-sm text-muted-foreground"
                                        >
                                            Receive a weekly summary of your
                                            feedback
                                        </p>
                                    </div>
                                    <ToggleSwitch
                                        v-model="form.weekly_summary"
                                        :disabled="
                                            !hasSummaryReportFeature ||
                                            !form.email_notifications_enabled
                                        "
                                    />
                                </div>

                                <div
                                    v-tooltip="
                                        !hasSummaryReportFeature
                                            ? 'Upgrade your plan to access this feature'
                                            : ''
                                    "
                                    class="flex items-center justify-between"
                                >
                                    <div class="space-y-0.5">
                                        <Label>Monthly report</Label>
                                        <p
                                            class="text-sm text-muted-foreground"
                                        >
                                            Get a detailed monthly analytics
                                            report
                                        </p>
                                    </div>
                                    <ToggleSwitch
                                        v-model="form.monthly_report"
                                        :disabled="
                                            !hasSummaryReportFeature ||
                                            !form.email_notifications_enabled
                                        "
                                    />
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Submit -->
                    <div
                        v-permission="'business-settings.notifications'"
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
