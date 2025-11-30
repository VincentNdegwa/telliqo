<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { home } from '@/routes';
import { Link, usePage } from '@inertiajs/vue3';
import { ArrowRight, CheckCircle2 } from 'lucide-vue-next';
import ToastLayout from '../ToastLayout.vue';

defineProps<{
    title?: string;
    description?: string;
}>();

const appName = usePage().props.name;

const benefits = [
    'Centralize all customer feedback in one place',
    'Make data-driven decisions with confidence',
    'Improve customer satisfaction scores',
    'Save time with automated workflows',
];
</script>

<template>
    <ToastLayout>
        <div class="flex min-h-svh flex-col lg:flex-row">
            <!-- Left Panel - Welcome & Branding -->
            <div
                class="relative hidden flex-col justify-center overflow-hidden bg-gradient-to-br from-primary/5 via-primary/10 to-background p-12 lg:flex lg:w-1/2 xl:w-2/5"
            >
                <!-- Decorative elements -->
                <div
                    class="absolute top-1/4 -left-32 h-96 w-96 rounded-full bg-gradient-to-br from-primary/30 to-primary/10 blur-3xl"
                ></div>
                <div
                    class="absolute -right-32 bottom-1/4 h-96 w-96 rounded-full bg-gradient-to-tl from-primary/20 to-transparent blur-3xl"
                ></div>

                <!-- Animated dots pattern -->
                <div class="absolute inset-0 opacity-10">
                    <div
                        class="absolute top-1/3 left-1/4 h-2 w-2 animate-pulse rounded-full bg-primary"
                        style="animation-delay: 0s"
                    ></div>
                    <div
                        class="absolute top-1/4 right-1/3 h-2 w-2 animate-pulse rounded-full bg-primary"
                        style="animation-delay: 0.5s"
                    ></div>
                    <div
                        class="absolute bottom-1/3 left-1/3 h-2 w-2 animate-pulse rounded-full bg-primary"
                        style="animation-delay: 1s"
                    ></div>
                    <div
                        class="absolute right-1/4 bottom-1/4 h-2 w-2 animate-pulse rounded-full bg-primary"
                        style="animation-delay: 1.5s"
                    ></div>
                </div>

                <!-- Content -->
                <div class="relative z-10 max-w-lg space-y-12">
                    <!-- Logo & Brand -->
                    <div class="space-y-8">
                        <Link
                            :href="home()"
                            class="group inline-flex items-center gap-3"
                        >
                            <div class="relative">
                                <div
                                    class="absolute inset-0 rounded-full bg-primary/20 blur-xl transition-colors group-hover:bg-primary/30"
                                ></div>
                                <AppLogoIcon class="relative h-12 w-12" />
                            </div>
                            <span
                                class="bg-gradient-to-r from-foreground to-foreground/70 bg-clip-text text-3xl font-bold"
                                >{{ appName }}</span
                            >
                        </Link>

                        <div class="space-y-4">
                            <h2
                                class="text-4xl leading-tight font-bold xl:text-5xl"
                            >
                                Welcome to Your Feedback Hub
                            </h2>
                            <p
                                class="text-lg leading-relaxed text-muted-foreground"
                            >
                                Transform how you manage customer feedback. Get
                                started in minutes and join businesses already
                                making smarter decisions.
                            </p>
                        </div>
                    </div>

                    <!-- Benefits List -->
                    <div class="space-y-4">
                        <div
                            v-for="benefit in benefits"
                            :key="benefit"
                            class="group flex items-start gap-3"
                        >
                            <div
                                class="mt-0.5 flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-primary/10 text-primary transition-colors group-hover:bg-primary/20"
                            >
                                <CheckCircle2 class="h-4 w-4" />
                            </div>
                            <p class="text-base text-foreground/90">
                                {{ benefit }}
                            </p>
                        </div>
                    </div>

                    <!-- CTA Card -->
                    <div
                        class="rounded-2xl border border-primary/20 bg-gradient-to-br from-primary/10 via-primary/5 to-transparent p-6 backdrop-blur-sm"
                    >
                        <div class="space-y-4">
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-full bg-primary text-primary-foreground"
                                >
                                    <ArrowRight class="h-5 w-5" />
                                </div>
                                <div>
                                    <p class="text-base font-semibold">
                                        Ready to get started?
                                    </p>
                                    <p class="text-sm text-muted-foreground">
                                        Free 14-day trial, no credit card
                                        required
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Panel - Auth Form -->
            <div
                class="flex flex-1 items-center justify-center p-6 md:p-10 lg:p-12"
            >
                <div class="w-full max-w-md">
                    <div class="flex flex-col gap-8">
                        <!-- Mobile Logo -->
                        <Link
                            :href="home()"
                            class="mx-auto flex items-center gap-3 lg:hidden"
                        >
                            <AppLogoIcon class="h-9 w-9" />
                            <span class="text-xl font-bold">{{ appName }}</span>
                        </Link>

                        <!-- Title Section -->
                        <div class="space-y-2 text-center lg:text-left">
                            <h1
                                class="text-2xl font-bold tracking-tight md:text-3xl"
                            >
                                {{ title }}
                            </h1>
                            <p
                                class="text-sm text-muted-foreground md:text-base"
                            >
                                {{ description }}
                            </p>
                        </div>

                        <!-- Form Slot -->
                        <slot />
                    </div>
                </div>
            </div>
        </div>
    </ToastLayout>
</template>
