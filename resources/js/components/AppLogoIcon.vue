<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();

const appName = usePage().props.name || 'Telliqo';
// served from public/ — use root URL
const logo = '/telliqo.svg';
</script>

<template>
    <img :src="logo" :alt="`${appName} logo`" :class="className" v-bind="$attrs" />
</template>
