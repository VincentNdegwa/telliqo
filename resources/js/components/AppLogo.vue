<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { usePage } from '@inertiajs/vue3';

const app = usePage().props.name;
const brand = usePage().props.brand || 'EpochWeave';
</script>

<template>
    <AppLogoIcon class="size-11 fill-current text-white dark:text-black" />
    <div class="ml-0 grid flex-1 text-start text-sm">
        <span class="mb-0.5 truncate leading-tight font-semibold">{{ app }}</span>
        <span class="text-xs text-muted-foreground truncate"
            >Powered by {{ brand }}</span>
    </div>
</template>
