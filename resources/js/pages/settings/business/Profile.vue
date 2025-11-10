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
import { Business, BusinessCategory } from '@/types/business';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Building2, Image, Palette, Save, X } from 'lucide-vue-next';
import Select from 'primevue/select';
import Textarea from 'primevue/textarea';
import { ref } from 'vue';

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
        href: businessRoutes.settings().url,
    },
];

const logoPreview = ref<string | null>(null);
const logoFile = ref<File | null>(null);

const businessAny = props.business as any;
const currentLogoUrl = businessAny.logo ? `/storage/${businessAny.logo}` : null;

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
    feedback_email_notifications:
        props.business.feedback_email_notifications || false,
    logo: null as File | null,
});

const handleLogoUpload = (event: Event) => {
    const input = event.target as HTMLInputElement;
    if (input.files && input.files[0]) {
        const file = input.files[0];
        logoFile.value = file;
        form.logo = file;

        const reader = new FileReader();
        reader.onload = (e) => {
            logoPreview.value = e.target?.result as string;
        };
        reader.readAsDataURL(file);
    }
};

const removeLogo = () => {
    logoPreview.value = null;
    logoFile.value = null;
    form.logo = null;
};

const removeCurrentLogo = () => {
    router.delete('/business/settings/remove-logo', {
        preserveScroll: true,
        onSuccess: () => {
            logoPreview.value = null;
        },
    });
};

const submit = () => {
    form.post(businessRoutes.settings().url, {
        preserveScroll: true,
        forceFormData: true,
    });
};
</script>

<template>
    <Head title="Business Settings" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <BusinessLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall
                    title="Business Settings"
                    description="Update your business details and preferences"
                />

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
                                        :class="{
                                            'border-destructive':
                                                form.errors.name,
                                        }"
                                        required
                                    />
                                    <p
                                        v-if="form.errors.name"
                                        class="text-sm text-destructive"
                                    >
                                        {{ form.errors.name }}
                                    </p>
                                </div>

                                <div class="space-y-2">
                                    <Label for="slug">URL Slug *</Label>
                                    <Input
                                        id="slug"
                                        v-model="form.slug"
                                        :class="{
                                            'border-destructive':
                                                form.errors.slug,
                                        }"
                                        required
                                    />
                                    <p class="text-xs text-muted-foreground">
                                        Your review URL: /review/{{ form.slug }}
                                    </p>
                                    <p
                                        v-if="form.errors.slug"
                                        class="text-sm text-destructive"
                                    >
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
                                    <p
                                        v-if="form.errors.category_id"
                                        class="text-sm text-destructive"
                                    >
                                        {{ form.errors.category_id }}
                                    </p>
                                </div>

                                <div class="space-y-2">
                                    <Label for="phone">Phone</Label>
                                    <Input
                                        id="phone"
                                        v-model="form.phone"
                                        type="tel"
                                    />
                                    <p
                                        v-if="form.errors.phone"
                                        class="text-sm text-destructive"
                                    >
                                        {{ form.errors.phone }}
                                    </p>
                                </div>

                                <div class="space-y-2">
                                    <Label for="email">Email</Label>
                                    <Input
                                        id="email"
                                        v-model="form.email"
                                        type="email"
                                    />
                                    <p
                                        v-if="form.errors.email"
                                        class="text-sm text-destructive"
                                    >
                                        {{ form.errors.email }}
                                    </p>
                                </div>

                                <div class="space-y-2">
                                    <Label for="website">Website</Label>
                                    <Input
                                        id="website"
                                        v-model="form.website"
                                        type="url"
                                    />
                                    <p
                                        v-if="form.errors.website"
                                        class="text-sm text-destructive"
                                    >
                                        {{ form.errors.website }}
                                    </p>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <Label for="address">Address</Label>
                                <Input id="address" v-model="form.address" />
                                <p
                                    v-if="form.errors.address"
                                    class="text-sm text-destructive"
                                >
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
                                <p
                                    v-if="form.errors.description"
                                    class="text-sm text-destructive"
                                >
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
                            <div class="space-y-2">
                                <Label>Business Logo</Label>
                                <div
                                    v-if="logoPreview || currentLogoUrl"
                                    class="relative inline-block"
                                >
                                    <img
                                        :src="(logoPreview || currentLogoUrl)!"
                                        class="h-32 w-32 rounded-lg border object-contain p-2"
                                    />
                                    <Button
                                        @click="
                                            logoPreview
                                                ? removeLogo()
                                                : removeCurrentLogo()
                                        "
                                        size="icon"
                                        variant="destructive"
                                        class="absolute -top-2 -right-2 h-6 w-6"
                                        type="button"
                                    >
                                        <X class="h-3 w-3" />
                                    </Button>
                                </div>
                                <div v-else>
                                    <label
                                        class="flex h-32 w-32 cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed hover:bg-accent"
                                    >
                                        <Image
                                            class="mb-2 h-8 w-8 text-muted-foreground"
                                        />
                                        <span
                                            class="text-xs text-muted-foreground"
                                            >Upload Logo</span
                                        >
                                        <input
                                            type="file"
                                            class="hidden"
                                            @change="handleLogoUpload"
                                            accept="image/*"
                                        />
                                    </label>
                                </div>
                                <p class="text-xs text-muted-foreground">
                                    Recommended: Square image, max 2MB
                                </p>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <div class="space-y-2">
                                    <Label for="brand_color_primary"
                                        >Primary Color</Label
                                    >
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
                                    <Label for="brand_color_secondary"
                                        >Secondary Color</Label
                                    >
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
                                <Label for="custom_thank_you_message"
                                    >Custom Thank You Message</Label
                                >
                                <Textarea
                                    id="custom_thank_you_message"
                                    v-model="form.custom_thank_you_message"
                                    :rows="3"
                                    placeholder="Thank you for your feedback! We appreciate your time."
                                    class="w-full"
                                />
                                <p class="text-xs text-muted-foreground">
                                    This message will be shown to customers
                                    after they submit feedback
                                </p>
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
