<script setup lang="ts">
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItemType } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItemType[];
    }>(),
    {
        breadcrumbs: () => [],
    },
);

const page = usePage();

const subscription = computed(() => page.props.subscription ?? null);

const hasActiveSubscription = computed(
    () => subscription.value?.has_active ?? page.props.hasSubscription ?? false,
);

const planName = computed(() => {
    return (
        subscription.value?.plan_name ||
        page.props.auth.business?.plan?.name ||
        'No plan'
    );
});

const daysRemaining = computed(() => {
    const endsAt = subscription.value?.ends_at;
    if (!endsAt) return null;

    const end = new Date(endsAt);
    const now = new Date();
    const diffMs = end.getTime() - now.getTime();
    const diffDays = Math.ceil(diffMs / (1000 * 60 * 60 * 24));

    return diffDays;
});
</script>

<template>
    <header
        class="flex h-16 shrink-0 items-center gap-2 border-b border-sidebar-border/70 px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4"
    >
        <div class="flex items-center gap-2">
            <SidebarTrigger class="-ml-1" />
            <template v-if="breadcrumbs && breadcrumbs.length > 0">
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </template>
        </div>

        <div class="ml-auto flex items-center gap-3 text-xs">
            <div
                class="flex items-center gap-2 rounded-full border bg-muted/60 px-3 py-1"
            >
                <span
                    class="h-2 w-2 rounded-full"
                    :class="
                        hasActiveSubscription
                            ? 'bg-emerald-500'
                            : 'bg-muted-foreground'
                    "
                />
                <span class="font-medium">
                    {{ planName }}
                </span>
            </div>

            <div class="text-muted-foreground">
                <span v-if="daysRemaining !== null">
                    <span v-if="daysRemaining > 0">
                        {{ daysRemaining }} day{{
                            daysRemaining === 1 ? '' : 's'
                        }}
                        left
                    </span>
                    <span v-else> Renews today </span>
                </span>
                <span v-else>
                    {{
                        hasActiveSubscription
                            ? 'Renews automatically'
                            : 'No active subscription'
                    }}
                </span>
            </div>
        </div>
    </header>
</template>
