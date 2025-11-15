<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import {
    Edit,
    Eye,
    Filter,
    Search,
    Trash2,
    UserPlus,
    Users,
} from 'lucide-vue-next';
import Chip from 'primevue/chip';
import Column from 'primevue/column';
import ConfirmDialog from 'primevue/confirmdialog';
import DataTable from 'primevue/datatable';
import Tag from 'primevue/tag';
import { useConfirm } from 'primevue/useconfirm';
import { ref } from 'vue';

interface Customer {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    company_name: string | null;
    tags: string[];
    total_requests_sent: number;
    total_feedbacks: number;
    opted_out: boolean;
    created_at: string;
}

interface Props {
    customers: {
        data: Customer[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    filters: {
        search: string | null;
        opted_out: string | null;
    };
    stats: {
        total: number;
        active: number;
        opted_out: number;
    };
}

const props = defineProps<Props>();
const confirm = useConfirm();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Customers',
        href: '/customers',
    },
];

const search = ref(props.filters.search || '');
const optedOutFilter = ref(props.filters.opted_out || 'all');

const applyFilters = () => {
    router.get(
        '/customers',
        {
            search: search.value || undefined,
            opted_out:
                optedOutFilter.value !== 'all'
                    ? optedOutFilter.value
                    : undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
};

const onPage = (event: any) => {
    router.get(
        `/customers?page=${event.page + 1}`,
        {
            search: search.value || undefined,
            opted_out:
                optedOutFilter.value !== 'all'
                    ? optedOutFilter.value
                    : undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
};

const deleteCustomer = (customer: Customer) => {
    confirm.require({
        message: `Are you sure you want to delete ${customer.name}? This will also delete all their review requests and unlink their feedback.`,
        header: 'Confirm Delete',
        icon: 'pi pi-exclamation-triangle',
        rejectClass: 'p-button-secondary p-button-outlined',
        acceptClass: 'p-button-danger',
        accept: () => {
            router.delete(`/customers/${customer.id}`);
        },
    });
};
</script>

<template>
    <Head title="Customers" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <ConfirmDialog />

        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <!-- Header -->
            <div
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Customers</h1>
                    <p class="text-muted-foreground">
                        Manage your customer database
                    </p>
                </div>
                <Button v-permission="'customer.create'" @click="router.visit('/customers/create')">
                    <UserPlus class="mr-2 h-4 w-4" />
                    Add Customer
                </Button>
            </div>

            <div class="grid gap-4 md:grid-cols-3">
                <Card v-permission="'customer.stats'">
                    <CardHeader
                        class="flex flex-row items-center justify-between space-y-0 pb-2"
                    >
                        <CardTitle class="text-sm font-medium"
                            >Total Customers</CardTitle
                        >
                        <Users class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.total }}</div>
                    </CardContent>
                </Card>
                <Card v-permission="'customer.stats'">
                    <CardHeader
                        class="flex flex-row items-center justify-between space-y-0 pb-2"
                    >
                        <CardTitle class="text-sm font-medium"
                            >Active Customers</CardTitle
                        >
                        <Users class="h-4 w-4 text-green-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-green-600">
                            {{ stats.active }}
                        </div>
                    </CardContent>
                </Card>
                <Card v-permission="'customer.stats'">
                    <CardHeader
                        class="flex flex-row items-center justify-between space-y-0 pb-2"
                    >
                        <CardTitle class="text-sm font-medium"
                            >Opted Out</CardTitle
                        >
                        <Users class="h-4 w-4 text-red-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-red-600">
                            {{ stats.opted_out }}
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Filters and Table -->
            <Card>
                <CardHeader>
                    <div
                        class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between"
                    >
                        <CardTitle>All Customers</CardTitle>
                        <div class="flex flex-col gap-3 sm:flex-row">
                            <div class="relative flex-1 sm:w-64">
                                <Search
                                    class="absolute top-2.5 left-2.5 h-4 w-4 text-muted-foreground"
                                />
                                <Input
                                    v-model="search"
                                    placeholder="Search by name, email, or company..."
                                    class="pl-8"
                                    @keyup.enter="applyFilters"
                                />
                            </div>
                            <div class="flex gap-2">
                                <Button
                                    @click="
                                        () => {
                                            optedOutFilter =
                                                optedOutFilter === 'all'
                                                    ? '0'
                                                    : optedOutFilter === '0'
                                                      ? '1'
                                                      : 'all';
                                            applyFilters();
                                        }
                                    "
                                    variant="outline"
                                >
                                    <Filter class="mr-2 h-4 w-4" />
                                    {{
                                        optedOutFilter === 'all'
                                            ? 'All'
                                            : optedOutFilter === '1'
                                              ? 'Opted Out'
                                              : 'Active'
                                    }}
                                </Button>
                                <Button @click="applyFilters">
                                    <Search class="mr-2 h-4 w-4" />
                                    Search
                                </Button>
                            </div>
                        </div>
                    </div>
                </CardHeader>
                <CardContent>
                    <DataTable
                        :value="customers.data"
                        :lazy="true"
                        :paginator="true"
                        :rows="customers.per_page"
                        :totalRecords="customers.total"
                        :rowsPerPageOptions="[10, 25, 50]"
                        @page="onPage"
                        :loading="false"
                        class="p-datatable-sm"
                    >
                        <Column field="name" header="Name" sortable>
                            <template #body="{ data }">
                                <div class="font-medium">{{ data.name }}</div>
                                <div class="text-sm text-gray-500">
                                    {{ data.email }}
                                </div>
                            </template>
                        </Column>

                        <Column field="company_name" header="Company">
                            <template #body="{ data }">
                                <span v-if="data.company_name">{{
                                    data.company_name
                                }}</span>
                                <span v-else class="text-gray-400">—</span>
                            </template>
                        </Column>

                        <Column field="phone" header="Phone">
                            <template #body="{ data }">
                                <span v-if="data.phone">{{ data.phone }}</span>
                                <span v-else class="text-gray-400">—</span>
                            </template>
                        </Column>

                        <Column field="tags" header="Tags">
                            <template #body="{ data }">
                                <div
                                    v-if="data.tags && data.tags.length"
                                    class="flex flex-wrap gap-1"
                                >
                                    <Chip
                                        v-for="tag in data.tags"
                                        :key="tag"
                                        :label="tag"
                                        class="text-xs"
                                    />
                                </div>
                                <span v-else class="text-gray-400">—</span>
                            </template>
                        </Column>

                        <Column header="Stats" class="hidden md:table-cell">
                            <template #body="{ data }">
                                <div class="text-sm">
                                    <div>
                                        {{ data.total_requests_sent }} requests
                                    </div>
                                    <div>
                                        {{ data.total_feedbacks }} feedbacks
                                    </div>
                                </div>
                            </template>
                        </Column>

                        <Column header="Status">
                            <template #body="{ data }">
                                <Tag
                                    v-if="data.opted_out"
                                    severity="danger"
                                    value="Opted Out"
                                />
                                <Tag v-else severity="success" value="Active" />
                            </template>
                        </Column>

                        <Column header="Actions" class="w-[180px]">
                            <template #body="{ data }">
                                <div class="flex gap-1">
                                    <Button
                                        v-permission="'customer.view'"
                                        size="icon"
                                        variant="ghost"
                                        @click="
                                            router.visit(
                                                `/customers/${data.id}`,
                                            )
                                        "
                                    >
                                        <Eye class="h-4 w-4" />
                                    </Button>
                                    <Button
                                        v-permission="'customer.edit'"
                                        size="icon"
                                        variant="ghost"
                                        @click="
                                            router.visit(
                                                `/customers/${data.id}/edit`,
                                            )
                                        "
                                    >
                                        <Edit class="h-4 w-4" />
                                    </Button>
                                    <Button
                                        v-permission="'customer.delete'"
                                        size="icon"
                                        variant="ghost"
                                        @click="deleteCustomer(data)"
                                    >
                                        <Trash2
                                            class="h-4 w-4 text-destructive"
                                        />
                                    </Button>
                                </div>
                            </template>
                        </Column>

                        <template #empty>
                            <div class="py-8 text-center text-gray-500">
                                No customers found.
                                <a
                                    href="/customers/create"
                                    class="text-blue-600 hover:underline"
                                    >Add your first customer</a
                                >
                            </div>
                        </template>
                    </DataTable>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
