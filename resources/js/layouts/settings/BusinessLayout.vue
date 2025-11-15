<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { usePermissions } from '@/composables/usePermissions';
import { toUrl, urlIsActive } from '@/lib/utils';
import business from '@/routes/business';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import {
    Bell,
    Building2,
    Eye,
    LucideMessagesSquare,
    Shield,
} from 'lucide-vue-next';

const sidebarNavItems: NavItem[] = [
    {
        title: 'Business Profile',
        href: business.settings(),
        icon: Building2,
        permission: "business-settings.manage",
    },
    {
        title: 'Feedback Settings',
        href: business.settings.feedback(),
        icon: LucideMessagesSquare,
        permission: "business-settings.feedback",
    },
    {
        title: 'Display',
        href: business.settings.display(),
        icon: Eye,
        permission: "business-settings.display",
    },
    {
        title: 'Notifications',
        href: business.settings.notifications(),
        icon: Bell,
        permission: "business-settings.notifications",
    },
    {
        title: 'Moderation',
        href: business.settings.moderation(),
        icon: Shield,
        permission: "business-settings.moderation", 
    },
];

const { can, canAny } = usePermissions();

const hasPermission = (permission: string | string[] | null | undefined): boolean => {
    if (!permission) {
        return true;
    }

    if (Array.isArray(permission)) {
        return canAny(permission);
    }

    return can(permission);
};

const currentPath = typeof window !== undefined ? window.location.pathname : '';
</script>

<template>
    <div class="px-4 py-6">
        <Heading
            title="Settings"
            description="Manage business profile and settings"
        />

        <div class="flex flex-col lg:flex-row lg:space-x-12">
            <aside class="w-full max-w-xl lg:w-48">
                <nav class="flex flex-col space-y-1 space-x-0">
                    <template v-for="item in sidebarNavItems" :key="toUrl(item.href)">
                        <Button
                            v-if="hasPermission(item.permission)"
                            variant="ghost"
                            :class="[
                                'w-full justify-start',
                                { 'bg-muted': urlIsActive(item.href, currentPath) },
                            ]"
                            as-child
                        >
                            <Link :href="item.href">
                                <component :is="item.icon" class="h-4 w-4" />
                                {{ item.title }}
                            </Link>
                        </Button>
                    </template>
                </nav>
            </aside>

            <Separator class="my-6 lg:hidden" />

            <div class="flex-1">
                <section class="max-w-4xl space-y-12">
                    <slot />
                </section>
            </div>
        </div>
    </div>
</template>
