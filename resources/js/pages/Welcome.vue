<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { dashboard, login, register } from '@/routes';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { 
    BarChart3, 
    MessageSquare, 
    Brain, 
    Mail, 
    Users, 
    Plug, 
    Star,
    TrendingUp,
    ArrowRight,
    Check,
    MessageCircle,
    BarChart,
    Sparkles,
    Shield,
    ThumbsUp,
    AlertCircle,
    Reply,
    Zap,
    Lock,
    Globe,
    CheckCircle2
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

const features = [
    {
        icon: BarChart3,
        title: 'Smart Dashboard',
        description: 'Monitor all your customer insights in one unified dashboard. Track NPS scores, sentiment trends, rating distribution, and daily feedback metrics in real-time.',
        benefits: ['NPS & Rating trends', 'Sentiment analysis charts', 'Daily performance metrics'],
        gradient: 'from-blue-500 to-cyan-500',
        bgGradient: 'from-blue-500/10 to-cyan-500/10',
        iconColor: 'text-blue-600'
    },
    {
        icon: MessageSquare,
        title: 'Customer Feedback Management',
        description: 'Collect, organize, and respond to customer reviews efficiently. Flag important feedback, manage moderation status, and keep track of all customer interactions.',
        benefits: ['Flag & moderate reviews', 'Reply to customers', 'Track response rates'],
        gradient: 'from-green-500 to-emerald-500',
        bgGradient: 'from-green-500/10 to-emerald-500/10',
        iconColor: 'text-green-600'
    },
    {
        icon: Brain,
        title: 'AI Sentiment & Moderation',
        description: 'Leverage AI to automatically analyze customer sentiment, detect inappropriate content, and identify policy violations before reviews go public.',
        benefits: ['AI sentiment detection', 'AI reply suggestions', 'Automated content moderation'],
        gradient: 'from-purple-500 to-pink-500',
        bgGradient: 'from-purple-500/10 to-pink-500/10',
        iconColor: 'text-purple-600'
    },
    {
        icon: Mail,
        title: 'Review Requests Automation',
        description: 'Send review requests via email, schedule for later, or send immediately. Track email opens, send reminders after 3 days, and monitor completion rates.',
        benefits: ['Email scheduling', 'Automated reminders', 'Track opens & completions'],
        gradient: 'from-amber-500 to-orange-500',
        bgGradient: 'from-amber-500/10 to-orange-500/10',
        iconColor: 'text-amber-600'
    },
    {
        icon: Users,
        title: 'Team & Role Management',
        description: 'Invite unlimited team members, create custom roles with specific permissions, and manage access control across your organization.',
        benefits: ['Custom roles & permissions', 'Unlimited team members', 'Business-scoped access'],
        gradient: 'from-indigo-500 to-violet-500',
        bgGradient: 'from-indigo-500/10 to-violet-500/10',
        iconColor: 'text-indigo-600'
    },
    {
        icon: Plug,
        title: 'API Integration',
        description: 'Connect your existing tools through our comprehensive REST API. Create, read, update, and delete review requests programmatically with API keys.',
        benefits: ['REST API access', 'API key management', 'Full CRUD operations'],
        gradient: 'from-rose-500 to-red-500',
        bgGradient: 'from-rose-500/10 to-red-500/10',
        iconColor: 'text-rose-600'
    }
];

const stats = [
    { value: '50K+', label: 'Reviews Processed' },
    { value: '1,000+', label: 'Active Businesses' },
    { value: '99.9%', label: 'Uptime SLA' },
    { value: '<2min', label: 'Avg Response Time' }
];

const testimonials = [
    {
        name: 'Sarah Johnson',
        role: 'Owner',
        company: 'Cafe Delight',
        rating: 5,
        comment: `${appName} transformed how we handle customer feedback. The AI sentiment analysis helps us identify issues before they escalate.`,
        avatar: 'SJ'
    },
    {
        name: 'Michael Chen',
        role: 'Operations Manager',
        company: 'TechHub Solutions',
        rating: 5,
        comment: 'The automated review requests increased our feedback volume by 300%. Now we truly understand our customers.',
        avatar: 'MC'
    },
    {
        name: 'Emily Rodriguez',
        role: 'Customer Success Lead',
        company: 'Retail Pro',
        rating: 5,
        comment: 'Best investment we made this year. The dashboard gives us actionable insights that directly impact our service quality.',
        avatar: 'ER'
    }
];
</script>

<template>
    <Head :title="`Turn Customer Feedback Into Actionable Growth | ${appName}`" />
    
    <div class="min-h-screen bg-background">
        <!-- Navigation -->
        <nav class="sticky top-0 z-50 w-full border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <div class="flex items-center gap-0">
                        <AppLogoIcon class="w-20 h-20" />
                        <span class="text-lg font-semibold">{{ appName }}</span>
                    </div>
                    
                    <div class="flex items-center gap-3">
                        <a
                            v-if="$page.props.auth.user"
                            :href="dashboard().url"
                            class="inline-flex items-center rounded-md border px-4 py-2 text-sm font-medium transition-colors hover:bg-accent"
                        >
                            Dashboard
                        </a>
                        <template v-else>
                            <Link
                                :href="login()"
                                class="inline-flex items-center rounded-md px-4 py-2 text-sm font-medium text-muted-foreground transition-colors hover:text-foreground"
                            >
                                Log in
                            </Link>
                            <Link
                                v-if="canRegister"
                                :href="register()"
                                class="inline-flex items-center gap-2 rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90"
                            >
                                Get Started
                                <ArrowRight class="h-4 w-4" />
                            </Link>
                        </template>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="relative overflow-hidden py-20 lg:py-28">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid gap-12 lg:grid-cols-2 lg:gap-16 items-center">
                    <!-- Left Column -->
                    <div class="space-y-8">
                        <div class="inline-flex items-center gap-2 rounded-full border bg-muted px-3 py-1 text-sm">
                            <Sparkles class="h-4 w-4 text-primary" />
                            <span class="font-medium">Powered by AI & Automation</span>
                        </div>

                        <div class="space-y-6">
                            <h1 class="text-4xl font-bold tracking-tight sm:text-5xl lg:text-6xl">
                                Turn Customer Feedback Into Actionable Growth
                            </h1>
                            <p class="text-lg text-muted-foreground sm:text-xl">
                                Join 1,000+ businesses using {{ appName }} to collect, analyze, and act on customer feedback. Our AI-powered platform helps you understand what your customers really think, improve satisfaction by 40%, and increase retention rates by 25%.
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-4">
                            <Link
                                v-if="canRegister"
                                :href="register()"
                                class="inline-flex items-center gap-2 rounded-md bg-primary px-6 py-3 text-base font-semibold text-primary-foreground transition-colors hover:bg-primary/90"
                            >
                                Start Free Trial
                                <ArrowRight class="h-5 w-5" />
                            </Link>
                            <a
                                href="#features"
                                class="inline-flex items-center gap-2 rounded-md border px-6 py-3 text-base font-semibold transition-colors hover:bg-accent"
                            >
                                Learn More
                            </a>
                        </div>

                        <!-- Stats -->
                        <div class="grid grid-cols-2 gap-6 border-t pt-8 sm:grid-cols-4">
                            <div
                                v-for="stat in stats"
                                :key="stat.label"
                                class="space-y-1"
                            >
                                <div class="text-2xl font-bold text-primary sm:text-3xl">
                                    {{ stat.value }}
                                </div>
                                <div class="text-sm text-muted-foreground">
                                    {{ stat.label }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Enhanced Dashboard Preview -->
                    <div class="relative">
                        <!-- Floating background elements for visual appeal -->
                        <div class="absolute -right-8 -top-8 h-64 w-64 rounded-full bg-primary/5 blur-3xl animate-pulse"></div>
                        <div class="absolute -bottom-8 -left-8 h-48 w-48 rounded-full bg-primary/10 blur-2xl animate-pulse" style="animation-delay: 1s"></div>
                        
                        <div class="relative overflow-hidden rounded-xl border bg-card shadow-2xl transition-all hover:shadow-primary/20">
                            <!-- Dashboard Header -->
                            <div class="border-b bg-gradient-to-r from-muted/50 to-muted/30 px-6 py-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10">
                                            <BarChart3 class="h-5 w-5 text-primary" />
                                        </div>
                                        <div>
                                            <h3 class="font-semibold">Dashboard</h3>
                                            <p class="text-xs text-muted-foreground">Real-time Customer Insights</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 rounded-full bg-green-500/10 px-3 py-1 text-xs font-medium text-green-700 dark:text-green-400">
                                        <div class="h-2 w-2 animate-pulse rounded-full bg-green-500"></div>
                                        Live
                                    </div>
                                </div>
                            </div>

                            <!-- Enhanced Metrics Cards -->
                            <div class="grid grid-cols-3 gap-3 p-6">
                                <!-- NPS Score Card -->
                                <div class="group relative overflow-hidden rounded-lg border bg-gradient-to-br from-green-50 to-transparent p-4 transition-all hover:shadow-md dark:from-green-950/20">
                                    <div class="absolute right-2 top-2 opacity-10">
                                        <TrendingUp class="h-12 w-12 text-green-600" />
                                    </div>
                                    <div class="relative space-y-2">
                                        <div class="flex items-center gap-2 text-xs font-medium text-muted-foreground">
                                            <TrendingUp class="h-3.5 w-3.5 text-green-600" />
                                            <span>NPS Score</span>
                                        </div>
                                        <div class="text-3xl font-bold text-green-600">+42</div>
                                        <div class="flex items-center gap-1 text-xs text-green-600">
                                            <ArrowRight class="h-3 w-3 rotate-[-45deg]" />
                                            <span>↑ 12% vs last month</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Average Rating Card -->
                                <div class="group relative overflow-hidden rounded-lg border bg-gradient-to-br from-yellow-50 to-transparent p-4 transition-all hover:shadow-md dark:from-yellow-950/20">
                                    <div class="absolute right-2 top-2 opacity-10">
                                        <Star class="h-12 w-12 text-yellow-600" />
                                    </div>
                                    <div class="relative space-y-2">
                                        <div class="flex items-center gap-2 text-xs font-medium text-muted-foreground">
                                            <Star class="h-3.5 w-3.5 text-yellow-600" />
                                            <span>Avg Rating</span>
                                        </div>
                                        <div class="text-3xl font-bold">4.8</div>
                                        <div class="flex gap-0.5">
                                            <Star class="h-3 w-3 fill-yellow-400 text-yellow-400" />
                                            <Star class="h-3 w-3 fill-yellow-400 text-yellow-400" />
                                            <Star class="h-3 w-3 fill-yellow-400 text-yellow-400" />
                                            <Star class="h-3 w-3 fill-yellow-400 text-yellow-400" />
                                            <Star class="h-3 w-3 fill-yellow-400 text-yellow-400" />
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Feedback Count Card -->
                                <div class="group relative overflow-hidden rounded-lg border bg-gradient-to-br from-blue-50 to-transparent p-4 transition-all hover:shadow-md dark:from-blue-950/20">
                                    <div class="absolute right-2 top-2 opacity-10">
                                        <MessageSquare class="h-12 w-12 text-primary" />
                                    </div>
                                    <div class="relative space-y-2">
                                        <div class="flex items-center gap-2 text-xs font-medium text-muted-foreground">
                                            <MessageSquare class="h-3.5 w-3.5 text-primary" />
                                            <span>Total Feedback</span>
                                        </div>
                                        <div class="text-3xl font-bold">1,247</div>
                                        <div class="text-xs text-muted-foreground">+328 this week</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Enhanced Chart Area with Sentiment Distribution -->
                            <div class="border-t bg-muted/20 px-6 py-4">
                                <div class="mb-4 flex items-center justify-between">
                                    <div class="text-sm font-semibold">Feedback Trend (Last 7 Days)</div>
                                    <div class="flex items-center gap-4 text-xs">
                                        <div class="flex items-center gap-1">
                                            <div class="h-2 w-2 rounded-full bg-green-500"></div>
                                            <span class="text-muted-foreground">Positive</span>
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <div class="h-2 w-2 rounded-full bg-yellow-500"></div>
                                            <span class="text-muted-foreground">Neutral</span>
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <div class="h-2 w-2 rounded-full bg-red-500"></div>
                                            <span class="text-muted-foreground">Negative</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="space-y-4">
                                    <!-- Stacked Bar Chart -->
                                    <div class="flex h-40 items-end gap-2">
                                        <!-- Day 1 -->
                                        <div class="group flex w-full flex-col-reverse gap-0.5">
                                            <div class="rounded-t bg-green-500 transition-all hover:bg-green-600" style="height: 30%" title="Positive: 15"></div>
                                            <div class="bg-yellow-500 transition-all hover:bg-yellow-600" style="height: 10%" title="Neutral: 5"></div>
                                            <div class="bg-red-500 transition-all hover:bg-red-600" style="height: 5%" title="Negative: 2"></div>
                                        </div>
                                        <!-- Day 2 -->
                                        <div class="group flex w-full flex-col-reverse gap-0.5">
                                            <div class="rounded-t bg-green-500 transition-all hover:bg-green-600" style="height: 45%"></div>
                                            <div class="bg-yellow-500 transition-all hover:bg-yellow-600" style="height: 12%"></div>
                                            <div class="bg-red-500 transition-all hover:bg-red-600" style="height: 3%"></div>
                                        </div>
                                        <!-- Day 3 -->
                                        <div class="group flex w-full flex-col-reverse gap-0.5">
                                            <div class="rounded-t bg-green-500 transition-all hover:bg-green-600" style="height: 55%"></div>
                                            <div class="bg-yellow-500 transition-all hover:bg-yellow-600" style="height: 15%"></div>
                                            <div class="bg-red-500 transition-all hover:bg-red-600" style="height: 5%"></div>
                                        </div>
                                        <!-- Day 4 -->
                                        <div class="group flex w-full flex-col-reverse gap-0.5">
                                            <div class="rounded-t bg-green-500 transition-all hover:bg-green-600" style="height: 70%"></div>
                                            <div class="bg-yellow-500 transition-all hover:bg-yellow-600" style="height: 15%"></div>
                                            <div class="bg-red-500 transition-all hover:bg-red-600" style="height: 5%"></div>
                                        </div>
                                        <!-- Day 5 -->
                                        <div class="group flex w-full flex-col-reverse gap-0.5">
                                            <div class="rounded-t bg-green-500 transition-all hover:bg-green-600" style="height: 85%"></div>
                                            <div class="bg-yellow-500 transition-all hover:bg-yellow-600" style="height: 10%"></div>
                                            <div class="bg-red-500 transition-all hover:bg-red-600" style="height: 5%"></div>
                                        </div>
                                        <!-- Day 6 -->
                                        <div class="group flex w-full flex-col-reverse gap-0.5">
                                            <div class="rounded-t bg-green-500 transition-all hover:bg-green-600" style="height: 75%"></div>
                                            <div class="bg-yellow-500 transition-all hover:bg-yellow-600" style="height: 12%"></div>
                                            <div class="bg-red-500 transition-all hover:bg-red-600" style="height: 3%"></div>
                                        </div>
                                        <!-- Day 7 -->
                                        <div class="group flex w-full flex-col-reverse gap-0.5">
                                            <div class="rounded-t bg-green-500 transition-all hover:bg-green-600" style="height: 90%"></div>
                                            <div class="bg-yellow-500 transition-all hover:bg-yellow-600" style="height: 8%"></div>
                                            <div class="bg-red-500 transition-all hover:bg-red-600" style="height: 2%"></div>
                                        </div>
                                    </div>
                                    <div class="flex justify-between text-xs font-medium text-muted-foreground">
                                        <span>Mon</span>
                                        <span>Tue</span>
                                        <span>Wed</span>
                                        <span>Thu</span>
                                        <span>Fri</span>
                                        <span>Sat</span>
                                        <span>Sun</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Quick Stats Footer -->
                            <div class="border-t bg-muted/30 px-6 py-3">
                                <div class="grid grid-cols-3 gap-4 text-center">
                                    <div>
                                        <div class="text-xs text-muted-foreground">Response Rate</div>
                                        <div class="text-sm font-bold text-primary">94%</div>
                                    </div>
                                    <div>
                                        <div class="text-xs text-muted-foreground">Avg Response</div>
                                        <div class="text-sm font-bold">1.8 hrs</div>
                                    </div>
                                    <div>
                                        <div class="text-xs text-muted-foreground">Published</div>
                                        <div class="text-sm font-bold text-green-600">1,156</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Floating stat badges -->
                        <div class="absolute -right-4 top-20 animate-bounce rounded-lg border bg-card px-3 py-2 shadow-lg" style="animation-duration: 3s">
                            <div class="flex items-center gap-2">
                                <ThumbsUp class="h-4 w-4 text-green-600" />
                                <div>
                                    <div class="text-xs text-muted-foreground">Satisfaction</div>
                                    <div class="text-sm font-bold text-green-600">96%</div>
                                </div>
                            </div>
                        </div>
                        <div class="absolute -left-4 bottom-20 animate-bounce rounded-lg border bg-card px-3 py-2 shadow-lg" style="animation-duration: 4s; animation-delay: 0.5s">
                            <div class="flex items-center gap-2">
                                <Zap class="h-4 w-4 text-yellow-600" />
                                <div>
                                    <div class="text-xs text-muted-foreground">AI Analyzed</div>
                                    <div class="text-sm font-bold">100%</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section id="features" class="border-t bg-muted/30 py-20 lg:py-28">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-16 text-center space-y-4">
                    <div class="inline-flex items-center gap-2 rounded-full border bg-background px-4 py-1.5 text-sm">
                        <Shield class="h-4 w-4 text-primary" />
                        <span class="font-medium">Everything You Need</span>
                    </div>
                    <h2 class="text-3xl font-bold tracking-tight sm:text-4xl">
                        Powerful Features for Customer Success
                    </h2>
                    <p class="mx-auto max-w-2xl text-lg text-muted-foreground">
                        Comprehensive tools to collect, analyze, and act on customer feedback
                    </p>
                </div>

                <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="(feature, index) in features"
                        :key="index"
                        class="group relative overflow-hidden rounded-xl border bg-card p-6 transition-all hover:shadow-xl hover:-translate-y-1"
                    >
                        <!-- Animated gradient background -->
                        <div 
                            class="absolute -right-6 -top-6 h-24 w-24 rounded-full blur-2xl transition-transform group-hover:scale-150"
                            :class="`bg-gradient-to-br ${feature.bgGradient}`"
                        ></div>

                        <div class="relative space-y-4">
                            <!-- Gradient icon with color -->
                            <div 
                                class="inline-flex h-12 w-12 items-center justify-center rounded-xl shadow-md transition-all group-hover:scale-110 group-hover:shadow-lg"
                                :class="`bg-gradient-to-br ${feature.gradient}`"
                            >
                                <component :is="feature.icon" class="h-6 w-6 text-white" />
                            </div>

                            <div class="space-y-2">
                                <h3 class="text-xl font-semibold">{{ feature.title }}</h3>
                                <p class="text-sm text-muted-foreground leading-relaxed">
                                    {{ feature.description }}
                                </p>
                            </div>

                            <ul class="space-y-2.5 border-t pt-4">
                                <li v-for="benefit in feature.benefits" :key="benefit" class="flex items-start gap-2.5">
                                    <div 
                                        class="flex h-5 w-5 items-center justify-center rounded-full flex-shrink-0 mt-0.5"
                                        :class="`bg-gradient-to-br ${feature.bgGradient}`"
                                    >
                                        <Check class="h-3 w-3" :class="feature.iconColor" />
                                    </div>
                                    <span class="text-sm text-muted-foreground">{{ benefit }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Why {{ appName }} Section -->
        <section class="py-20 lg:py-28">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid gap-12 lg:grid-cols-2 lg:gap-16 items-center">
                    <div class="space-y-8">
                        <div class="space-y-4">
                            <div class="inline-flex items-center gap-2 rounded-full border bg-background px-3 py-1 text-sm">
                                <TrendingUp class="h-4 w-4 text-green-600" />
                                <span class="font-medium">Proven Business Results</span>
                            </div>
                            <h2 class="text-3xl font-bold tracking-tight sm:text-4xl">
                                Why Over 1,000 Businesses Choose {{ appName }}
                            </h2>
                            <p class="text-lg text-muted-foreground">
                                Transform raw customer feedback into strategic insights that drive measurable business outcomes and revenue growth.
                            </p>
                        </div>

                        <div class="space-y-6">
                            <div class="flex gap-4 group">
                                <div class="flex-shrink-0">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-gradient-to-br from-green-100 to-green-50 text-green-700 transition-transform group-hover:scale-110 dark:from-green-900/30 dark:to-green-900/10 dark:text-green-400">
                                        <BarChart class="h-6 w-6" />
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <h3 class="text-lg font-semibold">Centralized Feedback Hub</h3>
                                    <p class="text-muted-foreground">
                                        Collect and organize all customer feedback from multiple channels in one unified platform. No more scattered reviews across different systems.
                                    </p>
                                </div>
                            </div>

                            <div class="flex gap-4 group">
                                <div class="flex-shrink-0">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-gradient-to-br from-blue-100 to-blue-50 text-blue-700 transition-transform group-hover:scale-110 dark:from-blue-900/30 dark:to-blue-900/10 dark:text-blue-400">
                                        <Brain class="h-6 w-6" />
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <h3 class="text-lg font-semibold">AI-Powered Sentiment Analysis</h3>
                                    <p class="text-muted-foreground">
                                        Our AI automatically analyzes every review for sentiment, detects patterns, and surfaces urgent issues requiring immediate attention.
                                    </p>
                                    
                                </div>
                            </div>

                            <div class="flex gap-4 group">
                                <div class="flex-shrink-0">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-gradient-to-br from-purple-100 to-purple-50 text-purple-700 transition-transform group-hover:scale-110 dark:from-purple-900/30 dark:to-purple-900/10 dark:text-purple-400">
                                        <ThumbsUp class="h-6 w-6" />
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <h3 class="text-lg font-semibold">Build Trust & Reputation</h3>
                                    <p class="text-muted-foreground">
                                        Showcase authentic customer testimonials with beautiful public review profiles that build credibility and attract new customers.
                                    </p>
                                  
                                </div>
                            </div>

                            <div class="flex gap-4 group">
                                <div class="flex-shrink-0">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-gradient-to-br from-orange-100 to-orange-50 text-orange-700 transition-transform group-hover:scale-110 dark:from-orange-900/30 dark:to-orange-900/10 dark:text-orange-400">
                                        <Zap class="h-6 w-6" />
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <h3 class="text-lg font-semibold">Automated Review Collection</h3>
                                    <p class="text-muted-foreground">
                                        Set it and forget it. Automatically send review requests after purchases, track responses, and send smart follow-ups.
                                    </p>
                                  
                                </div>
                            </div>
                            
                            <div class="flex gap-4 group">
                                <div class="flex-shrink-0">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-gradient-to-br from-pink-100 to-pink-50 text-pink-700 transition-transform group-hover:scale-110 dark:from-pink-900/30 dark:to-pink-900/10 dark:text-pink-400">
                                        <Shield class="h-6 w-6" />
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <h3 class="text-lg font-semibold">Enterprise-Grade Security</h3>
                                    <p class="text-muted-foreground">
                                        Your data is protected with bank-level encryption, SOC 2 compliance, and GDPR-ready privacy controls trusted by Fortune 500 companies.
                                    </p>
                                  
                                </div>
                            </div>
                        </div>
                        
                    </div>

                    <!-- Real Feedback Display -->
                    <div class="relative">
                        <div class="space-y-3 rounded-lg border bg-card p-6">
                            <div class="flex items-center justify-between border-b pb-4">
                                <h3 class="font-semibold">Recent Feedback</h3>
                                <span class="text-sm text-muted-foreground">Live updates</span>
                            </div>

                            <!-- Sample Feedback Items -->
                            <div class="space-y-3">
                                <div class="rounded-lg border p-4 space-y-3">
                                    <div class="flex items-start justify-between">
                                        <div class="flex items-start gap-3">
                                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary/10 font-semibold text-primary">
                                                AM
                                            </div>
                                            <div class="flex-1">
                                                <div class="font-medium">Alice Martinez</div>
                                                <div class="flex gap-0.5 mt-1">
                                                    <Star class="h-4 w-4 fill-yellow-400 text-yellow-400" />
                                                    <Star class="h-4 w-4 fill-yellow-400 text-yellow-400" />
                                                    <Star class="h-4 w-4 fill-yellow-400 text-yellow-400" />
                                                    <Star class="h-4 w-4 fill-yellow-400 text-yellow-400" />
                                                    <Star class="h-4 w-4 fill-yellow-400 text-yellow-400" />
                                                </div>
                                            </div>
                                        </div>
                                        <span class="inline-flex items-center gap-1 rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-700 dark:bg-green-900/30 dark:text-green-400">
                                            <ThumbsUp class="h-3 w-3" />
                                            Positive
                                        </span>
                                    </div>
                                    <p class="text-sm text-muted-foreground">
                                        "Excellent service! The team was very responsive and resolved my issue within minutes. Highly recommend!"
                                    </p>
                                    <div class="flex items-center gap-4 text-xs text-muted-foreground">
                                        <span>2 hours ago</span>
                                        <button class="flex items-center gap-1 hover:text-foreground">
                                            <Reply class="h-3 w-3" />
                                            Reply
                                        </button>
                                    </div>
                                </div>

                                <div class="rounded-lg border p-4 space-y-3">
                                    <div class="flex items-start justify-between">
                                        <div class="flex items-start gap-3">
                                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary/10 font-semibold text-primary">
                                                JD
                                            </div>
                                            <div class="flex-1">
                                                <div class="font-medium">John Davis</div>
                                                <div class="flex gap-0.5 mt-1">
                                                    <Star class="h-4 w-4 fill-yellow-400 text-yellow-400" />
                                                    <Star class="h-4 w-4 fill-yellow-400 text-yellow-400" />
                                                    <Star class="h-4 w-4 fill-yellow-400 text-yellow-400" />
                                                    <Star class="h-4 w-4 fill-yellow-400 text-yellow-400" />
                                                    <Star class="h-4 w-4 text-muted-foreground" />
                                                </div>
                                            </div>
                                        </div>
                                        <span class="inline-flex items-center gap-1 rounded-full bg-blue-100 px-2 py-1 text-xs font-medium text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
                                            <MessageCircle class="h-3 w-3" />
                                            Neutral
                                        </span>
                                    </div>
                                    <p class="text-sm text-muted-foreground">
                                        "Good product overall. Delivery was on time. Would be perfect with faster customer support response."
                                    </p>
                                    <div class="flex items-center gap-4 text-xs text-muted-foreground">
                                        <span>5 hours ago</span>
                                        <button class="flex items-center gap-1 hover:text-foreground">
                                            <Reply class="h-3 w-3" />
                                            Reply
                                        </button>
                                    </div>
                                </div>

                                <div class="rounded-lg border p-4 space-y-3">
                                    <div class="flex items-start justify-between">
                                        <div class="flex items-start gap-3">
                                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary/10 font-semibold text-primary">
                                                SK
                                            </div>
                                            <div class="flex-1">
                                                <div class="font-medium">Sarah Kim</div>
                                                <div class="flex gap-0.5 mt-1">
                                                    <Star class="h-4 w-4 fill-yellow-400 text-yellow-400" />
                                                    <Star class="h-4 w-4 fill-yellow-400 text-yellow-400" />
                                                    <Star class="h-4 w-4 fill-yellow-400 text-yellow-400" />
                                                    <Star class="h-4 w-4 text-muted-foreground" />
                                                    <Star class="h-4 w-4 text-muted-foreground" />
                                                </div>
                                            </div>
                                        </div>
                                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2 py-1 text-xs font-medium text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">
                                            <AlertCircle class="h-3 w-3" />
                                            Needs Review
                                        </span>
                                    </div>
                                    <p class="text-sm text-muted-foreground">
                                        "Product arrived damaged. Waiting for replacement. Hope the process goes smoothly."
                                    </p>
                                    <div class="flex items-center gap-4 text-xs text-muted-foreground">
                                        <span>1 day ago</span>
                                        <button class="flex items-center gap-1 text-primary hover:text-primary/80">
                                            <Reply class="h-3 w-3" />
                                            Reply sent
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Testimonials Section -->
        <section class="border-t bg-muted/30 py-20 lg:py-28">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-16 text-center space-y-4">
                    <h2 class="text-3xl font-bold tracking-tight sm:text-4xl">
                        Trusted by Growing Businesses
                    </h2>
                    <p class="mx-auto max-w-2xl text-lg text-muted-foreground">
                        See what our customers have to say about {{ appName }}
                    </p>
                </div>

                <div class="grid gap-8 md:grid-cols-3">
                    <div
                        v-for="testimonial in testimonials"
                        :key="testimonial.name"
                        class="rounded-lg border bg-card p-6 space-y-4"
                    >
                        <div class="flex gap-0.5">
                            <Star v-for="i in 5" :key="i" class="h-5 w-5 fill-yellow-400 text-yellow-400" />
                        </div>
                        <p class="text-muted-foreground">
                            "{{ testimonial.comment }}"
                        </p>
                        <div class="flex items-center gap-3 border-t pt-4">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary/10 font-semibold text-primary">
                                {{ testimonial.avatar }}
                            </div>
                            <div>
                                <div class="font-semibold">{{ testimonial.name }}</div>
                                <div class="text-sm text-muted-foreground">
                                    {{ testimonial.role }}, {{ testimonial.company }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Business Benefits Section -->
        <section class="border-t bg-gradient-to-b from-muted/30 to-background py-20 lg:py-28">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-12 text-center space-y-4">
                    <div class="inline-flex items-center gap-2 rounded-full border bg-background px-4 py-1.5 text-sm">
                        <Sparkles class="h-4 w-4 text-primary" />
                        <span class="font-medium">Complete Solution</span>
                    </div>
                    <h2 class="text-3xl font-bold tracking-tight sm:text-4xl">
                        Everything Your Business Needs to Succeed
                    </h2>
                    <p class="mx-auto max-w-2xl text-lg text-muted-foreground">
                        From small cafes to enterprise retailers, {{ appName }} scales with your business
                    </p>
                </div>

                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <!-- Benefit 1 -->
                    <div class="group relative overflow-hidden rounded-xl border bg-card p-6 transition-all hover:shadow-lg hover:-translate-y-1">
                        <div class="absolute right-4 top-4 opacity-10 transition-opacity group-hover:opacity-20">
                            <Globe class="h-16 w-16 text-primary" />
                        </div>
                        <div class="relative space-y-3">
                            <div class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-gradient-to-br from-blue-500/20 to-blue-500/5">
                                <Globe class="h-5 w-5 text-blue-600" />
                            </div>
                            <h3 class="font-semibold">Public Review Profiles</h3>
                            <p class="text-sm text-muted-foreground">
                                Showcase your best reviews with beautiful, SEO-optimized public pages that build trust and attract new customers through search engines.
                            </p>
                            <div class="flex items-center gap-2 text-xs font-medium text-blue-600">
                                <TrendingUp class="h-3 w-3" />
                                <span>Boost SEO rankings</span>
                            </div>
                        </div>
                    </div>

                    <!-- Benefit 2 -->
                    <div class="group relative overflow-hidden rounded-xl border bg-card p-6 transition-all hover:shadow-lg hover:-translate-y-1">
                        <div class="absolute right-4 top-4 opacity-10 transition-opacity group-hover:opacity-20">
                            <MessageCircle class="h-16 w-16 text-primary" />
                        </div>
                        <div class="relative space-y-3">
                            <div class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-gradient-to-br from-green-500/20 to-green-500/5">
                                <MessageCircle class="h-5 w-5 text-green-600" />
                            </div>
                            <h3 class="font-semibold">Smart Response Templates</h3>
                            <p class="text-sm text-muted-foreground">
                                Respond to reviews 10x faster with AI-generated response templates customized to your brand voice and specific feedback scenarios.
                            </p>
                            <div class="flex items-center gap-2 text-xs font-medium text-green-600">
                                <Zap class="h-3 w-3" />
                                <span>Save hours daily</span>
                            </div>
                        </div>
                    </div>

                    <!-- Benefit 3 -->
                    <div class="group relative overflow-hidden rounded-xl border bg-card p-6 transition-all hover:shadow-lg hover:-translate-y-1">
                        <div class="absolute right-4 top-4 opacity-10 transition-opacity group-hover:opacity-20">
                            <BarChart3 class="h-16 w-16 text-primary" />
                        </div>
                        <div class="relative space-y-3">
                            <div class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-gradient-to-br from-purple-500/20 to-purple-500/5">
                                <BarChart3 class="h-5 w-5 text-purple-600" />
                            </div>
                            <h3 class="font-semibold">Advanced Analytics</h3>
                            <p class="text-sm text-muted-foreground">
                                Track NPS scores, sentiment trends, rating distributions, and custom metrics. Export reports for stakeholders in seconds.
                            </p>
                            <div class="flex items-center gap-2 text-xs font-medium text-purple-600">
                                <BarChart class="h-3 w-3" />
                                <span>Data-driven decisions</span>
                            </div>
                        </div>
                    </div>

                    <!-- Benefit 4 -->
                    <div class="group relative overflow-hidden rounded-xl border bg-card p-6 transition-all hover:shadow-lg hover:-translate-y-1">
                        <div class="absolute right-4 top-4 opacity-10 transition-opacity group-hover:opacity-20">
                            <Users class="h-16 w-16 text-primary" />
                        </div>
                        <div class="relative space-y-3">
                            <div class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-gradient-to-br from-orange-500/20 to-orange-500/5">
                                <Users class="h-5 w-5 text-orange-600" />
                            </div>
                            <h3 class="font-semibold">Team Collaboration</h3>
                            <p class="text-sm text-muted-foreground">
                                Add unlimited team members, assign permissions by role, and collaborate seamlessly across departments with activity tracking.
                            </p>
                            <div class="flex items-center gap-2 text-xs font-medium text-orange-600">
                                <Users class="h-3 w-3" />
                                <span>Unlimited team members</span>
                            </div>
                        </div>
                    </div>

                    <!-- Benefit 5 -->
                    <div class="group relative overflow-hidden rounded-xl border bg-card p-6 transition-all hover:shadow-lg hover:-translate-y-1">
                        <div class="absolute right-4 top-4 opacity-10 transition-opacity group-hover:opacity-20">
                            <Shield class="h-16 w-16 text-primary" />
                        </div>
                        <div class="relative space-y-3">
                            <div class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-gradient-to-br from-red-500/20 to-red-500/5">
                                <Shield class="h-5 w-5 text-red-600" />
                            </div>
                            <h3 class="font-semibold">Content Moderation</h3>
                            <p class="text-sm text-muted-foreground">
                                AI automatically flags inappropriate content, spam, and policy violations before they go public. Full moderation control in your hands.
                            </p>
                            <div class="flex items-center gap-2 text-xs font-medium text-red-600">
                                <Lock class="h-3 w-3" />
                                <span>Brand protection</span>
                            </div>
                        </div>
                    </div>

                    <!-- Benefit 6 -->
                    <div class="group relative overflow-hidden rounded-xl border bg-card p-6 transition-all hover:shadow-lg hover:-translate-y-1">
                        <div class="absolute right-4 top-4 opacity-10 transition-opacity group-hover:opacity-20">
                            <Plug class="h-16 w-16 text-primary" />
                        </div>
                        <div class="relative space-y-3">
                            <div class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-gradient-to-br from-cyan-500/20 to-cyan-500/5">
                                <Plug class="h-5 w-5 text-cyan-600" />
                            </div>
                            <h3 class="font-semibold">API & Integrations</h3>
                            <p class="text-sm text-muted-foreground">
                                Connect with your existing tools via REST API, webhooks, and native integrations with popular platforms like Shopify, WooCommerce, and more.
                            </p>
                            <div class="flex items-center gap-2 text-xs font-medium text-cyan-600">
                                <Zap class="h-3 w-3" />
                                <span>Seamless workflow</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Enhanced CTA Section -->
        <section class="border-t py-20 lg:py-28">
            <div class="mx-auto max-w-4xl px-4 text-center sm:px-6 lg:px-8">
                <div class="space-y-8">
                    <div class="space-y-4">
                        <div class="inline-flex items-center gap-2 rounded-full bg-primary/10 px-4 py-2 text-sm font-medium text-primary">
                            <Sparkles class="h-4 w-4" />
                            <span>Start Growing Today</span>
                        </div>
                        <h2 class="text-3xl font-bold tracking-tight sm:text-5xl">
                            Ready to Transform Your Customer Feedback?
                        </h2>
                        <p class="mx-auto max-w-2xl text-lg text-muted-foreground">
                            Join 1,000+ businesses using {{ appName }} to increase customer satisfaction, improve retention, and drive revenue growth through actionable feedback insights.
                        </p>
                    </div>

                    <div class="flex flex-col items-center gap-4">
                        <div class="flex flex-wrap justify-center gap-4">
                            <Link
                                v-if="canRegister"
                                :href="register()"
                                class="group inline-flex items-center gap-2 rounded-lg bg-primary px-8 py-4 text-lg font-semibold text-primary-foreground shadow-lg transition-all hover:bg-primary/90 hover:shadow-xl hover:shadow-primary/20 hover:scale-105"
                            >
                                Start Your Free Trial
                                <ArrowRight class="h-5 w-5 transition-transform group-hover:translate-x-1" />
                            </Link>
                            <a
                                href="#features"
                                class="inline-flex items-center gap-2 rounded-lg border-2 px-8 py-4 text-lg font-semibold transition-all hover:bg-accent hover:border-primary/50"
                            >
                                Watch Demo
                                <Star class="h-5 w-5" />
                            </a>
                        </div>
                        
                        <div class="flex flex-wrap items-center justify-center gap-6 text-sm text-muted-foreground">
                            <div class="flex items-center gap-2">
                                <CheckCircle2 class="h-4 w-4 text-green-600" />
                                <span>No credit card required</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <CheckCircle2 class="h-4 w-4 text-green-600" />
                                <span>14-day free trial</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <CheckCircle2 class="h-4 w-4 text-green-600" />
                                <span>Cancel anytime</span>
                            </div>
                        </div>
                    </div>

                    <!-- Trust Indicators -->
                    <div class="border-t pt-8">
                        <div class="grid grid-cols-2 gap-8 md:grid-cols-4">
                            <div class="text-center">
                                <div class="text-2xl font-bold text-primary">50K+</div>
                                <div class="text-sm text-muted-foreground">Reviews Analyzed</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-primary">1,000+</div>
                                <div class="text-sm text-muted-foreground">Happy Businesses</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-primary">99.9%</div>
                                <div class="text-sm text-muted-foreground">Uptime SLA</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-primary">4.9/5</div>
                                <div class="text-sm text-muted-foreground">Customer Rating</div>
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
                    <div class="space-y-4">
                        <div class="flex items-center">
                            <AppLogoIcon class="h-20 w-20" />
                            <span class="text-2xl font-semibold ml-2">{{ appName }}</span>
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
                            <li><a href="#" class="hover:text-foreground transition-colors">API Docs</a></li>
                            <li><a href="#" class="hover:text-foreground transition-colors">Integrations</a></li>
                        </ul>
                    </div>

                    <div class="space-y-3">
                        <h4 class="font-semibold">Company</h4>
                        <ul class="space-y-2 text-sm text-muted-foreground">
                            <li><a href="#" class="hover:text-foreground transition-colors">About</a></li>
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
                            <li><a href="#" class="hover:text-foreground transition-colors">GDPR Compliance</a></li>
                        </ul>
                    </div>
                </div>

                <div class="mt-12 border-t pt-8 text-center text-sm text-muted-foreground">
                    <p>&copy; {{ new Date().getFullYear() }} {{ appName }}. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </div>
</template>
