<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { dashboard, login, register } from '@/routes';
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    ArrowRight,
    ArrowUpRight,
    BarChart3,
    Bell,
    Brain,
    CheckCircle2,
    EyeOff,
    Globe,
    Heart,
    Lightbulb,
    Lock,
    MessageCircle,
    MousePointerClick,
    QrCode,
    Send,
    Shield,
    Smile,
    Sparkles,
    Star,
    Target,
    ThumbsUp,
    TrendingUp,
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

// Platform integrations
const platforms = [
    { name: 'Google', icon: Globe },
    { name: 'Facebook', icon: ThumbsUp },
    { name: 'Yelp', icon: Star },
    { name: 'TripAdvisor', icon: MessageCircle },
];

// Stats
const stats = [
    { value: '4.8★', label: 'Avg Rating Boost', color: 'text-yellow-500' },
    { value: '300%', label: 'More Feedback', color: 'text-emerald-500' },
    { value: '10x', label: 'Faster Replies', color: 'text-blue-500' },
    { value: '85%', label: 'Issues Resolved', color: 'text-purple-500' },
];

// Features with explicit color classes (Tailwind JIT needs static classes)
const features = [
    {
        icon: Brain,
        title: 'AI Sentiment Detection',
        description:
            'Instantly know if customers are happy or frustrated. Prioritize what matters.',
        benefit: 'Save 5+ hours/week',
        bgClass: 'bg-violet-500',
    },
    {
        icon: Lightbulb,
        title: 'Smart Reply Suggestions',
        description:
            'AI drafts professional replies in seconds. Review, personalize, send.',
        benefit: '10x faster replies',
        bgClass: 'bg-amber-500',
    },
    {
        icon: MousePointerClick,
        title: 'One-Click Feedback',
        description:
            'No accounts needed. One-click links mean more responses, less friction.',
        benefit: '3x more feedback',
        bgClass: 'bg-blue-500',
    },
    {
        icon: Bell,
        title: 'Smart Reminders',
        description:
            'Auto-nudge customers who forgot to review. Polite and effective.',
        benefit: '300% more reviews',
        bgClass: 'bg-emerald-500',
    },
    {
        icon: Users,
        title: 'Team Collaboration',
        description:
            'Tag managers instantly. Keep your whole team in the loop.',
        benefit: '50% faster resolution',
        bgClass: 'bg-indigo-500',
    },
    {
        icon: Shield,
        title: 'Brand Protection',
        description:
            'AI flags spam and inappropriate content before it hurts your brand.',
        benefit: '24/7 protection',
        bgClass: 'bg-rose-500',
    },
];

// Funnel steps
const funnelSteps = [
    { step: 1, title: 'Request', desc: 'Send review links', icon: Send },
    { step: 2, title: 'Analyze', desc: 'AI detects sentiment', icon: Brain },
    { step: 3, title: 'Route', desc: 'Smart distribution', icon: Target },
];

// Testimonials
const testimonials = [
    {
        name: 'Sarah Johnson',
        role: 'Owner, Cafe Delight',
        result: '3.8 → 4.7 stars',
        comment:
            'We catch issues privately and push happy customers to Google. Amazing results!',
        avatar: 'SJ',
    },
    {
        name: 'Michael Chen',
        role: 'Operations, TechHub',
        result: '340% more reviews',
        comment:
            'Automated reminders are magic. Went from 5 reviews/month to 30+.',
        avatar: 'MC',
    },
    {
        name: 'Emily Rodriguez',
        role: 'Success Lead, RetailPro',
        result: '15 hrs saved/week',
        comment:
            'AI drafts replies instantly. What took hours now takes minutes.',
        avatar: 'ER',
    },
];
</script>

<template>
    <Head :title="`Build Your 5-Star Reputation | ${appName}`" />

    <div class="min-h-screen bg-background">
        <!-- Navigation -->
        <nav
            class="sticky top-0 z-50 border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60"
        >
            <div
                class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8"
            >
                <!-- Logo -->
                <div class="flex items-center gap-1">
                    <AppLogoIcon class="h-10 w-10" />
                    <span class="text-xl font-bold">{{ appName }}</span>
                </div>

                <!-- Nav Links -->
                <div class="hidden items-center gap-8 md:flex">
                    <a
                        href="#how-it-works"
                        class="text-sm font-medium text-muted-foreground hover:text-foreground"
                        >How It Works</a
                    >
                    <a
                        href="#features"
                        class="text-sm font-medium text-muted-foreground hover:text-foreground"
                        >Features</a
                    >
                    <a
                        href="#testimonials"
                        class="text-sm font-medium text-muted-foreground hover:text-foreground"
                        >Reviews</a
                    >
                </div>

                <!-- Auth Buttons -->
                <div class="flex items-center gap-3">
                    <template v-if="$page.props.auth.user">
                        <Link
                            :href="dashboard().url"
                            class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90"
                        >
                            Dashboard
                            <ArrowRight class="h-4 w-4" />
                        </Link>
                    </template>
                    <template v-else>
                        <Link
                            :href="login()"
                            class="hidden text-sm font-medium text-muted-foreground hover:text-foreground sm:block"
                            >Log in</Link
                        >
                        <Link
                            v-if="canRegister"
                            :href="register()"
                            class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow-lg shadow-primary/20 hover:bg-primary/90"
                        >
                            Start Free Trial
                            <ArrowRight class="h-4 w-4" />
                        </Link>
                    </template>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="relative overflow-hidden py-20 lg:py-32">
            <!-- Background -->
            <div class="absolute inset-0 -z-10">
                <div
                    class="absolute top-0 left-1/4 h-[500px] w-[500px] rounded-full bg-primary/5 blur-3xl"
                ></div>
                <div
                    class="absolute right-1/4 bottom-0 h-[400px] w-[400px] rounded-full bg-primary/10 blur-3xl"
                ></div>
            </div>

            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-4xl text-center">
                    <!-- Badge -->
                    <div
                        class="mb-6 inline-flex items-center gap-2 rounded-full border border-primary/20 bg-primary/5 px-4 py-1.5 text-sm font-medium text-primary"
                    >
                        <Sparkles class="h-4 w-4" />
                        AI-Powered Reputation Management
                    </div>

                    <!-- Headline -->
                    <h1
                        class="mb-6 text-4xl font-bold tracking-tight sm:text-5xl lg:text-6xl"
                    >
                        Turn Customer Silence Into
                        <span
                            class="bg-gradient-to-r from-primary to-primary/60 bg-clip-text text-transparent"
                        >
                            5-Star Reviews
                        </span>
                    </h1>

                    <!-- Subheadline -->
                    <p
                        class="mx-auto mb-10 max-w-2xl text-lg text-muted-foreground sm:text-xl"
                    >
                        Capture feedback before it hits Google. Route happy
                        customers to public reviews. Resolve unhappy ones
                        privately. All on autopilot.
                    </p>

                    <!-- CTA -->
                    <div
                        class="mb-12 flex flex-col items-center gap-4 sm:flex-row sm:justify-center"
                    >
                        <Link
                            v-if="canRegister"
                            :href="register()"
                            class="group inline-flex items-center gap-2 rounded-xl bg-primary px-8 py-4 text-lg font-semibold text-primary-foreground shadow-xl shadow-primary/25 transition-all hover:scale-105 hover:shadow-2xl"
                        >
                            Start 14-Day Free Trial
                            <ArrowRight
                                class="h-5 w-5 transition-transform group-hover:translate-x-1"
                            />
                        </Link>
                        <div
                            class="flex items-center gap-2 text-sm text-muted-foreground"
                        >
                            <CheckCircle2 class="h-4 w-4 text-green-500" />
                            No credit card required
                        </div>
                    </div>

                    <!-- Platform Logos -->
                    <!-- <div class="rounded-2xl border bg-card/50 p-6 backdrop-blur">
                        <p class="mb-4 text-sm text-muted-foreground">Push reviews to platforms that matter</p>
                        <div class="flex flex-wrap items-center justify-center gap-8">
                            <div v-for="platform in platforms" :key="platform.name" class="flex items-center gap-2 text-muted-foreground/50 transition-colors hover:text-foreground">
                                <component :is="platform.icon" class="h-5 w-5" />
                                <span class="font-medium">{{ platform.name }}</span>
                            </div>
                        </div>
                    </div> -->
                </div>

                <!-- Stats Row -->
                <div
                    class="mx-auto mt-16 grid max-w-4xl grid-cols-2 gap-8 md:grid-cols-4"
                >
                    <div
                        v-for="stat in stats"
                        :key="stat.label"
                        class="rounded-xl bg-card/50 p-4 text-center backdrop-blur"
                    >
                        <div class="text-3xl font-bold" :class="stat.color">
                            {{ stat.value }}
                        </div>
                        <div class="mt-1 text-sm text-muted-foreground">
                            {{ stat.label }}
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- How It Works -->
        <section id="how-it-works" class="border-y bg-muted/30 py-20 lg:py-28">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mx-auto mb-16 max-w-2xl text-center">
                    <div
                        class="mb-4 inline-flex items-center gap-2 rounded-full bg-green-100 px-4 py-1.5 text-sm font-medium text-green-700 dark:bg-green-900/30 dark:text-green-400"
                    >
                        <Shield class="h-4 w-4" />
                        Smart Review Funnel
                    </div>
                    <h2 class="mb-4 text-3xl font-bold sm:text-4xl">
                        Protect Your Brand Automatically
                    </h2>
                    <p class="text-lg text-muted-foreground">
                        Happy feedback goes public. Unhappy feedback stays
                        private. You win either way.
                    </p>
                </div>

                <!-- 3-Step Process -->
                <div class="mb-16 grid gap-8 md:grid-cols-3">
                    <div
                        v-for="step in funnelSteps"
                        :key="step.step"
                        class="relative rounded-2xl border bg-card p-8 text-center transition-all hover:shadow-lg"
                    >
                        <div
                            class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-primary text-white shadow-lg"
                        >
                            <component :is="step.icon" class="h-7 w-7" />
                        </div>
                        <div
                            class="mb-2 text-xs font-bold tracking-widest text-primary uppercase"
                        >
                            Step {{ step.step }}
                        </div>
                        <h3 class="mb-2 text-xl font-semibold">
                            {{ step.title }}
                        </h3>
                        <p class="text-muted-foreground">{{ step.desc }}</p>
                    </div>
                </div>

                <!-- Sentiment Fork -->
                <div
                    class="mx-auto max-w-4xl rounded-2xl border-2 border-dashed border-primary/20 bg-gradient-to-b from-primary/5 to-transparent p-8"
                >
                    <div class="mb-8 text-center">
                        <Brain class="mx-auto mb-2 h-10 w-10 text-primary" />
                        <h3 class="text-lg font-semibold">
                            AI Detects Sentiment
                        </h3>
                    </div>

                    <div class="grid gap-6 md:grid-cols-2">
                        <!-- Happy Path -->
                        <div
                            class="rounded-xl border-2 border-green-200 bg-green-50 p-6 dark:border-green-800 dark:bg-green-950/30"
                        >
                            <div class="mb-4 flex items-center gap-3">
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-full bg-green-500 text-white"
                                >
                                    <Smile class="h-5 w-5" />
                                </div>
                                <div>
                                    <div
                                        class="font-semibold text-green-800 dark:text-green-300"
                                    >
                                        Happy Customer
                                    </div>
                                    <div
                                        class="text-sm text-green-600 dark:text-green-400"
                                    >
                                        Positive sentiment
                                    </div>
                                </div>
                            </div>
                            <ul
                                class="space-y-2 text-sm text-green-700 dark:text-green-400"
                            >
                                <li class="flex items-center gap-2">
                                    <ArrowUpRight class="h-4 w-4" /> Share on
                                    Google
                                </li>
                                <li class="flex items-center gap-2">
                                    <ArrowUpRight class="h-4 w-4" /> Post to
                                    Facebook/Yelp
                                </li>
                                <li class="flex items-center gap-2">
                                    <TrendingUp class="h-4 w-4" /> Boost your
                                    SEO
                                </li>
                            </ul>
                        </div>

                        <!-- Unhappy Path -->
                        <div
                            class="rounded-xl border-2 border-amber-200 bg-amber-50 p-6 dark:border-amber-800 dark:bg-amber-950/30"
                        >
                            <div class="mb-4 flex items-center gap-3">
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-full bg-amber-500 text-white"
                                >
                                    <EyeOff class="h-5 w-5" />
                                </div>
                                <div>
                                    <div
                                        class="font-semibold text-amber-800 dark:text-amber-300"
                                    >
                                        Unhappy Customer
                                    </div>
                                    <div
                                        class="text-sm text-amber-600 dark:text-amber-400"
                                    >
                                        Negative sentiment
                                    </div>
                                </div>
                            </div>
                            <ul
                                class="space-y-2 text-sm text-amber-700 dark:text-amber-400"
                            >
                                <li class="flex items-center gap-2">
                                    <Lock class="h-4 w-4" /> Captured privately
                                </li>
                                <li class="flex items-center gap-2">
                                    <Bell class="h-4 w-4" /> Team alerted
                                    instantly
                                </li>
                                <li class="flex items-center gap-2">
                                    <Heart class="h-4 w-4" /> Resolve before
                                    public
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- QR Code Feature -->
        <section class="py-20 lg:py-28">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Section Header -->
                <div class="mx-auto mb-16 max-w-2xl text-center">
                    <div
                        class="mb-4 inline-flex items-center gap-2 rounded-full border border-primary/20 bg-primary/5 px-4 py-1.5 text-sm font-medium text-primary"
                    >
                        <QrCode class="h-4 w-4" />
                        Offline to Online
                    </div>
                    <h2 class="mb-4 text-3xl font-bold sm:text-4xl">
                        Capture Walk-in Reviews Instantly
                    </h2>
                    <p class="text-lg text-muted-foreground">
                        Print beautiful QR code posters. Customers scan, review,
                        done — no app needed.
                    </p>
                </div>

                <div class="grid items-center gap-16 lg:grid-cols-2">
                    <!-- Visual - QR Poster Mockup -->
                    <div class="relative order-2 lg:order-1">
                        <!-- Background glow -->
                        <div
                            class="absolute top-1/2 -left-10 h-80 w-80 -translate-y-1/2 rounded-full bg-primary/10 blur-3xl"
                        ></div>

                        <!-- Poster Card -->
                        <div class="relative mx-auto max-w-sm">
                            <!-- Main poster -->
                            <div
                                class="overflow-hidden rounded-3xl border bg-gradient-to-b from-card to-card/80 shadow-2xl"
                            >
                                <!-- Header with brand color -->
                                <div
                                    class="bg-primary px-6 py-5 text-center text-primary-foreground"
                                >
                                    <div
                                        class="flex items-center justify-center gap-2"
                                    >
                                        <AppLogoIcon
                                            class="h-8 w-8 brightness-0 invert"
                                        />
                                        <span class="text-xl font-bold">{{
                                            appName
                                        }}</span>
                                    </div>
                                </div>

                                <!-- QR Content -->
                                <div class="p-8">
                                    <div class="mb-6 text-center">
                                        <h3 class="text-2xl font-bold">
                                            How was your visit?
                                        </h3>
                                        <p class="mt-1 text-muted-foreground">
                                            Scan to share your experience
                                        </p>
                                    </div>

                                    <!-- QR Code Box -->
                                    <div
                                        class="mx-auto mb-6 w-fit rounded-2xl border-4 border-primary/20 bg-white p-4 shadow-inner"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            width="100%"
                                            height="100%"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            class="icon icon-tabler icons-tabler-outline icon-tabler-qrcode"
                                        >
                                            <path
                                                stroke="none"
                                                d="M0 0h24v24H0z"
                                                fill="none"
                                            />
                                            <path
                                                d="M4 5a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v4a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1l0 -4"
                                            />
                                            <path d="M7 17l0 .01" />
                                            <path
                                                d="M14 5a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v4a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1l0 -4"
                                            />
                                            <path d="M7 7l0 .01" />
                                            <path
                                                d="M4 15a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v4a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1l0 -4"
                                            />
                                            <path d="M17 7l0 .01" />
                                            <path d="M14 14l3 0" />
                                            <path d="M20 14l0 .01" />
                                            <path d="M14 14l0 3" />
                                            <path d="M14 20l3 0" />
                                            <path d="M17 17l3 0" />
                                            <path d="M20 17l0 3" />
                                        </svg>
                                    </div>

                                    <!-- Scan instruction -->
                                    <div
                                        class="flex items-center justify-center gap-2 text-sm text-muted-foreground"
                                    >
                                        <div
                                            class="flex h-8 w-8 items-center justify-center rounded-full bg-primary/10"
                                        >
                                            <svg
                                                class="h-4 w-4 text-primary"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="2"
                                            >
                                                <path
                                                    d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"
                                                />
                                                <circle cx="12" cy="13" r="4" />
                                            </svg>
                                        </div>
                                        <span>Point your camera here</span>
                                    </div>
                                </div>

                                <!-- Footer -->
                                <div
                                    class="border-t bg-muted/30 px-6 py-4 text-center"
                                >
                                    <p class="text-xs text-muted-foreground">
                                        Your feedback helps us improve ❤️
                                    </p>
                                </div>
                            </div>

                            <!-- Table stand effect -->
                            <!-- <div class="mx-auto -mt-1 h-4 w-40 rounded-b-xl bg-gradient-to-b from-muted to-muted/50 shadow-md"></div> -->
                            <!-- <div class="mx-auto h-2 w-32 rounded-b-lg bg-muted/30"></div> -->
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="order-1 space-y-8 lg:order-2">
                        <div class="space-y-4">
                            <h3 class="text-2xl font-bold">
                                Turn Every Visit Into a Review Opportunity
                            </h3>
                            <p class="text-lg text-muted-foreground">
                                Place QR posters on tables, at checkout, or on
                                receipts. Customers scan with their phone camera
                                and leave feedback in under 30 seconds.
                            </p>
                        </div>

                        <!-- Features grid -->
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div
                                class="flex items-start gap-3 rounded-xl border bg-card p-4"
                            >
                                <div
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-violet-100 text-violet-600 dark:bg-violet-900/30 dark:text-violet-400"
                                >
                                    <svg
                                        class="h-5 w-5"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    >
                                        <path d="M12 2L2 7l10 5 10-5-10-5z" />
                                        <path d="M2 17l10 5 10-5" />
                                        <path d="M2 12l10 5 10-5" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-semibold">Your Branding</p>
                                    <p class="text-sm text-muted-foreground">
                                        Logo, colors & custom messages
                                    </p>
                                </div>
                            </div>

                            <div
                                class="flex items-start gap-3 rounded-xl border bg-card p-4"
                            >
                                <div
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400"
                                >
                                    <svg
                                        class="h-5 w-5"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    >
                                        <rect
                                            x="5"
                                            y="2"
                                            width="14"
                                            height="20"
                                            rx="2"
                                            ry="2"
                                        />
                                        <line
                                            x1="12"
                                            y1="18"
                                            x2="12.01"
                                            y2="18"
                                        />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-semibold">Any Device</p>
                                    <p class="text-sm text-muted-foreground">
                                        Works on iOS & Android
                                    </p>
                                </div>
                            </div>

                            <div
                                class="flex items-start gap-3 rounded-xl border bg-card p-4"
                            >
                                <div
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400"
                                >
                                    <BarChart3 class="h-5 w-5" />
                                </div>
                                <div>
                                    <p class="font-semibold">Track Scans</p>
                                    <p class="text-sm text-muted-foreground">
                                        Analytics by location
                                    </p>
                                </div>
                            </div>

                            <div
                                class="flex items-start gap-3 rounded-xl border bg-card p-4"
                            >
                                <div
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400"
                                >
                                    <svg
                                        class="h-5 w-5"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    >
                                        <path
                                            d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"
                                        />
                                        <polyline points="14 2 14 8 20 8" />
                                        <line x1="16" y1="13" x2="8" y2="13" />
                                        <line x1="16" y1="17" x2="8" y2="17" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-semibold">Print Ready</p>
                                    <p class="text-sm text-muted-foreground">
                                        Download PDF or PNG
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- CTA -->
                        <Link
                            v-if="canRegister"
                            :href="register()"
                            class="group inline-flex items-center gap-2 rounded-xl bg-primary px-6 py-3 font-semibold text-primary-foreground shadow-lg shadow-primary/20 transition-all hover:scale-105 hover:shadow-xl"
                        >
                            Get Your Free QR Poster
                            <ArrowRight
                                class="h-4 w-4 transition-transform group-hover:translate-x-1"
                            />
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Grid -->
        <section id="features" class="border-y bg-muted/30 py-20 lg:py-28">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mx-auto mb-16 max-w-2xl text-center">
                    <div
                        class="mb-4 inline-flex items-center gap-2 rounded-full border px-4 py-1.5 text-sm font-medium"
                    >
                        <Sparkles class="h-4 w-4 text-primary" />
                        Everything You Need
                    </div>
                    <h2 class="mb-4 text-3xl font-bold sm:text-4xl">
                        Built for Busy Business Owners
                    </h2>
                    <p class="text-lg text-muted-foreground">
                        Save time, protect your brand, grow your reputation.
                    </p>
                </div>

                <!-- Grid -->
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="feature in features"
                        :key="feature.title"
                        class="group rounded-2xl border bg-card p-6 transition-all hover:-translate-y-1 hover:shadow-xl"
                    >
                        <div
                            :class="[
                                feature.bgClass,
                                'mb-4 flex h-12 w-12 items-center justify-center rounded-xl text-white shadow-lg transition-transform group-hover:scale-110',
                            ]"
                        >
                            <component :is="feature.icon" class="h-6 w-6" />
                        </div>
                        <h3 class="mb-2 text-lg font-semibold">
                            {{ feature.title }}
                        </h3>
                        <p class="mb-4 text-sm text-muted-foreground">
                            {{ feature.description }}
                        </p>
                        <div
                            class="inline-flex items-center gap-1.5 rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-700 dark:bg-green-900/30 dark:text-green-400"
                        >
                            <TrendingUp class="h-3 w-3" />
                            {{ feature.benefit }}
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Testimonials -->
        <section id="testimonials" class="py-20 lg:py-28">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mx-auto mb-16 max-w-2xl text-center">
                    <div
                        class="mb-4 inline-flex items-center gap-2 rounded-full border px-4 py-1.5 text-sm font-medium"
                    >
                        <Star class="h-4 w-4 text-yellow-500" />
                        Success Stories
                    </div>
                    <h2 class="mb-4 text-3xl font-bold sm:text-4xl">
                        Loved by Businesses Like Yours
                    </h2>
                    <p class="text-lg text-muted-foreground">
                        Real results from real customers.
                    </p>
                </div>

                <!-- Cards -->
                <div class="grid gap-6 md:grid-cols-3">
                    <div
                        v-for="testimonial in testimonials"
                        :key="testimonial.name"
                        class="rounded-2xl border bg-card p-6"
                    >
                        <!-- Result Badge -->
                        <div
                            class="mb-4 inline-flex items-center gap-1.5 rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700 dark:bg-green-900/30 dark:text-green-400"
                        >
                            <TrendingUp class="h-3 w-3" />
                            {{ testimonial.result }}
                        </div>

                        <!-- Stars -->
                        <div class="mb-4 flex gap-0.5">
                            <Star
                                v-for="i in 5"
                                :key="i"
                                class="h-4 w-4 fill-yellow-400 text-yellow-400"
                            />
                        </div>

                        <!-- Quote -->
                        <p class="mb-6 text-muted-foreground">
                            "{{ testimonial.comment }}"
                        </p>

                        <!-- Author -->
                        <div class="flex items-center gap-3 border-t pt-4">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-primary to-primary/70 text-sm font-semibold text-white"
                            >
                                {{ testimonial.avatar }}
                            </div>
                            <div>
                                <div class="font-medium">
                                    {{ testimonial.name }}
                                </div>
                                <div class="text-sm text-muted-foreground">
                                    {{ testimonial.role }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ROI Section -->
        <section
            class="border-t bg-gradient-to-b from-primary/5 to-background py-20"
        >
            <div class="mx-auto max-w-4xl px-4 text-center sm:px-6">
                <h2 class="mb-4 text-3xl font-bold sm:text-4xl">
                    One Saved Customer Pays for a Year
                </h2>
                <p
                    class="mx-auto mb-10 max-w-2xl text-lg text-muted-foreground"
                >
                    Catching one unhappy customer before they leave a 1-star
                    review is worth more than 12 months of {{ appName }}.
                </p>
                <div class="flex flex-col gap-6 sm:flex-row sm:justify-center">
                    <div
                        class="flex-1 rounded-xl border bg-card p-8 sm:max-w-xs"
                    >
                        <div class="text-4xl font-bold text-primary">$50</div>
                        <div class="mt-2 text-sm text-muted-foreground">
                            Avg. customer acquisition cost
                        </div>
                    </div>
                    <div
                        class="flex-1 rounded-xl border bg-card p-8 sm:max-w-xs"
                    >
                        <div class="text-4xl font-bold text-red-500">-22%</div>
                        <div class="mt-2 text-sm text-muted-foreground">
                            Revenue lost per bad review
                        </div>
                    </div>
                    <div
                        class="flex-1 rounded-xl border bg-card p-8 sm:max-w-xs"
                    >
                        <div class="text-4xl font-bold text-emerald-500">
                            10x
                        </div>
                        <div class="mt-2 text-sm text-muted-foreground">
                            ROI from prevention
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Final CTA -->
        <section class="py-20 lg:py-28">
            <div class="mx-auto max-w-4xl px-4 sm:px-6">
                <div
                    class="relative overflow-hidden rounded-3xl border bg-gradient-to-br from-primary/10 via-background to-primary/5 p-10 text-center sm:p-16"
                >
                    <!-- Decorative -->
                    <div
                        class="absolute -top-20 -left-20 h-60 w-60 rounded-full bg-primary/20 blur-3xl"
                    ></div>
                    <div
                        class="absolute -right-20 -bottom-20 h-60 w-60 rounded-full bg-primary/10 blur-3xl"
                    ></div>

                    <div class="relative">
                        <div
                            class="mb-6 inline-flex items-center gap-2 rounded-full bg-primary/10 px-4 py-1.5 text-sm font-medium text-primary"
                        >
                            <Sparkles class="h-4 w-4" />
                            Start Today
                        </div>
                        <h2
                            class="mb-4 text-3xl font-bold sm:text-4xl lg:text-5xl"
                        >
                            Ready for 5-Star Reviews?
                        </h2>
                        <p
                            class="mx-auto mb-8 max-w-xl text-lg text-muted-foreground"
                        >
                            Join thousands of businesses building better
                            reputations on autopilot.
                        </p>

                        <Link
                            v-if="canRegister"
                            :href="register()"
                            class="group inline-flex items-center gap-2 rounded-xl bg-primary px-8 py-4 text-lg font-semibold text-primary-foreground shadow-xl shadow-primary/25 transition-all hover:scale-105"
                        >
                            Start 14-Day Free Trial
                            <ArrowRight
                                class="h-5 w-5 transition-transform group-hover:translate-x-1"
                            />
                        </Link>

                        <div
                            class="mt-6 flex flex-wrap items-center justify-center gap-6 text-sm text-muted-foreground"
                        >
                            <div class="flex items-center gap-2">
                                <CheckCircle2 class="h-4 w-4 text-green-500" />
                                No credit card
                            </div>
                            <div class="flex items-center gap-2">
                                <CheckCircle2 class="h-4 w-4 text-green-500" />
                                5-min setup
                            </div>
                            <div class="flex items-center gap-2">
                                <CheckCircle2 class="h-4 w-4 text-green-500" />
                                Cancel anytime
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="border-t bg-muted/30 py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid gap-8 md:grid-cols-4">
                    <!-- Brand -->
                    <div class="space-y-4">
                        <div class="flex items-center gap-2">
                            <AppLogoIcon class="h-10 w-10" />
                            <span class="text-lg font-bold">{{ appName }}</span>
                        </div>
                        <p class="text-sm text-muted-foreground">
                            Turn silence into 5-star reviews.
                        </p>
                    </div>

                    <!-- Product -->
                    <div>
                        <h4 class="mb-3 font-semibold">Product</h4>
                        <ul class="space-y-2 text-sm text-muted-foreground">
                            <li>
                                <a
                                    href="#how-it-works"
                                    class="hover:text-foreground"
                                    >How It Works</a
                                >
                            </li>
                            <li>
                                <a
                                    href="#features"
                                    class="hover:text-foreground"
                                    >Features</a
                                >
                            </li>
                            <li>
                                <a
                                    href="#testimonials"
                                    class="hover:text-foreground"
                                    >Reviews</a
                                >
                            </li>
                        </ul>
                    </div>

                    <!-- Resources -->
                    <div>
                        <h4 class="mb-3 font-semibold">Resources</h4>
                        <ul class="space-y-2 text-sm text-muted-foreground">
                            <li>
                                <a href="#" class="hover:text-foreground"
                                    >Help Center</a
                                >
                            </li>
                            <li>
                                <a href="#" class="hover:text-foreground"
                                    >Blog</a
                                >
                            </li>
                            <li>
                                <a href="#" class="hover:text-foreground"
                                    >API Docs</a
                                >
                            </li>
                        </ul>
                    </div>

                    <!-- Legal -->
                    <div>
                        <h4 class="mb-3 font-semibold">Legal</h4>
                        <ul class="space-y-2 text-sm text-muted-foreground">
                            <li>
                                <a href="#" class="hover:text-foreground"
                                    >Privacy</a
                                >
                            </li>
                            <li>
                                <a href="#" class="hover:text-foreground"
                                    >Terms</a
                                >
                            </li>
                        </ul>
                    </div>
                </div>

                <div
                    class="mt-12 flex flex-col items-center justify-between gap-4 border-t pt-8 md:flex-row"
                >
                    <p class="text-sm text-muted-foreground">
                        &copy; {{ new Date().getFullYear() }} {{ appName }}. All
                        rights reserved.
                    </p>
                    <p class="text-sm text-muted-foreground">
                        Built by
                        <a
                            href="#"
                            class="font-medium text-foreground hover:text-primary"
                            >Epochweave</a
                        >
                    </p>
                </div>
            </div>
        </footer>
    </div>
</template>
