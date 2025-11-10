<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { toUrl, urlIsActive } from '@/lib/utils';
import business from '@/routes/business';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { 
    Building2, 
    Bell, 
    Eye, 
    Shield, 
    Heart, 
    LucideMessagesSquare,
    Plug, 
    BarChart3 
} from 'lucide-vue-next';

const sidebarNavItems: NavItem[] = [
    {
        title: 'Business Profile',
        href: business.settings(),
        icon: Building2,
    },
    {
        title: 'Feedback Settings',
        href: business.settings.feedback(),
        icon: LucideMessagesSquare,
    },
    {
        title: 'Display',
        href: business.settings.display(),
        icon: Eye,
    },
    {
        title: 'Notifications',
        href: business.settings.notifications(),
        icon: Bell,
    },
    {
        title: 'Moderation',
        href: business.settings.moderation(),
        icon: Shield,
    }
];

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
                    <Button
                        v-for="item in sidebarNavItems"
                        :key="toUrl(item.href)"
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
