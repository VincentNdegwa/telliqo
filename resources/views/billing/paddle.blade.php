<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="mode">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title inertia>{{ config('app.name', 'Laravel') }}</title>

    <link rel="icon" href="/telliqo.svg" sizes="any">
    <link rel="icon" href="/telliqo.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/telliqo.svg"> <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/css/theme.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    animation: {
                        'pulse-slow': 'pulse 4s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                        'pulse-slower': 'pulse 6s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                    },
                }
            },
            darkMode: 'class'
        }
    </script>
    @paddleJS
</head>

<body
    class="font-sans antialiased bg-slate-100 text-slate-900 dark:bg-slate-900 dark:text-slate-50 bg-gradient-to-br from-slate-50 to-slate-200 dark:from-slate-950 dark:to-slate-900">
    <!-- Decorative background elements -->
    <div class="fixed inset-0 -z-10 overflow-hidden">
        <div
            class="absolute top-0 right-0 w-1/3 h-1/3 bg-gradient-to-bl from-rose-500/10 to-blue-500/10 rounded-bl-full blur-3xl animate-pulse-slow">
        </div>
        <div
            class="absolute bottom-0 left-0 w-1/3 h-1/3 bg-gradient-to-tr from-amber-500/10 to-purple-500/10 rounded-tr-full blur-3xl animate-pulse-slower">
        </div>
        <div
            class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-1/2 h-1/2 bg-gradient-to-t from-blue-500/5 to-slate-500/5 rounded-full blur-3xl">
        </div>
    </div>

    <div class="min-h-screen flex items-center justify-center p-4">
        <div
            class="bg-white dark:bg-slate-800 shadow-xl rounded-xl w-full max-w-md p-8 border border-slate-200/50 dark:border-slate-700/50 relative overflow-hidden">
            <div class="space-y-4">
                <div class="text-center">
                    <h2 class="text-xl font-semibold">{{ $ui['plan']['name'] ?? 'Subscription' }}</h2>
                    @if (!empty($ui['plan']['description']))
                        <p class="text-sm text-muted-foreground">{{ $ui['plan']['description'] }}</p>
                    @endif
                </div>

                <div class="flex items-center justify-between rounded-md border p-4">
                    <div>
                        <div class="text-sm text-muted-foreground">Billing period</div>
                        <div class="font-medium">{{ ucfirst($ui['billing_period'] ?? 'monthly') }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm text-muted-foreground">Amount</div>
                        <div class="font-semibold">
                            <div>{{ 'KES' }}
                                {{ number_format($ui['plan']['display_price_kes'] ?? ($ui['plan']['price_kes'] ?? 0), 0) }}
                            </div>
                            <div class="text-xs text-muted-foreground">{{ 'USD' }}
                                {{ number_format($ui['plan']['display_price_usd'] ?? ($ui['plan']['price_usd'] ?? 0), 2) }}
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    @if ($checkout != null)
                        <x-paddle-button :checkout="$checkout"
                            class="w-full text-md font-semibold py-3 px-4 rounded-lg shadow transition-all duration-150 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20"
                                fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
                                    clip-rule="evenodd" />
                            </svg>
                            Subscribe now
                        </x-paddle-button>
                    @else
                        <div class="text-sm text-muted-foreground">Checkout is not available at the moment. Please try
                            again later.</div>
                    @endif
                </div>

                <div class="text-center">
                    <a href="{{ $ui['return_url'] ?? route('billing.index') }}"
                        class="inline-block text-sm text-muted-foreground hover:underline">Back to Billing</a>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
