<script setup lang="ts">
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import business from '@/routes/business';
import feedback from '@/routes/feedback';
import qrCode from '@/routes/qr-code';
import team from '@/routes/team';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import {
    Key,
    LayoutGrid,
    MessageSquare,
    QrCode,
    Send,
    Settings,
    Shield,
    Users,
} from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import NavFooter from './NavFooter.vue';
import apiKeys from '@/routes/api-keys';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Feedback',
        href: feedback.index(),
        icon: MessageSquare,
    },
    {
        title: 'Customers',
        href: '/customers',
        icon: Users,
    },
    {
        title: 'Review Requests',
        href: '/review-requests',
        icon: Send,
    },
    {
        title: 'QR Code',
        href: qrCode.index(),
        icon: QrCode,
    },
    {
        title: 'Team',
        href: team.users.index(),
        icon: Shield,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'API Integration',
        href: apiKeys.index(),
        icon: Key,
    },
    {
        title: 'Business Settings',
        href: business.settings(),
        icon: Settings,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
