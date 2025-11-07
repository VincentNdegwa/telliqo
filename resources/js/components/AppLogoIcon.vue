<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();

// served from public/ — use root URL
const logo = '/telliqo.svg';
</script>

<template>
    <img
        :src="logo"
        alt="Telliqo logo"
        :class="className"
        v-bind="$attrs"
    />
</template>
