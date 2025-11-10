<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';
import { watch } from 'vue';

interface FlashMessages {
    success?: string | null;
    error?: string | null;
    warning?: string | null;
    info?: string | null;
}

const page = usePage();

const toast = useToast();

watch(
    () => page.props.flash as FlashMessages,
    (flash) => {
        if (flash.success) {
            toast.add({
                severity: 'success',
                summary: 'Success',
                detail: flash.success,
                life: 5000,
            });
        }

        if (flash.error) {
            toast.add({
                severity: 'error',
                summary: 'Error',
                detail: flash.error,
                life: 5000,
            });
        }

        if (flash.warning) {
            toast.add({
                severity: 'warn',
                summary: 'Warning',
                detail: flash.warning,
                life: 5000,
            });
        }

        if (flash.info) {
            toast.add({
                severity: 'info',
                summary: 'Information',
                detail: flash.info,
                life: 5000,
            });
        }
    },
    { deep: true, immediate: true }
);
</script>

<template>
    <Toast position="top-right" />
</template>
