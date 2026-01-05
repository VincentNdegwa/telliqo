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
        <section class="relative overflow-hidden px-4 pt-24 pb-16 sm:pt-32 sm:pb-24">
            <!-- Background decoration -->
            <div class="pointer-events-none absolute inset-0 -z-10">
                <div class="absolute top-0 left-1/2 h-[600px] w-[600px] -translate-x-1/2 rounded-full bg-primary/5 blur-3xl"></div>
            </div>

            <div class="mx-auto max-w-6xl">
                <div class="grid items-center gap-12 lg:grid-cols-2">
                    <!-- Left: Content -->
                    <div class="text-center lg:text-left">
                        <!-- Badge -->
                        <div class="mb-6 inline-flex items-center gap-1.5 rounded-full border bg-primary/5 px-3 py-1 text-xs font-medium text-primary">
                            <Sparkles class="h-3 w-3" />
                            AI-Powered Reputation Management
                        </div>

                        <!-- Headline -->
                        <h1 class="mb-5 text-4xl font-bold tracking-tight sm:text-5xl lg:text-6xl">
                            Stop losing customers to
                            <span class="relative whitespace-nowrap">
                                <span class="relative text-primary">bad reviews</span>
                            </span>
                        </h1>

                        <!-- Subheadline -->
                        <p class="mx-auto mb-8 max-w-lg text-lg text-muted-foreground lg:mx-0">
                            Intercept unhappy customers before they go public. Route happy ones to Google. Build a 5-star reputation on autopilot.
                        </p>

                        <!-- CTA -->
                        <div class="flex flex-col gap-3 sm:flex-row sm:justify-center lg:justify-start">
                            <Link
                                v-if="canRegister"
                                :href="register()"
                                class="group inline-flex items-center justify-center gap-2 rounded-full bg-primary px-6 py-3.5 font-medium text-primary-foreground shadow-lg shadow-primary/25 transition-all hover:shadow-xl hover:shadow-primary/30"
                            >
                                Start free trial
                                <ArrowRight class="h-4 w-4 transition-transform group-hover:translate-x-0.5" />
                            </Link>
                            <a
                                href="#how-it-works"
                                class="inline-flex items-center justify-center gap-2 rounded-full border bg-background px-6 py-3.5 font-medium transition hover:bg-muted"
                            >
                                <Play class="h-4 w-4" />
                                See how it works
                            </a>
                        </div>

                        <!-- Trust indicators -->
                        <div class="mt-8 flex flex-wrap items-center justify-center gap-x-6 gap-y-2 text-sm text-muted-foreground lg:justify-start">
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

                    <!-- Right: Visual -->
                    <div class="relative hidden lg:block">
                        <!-- Main card - Dashboard preview -->
                        <div class="relative mx-auto w-full max-w-md rounded-2xl border bg-card p-5 shadow-2xl">
                            <!-- Header -->
                            <div class="mb-4 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="h-8 w-8 rounded-lg bg-primary/10 p-1.5">
                                        <Brain class="h-full w-full text-primary" />
                                    </div>
                                    <span class="text-sm font-medium">New Feedback</span>
                                </div>
                                <span class="text-xs text-muted-foreground">Just now</span>
                            </div>
                            
                            <!-- Feedback content -->
                            <div class="mb-4 rounded-xl bg-muted/50 p-4">
                                <div class="mb-2 flex items-center gap-2">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-amber-100 text-xs font-medium text-amber-700">JD</div>
                                    <div>
                                        <p class="text-sm font-medium">John D.</p>
                                        <div class="flex gap-0.5">
                                            <Star v-for="i in 3" :key="i" class="h-3 w-3 fill-yellow-400 text-yellow-400" />
                                            <Star v-for="i in 2" :key="i" class="h-3 w-3 text-muted-foreground/30" />
                                        </div>
                                    </div>
                                </div>
                                <p class="text-sm text-muted-foreground">"Service was slow today, had to wait 20 minutes..."</p>
                            </div>

                            <!-- AI Analysis -->
                            <div class="mb-4 flex items-center gap-3 rounded-lg border border-amber-200 bg-amber-50 p-3 dark:border-amber-800 dark:bg-amber-950/30">
                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-amber-500 text-white">
                                    <Shield class="h-4 w-4" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-medium text-amber-700 dark:text-amber-400">Negative sentiment detected</p>
                                    <p class="truncate text-xs text-amber-600 dark:text-amber-500">Kept private • Team notified</p>
                                </div>
                            </div>

                            <!-- Action buttons -->
                            <div class="flex gap-2">
                                <button class="flex-1 rounded-lg bg-primary px-3 py-2 text-xs font-medium text-primary-foreground">
                                    Respond with AI
                                </button>
                                <button class="rounded-lg border px-3 py-2 text-xs font-medium">
                                    View
                                </button>
                            </div>
                        </div>

                        <!-- Floating badge - Happy customer -->
                        <div class="absolute -right-4 top-8 rounded-xl border bg-card p-3 shadow-lg">
                            <div class="flex items-center gap-2">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-green-500 text-white">
                                    <Star class="h-4 w-4" />
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-green-600 dark:text-green-400">Happy customer!</p>
                                    <p class="text-xs text-muted-foreground">→ Sent to Google</p>
                                </div>
                            </div>
                        </div>

                        <!-- Stats badge -->
                        <div class="absolute -left-4 bottom-16 rounded-xl border bg-card p-3 shadow-lg">
                            <div class="flex items-center gap-3">
                                <div class="text-center">
                                    <p class="text-lg font-bold text-primary">4.8</p>
                                    <p class="text-xs text-muted-foreground">Rating</p>
                                </div>
                                <div class="h-8 w-px bg-border"></div>
                                <div class="text-center">
                                    <p class="text-lg font-bold text-green-500">+47%</p>
                                    <p class="text-xs text-muted-foreground">Reviews</p>
                                </div>
                            </div>
                        </div>
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
