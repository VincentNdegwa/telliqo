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
import apiKeys from '@/routes/api-keys';
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

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
        permission: 'dashboard.manage',
    },
    {
        title: 'Feedback',
        href: feedback.index(),
        icon: MessageSquare,
        permission: 'feedback.manage',
    },
    {
        title: 'Customers',
        href: '/customers',
        icon: Users,
        permission: 'customer.manage',
    },
    {
        title: 'Review Requests',
        href: '/review-requests',
        icon: Send,
        permission: 'review-request.manage',
    },
    {
        title: 'QR Code',
        href: qrCode.index(),
        icon: QrCode,
        permission: 'qr.manage',
    },
    {
        title: 'Team',
        href: team.users.index(),
        icon: Shield,
        permission: 'team.user-manage',
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'API Integration',
        href: apiKeys.index(),
        icon: Key,
        permission: 'api-integration.manage',
    },
    {
        title: 'Business Settings',
        href: business.settings(),
        icon: Settings,
        permission: 'business-settings.manage',
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
