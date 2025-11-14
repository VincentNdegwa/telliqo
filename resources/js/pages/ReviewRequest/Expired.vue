<script setup lang="ts">
interface Business {
    name: string;
    logo_url: string | null;
}

interface Props {
    business: Business;
}

defineProps<Props>();
</script>

<template>
    <div
        class="flex min-h-screen items-center justify-center bg-gray-50 px-4 py-8"
    >
        <div class="w-full max-w-md">
            <div
                class="overflow-hidden rounded-lg bg-white p-8 text-center shadow-lg"
            >
                <div class="mb-6">
                    <div
                        class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-red-100"
                    >
                        <i class="pi pi-times-circle text-3xl text-red-600"></i>
                    </div>
                    <h1 class="mb-2 text-2xl font-bold text-gray-900">
                        Review Request Expired
                    </h1>
                    <p class="text-gray-600">
                        This review request link has expired and is no longer
                        valid.
                    </p>
                </div>

                <div class="mb-6 rounded-lg bg-gray-50 p-4">
                    <p class="text-sm text-gray-700">
                        Review requests expire after 30 days for security
                        purposes. If you still wish to provide feedback, please
                        contact us directly.
                    </p>
                </div>

                <div v-if="business.logo_url" class="mb-4">
                    <img
                        :src="business.logo_url"
                        :alt="business.name"
                        class="mx-auto h-12 object-contain"
                    />
                </div>
                <!-- Powered By -->
                <div class="mt-8 text-center text-sm text-muted-foreground">
                    <p>Powered by <span class="font-semibold">Telliqo</span></p>
                </div>
            </div>
        </div>
    </div>
</template>
