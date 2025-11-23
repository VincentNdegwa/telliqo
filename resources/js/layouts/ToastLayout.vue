<script setup lang="ts">
import { useToast } from '@/composables/useToast';
import { usePage } from '@inertiajs/vue3';
import { CheckCircle, Info, X, XCircle } from 'lucide-vue-next';
import { onBeforeUnmount, onMounted, watch } from 'vue';

const page = usePage();
const { toasts, toast } = useToast();

function pushFlashToasts() {
    const flash = (page.props as any).flash ?? {};
    if (flash.success) toast.success(String(flash.success));
    if (flash.error) toast.error(String(flash.error));
}

onMounted(() => {
    pushFlashToasts();
    const inertia = (window as any).Inertia;
    if (inertia && inertia.on) inertia.on('success', pushFlashToasts);
    watch(
        () => page.props as any,
        () => {
            pushFlashToasts();
        },
    );
});

onBeforeUnmount(() => {
    const inertia = (window as any).Inertia;
    if (inertia && inertia.off) inertia.off('success', pushFlashToasts);
});
</script>

<template>
    <div>
        <slot />

        <!-- Toast container -->
        <div
            class="pointer-events-none fixed inset-0 z-50 flex items-start justify-end p-6"
        >
            <div class="w-full max-w-xs space-y-2">
                <div
                    v-for="toast in toasts"
                    :key="toast.id"
                    class="pointer-events-auto overflow-hidden rounded-lg shadow-lg"
                    :class="{
                        'bg-green-50 ring-1 ring-green-600/20':
                            toast.type === 'success',
                        'bg-red-50 ring-1 ring-red-600/20':
                            toast.type === 'error',
                        'bg-white ring-1 ring-gray-200': toast.type === 'info',
                    }"
                >
                    <div class="flex items-start gap-3 p-3">
                        <div class="mt-0.5 flex-shrink-0">
                            <CheckCircle
                                v-if="toast.type === 'success'"
                                class="h-6 w-6 text-green-600"
                            />
                            <XCircle
                                v-else-if="toast.type === 'error'"
                                class="h-6 w-6 text-red-600"
                            />
                            <Info v-else class="h-6 w-6 text-gray-600" />
                        </div>
                        <div class="flex-1 pr-2">
                            <p
                                class="text-sm font-medium"
                                :class="{
                                    'text-green-800': toast.type === 'success',
                                    'text-red-800': toast.type === 'error',
                                    'text-gray-900': toast.type === 'info',
                                }"
                            >
                                {{ toast.message }}
                            </p>
                        </div>
                        <div class="flex-shrink-0 self-start">
                            <button
                                @click="
                                    toasts = toasts.filter(
                                        (t: { id: string }) => t.id !== toast.id,
                                    )
                                "
                                class="text-gray-400 hover:text-gray-600"
                            >
                                <span class="sr-only">Dismiss</span>
                                <X class="h-5 w-5" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* ensure pointer events only on toasts */
.pointer-events-auto {
    pointer-events: auto;
}
</style>