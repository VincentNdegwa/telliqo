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
import { hasFeature } from '@/plugins/feature';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { router, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Info, Send } from 'lucide-vue-next';
import Dropdown from 'primevue/dropdown';
import Message from 'primevue/message';
import Textarea from 'primevue/textarea';

interface Customer {
    id: number;
    name: string;
    email: string;
    company_name: string | null;
}

interface Props {
    customers: Customer[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Review Requests',
        href: '/review-requests',
    },
    {
        title: 'Send Review Request',
        href: '/review-requests/create',
    },
];

const form = useForm({
    customer_id: null as number | null,
    subject: "We'd love your feedback!",
    message:
        'Thank you for choosing our business! We value your opinion and would appreciate if you could take a moment to share your experience with us.',
    send_mode: 'now',
    schedule_hours: undefined as number | undefined,
});

const submit = () => {
    form.post('/review-requests', {
        preserveScroll: true,
    });
};

const customerOptions = props.customers.map((c) => ({
    label: `${c.name} (${c.email})${c.company_name ? ' - ' + c.company_name : ''}`,
    value: c.id,
}));

const hasRequestFeature = hasFeature('review_request_emails');
</script>

<template>
    <AppLayout title="Send Review Request" :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">
                        Send Review Request
                    </h1>
                    <p class="text-muted-foreground">
                        Send a review request to a customer via email
                    </p>
                </div>
                <Button
                    variant="outline"
                    @click="router.visit('/review-requests')"
                >
                    <ArrowLeft class="mr-2 h-4 w-4" />
                    Back
                </Button>
            </div>

            <!-- Form and Help Sidebar -->
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
                <!-- Form Section (2 columns) -->
                <div class="space-y-4 lg:col-span-2">
                    <form @submit.prevent="submit" class="space-y-4">
                        <!-- Customer Selection -->
                        <Card>
                            <CardHeader>
                                <CardTitle>Customer</CardTitle>
                                <CardDescription
                                    >Select the customer to send the review
                                    request to</CardDescription
                                >
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div class="space-y-2">
                                    <Label for="customer_id"
                                        >Customer
                                        <span class="text-destructive"
                                            >*</span
                                        ></Label
                                    >
                                    <Dropdown
                                        id="customer_id"
                                        v-model="form.customer_id"
                                        :options="customerOptions"
                                        optionLabel="label"
                                        optionValue="value"
                                        placeholder="Select a customer"
                                        class="w-full"
                                        :class="{
                                            'p-invalid':
                                                form.errors.customer_id,
                                        }"
                                        filter
                                        filterPlaceholder="Search customers..."
                                    />
                                    <Message
                                        v-if="form.errors.customer_id"
                                        severity="error"
                                        :closable="false"
                                    >
                                        {{ form.errors.customer_id }}
                                    </Message>
                                    <p
                                        v-if="!customers.length"
                                        class="text-sm text-destructive"
                                    >
                                        No active customers found.
                                        <a
                                            href="/customers/create"
                                            class="text-primary hover:underline"
                                            >Add a customer first</a
                                        >
                                    </p>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Email Content -->
                        <Card>
                            <CardHeader>
                                <CardTitle>Email Content</CardTitle>
                                <CardDescription
                                    >Customize the email that will be sent to
                                    the customer</CardDescription
                                >
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div class="space-y-2">
                                    <Label for="subject"
                                        >Email Subject
                                        <span class="text-destructive"
                                            >*</span
                                        ></Label
                                    >
                                    <Input
                                        id="subject"
                                        v-model="form.subject"
                                        :class="{
                                            'border-destructive':
                                                form.errors.subject,
                                        }"
                                    />
                                    <Message
                                        v-if="form.errors.subject"
                                        severity="error"
                                        :closable="false"
                                    >
                                        {{ form.errors.subject }}
                                    </Message>
                                </div>

                                <div class="space-y-2">
                                    <Label for="message"
                                        >Message
                                        <span class="text-destructive"
                                            >*</span
                                        ></Label
                                    >
                                    <Textarea
                                        id="message"
                                        v-model="form.message"
                                        rows="6"
                                        class="w-full"
                                        :class="{
                                            'p-invalid': form.errors.message,
                                        }"
                                    />
                                    <p class="text-sm text-muted-foreground">
                                        This message will be included in the
                                        email to the customer.
                                    </p>
                                    <Message
                                        v-if="form.errors.message"
                                        severity="error"
                                        :closable="false"
                                    >
                                        {{ form.errors.message }}
                                    </Message>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Sending Options -->
                        <Card>
                            <CardHeader>
                                <CardTitle>Sending Options</CardTitle>
                                <CardDescription
                                    >Choose when to send the review
                                    request</CardDescription
                                >
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div class="space-y-3">
                                    <div class="flex items-center space-x-2">
                                        <input
                                            type="radio"
                                            id="send_now"
                                            value="now"
                                            v-model="form.send_mode"
                                            class="h-4 w-4"
                                        />
                                        <Label
                                            for="send_now"
                                            class="cursor-pointer font-normal"
                                        >
                                            Send immediately
                                        </Label>
                                    </div>
                                    <p
                                        class="ml-6 text-sm text-muted-foreground"
                                    >
                                        Send the review request right away
                                    </p>

                                    <div class="flex items-center space-x-2">
                                        <input
                                            type="radio"
                                            id="send_scheduled"
                                            value="scheduled"
                                            v-model="form.send_mode"
                                            class="h-4 w-4"
                                        />
                                        <Label
                                            for="send_scheduled"
                                            class="cursor-pointer font-normal"
                                        >
                                            Schedule for later
                                        </Label>
                                    </div>

                                    <div
                                        v-if="form.send_mode === 'scheduled'"
                                        class="ml-6 space-y-2"
                                    >
                                        <Label for="schedule_hours"
                                            >Send after (hours)</Label
                                        >
                                        <Input
                                            id="schedule_hours"
                                            type="number"
                                            v-model="form.schedule_hours"
                                            min="1"
                                            max="720"
                                            placeholder="e.g., 24"
                                            :class="{
                                                'border-destructive':
                                                    form.errors.schedule_hours,
                                            }"
                                        />
                                        <p
                                            class="text-xs text-muted-foreground"
                                        >
                                            Schedule the email to be sent after
                                            a specified number of hours (max 720
                                            hours / 30 days)
                                        </p>
                                        <Message
                                            v-if="form.errors.schedule_hours"
                                            severity="error"
                                            :closable="false"
                                        >
                                            {{ form.errors.schedule_hours }}
                                        </Message>
                                    </div>

                                    <div class="flex items-center space-x-2">
                                        <input
                                            type="radio"
                                            id="send_manual"
                                            value="manual"
                                            v-model="form.send_mode"
                                            class="h-4 w-4"
                                        />
                                        <Label
                                            for="send_manual"
                                            class="cursor-pointer font-normal"
                                        >
                                            Save as draft (send manually later)
                                        </Label>
                                    </div>
                                    <p
                                        class="ml-6 text-sm text-muted-foreground"
                                    >
                                        Create the request but don't send it
                                        until you're ready
                                    </p>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Actions -->
                        <div class="flex justify-end gap-3">
                            <Button
                                variant="outline"
                                @click="router.visit('/review-requests')"
                                type="button"
                            >
                                Cancel
                            </Button>
                            <div
                                v-tooltip="
                                    !hasRequestFeature
                                        ? 'Upgrade your plan to unlock review requests'
                                        : ''
                                "
                            >
                                <Button
                                    v-permission="'review-request.create'"
                                    type="submit"
                                    :disabled="
                                        form.processing ||
                                        !customers.length ||
                                        !hasRequestFeature
                                    "
                                >
                                    <Send class="mr-2 h-4 w-4" />
                                    {{
                                        form.send_mode === 'now'
                                            ? 'Create & Send Now'
                                            : form.send_mode === 'scheduled'
                                              ? 'Create & Schedule'
                                              : 'Save as Draft'
                                    }}
                                </Button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Help Sidebar (1 column) -->
                <div class="lg:col-span-1">
                    <Card class="sticky top-4">
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Info class="h-5 w-5" />
                                Review Request Process
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <ul class="space-y-3 text-sm text-muted-foreground">
                                <li class="flex gap-2">
                                    <span class="text-primary">•</span>
                                    <span
                                        >A unique secure link will be generated
                                        for the customer</span
                                    >
                                </li>
                                <li class="flex gap-2">
                                    <span class="text-primary">•</span>
                                    <span>The link expires after 30 days</span>
                                </li>
                                <li class="flex gap-2">
                                    <span class="text-primary">•</span>
                                    <span
                                        >You can send reminder emails after 3
                                        days</span
                                    >
                                </li>
                                <li class="flex gap-2">
                                    <span class="text-primary">•</span>
                                    <span
                                        >Customers can opt-out from future
                                        requests</span
                                    >
                                </li>
                                <li class="flex gap-2">
                                    <span class="text-primary">•</span>
                                    <span
                                        >Track when emails are opened and
                                        completed</span
                                    >
                                </li>
                            </ul>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
