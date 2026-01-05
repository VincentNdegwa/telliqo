<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { dashboard, login, register } from '@/routes';
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    ArrowRight,
    Bell,
    Brain,
    Check,
    Lightbulb,
    MessageSquare,
    MousePointerClick,
    Play,
    QrCode,
    Shield,
    Sparkles,
    Star,
    Users,
    Zap,
} from 'lucide-vue-next';

withDefaults(
    defineProps<{
        canRegister: boolean;
    }>(),
    {
        canRegister: true,
    },
);

const appName = usePage().props.name;

// Core features - simplified
const features = [
    {
        icon: Brain,
        title: 'AI Sentiment Analysis',
        description: 'Instantly detect customer mood and prioritize responses.',
    },
    {
        icon: Lightbulb,
        title: 'Smart Replies',
        description: 'AI-generated response suggestions in seconds.',
    },
    {
        icon: MousePointerClick,
        title: 'One-Click Feedback',
        description: 'Frictionless collection—no accounts required.',
    },
    {
        icon: Bell,
        title: 'Auto Reminders',
        description: 'Gentle nudges that triple your review count.',
    },
    {
        icon: Users,
        title: 'Team Inbox',
        description: 'Collaborate and assign reviews to your team.',
    },
    {
        icon: Shield,
        title: 'Brand Protection',
        description: 'Catch negative feedback before it goes public.',
    },
];

// Social proof stats
const stats = [
    { value: '4.8★', label: 'Average boost' },
    { value: '3x', label: 'More reviews' },
    { value: '85%', label: 'Issues resolved' },
];

// Testimonials - simplified
const testimonials = [
    {
        name: 'Sarah J.',
        role: 'Cafe Owner',
        comment: 'Went from 3.8 to 4.7 stars in just 3 months.',
        avatar: 'S',
    },
    {
        name: 'Michael C.',
        role: 'Restaurant Manager',
        comment: 'Reviews increased 340%. The AI replies save me hours.',
        avatar: 'M',
    },
    {
        name: 'Emily R.',
        role: 'Retail Director',
        comment: 'Finally, all our locations in one dashboard.',
        avatar: 'E',
    },
];
</script>

<template>
    <Head :title="`${appName} — Turn Feedback Into 5-Star Reviews`" />

    <div class="min-h-screen bg-background text-foreground">
        <!-- Navigation -->
        <nav class="fixed top-0 z-50 w-full border-b bg-background/80 backdrop-blur-lg">
            <div class="mx-auto flex h-14 max-w-6xl items-center justify-between px-4">
                <Link href="/" class="flex items-center gap-2">
                    <AppLogoIcon class="h-8 w-8" />
                    <span class="font-semibold">{{ appName }}</span>
                </Link>

                <div class="flex items-center gap-2">
                    <template v-if="$page.props.auth.user">
                        <Link
                            :href="dashboard().url"
                            class="inline-flex items-center gap-1.5 rounded-full bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition hover:bg-primary/90"
                        >
                            Dashboard
                            <ArrowRight class="h-3.5 w-3.5" />
                        </Link>
                    </template>
                    <template v-else>
                        <Link
                            :href="login()"
                            class="hidden px-3 py-2 text-sm font-medium text-muted-foreground transition hover:text-foreground sm:block"
                        >
                            Log in
                        </Link>
                        <Link
                            v-if="canRegister"
                            :href="register()"
                            class="inline-flex items-center gap-1.5 rounded-full bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition hover:bg-primary/90"
                        >
                            Get Started
                            <ArrowRight class="h-3.5 w-3.5" />
                        </Link>
                    </template>
                </div>
            </div>
        </nav>

        <!-- Hero -->
        <section class="relative flex min-h-[85vh] items-center justify-center px-4">
            <div class="mx-auto max-w-3xl text-center">
                <!-- Badge -->
                <div class="mb-6 inline-flex items-center gap-1.5 rounded-full border bg-muted/50 px-3 py-1 text-xs font-medium text-muted-foreground">
                    <Zap class="h-3 w-3 text-primary" />
                    AI-Powered Reputation Management
                </div>

                <!-- Headline -->
                <h1 class="mb-4 text-4xl font-bold tracking-tight sm:text-5xl md:text-6xl">
                    Turn feedback into<br />
                    <span class="text-primary">5-star reviews</span>
                </h1>

                <!-- Subheadline -->
                <p class="mx-auto mb-8 max-w-xl text-lg text-muted-foreground">
                    Capture customer feedback privately. Route happy customers to public reviews. Resolve issues before they hurt your brand.
                </p>

                <!-- CTA -->
                <div class="flex flex-col items-center gap-3 sm:flex-row sm:justify-center">
                    <Link
                        v-if="canRegister"
                        :href="register()"
                        class="inline-flex items-center gap-2 rounded-full bg-primary px-6 py-3 font-medium text-primary-foreground shadow-lg shadow-primary/20 transition-all hover:shadow-xl hover:shadow-primary/30"
                    >
                        Start free trial
                        <ArrowRight class="h-4 w-4" />
                    </Link>
                    <a
                        href="#how-it-works"
                        class="inline-flex items-center gap-2 rounded-full border px-6 py-3 font-medium transition hover:bg-muted"
                    >
                        <Play class="h-4 w-4" />
                        See how it works
                    </a>
                </div>

                <!-- Trust indicators -->
                <div class="mt-12 flex flex-wrap items-center justify-center gap-8 text-sm text-muted-foreground">
                    <div class="flex items-center gap-1.5">
                        <Check class="h-4 w-4 text-green-500" />
                        No credit card
                    </div>
                    <div class="flex items-center gap-1.5">
                        <Check class="h-4 w-4 text-green-500" />
                        14-day free trial
                    </div>
                    <div class="flex items-center gap-1.5">
                        <Check class="h-4 w-4 text-green-500" />
                        Cancel anytime
                    </div>
                </div>
            </div>
        </section>

        <!-- Stats bar -->
        <section class="border-y bg-muted/30">
            <div class="mx-auto flex max-w-4xl flex-wrap items-center justify-center gap-12 px-4 py-8 sm:gap-16">
                <div v-for="stat in stats" :key="stat.label" class="text-center">
                    <div class="text-2xl font-bold text-primary sm:text-3xl">{{ stat.value }}</div>
                    <div class="text-sm text-muted-foreground">{{ stat.label }}</div>
                </div>
            </div>
        </section>

        <!-- How it works -->
        <section id="how-it-works" class="py-20">
            <div class="mx-auto max-w-6xl px-4">
                <div class="mb-16 text-center">
                    <h2 class="mb-3 text-3xl font-bold sm:text-4xl">How it works</h2>
                    <p class="text-muted-foreground">Three simple steps to better reviews</p>
                </div>

                <div class="grid gap-8 md:grid-cols-3">
                    <!-- Step 1 -->
                    <div class="relative rounded-2xl border bg-card p-6 text-center">
                        <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-xl bg-primary text-lg font-bold text-primary-foreground">
                            1
                        </div>
                        <h3 class="mb-2 font-semibold">Collect Feedback</h3>
                        <p class="text-sm text-muted-foreground">
                            Send one-click feedback links via email, SMS, or QR code.
                        </p>
                    </div>

                    <!-- Step 2 -->
                    <div class="relative rounded-2xl border bg-card p-6 text-center">
                        <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-xl bg-primary text-lg font-bold text-primary-foreground">
                            2
                        </div>
                        <h3 class="mb-2 font-semibold">AI Analyzes Sentiment</h3>
                        <p class="text-sm text-muted-foreground">
                            Our AI instantly detects if feedback is positive or negative.
                        </p>
                    </div>

                    <!-- Step 3 -->
                    <div class="relative rounded-2xl border bg-card p-6 text-center">
                        <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-xl bg-primary text-lg font-bold text-primary-foreground">
                            3
                        </div>
                        <h3 class="mb-2 font-semibold">Smart Routing</h3>
                        <p class="text-sm text-muted-foreground">
                            Happy → Google/Yelp. Unhappy → your inbox for private resolution.
                        </p>
                    </div>
                </div>

                <!-- Visual flow -->
                <div class="mt-12 rounded-2xl border bg-gradient-to-r from-green-50 to-amber-50 p-6 dark:from-green-950/20 dark:to-amber-950/20">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="flex items-center gap-4 rounded-xl bg-white/80 p-4 dark:bg-card/80">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-green-500 text-white">
                                <Star class="h-5 w-5" />
                            </div>
                            <div>
                                <p class="font-medium text-green-700 dark:text-green-400">Happy customers</p>
                                <p class="text-sm text-muted-foreground">→ Directed to leave public reviews</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 rounded-xl bg-white/80 p-4 dark:bg-card/80">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-amber-500 text-white">
                                <MessageSquare class="h-5 w-5" />
                            </div>
                            <div>
                                <p class="font-medium text-amber-700 dark:text-amber-400">Unhappy customers</p>
                                <p class="text-sm text-muted-foreground">→ Captured privately for resolution</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- QR Code section -->
        <section class="border-y bg-muted/30 py-20">
            <div class="mx-auto max-w-6xl px-4">
                <div class="grid items-center gap-12 lg:grid-cols-2">
                    <!-- Content -->
                    <div>
                        <div class="mb-4 inline-flex items-center gap-1.5 rounded-full border bg-background px-3 py-1 text-xs font-medium">
                            <QrCode class="h-3 w-3 text-primary" />
                            Walk-in Reviews
                        </div>
                        <h2 class="mb-4 text-3xl font-bold sm:text-4xl">
                            Capture reviews from walk-in customers
                        </h2>
                        <p class="mb-6 text-muted-foreground">
                            Print QR code posters for your tables, checkout counter, or receipts. Customers scan and leave feedback in seconds—no app download needed.
                        </p>
                        <ul class="mb-8 space-y-3">
                            <li class="flex items-center gap-2 text-sm">
                                <Check class="h-4 w-4 text-green-500" />
                                Custom branded posters
                            </li>
                            <li class="flex items-center gap-2 text-sm">
                                <Check class="h-4 w-4 text-green-500" />
                                Works on any smartphone
                            </li>
                            <li class="flex items-center gap-2 text-sm">
                                <Check class="h-4 w-4 text-green-500" />
                                Track scans by location
                            </li>
                        </ul>
                        <Link
                            v-if="canRegister"
                            :href="register()"
                            class="inline-flex items-center gap-2 rounded-full bg-primary px-5 py-2.5 text-sm font-medium text-primary-foreground transition hover:bg-primary/90"
                        >
                            Get your free QR poster
                            <ArrowRight class="h-4 w-4" />
                        </Link>
                    </div>

                    <!-- QR Visual -->
                    <div class="flex justify-center">
                        <div class="w-full max-w-xs rounded-2xl border bg-card p-6 shadow-xl">
                            <div class="mb-4 rounded-xl bg-primary p-4 text-center text-primary-foreground">
                                <div class="flex items-center justify-center gap-1.5">
                                    <AppLogoIcon class="h-6 w-6 brightness-0 invert" />
                                    <span class="font-semibold">{{ appName }}</span>
                                </div>
                            </div>
                            <p class="mb-4 text-center text-sm font-medium">How was your visit?</p>
                            <div class="mx-auto mb-4 w-fit rounded-xl border-2 border-muted bg-white p-3">
                                <QrCode class="h-24 w-24 text-foreground" />
                            </div>
                            <p class="text-center text-xs text-muted-foreground">
                                Scan to share your experience
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features -->
        <section id="features" class="py-20">
            <div class="mx-auto max-w-6xl px-4">
                <div class="mb-16 text-center">
                    <h2 class="mb-3 text-3xl font-bold sm:text-4xl">Everything you need</h2>
                    <p class="text-muted-foreground">Powerful features to manage your reputation</p>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="feature in features"
                        :key="feature.title"
                        class="group rounded-xl border bg-card p-5 transition hover:border-primary/50 hover:shadow-md"
                    >
                        <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10 text-primary transition group-hover:bg-primary group-hover:text-primary-foreground">
                            <component :is="feature.icon" class="h-5 w-5" />
                        </div>
                        <h3 class="mb-1 font-semibold">{{ feature.title }}</h3>
                        <p class="text-sm text-muted-foreground">{{ feature.description }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Testimonials -->
        <section id="testimonials" class="border-y bg-muted/30 py-20">
            <div class="mx-auto max-w-6xl px-4">
                <div class="mb-16 text-center">
                    <h2 class="mb-3 text-3xl font-bold sm:text-4xl">Loved by businesses</h2>
                    <p class="text-muted-foreground">See what our customers say</p>
                </div>

                <div class="grid gap-6 md:grid-cols-3">
                    <div
                        v-for="testimonial in testimonials"
                        :key="testimonial.name"
                        class="rounded-xl border bg-card p-5"
                    >
                        <div class="mb-3 flex gap-0.5">
                            <Star v-for="i in 5" :key="i" class="h-4 w-4 fill-yellow-400 text-yellow-400" />
                        </div>
                        <p class="mb-4 text-sm text-muted-foreground">"{{ testimonial.comment }}"</p>
                        <div class="flex items-center gap-3">
                            <div class="flex h-9 w-9 items-center justify-center rounded-full bg-primary text-sm font-medium text-primary-foreground">
                                {{ testimonial.avatar }}
                            </div>
                            <div>
                                <p class="text-sm font-medium">{{ testimonial.name }}</p>
                                <p class="text-xs text-muted-foreground">{{ testimonial.role }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Final CTA -->
        <section class="py-20">
            <div class="mx-auto max-w-2xl px-4 text-center">
                <div class="mb-4 inline-flex items-center gap-1.5 rounded-full border bg-muted/50 px-3 py-1 text-xs font-medium text-muted-foreground">
                    <Sparkles class="h-3 w-3 text-primary" />
                    Start today
                </div>
                <h2 class="mb-4 text-3xl font-bold sm:text-4xl">
                    Ready to build your 5-star reputation?
                </h2>
                <p class="mb-8 text-muted-foreground">
                    Join thousands of businesses turning customer feedback into growth.
                </p>
                <Link
                    v-if="canRegister"
                    :href="register()"
                    class="inline-flex items-center gap-2 rounded-full bg-primary px-8 py-4 text-lg font-medium text-primary-foreground shadow-lg shadow-primary/20 transition-all hover:shadow-xl hover:shadow-primary/30"
                >
                    Start 14-day free trial
                    <ArrowRight class="h-5 w-5" />
                </Link>
                <div class="mt-6 flex flex-wrap items-center justify-center gap-6 text-sm text-muted-foreground">
                    <span class="flex items-center gap-1.5">
                        <Check class="h-4 w-4 text-green-500" />
                        No credit card
                    </span>
                    <span class="flex items-center gap-1.5">
                        <Check class="h-4 w-4 text-green-500" />
                        5-minute setup
                    </span>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="border-t py-8">
            <div class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-4 px-4 sm:flex-row">
                <div class="flex items-center gap-2">
                    <AppLogoIcon class="h-6 w-6" />
                    <span class="text-sm font-medium">{{ appName }}</span>
                </div>
                <div class="flex items-center gap-6 text-sm text-muted-foreground">
                    <a href="#features" class="transition hover:text-foreground">Features</a>
                    <a href="#" class="transition hover:text-foreground">Privacy</a>
                    <a href="#" class="transition hover:text-foreground">Terms</a>
                </div>
                <p class="text-sm text-muted-foreground">
                    © {{ new Date().getFullYear() }} {{ appName }}
                </p>
            </div>
        </footer>
    </div>
</template>
