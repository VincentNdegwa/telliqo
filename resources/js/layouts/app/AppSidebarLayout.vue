<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import type { BreadcrumbItemType } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { useEchoModel } from '@laravel/echo-vue';
import { onMounted } from 'vue';
import ToastLayout from '../ToastLayout.vue';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const page = usePage<any>();

onMounted(() => {
    if ('Notification' in window && Notification.permission === 'default') {
        Notification.requestPermission();
    }

    const user = (page.props as any)?.auth?.user;

    if (!user) return;

    const { channel } = useEchoModel('App.Models.User', user.id);

    channel().notification((notification: any) => {
        const customerId = (notification as any).customer_id as
            | number
            | undefined;
        const type = (notification as any).type || '';

        if (
            !customerId ||
            (type !== 'App\\Notifications\\CustomerCommentNotification' &&
                type !== 'App\\Notifications\\CommentMentionNotification')
        ) {
            return;
        }

        if ('Notification' in window && Notification.permission === 'granted') {
            const title =
                type === 'App\\Notifications\\CommentMentionNotification'
                    ? 'You were mentioned in a comment'
                    : 'New comment on a customer';

            new Notification(title, {
                body: (notification as any).body ?? `Customer #${customerId}`,
                tag: `customer-comment-${customerId}`,
            });
        }

        window.dispatchEvent(
            new CustomEvent('customer-comments-updated', {
                detail: { customer_id: customerId },
            }),
        );
    });
});
</script>

<template>
    <ToastLayout>
        <AppShell variant="sidebar">
            <AppSidebar />
            <AppContent variant="sidebar" class="">
                <AppSidebarHeader :breadcrumbs="breadcrumbs" />
                <slot />
            </AppContent>
        </AppShell>
    </ToastLayout>
</template>
