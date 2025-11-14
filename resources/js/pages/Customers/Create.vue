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
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Save } from 'lucide-vue-next';
import Chips from 'primevue/chips';
import Textarea from 'primevue/textarea';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Customers',
        href: '/customers',
    },
    {
        title: 'Add Customer',
        href: '/customers/create',
    },
];

const form = useForm({
    name: '',
    email: '',
    phone: '',
    company_name: '',
    tags: [] as string[],
    notes: '',
});

const submit = () => {
    form.post('/customers', {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Add Customer" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <!-- Header -->
            <div
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">
                        Add Customer
                    </h1>
                    <p class="text-muted-foreground">
                        Create a new customer profile
                    </p>
                </div>
                <Button @click="$inertia.visit('/customers')" variant="outline">
                    <ArrowLeft class="mr-2 h-4 w-4" />
                    Back to Customers
                </Button>
            </div>

            <!-- Form -->
            <div class="grid gap-6 lg:grid-cols-3">
                <div class="lg:col-span-2">
                    <form @submit.prevent="submit" class="space-y-6">
                        <Card>
                            <CardHeader>
                                <CardTitle>Customer Information</CardTitle>
                                <CardDescription
                                    >Enter the customer's basic
                                    details</CardDescription
                                >
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div class="grid gap-4 md:grid-cols-2">
                                    <div class="space-y-2">
                                        <Label for="name"
                                            >Name
                                            <span class="text-destructive"
                                                >*</span
                                            ></Label
                                        >
                                        <Input
                                            id="name"
                                            v-model="form.name"
                                            :class="{
                                                'border-destructive':
                                                    form.errors.name,
                                            }"
                                        />
                                        <p
                                            v-if="form.errors.name"
                                            class="text-sm text-destructive"
                                        >
                                            {{ form.errors.name }}
                                        </p>
                                    </div>

                                    <div class="space-y-2">
                                        <Label for="email"
                                            >Email
                                            <span class="text-destructive"
                                                >*</span
                                            ></Label
                                        >
                                        <Input
                                            id="email"
                                            v-model="form.email"
                                            type="email"
                                            :class="{
                                                'border-destructive':
                                                    form.errors.email,
                                            }"
                                        />
                                        <p
                                            v-if="form.errors.email"
                                            class="text-sm text-destructive"
                                        >
                                            {{ form.errors.email }}
                                        </p>
                                    </div>
                                </div>

                                <div class="grid gap-4 md:grid-cols-2">
                                    <div class="space-y-2">
                                        <Label for="phone">Phone</Label>
                                        <Input
                                            id="phone"
                                            v-model="form.phone"
                                            type="tel"
                                            :class="{
                                                'border-destructive':
                                                    form.errors.phone,
                                            }"
                                        />
                                        <p
                                            v-if="form.errors.phone"
                                            class="text-sm text-destructive"
                                        >
                                            {{ form.errors.phone }}
                                        </p>
                                    </div>

                                    <div class="space-y-2">
                                        <Label for="company_name"
                                            >Company Name</Label
                                        >
                                        <Input
                                            id="company_name"
                                            v-model="form.company_name"
                                            :class="{
                                                'border-destructive':
                                                    form.errors.company_name,
                                            }"
                                        />
                                        <p
                                            v-if="form.errors.company_name"
                                            class="text-sm text-destructive"
                                        >
                                            {{ form.errors.company_name }}
                                        </p>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>Additional Details</CardTitle>
                                <CardDescription
                                    >Optional information about the
                                    customer</CardDescription
                                >
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div class="space-y-2">
                                    <Label for="tags">Tags</Label>
                                    <Chips
                                        id="tags"
                                        v-model="form.tags"
                                        placeholder="Press enter to add tags (e.g., VIP, Regular)"
                                        class="w-full"
                                        :class="{
                                            'p-invalid': form.errors.tags,
                                        }"
                                    />
                                    <p class="text-sm text-muted-foreground">
                                        Press enter after each tag
                                    </p>
                                    <p
                                        v-if="form.errors.tags"
                                        class="text-sm text-destructive"
                                    >
                                        {{ form.errors.tags }}
                                    </p>
                                </div>

                                <div class="space-y-2">
                                    <Label for="notes">Notes</Label>
                                    <Textarea
                                        id="notes"
                                        v-model="form.notes"
                                        rows="4"
                                        placeholder="Add any notes about this customer..."
                                        :class="{
                                            'border-destructive':
                                                form.errors.notes,
                                        }"
                                    />
                                    <p
                                        v-if="form.errors.notes"
                                        class="text-sm text-destructive"
                                    >
                                        {{ form.errors.notes }}
                                    </p>
                                </div>
                            </CardContent>
                        </Card>

                        <div class="flex justify-end gap-3">
                            <Button
                                @click="$inertia.visit('/customers')"
                                type="button"
                                variant="outline"
                            >
                                Cancel
                            </Button>
                            <Button type="submit" :disabled="form.processing">
                                <Save class="mr-2 h-4 w-4" />
                                Create Customer
                            </Button>
                        </div>
                    </form>
                </div>

                <!-- Help Sidebar -->
                <div class="space-y-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>Customer Information</CardTitle>
                        </CardHeader>
                        <CardContent
                            class="space-y-4 text-sm text-muted-foreground"
                        >
                            <p>
                                Add customers to your database to send them
                                review requests and track their feedback.
                            </p>
                            <div class="space-y-2">
                                <p class="font-medium text-foreground">
                                    Required Fields:
                                </p>
                                <ul class="list-inside list-disc space-y-1">
                                    <li>Name</li>
                                    <li>Email (must be unique)</li>
                                </ul>
                            </div>
                            <div class="space-y-2">
                                <p class="font-medium text-foreground">
                                    Optional Fields:
                                </p>
                                <ul class="list-inside list-disc space-y-1">
                                    <li>Phone number</li>
                                    <li>Company name</li>
                                    <li>Tags for categorization</li>
                                    <li>Notes for internal use</li>
                                </ul>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
