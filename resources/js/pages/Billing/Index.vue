<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import billing from '@/routes/billing';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { Check, GaugeCircle, X } from 'lucide-vue-next';
import Tab from 'primevue/tab';
import TabList from 'primevue/tablist';
import TabPanel from 'primevue/tabpanel';
import TabPanels from 'primevue/tabpanels';
import Tabs from 'primevue/tabs';
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

interface LocalSubscription {
    id: number;
    provider: string;
    status: string;
    billing_period: string | null;
    amount: number | null;
    currency: string | null;
    starts_at: string | null;
    trial_ends_at: string | null;
    ends_at: string | null;
}

interface LocalTransaction {
    id: number;
    local_subscription_id: number | null;
    provider: string;
    status: string;
    amount: number | null;
    currency: string | null;
    paid_at: string | null;
}

interface Props {
    plan: PlanData | null;
    addons: Addon[];
    usage: UsageItem[];
    localSubscriptions: LocalSubscription[];
    localTransactions: LocalTransaction[];
    availablePlans: {
        id: number;
        key: string;
        name: string;
        description: string | null;
        price_kes: number;
        price_kes_yearly: number;
        is_current: boolean;
        features: PlanFeature[];
    }[];
    currentSubscription: {
        id: number;
        plan_name: string | null;
        provider: string;
        status: string;
        billing_period: string | null;
        amount: number | null;
        currency: string | null;
        starts_at: string | null;
        ends_at: string | null;
    } | null;
    hasAnyActiveSubscription: boolean;
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

const activeTab = ref<'subscription' | 'usage' | 'plans' | 'payments'>(
    'subscription',
);

const selectedPlanId = ref<number | null>(null);
const selectedBillingPeriod = ref<'monthly' | 'yearly'>('monthly');
const selectedPaymentMethod = ref<'mpesa' | 'card' | 'paypal' | null>(null);
const mpesaPhone = ref('');

const currencyLabel = computed(() => 'KES');

const requestAddon = (addon: Addon) => {
    router.post('/billing/addons/request', {
        addon_id: addon.id,
    });
};

const localSubscriptionCurrency = ref('KES');
const localSubscriptionExternalId = ref('');

const createLocalSubscription = (
    planId: number,
    billingPeriod: 'monthly' | 'yearly',
    provider: string,
    extraMeta: Record<string, unknown> = {},
) => {
    const plan = props.availablePlans.find((p) => p.id === planId);

    let amount: number | null = null;

    if (plan) {
        amount =
            billingPeriod === 'monthly'
                ? plan.price_kes
                : plan.price_kes_yearly;
    }

    router.post('/billing/subscriptions/local', {
        plan_id: planId,
        provider,
        billing_period: billingPeriod,
        amount,
        currency: localSubscriptionCurrency.value,
        external_id: localSubscriptionExternalId.value || null,
        meta: extraMeta,
    });
};

// const cancelLocalSubscription = (subscription: LocalSubscription) => {
//     router.post(`/billing/subscriptions/local/${subscription.id}/cancel`);
// };

const startPaddleSubscription = (planId: number) => {
    router.post('/billing/subscriptions/paddle/start', {
        plan_id: planId,
    });
};

const startPaypalSubscription = (planId: number) => {
    router.post('/billing/subscriptions/paypal/start', {
        plan_id: planId,
    });
};

// const cancelCurrentSubscription = () => {
//     if (!props.currentSubscription) return;

//     router.post(
//         `/billing/subscriptions/local/${props.currentSubscription.id}/cancel`,
//     );
// };
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
                    <Tabs v-model:value="activeTab">
                        <TabList>
                            <Tab value="subscription">Subscription</Tab>
                            <Tab value="usage">Usage</Tab>
                            <Tab value="plans">Plans</Tab>
                            <Tab value="payments">Payments</Tab>
                        </TabList>
                        <TabPanels>
                            <TabPanel value="subscription">
                                <div
                                    class="flex w-full flex-col gap-5 lg:flex-row"
                                >
                                    <div class="flex-1 space-y-6 lg:flex-1/2">
                                        <div
                                            v-if="currentSubscription"
                                            class="space-y-3 text-sm"
                                        >
                                            <div
                                                class="flex items-center justify-between gap-3 rounded-md border p-3"
                                            >
                                                <div class="space-y-1">
                                                    <div
                                                        class="text-base font-semibold"
                                                    >
                                                        {{
                                                            currentSubscription.plan_name ||
                                                            'Current subscription'
                                                        }}
                                                    </div>
                                                    <div
                                                        class="text-sm text-muted-foreground"
                                                    >
                                                        Provider:
                                                        {{
                                                            currentSubscription.provider
                                                        }}
                                                        • Status:
                                                        {{
                                                            currentSubscription.status
                                                        }}
                                                    </div>
                                                    <div
                                                        v-if="
                                                            currentSubscription.billing_period
                                                        "
                                                        class="text-sm text-muted-foreground"
                                                    >
                                                        Billing period:
                                                        {{
                                                            currentSubscription.billing_period
                                                        }}
                                                    </div>
                                                    <div
                                                        v-if="
                                                            currentSubscription.starts_at
                                                        "
                                                        class="text-sm text-muted-foreground"
                                                    >
                                                        Starts:
                                                        {{
                                                            new Date(
                                                                currentSubscription.starts_at,
                                                            ).toLocaleString()
                                                        }}
                                                    </div>
                                                    <div
                                                        v-if="
                                                            currentSubscription.ends_at
                                                        "
                                                        class="text-sm text-muted-foreground"
                                                    >
                                                        Ends:
                                                        {{
                                                            new Date(
                                                                currentSubscription.ends_at,
                                                            ).toLocaleString()
                                                        }}
                                                    </div>
                                                </div>
                                                <div
                                                    class="flex flex-col items-end gap-2 text-right"
                                                >
                                                    <div
                                                        v-if="
                                                            currentSubscription.amount !==
                                                                null &&
                                                            currentSubscription.currency
                                                        "
                                                        class="text-sm font-semibold"
                                                    >
                                                        {{
                                                            currentSubscription.currency
                                                        }}
                                                        {{
                                                            currentSubscription.amount.toLocaleString()
                                                        }}
                                                    </div>
                                                    <div
                                                        v-else
                                                        class="text-sm text-muted-foreground"
                                                    >
                                                        Amount based on selected
                                                        plan.
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div
                                            v-else
                                            class="py-4 text-center text-sm text-muted-foreground"
                                        >
                                            No subscription found. Please select
                                            a plan in the Plans tab.
                                        </div>

                                        <div class="space-y-3">
                                            <div class="space-y-1">
                                                <h3
                                                    class="text-sm font-semibold tracking-tight"
                                                >
                                                    All subscriptions
                                                </h3>
                                                <p
                                                    class="text-sm text-muted-foreground"
                                                >
                                                    History of your local
                                                    subscriptions.
                                                </p>
                                            </div>

                                            <div
                                                v-if="localSubscriptions.length"
                                                class="space-y-3 text-sm"
                                            >
                                                <div
                                                    v-for="subscription in localSubscriptions"
                                                    :key="subscription.id"
                                                    class="flex items-center justify-between gap-3 rounded-md border p-3"
                                                >
                                                    <div>
                                                        <div
                                                            class="font-medium"
                                                        >
                                                            {{
                                                                subscription.provider
                                                            }}
                                                            <span
                                                                v-if="
                                                                    subscription.billing_period
                                                                "
                                                                class="text-sm text-muted-foreground"
                                                            >
                                                                ({{
                                                                    subscription.billing_period
                                                                }})
                                                            </span>
                                                        </div>
                                                        <div
                                                            class="text-sm text-muted-foreground"
                                                        >
                                                            Status:
                                                            {{
                                                                subscription.status
                                                            }}
                                                        </div>
                                                        <div
                                                            v-if="
                                                                subscription.starts_at
                                                            "
                                                            class="text-sm text-muted-foreground"
                                                        >
                                                            Starts:
                                                            {{
                                                                new Date(
                                                                    subscription.starts_at,
                                                                ).toLocaleString()
                                                            }}
                                                        </div>
                                                    </div>
                                                    <div
                                                        class="flex flex-col items-end gap-1"
                                                    >
                                                        <div
                                                            v-if="
                                                                subscription.amount !==
                                                                    null &&
                                                                subscription.currency
                                                            "
                                                            class="text-sm font-semibold"
                                                        >
                                                            {{
                                                                subscription.currency
                                                            }}
                                                            {{
                                                                subscription.amount.toLocaleString()
                                                            }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <p
                                                v-else
                                                class="text-sm text-muted-foreground"
                                            >
                                                No subscriptions recorded yet.
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex-1 space-y-3 lg:flex-1/2">
                                        <div class="space-y-1">
                                            <h3
                                                class="text-sm font-semibold tracking-tight"
                                            >
                                                Addons
                                            </h3>
                                            <p
                                                class="text-sm text-muted-foreground"
                                            >
                                                Extra capacity or features that
                                                can be added on top of your
                                                plan.
                                            </p>
                                        </div>

                                        <div
                                            v-if="addons.length"
                                            class="space-y-3 text-sm"
                                        >
                                            <div
                                                v-for="addon in addons"
                                                :key="addon.id"
                                                class="flex items-center justify-between gap-3 rounded-md border p-3"
                                            >
                                                <div class="space-y-1">
                                                    <div class="font-medium">
                                                        {{ addon.name }}
                                                    </div>
                                                    <div
                                                        class="text-sm text-muted-foreground"
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
                                                            requestAddon(addon)
                                                        "
                                                    >
                                                        Request addon
                                                    </Button>
                                                </div>
                                            </div>
                                        </div>
                                        <p
                                            v-else
                                            class="text-sm text-muted-foreground"
                                        >
                                            No addons available for your account
                                            yet.
                                        </p>
                                    </div>
                                </div>
                            </TabPanel>

                            <TabPanel value="usage">
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
                                                            v-if="
                                                                item.is_unlimited
                                                            "
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
                                                        class="text-sm text-muted-foreground"
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

                            <TabPanel value="plans">
                                <div class="space-y-6">
                                    <div class="space-y-2">
                                        <h2
                                            class="text-lg font-semibold tracking-tight"
                                        >
                                            Choose a plan
                                        </h2>
                                        <p
                                            class="text-sm text-muted-foreground"
                                        >
                                            Select the plan you want to
                                            subscribe to or upgrade to, then
                                            choose your preferred payment
                                            method.
                                        </p>
                                    </div>

                                    <div class="grid gap-4 md:grid-cols-3">
                                        <Card
                                            v-for="p in availablePlans"
                                            :key="p.id"
                                            :class="[
                                                'cursor-pointer border',
                                                selectedPlanId === p.id
                                                    ? 'border-primary shadow-sm'
                                                    : 'border-border',
                                            ]"
                                            @click="
                                                () => {
                                                    selectedPlanId = p.id;
                                                    selectedPaymentMethod =
                                                        null;
                                                }
                                            "
                                        >
                                            <CardHeader class="space-y-1">
                                                <CardTitle
                                                    class="flex items-center justify-between text-sm font-medium"
                                                >
                                                    <span>{{ p.name }}</span>
                                                    <span
                                                        v-if="p.is_current"
                                                        class="rounded-full bg-emerald-50 px-2 py-0.5 text-sm font-semibold text-emerald-700"
                                                    >
                                                        Current
                                                    </span>
                                                </CardTitle>
                                            </CardHeader>
                                            <CardContent
                                                class="space-y-2 text-sm"
                                            >
                                                <div
                                                    class="text-lg font-semibold"
                                                >
                                                    {{ currencyLabel }}
                                                    {{
                                                        p.price_kes.toLocaleString()
                                                    }}
                                                    <span
                                                        class="text-sm font-normal text-muted-foreground"
                                                    >
                                                        / month
                                                    </span>
                                                </div>
                                                <div
                                                    v-if="
                                                        p.price_kes_yearly > 0
                                                    "
                                                    class="text-sm text-muted-foreground"
                                                >
                                                    {{ currencyLabel }}
                                                    {{
                                                        p.price_kes_yearly.toLocaleString()
                                                    }}
                                                    / year
                                                </div>
                                                <ul
                                                    class="mt-2 space-y-1 text-sm"
                                                >
                                                    <li
                                                        v-for="feature in p.features"
                                                        :key="feature.key"
                                                        class="flex items-start gap-2"
                                                    >
                                                        <Check
                                                            v-if="
                                                                feature.is_enabled
                                                            "
                                                            class="mt-0.5 h-3 w-3 text-emerald-500"
                                                        />
                                                        <X
                                                            v-else
                                                            class="mt-0.5 h-3 w-3 text-muted-foreground"
                                                        />
                                                        <div>
                                                            <div
                                                                class="font-medium"
                                                            >
                                                                {{
                                                                    feature.name
                                                                }}
                                                                <span
                                                                    v-if="
                                                                        feature.type ===
                                                                            'quota' &&
                                                                        feature.is_enabled
                                                                    "
                                                                    class="ml-1 text-[10px] text-muted-foreground"
                                                                >
                                                                    (
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
                                                                    )
                                                                </span>
                                                            </div>
                                                            <p
                                                                v-if="
                                                                    feature.description
                                                                "
                                                                class="text-[10px] text-muted-foreground"
                                                            >
                                                                {{
                                                                    feature.description
                                                                }}
                                                            </p>
                                                        </div>
                                                    </li>
                                                </ul>
                                                <Button
                                                    size="sm"
                                                    variant="outline"
                                                    class="mt-2 w-full"
                                                >
                                                    {{
                                                        selectedPlanId === p.id
                                                            ? 'Selected'
                                                            : 'Select'
                                                    }}
                                                </Button>
                                            </CardContent>
                                        </Card>
                                    </div>

                                    <div
                                        v-if="selectedPlanId"
                                        class="space-y-4 rounded-md border p-4 text-sm"
                                    >
                                        <div class="space-y-1">
                                            <h3 class="text-sm font-semibold">
                                                Payment method
                                            </h3>
                                            <div class="flex flex-wrap gap-2">
                                                <Button
                                                    size="sm"
                                                    variant="outline"
                                                    :class="
                                                        selectedPaymentMethod ===
                                                        'mpesa'
                                                            ? 'border-primary'
                                                            : ''
                                                    "
                                                    @click="
                                                        selectedPaymentMethod =
                                                            'mpesa'
                                                    "
                                                >
                                                    Mpesa
                                                </Button>
                                                <Button
                                                    size="sm"
                                                    variant="outline"
                                                    :class="
                                                        selectedPaymentMethod ===
                                                        'card'
                                                            ? 'border-primary'
                                                            : ''
                                                    "
                                                    @click="
                                                        selectedPaymentMethod =
                                                            'card'
                                                    "
                                                >
                                                    Credit card
                                                </Button>
                                                <Button
                                                    size="sm"
                                                    variant="outline"
                                                    :class="
                                                        selectedPaymentMethod ===
                                                        'paypal'
                                                            ? 'border-primary'
                                                            : ''
                                                    "
                                                    @click="
                                                        selectedPaymentMethod =
                                                            'paypal'
                                                    "
                                                >
                                                    PayPal
                                                </Button>
                                            </div>
                                        </div>

                                        <div
                                            v-if="
                                                selectedPaymentMethod ===
                                                'mpesa'
                                            "
                                            class="space-y-3"
                                        >
                                            <div class="space-y-1">
                                                <label
                                                    class="text-sm font-medium"
                                                    >Mpesa phone number</label
                                                >
                                                <input
                                                    v-model="mpesaPhone"
                                                    type="tel"
                                                    class="block w-full rounded-md border px-2 py-1 text-sm"
                                                    placeholder="e.g. 0712 345 678"
                                                />
                                            </div>
                                            <div class="space-y-1">
                                                <label
                                                    class="text-sm font-medium"
                                                    >Billing period</label
                                                >
                                                <select
                                                    v-model="
                                                        selectedBillingPeriod
                                                    "
                                                    class="block w-full rounded-md border px-2 py-1 text-sm"
                                                >
                                                    <option value="monthly">
                                                        Monthly
                                                    </option>
                                                    <option value="yearly">
                                                        Yearly
                                                    </option>
                                                </select>
                                            </div>
                                            <Button
                                                size="sm"
                                                class="mt-1"
                                                :disabled="!mpesaPhone"
                                                @click="
                                                    () => {
                                                        if (!selectedPlanId)
                                                            return;
                                                        createLocalSubscription(
                                                            selectedPlanId,
                                                            selectedBillingPeriod,
                                                            'mpesa',
                                                            {
                                                                phone: mpesaPhone,
                                                            },
                                                        );
                                                    }
                                                "
                                            >
                                                Pay with Mpesa
                                            </Button>
                                        </div>

                                        <div
                                            v-else-if="
                                                selectedPaymentMethod === 'card'
                                            "
                                            class="space-y-3"
                                        >
                                            <div class="space-y-1">
                                                <label
                                                    class="text-sm font-medium"
                                                    >Billing period</label
                                                >
                                                <select
                                                    v-model="
                                                        selectedBillingPeriod
                                                    "
                                                    class="block w-full rounded-md border px-2 py-1 text-sm"
                                                >
                                                    <option value="monthly">
                                                        Monthly
                                                    </option>
                                                    <option value="yearly">
                                                        Yearly
                                                    </option>
                                                </select>
                                            </div>
                                            <Button
                                                size="sm"
                                                class="mt-1"
                                                @click="
                                                    () => {
                                                        if (!selectedPlanId)
                                                            return;
                                                        startPaddleSubscription(
                                                            selectedPlanId,
                                                        );
                                                    }
                                                "
                                            >
                                                Pay with card (Paddle)
                                            </Button>
                                        </div>

                                        <div
                                            v-else-if="
                                                selectedPaymentMethod ===
                                                'paypal'
                                            "
                                            class="space-y-3"
                                        >
                                            <div class="space-y-1">
                                                <label
                                                    class="text-sm font-medium"
                                                    >Billing period</label
                                                >
                                                <select
                                                    v-model="
                                                        selectedBillingPeriod
                                                    "
                                                    class="block w-full rounded-md border px-2 py-1 text-sm"
                                                >
                                                    <option value="monthly">
                                                        Monthly
                                                    </option>
                                                    <option value="yearly">
                                                        Yearly
                                                    </option>
                                                </select>
                                            </div>
                                            <Button
                                                size="sm"
                                                class="mt-1"
                                                @click="
                                                    () => {
                                                        if (!selectedPlanId)
                                                            return;
                                                        startPaypalSubscription(
                                                            selectedPlanId,
                                                        );
                                                    }
                                                "
                                            >
                                                Continue with PayPal
                                            </Button>
                                        </div>
                                    </div>
                                </div>
                            </TabPanel>

                            <TabPanel value="payments">
                                <div class="space-y-4">
                                    <div class="space-y-1">
                                        <h2
                                            class="text-lg font-semibold tracking-tight"
                                        >
                                            Payment history
                                        </h2>
                                        <p
                                            class="text-sm text-muted-foreground"
                                        >
                                            A record of your recent subscription
                                            payments.
                                        </p>

                                        <div class="space-y-3 text-sm">
                                            <div
                                                v-if="localTransactions.length"
                                                class="space-y-3"
                                            >
                                                <div
                                                    v-for="transaction in localTransactions"
                                                    :key="transaction.id"
                                                    class="flex items-center justify-between gap-3 rounded-md border p-3"
                                                >
                                                    <div>
                                                        <div
                                                            class="font-medium"
                                                        >
                                                            {{
                                                                transaction.provider
                                                            }}
                                                            <span
                                                                v-if="
                                                                    transaction.local_subscription_id
                                                                "
                                                                class="text-sm text-muted-foreground"
                                                            >
                                                                (Subscription
                                                                #{{
                                                                    transaction.local_subscription_id
                                                                }})
                                                            </span>
                                                        </div>
                                                        <div
                                                            class="text-sm text-muted-foreground"
                                                        >
                                                            Status:
                                                            {{
                                                                transaction.status
                                                            }}
                                                        </div>
                                                        <div
                                                            v-if="
                                                                transaction.paid_at
                                                            "
                                                            class="text-sm text-muted-foreground"
                                                        >
                                                            Date:
                                                            {{
                                                                new Date(
                                                                    transaction.paid_at,
                                                                ).toLocaleString()
                                                            }}
                                                        </div>
                                                    </div>
                                                    <div
                                                        class="flex flex-col items-end gap-1"
                                                    >
                                                        <div
                                                            v-if="
                                                                transaction.amount !==
                                                                    null &&
                                                                transaction.currency
                                                            "
                                                            class="text-sm font-semibold"
                                                        >
                                                            {{
                                                                transaction.currency
                                                            }}
                                                            {{
                                                                transaction.amount.toLocaleString()
                                                            }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div
                                                v-else
                                                class="py-4 text-center text-muted-foreground"
                                            >
                                                No payments recorded yet.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </TabPanel>
                        </TabPanels>
                    </Tabs>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
