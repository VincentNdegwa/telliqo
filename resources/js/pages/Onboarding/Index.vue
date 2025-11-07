<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import Stepper from 'primevue/stepper';
import StepList from 'primevue/steplist';
import StepPanels from 'primevue/steppanels';
import Step from 'primevue/step';
import StepPanel from 'primevue/steppanel';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import type { BusinessCategory, BusinessFormData } from '@/types/business';

interface Props {
    categories: BusinessCategory[];
}

const props = defineProps<Props>();
const toast = useToast();

const currentStep = ref('1');
const isSubmitting = ref(false);

const formData = ref<BusinessFormData>({
    name: '',
    description: '',
    category_id: null,
    email: '',
    phone: '',
    address: '',
    city: '',
    state: '',
    country: '',
    postal_code: '',
    website: '',
});

const validateBasicInfo = (): boolean => {
    return !!(formData.value.name && formData.value.email);
};

const validateCategory = (): boolean => {
    return formData.value.category_id !== null;
};

const handleNextFromBasicInfo = (activateCallback: (step: string) => void) => {
    if (!validateBasicInfo()) {
        toast.add({
            severity: 'warn',
            summary: 'Required Fields',
            detail: 'Please fill in Business Name and Email',
            life: 3000,
        });
        return;
    }
    activateCallback('2');
};

const handleNextFromCategory = (activateCallback: (step: string) => void) => {
    if (!validateCategory()) {
        toast.add({
            severity: 'warn',
            summary: 'Category Required',
            detail: 'Please select a business category',
            life: 3000,
        });
        return;
    }
    activateCallback('4');
};

const handleSubmit = () => {
    if (!validateBasicInfo() || !validateCategory()) {
        toast.add({
            severity: 'error',
            summary: 'Incomplete Form',
            detail: 'Please complete all required fields',
            life: 3000,
        });
        return;
    }

    isSubmitting.value = true;

    router.post('/onboarding', formData.value, {
        onFinish: () => {
            isSubmitting.value = false;
        },
        onError: (errors) => {
            console.error('Onboarding error:', errors);
            toast.add({
                severity: 'error',
                summary: 'Error',
                detail: 'Failed to create business. Please try again.',
                life: 5000,
            });
            isSubmitting.value = false;
        },
    });
};
</script>

<template>
    <Head title="Welcome - Setup Your Business" />
    <Toast />

    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-primary-50 via-surface-50 to-surface-100 dark:from-surface-950 dark:via-surface-900 dark:to-surface-950 p-4">
        <div class="w-full max-w-5xl">
            <!-- Header -->
            <div class="text-center mb-6">
                <div class="inline-flex items-center justify-center mb-3">
                    <div class="flex items-center gap-2">
                        <AppLogoIcon class="size-20 fill-current text-white dark:text-black" />
                        <span class="text-2xl font-bold text-surface-900 dark:text-surface-0">Telliqo</span>
                    </div>
                </div>
                <h1 class="text-2xl md:text-3xl font-bold text-surface-900 dark:text-surface-0 mb-1">
                    Welcome! Let's setup your business
                </h1>
                <p class="text-sm md:text-base text-surface-600 dark:text-surface-400 max-w-xl mx-auto">
                    Get started in just a few steps to collect valuable customer feedback
                </p>
            </div>

            <!-- Main Card -->
            <div class="bg-white dark:bg-surface-900 rounded-2xl shadow-2xl border border-surface-200 dark:border-surface-800">
                <Stepper v-model:value="currentStep" linear class="p-6 md:p-8">
                    <StepList class="mb-8">
                        <Step value="1">
                            <template #default>
                                <div class="flex flex-col items-center gap-2">
                                    <i class="pi pi-info-circle text-xl" />
                                    <span class="hidden sm:inline">Basic Info</span>
                                </div>
                            </template>
                        </Step>
                        <Step value="2">
                            <template #default>
                                <div class="flex flex-col items-center gap-2">
                                    <i class="pi pi-map-marker text-xl" />
                                    <span class="hidden sm:inline">Location</span>
                                </div>
                            </template>
                        </Step>
                        <Step value="3">
                            <template #default>
                                <div class="flex flex-col items-center gap-2">
                                    <i class="pi pi-tag text-xl" />
                                    <span class="hidden sm:inline">Category</span>
                                </div>
                            </template>
                        </Step>
                        <Step value="4">
                            <template #default>
                                <div class="flex flex-col items-center gap-2">
                                    <i class="pi pi-check-circle text-xl" />
                                    <span class="hidden sm:inline">Review</span>
                                </div>
                            </template>
                        </Step>
                    </StepList>

                    <StepPanels>
                        <!-- Step 1: Basic Information -->
                        <StepPanel v-slot="{ activateCallback }" value="1">
                            <div class="min-h-[400px] flex flex-col">
                                <div class="mb-6">
                                    <h2 class="text-2xl font-bold text-surface-900 dark:text-surface-0 mb-2">
                                        Tell us about your business
                                    </h2>
                                    <p class="text-surface-600 dark:text-surface-400">
                                        Let's start with the basics
                                    </p>
                                </div>

                                <div class="flex-1 space-y-5">
                                    <div>
                                        <label for="business-name" class="block text-sm font-semibold mb-2">
                                            Business Name <span class="text-red-500">*</span>
                                        </label>
                                        <InputText
                                            id="business-name"
                                            v-model="formData.name"
                                            placeholder="e.g., The Coffee House"
                                            class="w-full"
                                            size="large"
                                        />
                                    </div>

                                    <div>
                                        <label for="description" class="block text-sm font-semibold mb-2">
                                            Description
                                        </label>
                                        <Textarea
                                            id="description"
                                            v-model="formData.description"
                                            placeholder="Tell us what makes your business special..."
                                            rows="4"
                                            class="w-full"
                                        />
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                        <div>
                                            <label for="email" class="block text-sm font-semibold mb-2">
                                                Business Email <span class="text-red-500">*</span>
                                            </label>
                                            <InputText
                                                id="email"
                                                v-model="formData.email"
                                                type="email"
                                                placeholder="contact@business.com"
                                                class="w-full"
                                                size="large"
                                            />
                                        </div>

                                        <div>
                                            <label for="phone" class="block text-sm font-semibold mb-2">
                                                Phone Number
                                            </label>
                                            <InputText
                                                id="phone"
                                                v-model="formData.phone"
                                                type="tel"
                                                placeholder="+1 (555) 123-4567"
                                                class="w-full"
                                                size="large"
                                            />
                                        </div>
                                    </div>

                                    <div>
                                        <label for="website" class="block text-sm font-semibold mb-2">
                                            Website
                                        </label>
                                        <InputText
                                            id="website"
                                            v-model="formData.website"
                                            type="url"
                                            placeholder="https://yourbusiness.com"
                                            class="w-full"
                                            size="large"
                                        />
                                    </div>
                                </div>

                                <div class="flex justify-end pt-6 mt-6 border-t border-surface-200 dark:border-surface-700">
                                    <Button
                                        label="Continue"
                                        icon="pi pi-arrow-right"
                                        iconPos="right"
                                        size="large"
                                        @click="handleNextFromBasicInfo(activateCallback)"
                                    />
                                </div>
                            </div>
                        </StepPanel>

                        <!-- Step 2: Location Details -->
                        <StepPanel v-slot="{ activateCallback }" value="2">
                            <div class="min-h-[400px] flex flex-col">
                                <div class="mb-6">
                                    <h2 class="text-2xl font-bold text-surface-900 dark:text-surface-0 mb-2">
                                        Where are you located?
                                    </h2>
                                    <p class="text-surface-600 dark:text-surface-400">
                                        Add your business address (optional but recommended)
                                    </p>
                                </div>

                                <div class="flex-1 space-y-5">
                                    <div>
                                        <label for="address" class="block text-sm font-semibold mb-2">
                                            Street Address
                                        </label>
                                        <InputText
                                            id="address"
                                            v-model="formData.address"
                                            placeholder="123 Main Street"
                                            class="w-full"
                                            size="large"
                                        />
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                        <div>
                                            <label for="city" class="block text-sm font-semibold mb-2">
                                                City
                                            </label>
                                            <InputText
                                                id="city"
                                                v-model="formData.city"
                                                placeholder="San Francisco"
                                                class="w-full"
                                                size="large"
                                            />
                                        </div>

                                        <div>
                                            <label for="state" class="block text-sm font-semibold mb-2">
                                                State/Province
                                            </label>
                                            <InputText
                                                id="state"
                                                v-model="formData.state"
                                                placeholder="California"
                                                class="w-full"
                                                size="large"
                                            />
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                        <div>
                                            <label for="country" class="block text-sm font-semibold mb-2">
                                                Country
                                            </label>
                                            <InputText
                                                id="country"
                                                v-model="formData.country"
                                                placeholder="United States"
                                                class="w-full"
                                                size="large"
                                            />
                                        </div>

                                        <div>
                                            <label for="postal-code" class="block text-sm font-semibold mb-2">
                                                Postal Code
                                            </label>
                                            <InputText
                                                id="postal-code"
                                                v-model="formData.postal_code"
                                                placeholder="94102"
                                                class="w-full"
                                                size="large"
                                            />
                                        </div>
                                    </div>
                                </div>

                                <div class="flex justify-between pt-6 mt-6 border-t border-surface-200 dark:border-surface-700">
                                    <Button
                                        label="Back"
                                        severity="secondary"
                                        icon="pi pi-arrow-left"
                                        size="large"
                                        outlined
                                        @click="activateCallback('1')"
                                    />
                                    <Button
                                        label="Continue"
                                        icon="pi pi-arrow-right"
                                        iconPos="right"
                                        size="large"
                                        @click="activateCallback('3')"
                                    />
                                </div>
                            </div>
                        </StepPanel>

                        <!-- Step 3: Select Category -->
                        <StepPanel v-slot="{ activateCallback }" value="3">
                            <div class="min-h-[400px] flex flex-col">
                                <div class="mb-6">
                                    <h2 class="text-2xl font-bold text-surface-900 dark:text-surface-0 mb-2">
                                        What type of business do you run?
                                    </h2>
                                    <p class="text-surface-600 dark:text-surface-400">
                                        Select the category that best describes your business
                                    </p>
                                </div>

                                <div class="flex-1 overflow-y-auto max-h-[500px] pr-2">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                        <button
                                            v-for="category in categories"
                                            :key="category.id"
                                            type="button"
                                            :class="[
                                                'group relative p-6 border-2 rounded-xl transition-all duration-200',
                                                'hover:shadow-lg hover:-translate-y-1',
                                                formData.category_id === category.id
                                                    ? 'border-primary bg-primary-50 dark:bg-primary-900/20 shadow-md'
                                                    : 'border-surface-200 dark:border-surface-700 hover:border-primary-300 bg-white dark:bg-surface-800',
                                            ]"
                                            @click="formData.category_id = category.id"
                                        >
                                            <div class="flex flex-col items-center gap-3 text-center">
                                                <div
                                                    :class="[
                                                        'w-12 h-12 rounded-full flex items-center justify-center transition-colors',
                                                        formData.category_id === category.id
                                                            ? 'bg-primary text-white'
                                                            : 'bg-surface-100 dark:bg-surface-700 text-primary group-hover:bg-primary group-hover:text-white',
                                                    ]"
                                                >
                                                    <i :class="category.icon || 'pi pi-tag'" class="text-2xl" />
                                                </div>
                                                <div class="font-semibold text-surface-900 dark:text-surface-0">
                                                    {{ category.name }}
                                                </div>
                                                <div
                                                    v-if="category.description"
                                                    class="text-xs text-surface-600 dark:text-surface-400 line-clamp-2"
                                                >
                                                    {{ category.description }}
                                                </div>
                                            </div>
                                            <div
                                                v-if="formData.category_id === category.id"
                                                class="absolute top-3 right-3"
                                            >
                                                <i class="pi pi-check-circle text-primary text-xl" />
                                            </div>
                                        </button>
                                    </div>
                                </div>

                                <div class="flex justify-between pt-6 mt-6 border-t border-surface-200 dark:border-surface-700">
                                    <Button
                                        label="Back"
                                        severity="secondary"
                                        icon="pi pi-arrow-left"
                                        size="large"
                                        outlined
                                        @click="activateCallback('2')"
                                    />
                                    <Button
                                        label="Continue"
                                        icon="pi pi-arrow-right"
                                        iconPos="right"
                                        size="large"
                                        @click="handleNextFromCategory(activateCallback)"
                                    />
                                </div>
                            </div>
                        </StepPanel>

                        <!-- Step 4: Review & Complete -->
                        <StepPanel v-slot="{ activateCallback }" value="4">
                            <div class="min-h-[400px] flex flex-col">
                                <div class="mb-6 text-center">
                                    <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-green-100 dark:bg-green-900/30 mb-4">
                                        <i class="pi pi-check text-5xl text-green-600 dark:text-green-400" />
                                    </div>
                                    <h2 class="text-3xl font-bold text-surface-900 dark:text-surface-0 mb-2">
                                        You're all set! 🎉
                                    </h2>
                                    <p class="text-surface-600 dark:text-surface-400">
                                        Review your information and complete setup
                                    </p>
                                </div>

                                <div class="flex-1">
                                    <div class="max-w-2xl mx-auto space-y-4">
                                        <!-- Business Info Card -->
                                        <div class="p-5 bg-gradient-to-br from-primary-50 to-surface-50 dark:from-primary-900/20 dark:to-surface-800 rounded-xl border border-primary-200 dark:border-primary-800">
                                            <div class="flex items-start gap-4">
                                                <div class="w-12 h-12 rounded-lg bg-primary text-white flex items-center justify-center flex-shrink-0">
                                                    <i class="pi pi-building text-xl" />
                                                </div>
                                                <div class="flex-1">
                                                    <div class="text-sm font-medium text-surface-600 dark:text-surface-400 mb-1">
                                                        Business Name
                                                    </div>
                                                    <div class="text-lg font-bold text-surface-900 dark:text-surface-0">
                                                        {{ formData.name }}
                                                    </div>
                                                    <div
                                                        v-if="formData.description"
                                                        class="text-sm text-surface-600 dark:text-surface-400 mt-2"
                                                    >
                                                        {{ formData.description }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Category Card -->
                                        <div class="p-5 bg-surface-50 dark:bg-surface-800 rounded-xl border border-surface-200 dark:border-surface-700">
                                            <div class="flex items-center gap-4">
                                                <div class="w-12 h-12 rounded-lg bg-surface-100 dark:bg-surface-700 flex items-center justify-center flex-shrink-0">
                                                    <i
                                                        :class="
                                                            categories.find((c) => c.id === formData.category_id)
                                                                ?.icon || 'pi pi-tag'
                                                        "
                                                        class="text-primary text-xl"
                                                    />
                                                </div>
                                                <div class="flex-1">
                                                    <div class="text-sm font-medium text-surface-600 dark:text-surface-400 mb-1">
                                                        Category
                                                    </div>
                                                    <div class="font-semibold text-surface-900 dark:text-surface-0">
                                                        {{
                                                            categories.find((c) => c.id === formData.category_id)
                                                                ?.name || 'Not selected'
                                                        }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Contact Info Card -->
                                        <div class="p-5 bg-surface-50 dark:bg-surface-800 rounded-xl border border-surface-200 dark:border-surface-700">
                                            <div class="flex items-start gap-4">
                                                <div class="w-12 h-12 rounded-lg bg-surface-100 dark:bg-surface-700 flex items-center justify-center flex-shrink-0">
                                                    <i class="pi pi-envelope text-primary text-xl" />
                                                </div>
                                                <div class="flex-1 space-y-2">
                                                    <div>
                                                        <div class="text-sm font-medium text-surface-600 dark:text-surface-400 mb-1">
                                                            Email
                                                        </div>
                                                        <div class="font-semibold text-surface-900 dark:text-surface-0">
                                                            {{ formData.email }}
                                                        </div>
                                                    </div>
                                                    <div v-if="formData.phone">
                                                        <div class="text-sm font-medium text-surface-600 dark:text-surface-400 mb-1">
                                                            Phone
                                                        </div>
                                                        <div class="font-semibold text-surface-900 dark:text-surface-0">
                                                            {{ formData.phone }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Location Card (if provided) -->
                                        <div
                                            v-if="formData.address || formData.city"
                                            class="p-5 bg-surface-50 dark:bg-surface-800 rounded-xl border border-surface-200 dark:border-surface-700"
                                        >
                                            <div class="flex items-start gap-4">
                                                <div class="w-12 h-12 rounded-lg bg-surface-100 dark:bg-surface-700 flex items-center justify-center flex-shrink-0">
                                                    <i class="pi pi-map-marker text-primary text-xl" />
                                                </div>
                                                <div class="flex-1">
                                                    <div class="text-sm font-medium text-surface-600 dark:text-surface-400 mb-1">
                                                        Location
                                                    </div>
                                                    <div class="text-surface-900 dark:text-surface-0">
                                                        <div v-if="formData.address">{{ formData.address }}</div>
                                                        <div>
                                                            {{ [formData.city, formData.state, formData.postal_code].filter(Boolean).join(', ') }}
                                                        </div>
                                                        <div v-if="formData.country">{{ formData.country }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex justify-between pt-6 mt-6 border-t border-surface-200 dark:border-surface-700">
                                    <Button
                                        label="Back"
                                        severity="secondary"
                                        icon="pi pi-arrow-left"
                                        size="large"
                                        outlined
                                        @click="activateCallback('3')"
                                    />
                                    <Button
                                        label="Complete Setup"
                                        icon="pi pi-check"
                                        iconPos="right"
                                        size="large"
                                        :loading="isSubmitting"
                                        :disabled="isSubmitting"
                                        @click="handleSubmit"
                                    />
                                </div>
                            </div>
                        </StepPanel>
                    </StepPanels>
                </Stepper>
            </div>

            <!-- Footer -->
            <div class="text-center mt-6 text-sm text-surface-500 dark:text-surface-400">
                Need help? Contact our support team
            </div>
        </div>
    </div>
</template>