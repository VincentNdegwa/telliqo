<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
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
import Textarea from 'primevue/textarea';
import InputSwitch from 'primevue/inputswitch';
import Select from 'primevue/select';
import { Business, BusinessCategory } from '@/types/business';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { dashboard } from '@/routes';
import business from '@/routes/business';
import { Save, Building2, Palette, Settings as SettingsIcon } from 'lucide-vue-next';

interface Props {
    business: Business;
    categories: BusinessCategory[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Business Settings',
        href: business.settings().url,
    },
];

const form = useForm({
    name: props.business.name,
    slug: props.business.slug,
    description: props.business.description || '',
    category_id: props.business.category_id,
    address: props.business.address || '',
    phone: props.business.phone || '',
    email: props.business.email || '',
    website: props.business.website || '',
    custom_thank_you_message: props.business.custom_thank_you_message || '',
    brand_color_primary: props.business.brand_color_primary || '#3b82f6',
    brand_color_secondary: props.business.brand_color_secondary || '#8b5cf6',
    auto_approve_feedback: props.business.auto_approve_feedback || false,
    require_customer_name: props.business.require_customer_name || false,
    feedback_email_notifications: props.business.feedback_email_notifications || false,
});

const submit = () => {
    form.put(business.settings.url());
};
</script>

<template>
    <Head title="Business Settings" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div 
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <!-- Header -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Business Settings</h1>
                    <p class="text-muted-foreground">
                        Manage your business profile and feedback preferences
                    </p>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Business Information -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Building2 class="h-5 w-5" />
                            Business Information
                        </CardTitle>
                        <CardDescription>
                            Update your basic business details
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="name">Business Name *</Label>
                                <Input
                                    id="name"
                                    v-model="form.name"
                                    :class="{ 'border-destructive': form.errors.name }"
                                    required
                                />
                                <p v-if="form.errors.name" class="text-sm text-destructive">
                                    {{ form.errors.name }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label for="slug">URL Slug *</Label>
                                <Input
                                    id="slug"
                                    v-model="form.slug"
                                    :class="{ 'border-destructive': form.errors.slug }"
                                    required
                                />
                                <p class="text-xs text-muted-foreground">
                                    Your review URL: /review/{{ form.slug }}
                                </p>
                                <p v-if="form.errors.slug" class="text-sm text-destructive">
                                    {{ form.errors.slug }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label for="category">Category *</Label>
                                <Select
                                    id="category"
                                    v-model="form.category_id"
                                    :options="categories"
                                    option-label="name"
                                    option-value="id"
                                    placeholder="Select a category"
                                    :invalid="!!form.errors.category_id"
                                    class="w-full"
                                />
                                <p v-if="form.errors.category_id" class="text-sm text-destructive">
                                    {{ form.errors.category_id }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label for="phone">Phone</Label>
                                <Input id="phone" v-model="form.phone" type="tel" />
                                <p v-if="form.errors.phone" class="text-sm text-destructive">
                                    {{ form.errors.phone }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label for="email">Email</Label>
                                <Input id="email" v-model="form.email" type="email" />
                                <p v-if="form.errors.email" class="text-sm text-destructive">
                                    {{ form.errors.email }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label for="website">Website</Label>
                                <Input id="website" v-model="form.website" type="url" />
                                <p v-if="form.errors.website" class="text-sm text-destructive">
                                    {{ form.errors.website }}
                                </p>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="address">Address</Label>
                            <Input id="address" v-model="form.address" />
                            <p v-if="form.errors.address" class="text-sm text-destructive">
                                {{ form.errors.address }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <Label for="description">Description</Label>
                            <Textarea
                                id="description"
                                v-model="form.description"
                                :rows="3"
                                placeholder="Tell customers about your business..."
                                :invalid="!!form.errors.description"
                                class="w-full"
                            />
                            <p v-if="form.errors.description" class="text-sm text-destructive">
                                {{ form.errors.description }}
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Branding -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Palette class="h-5 w-5" />
                            Branding
                        </CardTitle>
                        <CardDescription>
                            Customize the look of your feedback form
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="brand_color_primary">Primary Color</Label>
                                <div class="flex gap-2">
                                    <Input
                                        id="brand_color_primary"
                                        v-model="form.brand_color_primary"
                                        type="color"
                                        class="h-10 w-20"
                                    />
                                    <Input
                                        v-model="form.brand_color_primary"
                                        class="font-mono"
                                    />
                                </div>
                            </div>

                            <div class="space-y-2">
                                <Label for="brand_color_secondary">Secondary Color</Label>
                                <div class="flex gap-2">
                                    <Input
                                        id="brand_color_secondary"
                                        v-model="form.brand_color_secondary"
                                        type="color"
                                        class="h-10 w-20"
                                    />
                                    <Input
                                        v-model="form.brand_color_secondary"
                                        class="font-mono"
                                    />
                                </div>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="custom_thank_you_message">Custom Thank You Message</Label>
                            <Textarea
                                id="custom_thank_you_message"
                                v-model="form.custom_thank_you_message"
                                :rows="3"
                                placeholder="Thank you for your feedback! We appreciate your time."
                                class="w-full"
                            />
                            <p class="text-xs text-muted-foreground">
                                This message will be shown to customers after they submit feedback
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Feedback Settings -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <SettingsIcon class="h-5 w-5" />
                            Feedback Settings
                        </CardTitle>
                        <CardDescription>
                            Configure how feedback is collected and moderated
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-6">
                        <div class="flex items-center justify-between">
                            <div class="space-y-0.5">
                                <Label>Auto-approve feedback</Label>
                                <p class="text-sm text-muted-foreground">
                                    Automatically approve all feedback without manual review
                                </p>
                            </div>
                            <InputSwitch
                                v-model="form.auto_approve_feedback"
                            />
                        </div>

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
                                <Label>Email notifications</Label>
                                <p class="text-sm text-muted-foreground">
                                    Receive an email when new feedback is submitted
                                </p>
                            </div>
                            <InputSwitch
                                v-model="form.feedback_email_notifications"
                            />
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
    </AppLayout>
</template>
