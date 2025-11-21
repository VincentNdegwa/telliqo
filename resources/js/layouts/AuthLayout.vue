<script setup lang="ts">
import FlashMessages from '@/components/FlashMessages.vue';
import AuthLayout from '@/layouts/auth/AuthSimpleLayout.vue';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <AuthLayout :title="title" :description="description">
        <FlashMessages />
        <slot />
    </AuthLayout>
</template>
