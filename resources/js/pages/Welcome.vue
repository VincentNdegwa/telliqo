<script setup lang="ts">
import { dashboard, login, register } from '@/routes';
import { Head, Link } from '@inertiajs/vue3';
import { 
    BarChart3, 
    MessageSquare, 
    Brain, 
    Mail, 
    Users, 
    Plug, 
    Globe, 
    Star,
    TrendingUp,
    Shield,
    Zap,
    ChevronRight,
    CheckCircle2
} from 'lucide-vue-next';
import { ref } from 'vue';

withDefaults(
    defineProps<{
        canRegister: boolean;
    }>(),
    {
        canRegister: true,
    },
);

const features = [
    {
        icon: BarChart3,
        title: 'Smart Dashboard',
        description: 'All your customer insights in one place — NPS, sentiment trends, category comparison, and daily metrics that matter.',
        gradient: 'from-blue-500 to-cyan-500'
    },
    {
        icon: MessageSquare,
        title: 'Customer Feedback Management',
        description: 'Respond, flag, analyze, and track customer reviews with ease. Never miss a conversation.',
        gradient: 'from-purple-500 to-pink-500'
    },
    {
        icon: Brain,
        title: 'AI Sentiment & Moderation',
        description: 'Understand tone, detect policy violations, and maintain quality automatically with AI-powered analysis.',
        gradient: 'from-orange-500 to-red-500'
    },
    {
        icon: Mail,
        title: 'Review Requests Automation',
        description: 'Send requests via email, schedule reminders, and track verified customers effortlessly.',
        gradient: 'from-green-500 to-emerald-500'
    },
    {
        icon: Users,
        title: 'Team & Role Management',
        description: 'Invite teammates, assign permissions, and collaborate seamlessly across your organization.',
        gradient: 'from-indigo-500 to-blue-500'
    },
    {
        icon: Plug,
        title: 'API Integration',
        description: 'Connect POS, CRM, e-commerce, or custom systems effortlessly with our flexible API.',
        gradient: 'from-yellow-500 to-orange-500'
    }
];

const stats = [
    { value: '10K+', label: 'Reviews Analyzed' },
    { value: '500+', label: 'Businesses' },
    { value: '99.9%', label: 'Uptime' },
    { value: '24/7', label: 'Support' }
];

const activeFeature = ref(0);
</script>

<template>
    <Head title="Turn Customer Feedback Into Actionable Growth | Telliqo" />
    
    <div class="min-h-screen bg-background">
        <!-- Navigation -->
        <nav class="fixed top-0 z-50 w-full border-b border-border/40 bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-[var(--brand)] to-blue-600">
                            <Star class="h-5 w-5 text-white" :fill="'white'" />
                        </div>
                        <span class="text-xl font-bold tracking-tight">Telliqo</span>
                    </div>
                    
                    <div class="flex items-center gap-4">
                        <a
                            v-if="$page.props.auth.user"
                            :href="dashboard().url"
                            class="inline-flex items-center gap-2 rounded-lg border border-border bg-background px-4 py-2 text-sm font-medium transition-colors hover:bg-accent hover:text-accent-foreground"
                        >
                            Dashboard
                        </a>
                        <template v-else>
                            <Link
                                :href="login()"
                                class="inline-flex items-center rounded-lg px-4 py-2 text-sm font-medium text-foreground transition-colors hover:text-foreground/80"
                            >
                                Log in
                            </Link>
                            <Link
                                v-if="canRegister"
                                :href="register()"
                                class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-[var(--brand)] to-blue-600 px-4 py-2 text-sm font-medium text-white shadow-lg shadow-[var(--brand)]/25 transition-all hover:shadow-xl hover:shadow-[var(--brand)]/40"
                            >
                                Get Started
                                <ChevronRight class="h-4 w-4" />
                            </Link>
                        </template>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="relative overflow-hidden pt-32 pb-20">
            <!-- Gradient Background -->
            <div class="absolute inset-0 -z-10">
                <div class="absolute left-1/2 top-0 h-[600px] w-[600px] -translate-x-1/2 rounded-full bg-[var(--brand)] opacity-20 blur-3xl"></div>
                <div class="absolute right-0 top-20 h-[400px] w-[400px] rounded-full bg-blue-500 opacity-15 blur-3xl"></div>
            </div>

            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid gap-12 lg:grid-cols-2 lg:gap-16 items-center">
                    <!-- Left Column -->
                    <div class="space-y-8">
                        <div class="inline-flex items-center gap-2 rounded-full border border-border bg-background/50 px-4 py-2 text-sm backdrop-blur">
                            <Zap class="h-4 w-4 text-[var(--brand)]" />
                            <span class="font-medium">Powered by AI & Automation</span>
                        </div>

                        <div class="space-y-4">
                            <h1 class="text-5xl font-bold leading-tight tracking-tight lg:text-6xl">
                                Turn Customer Feedback Into 
                                <span class="bg-gradient-to-r from-[var(--brand)] to-blue-600 bg-clip-text text-transparent">
                                    Actionable Growth
                                </span>
                            </h1>
                            <p class="text-lg text-muted-foreground lg:text-xl">
                                Insightful, trustworthy, modern — a platform that listens, analyzes, and empowers businesses to unlock customer-driven growth.
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-4">
                            <Link
                                v-if="canRegister"
                                :href="register()"
                                class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-[var(--brand)] to-blue-600 px-6 py-3 text-base font-semibold text-white shadow-lg shadow-[var(--brand)]/30 transition-all hover:shadow-xl hover:shadow-[var(--brand)]/50"
                            >
                                Start Free Trial
                                <ChevronRight class="h-5 w-5" />
                            </Link>
                            <a
                                href="#features"
                                class="inline-flex items-center gap-2 rounded-lg border border-border bg-background px-6 py-3 text-base font-semibold transition-all hover:bg-accent hover:border-accent-foreground/20"
                            >
                                Explore Features
                            </a>
                        </div>

                        <!-- Stats -->
                        <div class="grid grid-cols-2 gap-6 pt-8 sm:grid-cols-4">
                            <div
                                v-for="stat in stats"
                                :key="stat.label"
                                class="space-y-1"
                            >
                                <div class="text-3xl font-bold bg-gradient-to-r from-[var(--brand)] to-blue-600 bg-clip-text text-transparent">
                                    {{ stat.value }}
                                </div>
                                <div class="text-sm text-muted-foreground">
                                    {{ stat.label }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Visual -->
                    <div class="relative">
                        <div class="relative rounded-2xl border border-border bg-card p-8 shadow-2xl">
                            <!-- Dashboard Preview -->
                            <div class="space-y-4">
                                <!-- Header -->
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="h-10 w-10 rounded-lg bg-gradient-to-br from-[var(--brand)] to-blue-600"></div>
                                        <div>
                                            <div class="h-3 w-24 rounded bg-foreground/10"></div>
                                            <div class="mt-1.5 h-2 w-16 rounded bg-foreground/5"></div>
                                        </div>
                                    </div>
                                    <div class="h-8 w-8 rounded-full bg-foreground/5"></div>
                                </div>

                                <!-- Metrics -->
                                <div class="grid grid-cols-3 gap-4 pt-4">
                                    <div class="space-y-2 rounded-lg border border-border p-3">
                                        <div class="flex items-center gap-2">
                                            <TrendingUp class="h-4 w-4 text-green-500" />
                                            <span class="text-xs text-muted-foreground">NPS</span>
                                        </div>
                                        <div class="text-2xl font-bold text-green-500">+42</div>
                                    </div>
                                    <div class="space-y-2 rounded-lg border border-border p-3">
                                        <div class="flex items-center gap-2">
                                            <Star class="h-4 w-4 text-yellow-500" />
                                            <span class="text-xs text-muted-foreground">Rating</span>
                                        </div>
                                        <div class="text-2xl font-bold">4.8</div>
                                    </div>
                                    <div class="space-y-2 rounded-lg border border-border p-3">
                                        <div class="flex items-center gap-2">
                                            <MessageSquare class="h-4 w-4 text-[var(--brand)]" />
                                            <span class="text-xs text-muted-foreground">Reviews</span>
                                        </div>
                                        <div class="text-2xl font-bold">1.2K</div>
                                    </div>
                                </div>

                                <!-- Chart Placeholder -->
                                <div class="space-y-2 pt-4">
                                    <div class="h-2 w-20 rounded bg-foreground/10"></div>
                                    <div class="flex h-32 items-end gap-2">
                                        <div class="w-full rounded-t bg-gradient-to-t from-[var(--brand)]/80 to-[var(--brand)]/20" style="height: 60%"></div>
                                        <div class="w-full rounded-t bg-gradient-to-t from-[var(--brand)]/80 to-[var(--brand)]/20" style="height: 80%"></div>
                                        <div class="w-full rounded-t bg-gradient-to-t from-[var(--brand)]/80 to-[var(--brand)]/20" style="height: 100%"></div>
                                        <div class="w-full rounded-t bg-gradient-to-t from-[var(--brand)]/80 to-[var(--brand)]/20" style="height: 75%"></div>
                                        <div class="w-full rounded-t bg-gradient-to-t from-[var(--brand)]/80 to-[var(--brand)]/20" style="height: 90%"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Floating Elements -->
                            <div class="absolute -right-4 -top-4 rounded-lg border border-border bg-card p-3 shadow-lg">
                                <Brain class="h-6 w-6 text-purple-500" />
                            </div>
                            <div class="absolute -bottom-4 -left-4 rounded-lg border border-border bg-card p-3 shadow-lg">
                                <Globe class="h-6 w-6 text-green-500" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section id="features" class="py-24 bg-muted/30">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center space-y-4 mb-16">
                    <div class="inline-flex items-center gap-2 rounded-full border border-border bg-background px-4 py-2 text-sm">
                        <Shield class="h-4 w-4 text-[var(--brand)]" />
                        <span class="font-medium">Everything You Need</span>
                    </div>
                    <h2 class="text-4xl font-bold tracking-tight lg:text-5xl">
                        Powerful Features for 
                        <span class="bg-gradient-to-r from-[var(--brand)] to-blue-600 bg-clip-text text-transparent">
                            Customer Success
                        </span>
                    </h2>
                    <p class="mx-auto max-w-2xl text-lg text-muted-foreground">
                        Understand your customers deeply through feedback, analytics, and AI-driven clarity.
                    </p>
                </div>

                <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="(feature, index) in features"
                        :key="index"
                        class="group relative overflow-hidden rounded-2xl border border-border bg-card p-8 transition-all hover:shadow-xl hover:shadow-[var(--brand)]/10 hover:-translate-y-1"
                        @mouseenter="activeFeature = index"
                    >
                        <!-- Gradient Background on Hover -->
                        <div 
                            class="absolute inset-0 -z-10 opacity-0 transition-opacity group-hover:opacity-5"
                            :class="`bg-gradient-to-br ${feature.gradient}`"
                        ></div>

                        <div class="space-y-4">
                            <div 
                                class="inline-flex h-14 w-14 items-center justify-center rounded-xl bg-gradient-to-br text-white shadow-lg transition-transform group-hover:scale-110"
                                :class="feature.gradient"
                            >
                                <component :is="feature.icon" class="h-7 w-7" />
                            </div>

                            <div class="space-y-2">
                                <h3 class="text-xl font-semibold">{{ feature.title }}</h3>
                                <p class="text-muted-foreground leading-relaxed">
                                    {{ feature.description }}
                                </p>
                            </div>

                            <div class="flex items-center gap-2 text-sm font-medium text-[var(--brand)] opacity-0 transition-opacity group-hover:opacity-100">
                                Learn more
                                <ChevronRight class="h-4 w-4" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Benefits Section -->
        <section class="py-24">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid gap-12 lg:grid-cols-2 lg:gap-16 items-center">
                    <div class="space-y-8">
                        <div class="space-y-4">
                            <h2 class="text-4xl font-bold tracking-tight lg:text-5xl">
                                Why Businesses Choose 
                                <span class="bg-gradient-to-r from-[var(--brand)] to-blue-600 bg-clip-text text-transparent">
                                    Telliqo
                                </span>
                            </h2>
                            <p class="text-lg text-muted-foreground">
                                Transform raw feedback into strategic insights that drive real business outcomes.
                            </p>
                        </div>

                        <div class="space-y-6">
                            <div class="flex gap-4">
                                <div class="flex-shrink-0">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-green-500/10">
                                        <CheckCircle2 class="h-6 w-6 text-green-500" />
                                    </div>
                                </div>
                                <div class="space-y-1">
                                    <h3 class="font-semibold">Centralized Feedback Hub</h3>
                                    <p class="text-muted-foreground">
                                        Collect, organize, and analyze all customer feedback from multiple channels in one unified platform.
                                    </p>
                                </div>
                            </div>

                            <div class="flex gap-4">
                                <div class="flex-shrink-0">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-500/10">
                                        <CheckCircle2 class="h-6 w-6 text-blue-500" />
                                    </div>
                                </div>
                                <div class="space-y-1">
                                    <h3 class="font-semibold">AI-Powered Insights</h3>
                                    <p class="text-muted-foreground">
                                        Let AI analyze sentiment, detect patterns, and surface actionable recommendations automatically.
                                    </p>
                                </div>
                            </div>

                            <div class="flex gap-4">
                                <div class="flex-shrink-0">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-500/10">
                                        <CheckCircle2 class="h-6 w-6 text-purple-500" />
                                    </div>
                                </div>
                                <div class="space-y-1">
                                    <h3 class="font-semibold">Build Trust & Reputation</h3>
                                    <p class="text-muted-foreground">
                                        Showcase your commitment to customers with beautiful public profiles and verified reviews.
                                    </p>
                                </div>
                            </div>

                            <div class="flex gap-4">
                                <div class="flex-shrink-0">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-orange-500/10">
                                        <CheckCircle2 class="h-6 w-6 text-orange-500" />
                                    </div>
                                </div>
                                <div class="space-y-1">
                                    <h3 class="font-semibold">Seamless Integration</h3>
                                    <p class="text-muted-foreground">
                                        Connect with your existing tools — POS, CRM, e-commerce platforms, and more.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="relative">
                        <div class="aspect-square rounded-2xl border border-border bg-gradient-to-br from-[var(--brand)]/5 to-blue-500/5 p-8">
                            <div class="flex h-full flex-col justify-between">
                                <div class="space-y-4">
                                    <div class="inline-flex items-center gap-2 rounded-full bg-background border border-border px-4 py-2 text-sm shadow-sm">
                                        <div class="h-2 w-2 rounded-full bg-green-500 animate-pulse"></div>
                                        <span class="font-medium">Live Monitoring</span>
                                    </div>
                                    
                                    <!-- Review Cards -->
                                    <div class="space-y-3">
                                        <div class="rounded-lg border border-border bg-card p-4 shadow-sm">
                                            <div class="flex items-start gap-3">
                                                <div class="h-10 w-10 rounded-full bg-gradient-to-br from-blue-500 to-cyan-500"></div>
                                                <div class="flex-1 space-y-2">
                                                    <div class="flex items-center gap-2">
                                                        <div class="h-2 w-20 rounded bg-foreground/10"></div>
                                                        <div class="flex gap-0.5">
                                                            <Star class="h-3 w-3 text-yellow-500" :fill="'currentColor'" />
                                                            <Star class="h-3 w-3 text-yellow-500" :fill="'currentColor'" />
                                                            <Star class="h-3 w-3 text-yellow-500" :fill="'currentColor'" />
                                                            <Star class="h-3 w-3 text-yellow-500" :fill="'currentColor'" />
                                                            <Star class="h-3 w-3 text-yellow-500" :fill="'currentColor'" />
                                                        </div>
                                                    </div>
                                                    <div class="space-y-1">
                                                        <div class="h-2 w-full rounded bg-foreground/5"></div>
                                                        <div class="h-2 w-4/5 rounded bg-foreground/5"></div>
                                                    </div>
                                                    <div class="inline-flex items-center gap-1 rounded-full bg-green-500/10 px-2 py-1 text-xs text-green-600">
                                                        <div class="h-1.5 w-1.5 rounded-full bg-green-500"></div>
                                                        Positive
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="rounded-lg border border-border bg-card p-4 shadow-sm">
                                            <div class="flex items-start gap-3">
                                                <div class="h-10 w-10 rounded-full bg-gradient-to-br from-purple-500 to-pink-500"></div>
                                                <div class="flex-1 space-y-2">
                                                    <div class="flex items-center gap-2">
                                                        <div class="h-2 w-24 rounded bg-foreground/10"></div>
                                                        <div class="flex gap-0.5">
                                                            <Star class="h-3 w-3 text-yellow-500" :fill="'currentColor'" />
                                                            <Star class="h-3 w-3 text-yellow-500" :fill="'currentColor'" />
                                                            <Star class="h-3 w-3 text-yellow-500" :fill="'currentColor'" />
                                                            <Star class="h-3 w-3 text-yellow-500" :fill="'currentColor'" />
                                                            <Star class="h-3 w-3 text-muted-foreground/20" />
                                                        </div>
                                                    </div>
                                                    <div class="space-y-1">
                                                        <div class="h-2 w-full rounded bg-foreground/5"></div>
                                                        <div class="h-2 w-3/4 rounded bg-foreground/5"></div>
                                                    </div>
                                                    <div class="inline-flex items-center gap-1 rounded-full bg-blue-500/10 px-2 py-1 text-xs text-blue-600">
                                                        <div class="h-1.5 w-1.5 rounded-full bg-blue-500"></div>
                                                        Neutral
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="rounded-lg border border-dashed border-border bg-background/50 p-4 text-center">
                                    <p class="text-sm text-muted-foreground">
                                        <span class="font-semibold text-foreground">Real-time</span> sentiment analysis
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-24 bg-gradient-to-br from-[var(--brand)]/5 to-blue-500/5">
            <div class="mx-auto max-w-4xl px-4 text-center sm:px-6 lg:px-8">
                <div class="space-y-8">
                    <div class="space-y-4">
                        <h2 class="text-4xl font-bold tracking-tight lg:text-5xl">
                            Ready to Transform Your 
                            <span class="bg-gradient-to-r from-[var(--brand)] to-blue-600 bg-clip-text text-transparent">
                                Customer Feedback
                            </span>
                            ?
                        </h2>
                        <p class="mx-auto max-w-2xl text-lg text-muted-foreground">
                            Join hundreds of businesses that trust Telliqo to listen, analyze, and grow with their customers.
                        </p>
                    </div>

                    <div class="flex flex-wrap justify-center gap-4">
                        <Link
                            v-if="canRegister"
                            :href="register()"
                            class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-[var(--brand)] to-blue-600 px-8 py-4 text-lg font-semibold text-white shadow-xl shadow-[var(--brand)]/30 transition-all hover:shadow-2xl hover:shadow-[var(--brand)]/50"
                        >
                            Start Your Free Trial
                            <ChevronRight class="h-5 w-5" />
                        </Link>
                        <a
                            href="#features"
                            class="inline-flex items-center gap-2 rounded-lg border-2 border-border bg-background px-8 py-4 text-lg font-semibold transition-all hover:bg-accent hover:border-accent-foreground/20"
                        >
                            View Demo
                        </a>
                    </div>

                    <p class="text-sm text-muted-foreground">
                        No credit card required • Free 14-day trial • Cancel anytime
                    </p>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="border-t border-border bg-muted/30 py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid gap-8 md:grid-cols-4">
                    <div class="space-y-4">
                        <div class="flex items-center gap-2">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-[var(--brand)] to-blue-600">
                                <Star class="h-4 w-4 text-white" :fill="'white'" />
                            </div>
                            <span class="text-lg font-bold">Telliqo</span>
                        </div>
                        <p class="text-sm text-muted-foreground">
                            Turn customer feedback into actionable growth.
                        </p>
                    </div>

                    <div class="space-y-3">
                        <h4 class="font-semibold">Product</h4>
                        <ul class="space-y-2 text-sm text-muted-foreground">
                            <li><a href="#features" class="hover:text-foreground transition-colors">Features</a></li>
                            <li><a href="#" class="hover:text-foreground transition-colors">Pricing</a></li>
                            <li><a href="#" class="hover:text-foreground transition-colors">API</a></li>
                            <li><a href="#" class="hover:text-foreground transition-colors">Integrations</a></li>
                        </ul>
                    </div>

                    <div class="space-y-3">
                        <h4 class="font-semibold">Company</h4>
                        <ul class="space-y-2 text-sm text-muted-foreground">
                            <li><a href="#" class="hover:text-foreground transition-colors">About Us</a></li>
                            <li><a href="#" class="hover:text-foreground transition-colors">Blog</a></li>
                            <li><a href="#" class="hover:text-foreground transition-colors">Careers</a></li>
                            <li><a href="#" class="hover:text-foreground transition-colors">Contact</a></li>
                        </ul>
                    </div>

                    <div class="space-y-3">
                        <h4 class="font-semibold">Legal</h4>
                        <ul class="space-y-2 text-sm text-muted-foreground">
                            <li><a href="#" class="hover:text-foreground transition-colors">Privacy Policy</a></li>
                            <li><a href="#" class="hover:text-foreground transition-colors">Terms of Service</a></li>
                            <li><a href="#" class="hover:text-foreground transition-colors">Cookie Policy</a></li>
                            <li><a href="#" class="hover:text-foreground transition-colors">GDPR</a></li>
                        </ul>
                    </div>
                </div>

                <div class="mt-12 border-t border-border pt-8 text-center text-sm text-muted-foreground">
                    <p>&copy; {{ new Date().getFullYear() }} Telliqo. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </div>
</template>
