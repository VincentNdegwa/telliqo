<script setup lang="ts">
import FlashMessages from '@/components/FlashMessages.vue';


</script>

<template>
    <FlashMessages/>

    <div class="min-h-screen bg-background" >
        <slot />
    </div>
</template>
    