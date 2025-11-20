<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import billing from '@/routes/billing'
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { CreditCard, GaugeCircle, ShoppingBag } from 'lucide-vue-next';
import TabView from 'primevue/tabview';
import TabPanel from 'primevue/tabpanel';
import { computed, ref } from 'vue';

interface PlanFeature {
    key: string;
    name: string;
    category: string | null;
    type: 'boolean' | 'quota';
    description: string | null;
    is_enabled: boolean;
    is_unlimited: boolean;
    quota: number | null;
    unit: string | null;
}

interface PlanData {
    id: number;
    key: string;
    name: string;
    description: string | null;
    price_kes: number;
    price_usd: number;
    price_kes_yearly: number;
    price_usd_yearly: number;
    features: PlanFeature[];
}

interface Addon {
    id: number;
    feature_key: string | null;
    feature_name: string | null;
    name: string;
    increment_quota: number;
    price_kes: number;
    price_usd: number;
}

interface UsageItem {
    key: string;
    name: string;
    unit: string | null;
    used: number;
    quota: number | null;
    remaining: number | null;
    is_unlimited: boolean;
}

interface Props {
    plan: PlanData | null;
    addons: Addon[];
    usage: UsageItem[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Billing',
        href: billing.index().url,
    },
];

const activeTab = ref(0);

const currencyLabel = computed(() => 'KES');

const requestAddon = (addon: Addon) => {
    router.post('/billing/addons/request', {
        addon_id: addon.id,
    });
};
</script>

<template>
    <Head title="Billing" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <!-- Header -->
            <div
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Billing</h1>
                    <p class="text-muted-foreground">
                        Manage your subscription plan, usage and addons
                    </p>
                </div>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Billing Overview</CardTitle>
                </CardHeader>
                <CardContent>
                    <TabView v-model:activeIndex="activeTab">
                        <TabPanel header="Your Plan">
                            <div v-if="plan" class="space-y-4">
                                <div
                                    class="flex flex-col justify-between gap-4 md:flex-row md:items-center"
                                >
                                    <div>
                                        <h2 class="text-2xl font-semibold">
                                            {{ plan.name }}
                                        </h2>
                                        <p
                                            v-if="plan.description"
                                            class="text-muted-foreground"
                                        >
                                            {{ plan.description }}
                                        </p>
                                    </div>
                                    <div class="flex flex-col items-end gap-1">
                                        <div class="text-lg font-semibold">
                                            {{ currencyLabel }}
                                            {{ plan.price_kes.toLocaleString() }}
                                            <span
                                                class="text-sm font-normal text-muted-foreground"
                                            >
                                                / month
                                            </span>
                                        </div>
                                        <div
                                            v-if="plan.price_kes_yearly > 0"
                                            class="text-sm text-muted-foreground"
                                        >
                                            {{ currencyLabel }}
                                            {{ plan.price_kes_yearly.toLocaleString() }}
                                            / year
                                        </div>
                                    </div>
                                </div>

                                <div class="grid gap-4 md:grid-cols-2">
                                    <Card>
                                        <CardHeader
                                            class="flex flex-row items-center justify-between space-y-0 pb-2"
                                        >
                                            <CardTitle
                                                class="text-sm font-medium"
                                            >
                                                Plan Features
                                            </CardTitle>
                                            <CreditCard
                                                class="h-4 w-4 text-muted-foreground"
                                            />
                                        </CardHeader>
                                        <CardContent>
                                            <ul class="space-y-2 text-sm">
                                                <li
                                                    v-for="feature in plan.features"
                                                    :key="feature.key"
                                                    class="flex items-start gap-2"
                                                >
                                                    <span
                                                        class="mt-0.5 h-2 w-2 rounded-full"
                                                        :class="
                                                            feature.is_enabled
                                                                ? 'bg-green-500'
                                                                : 'bg-gray-300'
                                                        "
                                                    ></span>
                                                    <div>
                                                        <div
                                                            class="font-medium"
                                                        >
                                                            {{ feature.name }}
                                                            <span
                                                                v-if="
                                                                    feature.type ===
                                                                        'quota' &&
                                                                    feature.is_enabled
                                                                "
                                                                class="text-xs text-muted-foreground"
                                                            >
                                                                -
                                                                <span
                                                                    v-if="
                                                                        feature.is_unlimited
                                                                    "
                                                                >
                                                                    Unlimited
                                                                    {{
                                                                        feature.unit
                                                                    }}
                                                                </span>
                                                                <span
                                                                    v-else
                                                                >
                                                                    {{
                                                                        feature.quota
                                                                    }}
                                                                    {{
                                                                        feature.unit
                                                                    }}
                                                                </span>
                                                            </span>
                                                        </div>
                                                        <p
                                                            v-if="feature.description"
                                                            class="text-xs text-muted-foreground"
                                                        >
                                                            {{
                                                                feature.description
                                                            }}
                                                        </p>
                                                    </div>
                                                </li>
                                            </ul>
                                        </CardContent>
                                    </Card>

                                    <Card>
                                        <CardHeader
                                            class="flex flex-row items-center justify-between space-y-0 pb-2"
                                        >
                                            <CardTitle
                                                class="text-sm font-medium"
                                            >
                                                Available Addons
                                            </CardTitle>
                                            <ShoppingBag
                                                class="h-4 w-4 text-muted-foreground"
                                            />
                                        </CardHeader>
                                        <CardContent>
                                            <div
                                                v-if="addons.length"
                                                class="space-y-3 text-sm"
                                            >
                                                <div
                                                    v-for="addon in addons"
                                                    :key="addon.id"
                                                    class="flex items-center justify-between gap-3 rounded-md border p-3"
                                                >
                                                    <div>
                                                        <div
                                                            class="font-medium"
                                                        >
                                                            {{ addon.name }}
                                                        </div>
                                                        <div
                                                            class="text-xs text-muted-foreground"
                                                        >
                                                            {{
                                                                addon.increment_quota
                                                            }}
                                                            extra for
                                                            {{
                                                                addon.feature_name ||
                                                                addon.feature_key
                                                            }}
                                                        </div>
                                                    </div>
                                                    <div
                                                        class="flex flex-col items-end gap-2"
                                                    >
                                                        <div
                                                            class="text-sm font-semibold"
                                                        >
                                                            {{ currencyLabel }}
                                                            {{
                                                                addon.price_kes.toLocaleString()
                                                            }}
                                                        </div>
                                                        <Button
                                                            size="sm"
                                                            variant="outline"
                                                            @click="
                                                                requestAddon(
                                                                    addon,
                                                                )
                                                            "
                                                        >
                                                            Request
                                                        </Button>
                                                    </div>
                                                </div>
                                            </div>
                                            <p
                                                v-else
                                                class="text-sm text-muted-foreground"
                                            >
                                                No addons available at the
                                                moment.
                                            </p>
                                        </CardContent>
                                    </Card>
                                </div>
                            </div>
                            <div
                                v-else
                                class="py-8 text-center text-muted-foreground"
                            >
                                No plan is currently assigned to this
                                business.
                            </div>
                        </TabPanel>

                        <TabPanel header="Usage">
                            <div class="space-y-4">
                                <div
                                    class="flex items-center justify-between"
                                >
                                    <h2
                                        class="text-lg font-semibold tracking-tight"
                                    >
                                        Feature usage this month
                                    </h2>
                                </div>

                                <div
                                    v-if="usage.length"
                                    class="grid gap-4 md:grid-cols-2"
                                >
                                    <Card
                                        v-for="item in usage"
                                        :key="item.key"
                                    >
                                        <CardHeader
                                            class="flex flex-row items-center justify-between space-y-0 pb-2"
                                        >
                                            <CardTitle
                                                class="text-sm font-medium"
                                            >
                                                {{ item.name }}
                                            </CardTitle>
                                            <GaugeCircle
                                                class="h-4 w-4 text-muted-foreground"
                                            />
                                        </CardHeader>
                                        <CardContent>
                                            <div class="text-sm">
                                                <div class="mb-1">
                                                    <span
                                                        v-if="item.is_unlimited"
                                                    >
                                                        Unlimited
                                                        {{ item.unit }}
                                                    </span>
                                                    <span v-else>
                                                        {{ item.used }}
                                                        /
                                                        {{ item.quota }}
                                                        {{ item.unit }}
                                                    </span>
                                                </div>
                                                <div
                                                    v-if="
                                                        !item.is_unlimited &&
                                                        item.quota !== null
                                                    "
                                                    class="text-xs text-muted-foreground"
                                                >
                                                    Remaining:
                                                    {{ item.remaining }}
                                                    {{ item.unit }}
                                                </div>
                                            </div>
                                        </CardContent>
                                    </Card>
                                </div>
                                <div
                                    v-else
                                    class="py-8 text-center text-muted-foreground"
                                >
                                    No usage data available yet.
                                </div>
                            </div>
                        </TabPanel>

                        <TabPanel header="Payments (Coming Soon)">
                            <div
                                class="py-8 text-center text-muted-foreground"
                            >
                                Payments history and invoices will appear
                                here.
                            </div>
                        </TabPanel>
                    </TabView>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
