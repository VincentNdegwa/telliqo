<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Save } from 'lucide-vue-next';
import Chips from 'primevue/chips';
import Textarea from 'primevue/textarea';

interface Customer {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    company_name: string | null;
    tags: string[];
    notes: string | null;
    opted_out: boolean;
}

interface Props {
    customer: Customer;
}

const props = defineProps<Props>();

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
        title: props.customer.name,
        href: `/customers/${props.customer.id}`,
    },
    {
        title: 'Edit',
        href: `/customers/${props.customer.id}/edit`,
    },
];

const form = useForm({
    name: props.customer.name,
    email: props.customer.email,
    phone: props.customer.phone || '',
    company_name: props.customer.company_name || '',
    tags: props.customer.tags || [],
    notes: props.customer.notes || '',
    opted_out: props.customer.opted_out,
});

const submit = () => {
    form.put(`/customers/${props.customer.id}`, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Edit Customer" />
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
                        Edit Customer
                    </h1>
                    <p class="text-muted-foreground">
                        Update customer information
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
                                    >Update the customer's basic
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
                                        placeholder="Press enter to add tags"
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
                                            'p-invalid': form.errors.notes,
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

                        <Card>
                            <CardHeader>
                                <CardTitle>Communication Preferences</CardTitle>
                                <CardDescription
                                    >Manage customer's email
                                    preferences</CardDescription
                                >
                            </CardHeader>
                            <CardContent>
                                <div class="flex items-center space-x-2">
                                    <Checkbox
                                        id="opted_out"
                                        :checked="form.opted_out"
                                        @update:checked="
                                            form.opted_out = $event
                                        "
                                    />
                                    <Label
                                        for="opted_out"
                                        class="cursor-pointer font-normal"
                                    >
                                        Customer has opted out of review
                                        requests
                                    </Label>
                                </div>
                                <p class="mt-2 text-sm text-muted-foreground">
                                    When checked, this customer will not receive
                                    any future review requests
                                </p>
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
                            <Button
                                v-permission="'customer.edit'"
                                type="submit"
                                :disabled="form.processing"
                            >
                                <Save class="mr-2 h-4 w-4" />
                                Update Customer
                            </Button>
                        </div>
                    </form>
                </div>

                <!-- Help Sidebar -->
                <div class="space-y-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>Update Information</CardTitle>
                        </CardHeader>
                        <CardContent
                            class="space-y-4 text-sm text-muted-foreground"
                        >
                            <p>
                                Update customer information and manage their
                                communication preferences.
                            </p>
                            <div class="space-y-2">
                                <p class="font-medium text-foreground">
                                    Opt-out Status:
                                </p>
                                <p>
                                    Customers who have opted out will not
                                    receive any future review requests
                                    automatically.
                                </p>
                            </div>
                            <div class="space-y-2">
                                <p class="font-medium text-foreground">
                                    Email Uniqueness:
                                </p>
                                <p>
                                    Each customer must have a unique email
                                    address within your business.
                                </p>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
