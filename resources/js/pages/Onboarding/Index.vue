<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import type { BusinessCategory, BusinessFormData } from '@/types/business';
import { Head, router } from '@inertiajs/vue3';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Step from 'primevue/step';
import StepList from 'primevue/steplist';
import StepPanel from 'primevue/steppanel';
import StepPanels from 'primevue/steppanels';
import Stepper from 'primevue/stepper';
import Textarea from 'primevue/textarea';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';
import { ref } from 'vue';

interface Props {
    categories: BusinessCategory[];
}

defineProps<Props>();
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

    <div
        class="from-primary-50 via-surface-50 to-surface-100 dark:from-surface-950 dark:via-surface-900 dark:to-surface-950 flex min-h-screen items-center justify-center bg-gradient-to-br p-4"
    >
        <div class="w-full max-w-5xl">
            <!-- Header -->
            <div class="mb-6 text-center">
                <div class="mb-3 inline-flex items-center justify-center">
                    <div class="flex items-center gap-2">
                        <AppLogoIcon
                            class="size-20 fill-current text-white dark:text-black"
                        />
                        <span
                            class="text-surface-900 dark:text-surface-0 text-2xl font-bold"
                            >Telliqo</span
                        >
                    </div>
                </div>
                <h1
                    class="text-surface-900 dark:text-surface-0 mb-1 text-2xl font-bold md:text-3xl"
                >
                    Welcome! Let's setup your business
                </h1>
                <p
                    class="text-surface-600 dark:text-surface-400 mx-auto max-w-xl text-sm md:text-base"
                >
                    Get started in just a few steps to collect valuable customer
                    feedback
                </p>
            </div>

            <!-- Main Card -->
            <div
                class="dark:bg-surface-900 border-surface-200 dark:border-surface-800 rounded-2xl border bg-white shadow-2xl"
            >
                <Stepper v-model:value="currentStep" linear class="p-6 md:p-8">
                    <StepList class="mb-8">
                        <Step value="1">
                            <template #default>
                                <div class="flex flex-col items-center gap-2">
                                    <i class="pi pi-info-circle text-xl" />
                                    <span class="hidden sm:inline"
                                        >Basic Info</span
                                    >
                                </div>
                            </template>
                        </Step>
                        <Step value="2">
                            <template #default>
                                <div class="flex flex-col items-center gap-2">
                                    <i class="pi pi-map-marker text-xl" />
                                    <span class="hidden sm:inline"
                                        >Location</span
                                    >
                                </div>
                            </template>
                        </Step>
                        <Step value="3">
                            <template #default>
                                <div class="flex flex-col items-center gap-2">
                                    <i class="pi pi-tag text-xl" />
                                    <span class="hidden sm:inline"
                                        >Category</span
                                    >
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
                            <div class="flex min-h-[400px] flex-col">
                                <div class="mb-6">
                                    <h2
                                        class="text-surface-900 dark:text-surface-0 mb-2 text-2xl font-bold"
                                    >
                                        Tell us about your business
                                    </h2>
                                    <p
                                        class="text-surface-600 dark:text-surface-400"
                                    >
                                        Let's start with the basics
                                    </p>
                                </div>

                                <div class="flex-1 space-y-5">
                                    <div>
                                        <label
                                            for="business-name"
                                            class="mb-2 block text-sm font-semibold"
                                        >
                                            Business Name
                                            <span class="text-red-500">*</span>
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
                                        <label
                                            for="description"
                                            class="mb-2 block text-sm font-semibold"
                                        >
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

                                    <div
                                        class="grid grid-cols-1 gap-5 md:grid-cols-2"
                                    >
                                        <div>
                                            <label
                                                for="email"
                                                class="mb-2 block text-sm font-semibold"
                                            >
                                                Business Email
                                                <span class="text-red-500"
                                                    >*</span
                                                >
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
                                            <label
                                                for="phone"
                                                class="mb-2 block text-sm font-semibold"
                                            >
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
                                        <label
                                            for="website"
                                            class="mb-2 block text-sm font-semibold"
                                        >
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

                                <div
                                    class="border-surface-200 dark:border-surface-700 mt-6 flex justify-end border-t pt-6"
                                >
                                    <Button
                                        label="Continue"
                                        icon="pi pi-arrow-right"
                                        iconPos="right"
                                        size="large"
                                        @click="
                                            handleNextFromBasicInfo(
                                                activateCallback,
                                            )
                                        "
                                    />
                                </div>
                            </div>
                        </StepPanel>

                        <!-- Step 2: Location Details -->
                        <StepPanel v-slot="{ activateCallback }" value="2">
                            <div class="flex min-h-[400px] flex-col">
                                <div class="mb-6">
                                    <h2
                                        class="text-surface-900 dark:text-surface-0 mb-2 text-2xl font-bold"
                                    >
                                        Where are you located?
                                    </h2>
                                    <p
                                        class="text-surface-600 dark:text-surface-400"
                                    >
                                        Add your business address (optional but
                                        recommended)
                                    </p>
                                </div>

                                <div class="flex-1 space-y-5">
                                    <div>
                                        <label
                                            for="address"
                                            class="mb-2 block text-sm font-semibold"
                                        >
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

                                    <div
                                        class="grid grid-cols-1 gap-5 md:grid-cols-2"
                                    >
                                        <div>
                                            <label
                                                for="city"
                                                class="mb-2 block text-sm font-semibold"
                                            >
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
                                            <label
                                                for="state"
                                                class="mb-2 block text-sm font-semibold"
                                            >
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

                                    <div
                                        class="grid grid-cols-1 gap-5 md:grid-cols-2"
                                    >
                                        <div>
                                            <label
                                                for="country"
                                                class="mb-2 block text-sm font-semibold"
                                            >
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
                                            <label
                                                for="postal-code"
                                                class="mb-2 block text-sm font-semibold"
                                            >
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

                                <div
                                    class="border-surface-200 dark:border-surface-700 mt-6 flex justify-between border-t pt-6"
                                >
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
                            <div class="flex min-h-[400px] flex-col">
                                <div class="mb-6">
                                    <h2
                                        class="text-surface-900 dark:text-surface-0 mb-2 text-2xl font-bold"
                                    >
                                        What type of business do you run?
                                    </h2>
                                    <p
                                        class="text-surface-600 dark:text-surface-400"
                                    >
                                        Select the category that best describes
                                        your business
                                    </p>
                                </div>

                                <div
                                    class="max-h-[500px] flex-1 overflow-y-auto pr-2"
                                >
                                    <div
                                        class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3"
                                    >
                                        <button
                                            v-for="category in categories"
                                            :key="category.id"
                                            type="button"
                                            :class="[
                                                'group relative rounded-xl border-2 p-6 transition-all duration-200',
                                                'hover:-translate-y-1 hover:shadow-lg',
                                                formData.category_id ===
                                                category.id
                                                    ? 'bg-primary-50 dark:bg-primary-900/20 border-primary shadow-md'
                                                    : 'border-surface-200 dark:border-surface-700 hover:border-primary-300 dark:bg-surface-800 bg-white',
                                            ]"
                                            @click="
                                                formData.category_id =
                                                    category.id
                                            "
                                        >
                                            <div
                                                class="flex flex-col items-center gap-3 text-center"
                                            >
                                                <div
                                                    :class="[
                                                        'flex h-12 w-12 items-center justify-center rounded-full transition-colors',
                                                        formData.category_id ===
                                                        category.id
                                                            ? 'bg-primary text-white'
                                                            : 'bg-surface-100 dark:bg-surface-700 text-primary group-hover:bg-primary group-hover:text-white',
                                                    ]"
                                                >
                                                    <i
                                                        :class="
                                                            category.icon ||
                                                            'pi pi-tag'
                                                        "
                                                        class="text-2xl"
                                                    />
                                                </div>
                                                <div
                                                    class="text-surface-900 dark:text-surface-0 font-semibold"
                                                >
                                                    {{ category.name }}
                                                </div>
                                                <div
                                                    v-if="category.description"
                                                    class="text-surface-600 dark:text-surface-400 line-clamp-2 text-xs"
                                                >
                                                    {{ category.description }}
                                                </div>
                                            </div>
                                            <div
                                                v-if="
                                                    formData.category_id ===
                                                    category.id
                                                "
                                                class="absolute top-3 right-3"
                                            >
                                                <i
                                                    class="pi pi-check-circle text-xl text-primary"
                                                />
                                            </div>
                                        </button>
                                    </div>
                                </div>

                                <div
                                    class="border-surface-200 dark:border-surface-700 mt-6 flex justify-between border-t pt-6"
                                >
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
                                        @click="
                                            handleNextFromCategory(
                                                activateCallback,
                                            )
                                        "
                                    />
                                </div>
                            </div>
                        </StepPanel>

                        <!-- Step 4: Review & Complete -->
                        <StepPanel v-slot="{ activateCallback }" value="4">
                            <div class="flex min-h-[400px] flex-col">
                                <div class="mb-6 text-center">
                                    <div
                                        class="mb-4 inline-flex h-20 w-20 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/30"
                                    >
                                        <i
                                            class="pi pi-check text-5xl text-green-600 dark:text-green-400"
                                        />
                                    </div>
                                    <h2
                                        class="text-surface-900 dark:text-surface-0 mb-2 text-3xl font-bold"
                                    >
                                        You're all set! 🎉
                                    </h2>
                                    <p
                                        class="text-surface-600 dark:text-surface-400"
                                    >
                                        Review your information and complete
                                        setup
                                    </p>
                                </div>

                                <div class="flex-1">
                                    <div class="mx-auto max-w-2xl space-y-4">
                                        <!-- Business Info Card -->
                                        <div
                                            class="from-primary-50 to-surface-50 dark:from-primary-900/20 dark:to-surface-800 border-primary-200 dark:border-primary-800 rounded-xl border bg-gradient-to-br p-5"
                                        >
                                            <div class="flex items-start gap-4">
                                                <div
                                                    class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-lg bg-primary text-white"
                                                >
                                                    <i
                                                        class="pi pi-building text-xl"
                                                    />
                                                </div>
                                                <div class="flex-1">
                                                    <div
                                                        class="text-surface-600 dark:text-surface-400 mb-1 text-sm font-medium"
                                                    >
                                                        Business Name
                                                    </div>
                                                    <div
                                                        class="text-surface-900 dark:text-surface-0 text-lg font-bold"
                                                    >
                                                        {{ formData.name }}
                                                    </div>
                                                    <div
                                                        v-if="
                                                            formData.description
                                                        "
                                                        class="text-surface-600 dark:text-surface-400 mt-2 text-sm"
                                                    >
                                                        {{
                                                            formData.description
                                                        }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Category Card -->
                                        <div
                                            class="bg-surface-50 dark:bg-surface-800 border-surface-200 dark:border-surface-700 rounded-xl border p-5"
                                        >
                                            <div
                                                class="flex items-center gap-4"
                                            >
                                                <div
                                                    class="bg-surface-100 dark:bg-surface-700 flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-lg"
                                                >
                                                    <i
                                                        :class="
                                                            categories.find(
                                                                (c) =>
                                                                    c.id ===
                                                                    formData.category_id,
                                                            )?.icon ||
                                                            'pi pi-tag'
                                                        "
                                                        class="text-xl text-primary"
                                                    />
                                                </div>
                                                <div class="flex-1">
                                                    <div
                                                        class="text-surface-600 dark:text-surface-400 mb-1 text-sm font-medium"
                                                    >
                                                        Category
                                                    </div>
                                                    <div
                                                        class="text-surface-900 dark:text-surface-0 font-semibold"
                                                    >
                                                        {{
                                                            categories.find(
                                                                (c) =>
                                                                    c.id ===
                                                                    formData.category_id,
                                                            )?.name ||
                                                            'Not selected'
                                                        }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Contact Info Card -->
                                        <div
                                            class="bg-surface-50 dark:bg-surface-800 border-surface-200 dark:border-surface-700 rounded-xl border p-5"
                                        >
                                            <div class="flex items-start gap-4">
                                                <div
                                                    class="bg-surface-100 dark:bg-surface-700 flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-lg"
                                                >
                                                    <i
                                                        class="pi pi-envelope text-xl text-primary"
                                                    />
                                                </div>
                                                <div class="flex-1 space-y-2">
                                                    <div>
                                                        <div
                                                            class="text-surface-600 dark:text-surface-400 mb-1 text-sm font-medium"
                                                        >
                                                            Email
                                                        </div>
                                                        <div
                                                            class="text-surface-900 dark:text-surface-0 font-semibold"
                                                        >
                                                            {{ formData.email }}
                                                        </div>
                                                    </div>
                                                    <div v-if="formData.phone">
                                                        <div
                                                            class="text-surface-600 dark:text-surface-400 mb-1 text-sm font-medium"
                                                        >
                                                            Phone
                                                        </div>
                                                        <div
                                                            class="text-surface-900 dark:text-surface-0 font-semibold"
                                                        >
                                                            {{ formData.phone }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Location Card (if provided) -->
                                        <div
                                            v-if="
                                                formData.address ||
                                                formData.city
                                            "
                                            class="bg-surface-50 dark:bg-surface-800 border-surface-200 dark:border-surface-700 rounded-xl border p-5"
                                        >
                                            <div class="flex items-start gap-4">
                                                <div
                                                    class="bg-surface-100 dark:bg-surface-700 flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-lg"
                                                >
                                                    <i
                                                        class="pi pi-map-marker text-xl text-primary"
                                                    />
                                                </div>
                                                <div class="flex-1">
                                                    <div
                                                        class="text-surface-600 dark:text-surface-400 mb-1 text-sm font-medium"
                                                    >
                                                        Location
                                                    </div>
                                                    <div
                                                        class="text-surface-900 dark:text-surface-0"
                                                    >
                                                        <div
                                                            v-if="
                                                                formData.address
                                                            "
                                                        >
                                                            {{
                                                                formData.address
                                                            }}
                                                        </div>
                                                        <div>
                                                            {{
                                                                [
                                                                    formData.city,
                                                                    formData.state,
                                                                    formData.postal_code,
                                                                ]
                                                                    .filter(
                                                                        Boolean,
                                                                    )
                                                                    .join(', ')
                                                            }}
                                                        </div>
                                                        <div
                                                            v-if="
                                                                formData.country
                                                            "
                                                        >
                                                            {{
                                                                formData.country
                                                            }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div
                                    class="border-surface-200 dark:border-surface-700 mt-6 flex justify-between border-t pt-6"
                                >
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
            <div
                class="text-surface-500 dark:text-surface-400 mt-6 text-center text-sm"
            >
                Need help? Contact our support team
            </div>
        </div>
    </div>
</template>
