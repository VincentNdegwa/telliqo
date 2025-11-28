<?php

namespace App\Services;

use App\Models\Business;
use App\Models\LocalSubscription;
use App\Models\LocalTransaction;
use App\Models\Plan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SubscriptionService
{
    public function __construct(
        protected FeatureService $features,
        protected PaypalService $paypal,
        protected PaddleService $paddle,
    ) {}

    public function createLocalSubscription(Business $business, ?Plan $plan, array $data): LocalSubscription
    {
        try {
            // Ensure only one active subscription per business
            $existingActive = $business->localSubscriptions()
                ->where('status', 'active')
                ->get();

            foreach ($existingActive as $active) {
                $active->status = 'expired';
                $active->ends_at = $active->ends_at ?? now();
                $active->save();
            }

            $subscription = new LocalSubscription();

            $subscription->business_id = $business->id;
            $subscription->plan_id = $plan?->id;
            $subscription->provider = $data['provider'];
            $subscription->external_id = $data['external_id'] ?? null;
            $subscription->status = $data['status'] ?? 'active';
            $subscription->billing_period = $data['billing_period'] ?? null;
            $subscription->starts_at = $data['starts_at'] ?? now();
            $subscription->trial_ends_at = $data['trial_ends_at'] ?? null;
            $subscription->amount = $data['amount'] ?? null;
            $subscription->currency = $data['currency'] ?? null;

            $effectiveWhen = $data['effective_when'] ?? 'immediately';
            $durationMultiplier = (int) ($data['duration_multiplier'] ?? 1);
            if ($durationMultiplier < 1) {
                $durationMultiplier = 1;
            }

            $nonAutoRenewProviders = ['mpesa', 'manual', 'coupon'];
            $isNonAutoRenew = in_array($subscription->provider, $nonAutoRenewProviders, true);

            $incomingMeta = $data['meta'] ?? null;

            if (is_string($incomingMeta)) {
                $decoded = json_decode($incomingMeta, true);

                if (json_last_error() === JSON_ERROR_NONE) {
                    $incomingMeta = $decoded;
                } else {
                    $incomingMeta = ['raw' => $incomingMeta];
                }
            }

            $baseMeta = [
                'change_timing' => $effectiveWhen,
                'duration_multiplier' => $durationMultiplier,
                'auto_renew' => ! $isNonAutoRenew,
            ];

            $subscription->meta = array_merge(
                $baseMeta,
                is_array($incomingMeta) ? $incomingMeta : []
            );

            if ($isNonAutoRenew && $subscription->billing_period) {
                $periodCount = $durationMultiplier > 0 ? $durationMultiplier : 1;

                if ($subscription->billing_period === 'monthly') {
                    $subscription->ends_at = $subscription->starts_at->copy()->addMonths($periodCount);
                } elseif ($subscription->billing_period === 'yearly') {
                    $subscription->ends_at = $subscription->starts_at->copy()->addYears($periodCount);
                }
            } else {
                $subscription->ends_at = $data['ends_at'] ?? $subscription->ends_at;
            }

            $subscription->save();

            if ($plan) {
                $business->plan()->associate($plan);
                $business->save();
            }

            LocalTransaction::create([
                'business_id' => $business->id,
                'local_subscription_id' => $subscription->id,
                'provider' => $subscription->provider,
                'status' => $data['transaction_status'] ?? 'completed',
                'amount' => $subscription->amount,
                'currency' => $subscription->currency,
                'external_id' => $subscription->external_id,
                'meta' => $data['transaction_meta'] ?? null,
                'paid_at' => $data['paid_at'] ?? now(),
            ]);

            $this->features->syncBusinessFeatures($business);

            return $subscription;
        } catch (\Throwable $th) {
            Log::info("Error recording subscription", [
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
            ]);
            throw $th;
        }
    }


    public function scheduleLocalPlanChange(Business $business, ?Plan $plan, array $data): ?LocalSubscription
    {
        $current = $this->getCurrentLocalSubscription($business);

        if (! $current) {
            return null;
        }

        $incomingMeta = $data['meta'] ?? [];

        if (is_string($incomingMeta)) {
            $decoded = json_decode($incomingMeta, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $incomingMeta = $decoded;
            } else {
                $incomingMeta = ['raw' => $incomingMeta];
            }
        }

        $scheduledChange = [
            'new_plan_id' => $plan?->id,
            'provider' => $data['provider'] ?? $current->provider,
            'billing_period' => $data['billing_period'] ?? $current->billing_period,
            'duration_multiplier' => (int) ($data['duration_multiplier'] ?? 1),
            'currency' => $data['currency'] ?? $current->currency,
        ];

        $meta = array_merge(
            $current->meta ?? [],
            [
                'change_timing' => 'end_of_cycle',
                'scheduled_change' => $scheduledChange,
            ],
            is_array($incomingMeta) ? $incomingMeta : []
        );

        $current->scheduled_plan_id = $plan?->id;
        $current->meta = $meta;
        $current->save();

        $this->clearCacheForBusiness($business);

        return $current;
    }


    public function cancelLocalSubscription(LocalSubscription $subscription, Business $business): void
    {
        $subscription->status = 'canceled';
        $subscription->ends_at = $subscription->ends_at ?? now();
        $subscription->save();

        $business->plan_id = null;
        $business->save();

        $this->clearCacheForBusiness($business);
    }

    public function revisePaypalSubscriptionPlan(Business $business, LocalSubscription $subscription, Plan $newPlan, array $options = []): array
    {
        if ($subscription->provider !== 'paypal') {
            throw new \InvalidArgumentException('Subscription provider is not PayPal');
        }

        if (! $subscription->external_id) {
            throw new \InvalidArgumentException('Missing PayPal subscription id on local record');
        }

        if (strpos($subscription->external_id, 'I-') !== 0) {
            throw new \InvalidArgumentException('Current PayPal record is not a Subscriptions API subscription and cannot be revised. Please start a new PayPal subscription.');
        }

        $billingPeriod = $options['billing_period'] ?? ($subscription->billing_period ?? 'monthly');
        $currency = $options['currency'] ?? env('PAYPAL_CURRENCY', 'USD');

        $periodKey = $billingPeriod === 'yearly' ? 'yearly' : 'monthly';
        $paypalPlanId = $periodKey === 'yearly'
            ? $newPlan->paypal_plan_id_yearly
            : $newPlan->paypal_plan_id_monthly;

        if (! $paypalPlanId) {
            throw new \RuntimeException('Missing PayPal plan id on target plan for period: '.$periodKey);
        }

        $meta = $subscription->meta ?? [];
        $meta['pending_revision'] = [
            'new_plan_id' => $newPlan->id,
            'billing_period' => $billingPeriod,
            'currency' => $currency,
        ];
        $subscription->meta = $meta;
        $subscription->save();

        $revision = $this->paypal->reviseSubscriptionPlan($subscription->external_id, $paypalPlanId, [
            'billing_period' => $billingPeriod,
            'currency' => $currency,
        ]);

        return [
            'error' => false,
            'message' => 'Subscription revision created successfully',
            'url' => $revision['approval_url'] ?? null,
            'paypal_subscription_id' => $revision['id'] ?? $subscription->external_id,
        ];
    }


    public function suspendPaypalSubscription(LocalSubscription $subscription, Business $business, string $reason): void
    {
        if ($subscription->provider !== 'paypal') {
            throw new \InvalidArgumentException('Subscription provider is not PayPal');
        }

        if (! $subscription->external_id) {
            throw new \InvalidArgumentException('Missing PayPal subscription id on local record');
        }

        $this->paypal->suspendSubscription($subscription->external_id, $reason);

        $subscription->status = 'paused';
        $subscription->save();

        $this->clearCacheForBusiness($business);
    }


    public function cancelPaypalSubscription(LocalSubscription $subscription, Business $business, string $reason): void
    {
        if ($subscription->provider !== 'paypal') {
            throw new \InvalidArgumentException('Subscription provider is not PayPal');
        }

        if (! $subscription->external_id) {
            throw new \InvalidArgumentException('Missing PayPal subscription id on local record');
        }

        $this->paypal->cancelSubscription($subscription->external_id, $reason);

        $subscription->status = 'canceled';
        $subscription->ends_at = $subscription->ends_at ?? now();
        $subscription->save();

        $business->plan_id = null;
        $business->save();

        $this->clearCacheForBusiness($business);
    }


    public function reactivatePaypalSubscription(LocalSubscription $subscription, Business $business, string $reason): void
    {
        if ($subscription->provider !== 'paypal') {
            throw new \InvalidArgumentException('Subscription provider is not PayPal');
        }

        if (! $subscription->external_id) {
            throw new \InvalidArgumentException('Missing PayPal subscription id on local record');
        }

        $this->paypal->activateSubscription($subscription->external_id, $reason);

        $subscription->status = 'active';
        $subscription->save();

        if ($subscription->plan) {
            $business->plan()->associate($subscription->plan);
            $business->save();
        }

        $this->clearCacheForBusiness($business);
    }


    public function cancelPaddleSubscription(Business $business, bool $immediately = false): void
    {
        $subscription = $business->subscription();

        if (! $subscription) {
            throw new \RuntimeException('No active Paddle subscription to cancel');
        }

        if ($immediately) {
            $subscription->cancelNow();
        } else {
            $subscription->cancel();
        }
    }


    public function pausePaddleSubscription(Business $business, bool $immediately = false): void
    {
        $subscription = $business->subscription();

        if (! $subscription) {
            throw new \RuntimeException('No active Paddle subscription to pause');
        }

        if ($immediately) {
            $subscription->pauseNow();
        } else {
            $subscription->pause();
        }
    }


    public function resumePaddleSubscription(Business $business): void
    {
        $subscription = $business->subscription();

        if (! $subscription) {
            throw new \RuntimeException('No paused Paddle subscription to resume');
        }

        $subscription->resume();
    }


    public function swapPaddleSubscription(Business $business, Plan $plan, string $billingPeriod): void
    {
        $subscription = $business->subscription();

        if (! $subscription) {
            throw new \RuntimeException('No active Paddle subscription to swap');
        }

        $periodKey = $billingPeriod === 'yearly' ? 'yearly' : 'monthly';

        $priceId = $periodKey === 'yearly'
            ? $plan->paddle_plan_id_yearly
            : $plan->paddle_plan_id_monthly;

        if (! $priceId) {
            throw new \RuntimeException('Missing Paddle price id on target plan for period: '.$periodKey);
        }

        // Enforce policy: no proration, next billing only
        $subscription->noProrate()->swap($priceId);
    }

    public function getLocalSubscriptions(Business $business)
    {
        $cacheKey = $this->cacheKey('local_list', $business);

        return $this->rememberForever($cacheKey, function () use ($business) {
            return $business->localSubscriptions()
                ->orderByDesc('starts_at')
                ->orderByDesc('created_at')
                ->limit(10)
                ->get();
        });
    }

    public function getCurrentLocalSubscription(Business $business): ?LocalSubscription
    {
        $cacheKey = $this->cacheKey('local_current', $business);

        return $this->rememberForever($cacheKey, function () use ($business) {
            return $business->activeLocalSubscriptions()
                ->orderByDesc('starts_at')
                ->orderByDesc('created_at')
                ->first();
        });
    }

    /**
     * Get current active subscription (prioritizes Paddle, falls back to Local)
     */
    public function getCurrentSubscription(Business $business): ?array
    {
        $paddleSubscription = $business->subscription('default');
        
        if ($paddleSubscription && $paddleSubscription->valid()) {
            return [
                'id' => $paddleSubscription->id,
                'plan_name' => optional($business->plan)->name,
                'provider' => 'paddle',
                'status' => $paddleSubscription->status,
                'billing_period' => null, // Can be determined from subscription items if needed
                'amount' => null,
                'currency' => null,
                'starts_at' => optional($paddleSubscription->created_at)->toIso8601String(),
                'ends_at' => optional($paddleSubscription->ends_at)->toIso8601String(),
                'trial_ends_at' => optional($paddleSubscription->trial_ends_at)->toIso8601String(),
                'paused_at' => optional($paddleSubscription->paused_at)->toIso8601String(),
                'paddle_id' => $paddleSubscription->paddle_id,
            ];
        }

        $localSubscription = $this->getCurrentLocalSubscription($business);
        
        if ($localSubscription) {
            return [
                'id' => $localSubscription->id,
                'plan_name' => optional($localSubscription->plan)->name,
                'provider' => $localSubscription->provider,
                'status' => $localSubscription->status,
                'billing_period' => $localSubscription->billing_period,
                'amount' => $localSubscription->amount !== null ? (float) $localSubscription->amount : null,
                'currency' => $localSubscription->currency,
                'starts_at' => optional($localSubscription->starts_at)->toIso8601String(),
                'ends_at' => optional($localSubscription->ends_at)->toIso8601String(),
                'trial_ends_at' => null,
                'paused_at' => null,
                'paddle_id' => null,
            ];
        }

        return null;
    }

    public function getLocalTransactions(Business $business)
    {
        $cacheKey = $this->cacheKey('local_transactions', $business);

        return $this->rememberForever($cacheKey, function () use ($business) {
            return $business->localTransactions()
                ->orderByDesc('paid_at')
                ->orderByDesc('created_at')
                ->get();
        });
    }

    public function getAllSubscriptions(Business $business)
    {
        $subscriptions = collect();

        // Get Paddle subscriptions
        $paddleSubscriptions = $business->subscriptions()
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($sub) {
                return [
                    'id' => $sub->id,
                    'provider' => 'paddle',
                    'paddle_id' => $sub->paddle_id,
                    'status' => $sub->status,
                    'billing_period' => $this->determinePaddleBillingPeriod($sub),
                    'amount' => null, 
                    'currency' => null,
                    'starts_at' => optional($sub->created_at)->toIso8601String(),
                    'trial_ends_at' => optional($sub->trial_ends_at)->toIso8601String(),
                    'ends_at' => optional($sub->ends_at)->toIso8601String(),
                    'paused_at' => optional($sub->paused_at)->toIso8601String(),
                ];
            });

        // Get Local subscriptions
        $localSubscriptions = $this->getLocalSubscriptions($business)
            ->map(function ($sub) {
                return [
                    'id' => $sub->id,
                    'provider' => $sub->provider,
                    'paddle_id' => null,
                    'status' => $sub->status,
                    'billing_period' => $sub->billing_period,
                    'amount' => $sub->amount !== null ? (float) $sub->amount : null,
                    'currency' => $sub->currency,
                    'starts_at' => optional($sub->starts_at)->toIso8601String(),
                    'trial_ends_at' => optional($sub->trial_ends_at)->toIso8601String(),
                    'ends_at' => optional($sub->ends_at)->toIso8601String(),
                    'paused_at' => null,
                ];
            });

        return $subscriptions->concat($paddleSubscriptions)->concat($localSubscriptions);
    }

    public function getAllTransactions(Business $business)
    {
        $transactions = collect();

        // Get Paddle transactions
        $paddleTransactions = $business->transactions()
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($txn) {
                return [
                    'id' => $txn->id,
                    'provider' => 'paddle',
                    'paddle_id' => $txn->paddle_id,
                    'subscription_id' => $txn->paddle_subscription_id ?? null,
                    'status' => $txn->status,
                    'amount' => $txn->total !== null ? (float) $txn->total : null,
                    'currency' => $txn->currency_code ?? null,
                    'paid_at' => optional($txn->billed_at)->toIso8601String(),
                    'created_at' => optional($txn->created_at)->toIso8601String(),
                ];
            });

        // Get Local transactions
        $localTransactions = $this->getLocalTransactions($business)
            ->map(function ($txn) {
                return [
                    'id' => $txn->id,
                    'provider' => $txn->provider,
                    'paddle_id' => null,
                    'subscription_id' => $txn->local_subscription_id,
                    'status' => $txn->status,
                    'amount' => $txn->amount !== null ? (float) $txn->amount : null,
                    'currency' => $txn->currency,
                    'paid_at' => optional($txn->paid_at)->toIso8601String(),
                    'created_at' => optional($txn->created_at)->toIso8601String(),
                ];
            });

        return $transactions->concat($paddleTransactions)->concat($localTransactions)
            ->sortByDesc('created_at')
            ->values();
    }

    protected function determinePaddleBillingPeriod($subscription)
    {
        $items = $subscription->items ?? collect();
        
        if ($items->isEmpty()) {
            return null;
        }

        return null;
    }


    public function startPaddleSubscription(Business $business, ?Plan $plan = null, array $options = []): array
    {
        if (! $plan) {
            return [
                'error' => true,
                'message' => 'Plan not found',
                'options' => null,
            ];
        }

        try {
            $billingPeriod = $options['billing_period'] ?? 'monthly';
            $currency = $options['currency'] ?? null;

            $checkout = $this->paddle->createSubscriptionCheckout($business, $plan, $billingPeriod, [
                'currency' => $currency,
                'type' => $options['type'] ?? 'default',
            ]);

            return [
                'error' => false,
                'message' => 'Paddle checkout created successfully',
                'options' => $checkout,
            ];
        } catch (\Throwable $th) {
            Log::error('Failed to create Paddle subscription checkout', [
                'error' => $th->getMessage(),
            ]);

            return [
                'error' => true,
                'message' => 'Failed to create Paddle subscription checkout: '.$th->getMessage(),
                'options' => null,
            ];
        }
    }


    public function startPaypalSubscription(Business $business, ?Plan $plan = null, array $options = []): array
    {
        if (! $plan) {
            return [
                'error' => true,
                'message' => 'Plan not found',
                'url' => null,
            ];
        }

        try {
            $billingPeriod = $options['billing_period'] ?? 'monthly';
            $currency = $options['currency'] ?? env('PAYPAL_CURRENCY', 'USD');
            $subscription = $this->paypal->createBillingSubscription($business, $plan, $billingPeriod, $currency, $options);

            return [
                'error' => false,
                'message' => 'Subscription created successfully',
                'url' => $subscription['approval_url'] ?? null,
                'paypal_subscription_id' => $subscription['id'] ?? null,
            ];
        } catch (\Throwable $th) {
            Log::error("Failed to create PayPal subscription order", [
                'error' => $th->getMessage(),
            ]);
            return [
                'error' => true,
                'message' => 'Failed to create PayPal subscription',
                'url' => null,
            ];
        }
    }

    public function clearCacheForBusiness(Business $business): void
    {
        $keys = [
            $this->cacheKey('local_list', $business),
            $this->cacheKey('local_current', $business),
            $this->cacheKey('local_transactions', $business),
        ];

        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }

    public function clearAllCache(): void
    {
        $keys = Cache::get('subscriptions:cache_keys', []);

        foreach ($keys as $key) {
            Cache::forget($key);
        }

        Cache::forget('subscriptions:cache_keys');
    }

    public function processScheduledPlanChanges(): void
    {
        LocalSubscription::query()
            ->whereNotNull('scheduled_plan_id')
            ->where('status', 'active')
            ->whereNotNull('ends_at')
            ->where('ends_at', '<=', now())
            ->chunkById(100, function ($subscriptions) {
                foreach ($subscriptions as $subscription) {
                    $business = $subscription->business;
                    $plan = $subscription->scheduledPlan;

                    if (! $business || ! $plan) {
                        $subscription->scheduled_plan_id = null;
                        $subscription->save();
                        continue;
                    }

                    $meta = $subscription->meta ?? [];
                    $scheduled = $meta['scheduled_change'] ?? [];

                    $billingPeriod = $scheduled['billing_period'] ?? $subscription->billing_period ?? 'monthly';
                    $durationMultiplier = (int) ($scheduled['duration_multiplier'] ?? 1);
                    if ($durationMultiplier < 1) {
                        $durationMultiplier = 1;
                    }

                    $currency = $scheduled['currency'] ?? $subscription->currency ?? 'KES';
                    $provider = $scheduled['provider'] ?? $subscription->provider;

                    $basePrice = null;

                    if ($billingPeriod === 'yearly') {
                        $basePrice = $currency === 'USD'
                            ? $plan->price_usd_yearly
                            : $plan->price_kes_yearly;
                    } else {
                        $basePrice = $currency === 'USD'
                            ? $plan->price_usd
                            : $plan->price_kes;
                    }

                    $amount = $basePrice !== null
                        ? (float) $basePrice * $durationMultiplier
                        : null;

                    $subscription->status = 'expired';
                    $subscription->scheduled_plan_id = null;
                    $subscription->save();

                    $this->createLocalSubscription($business, $plan, [
                        'provider' => $provider,
                        'billing_period' => $billingPeriod,
                        'amount' => $amount,
                        'currency' => $currency,
                        'external_id' => null,
                        'effective_when' => 'immediately',
                        'duration_multiplier' => $durationMultiplier,
                        'meta' => array_merge($meta, [
                            'scheduled_from_subscription_id' => $subscription->id,
                        ]),
                    ]);

                    $this->clearCacheForBusiness($business);
                }
            });
    }

    protected function cacheKey(string $suffix, Business $business): string
    {
        return sprintf('subscriptions:%s:%d', $suffix, $business->id);
    }

    protected function rememberForever(string $key, \Closure $callback)
    {
        $this->registerCacheKey($key);

        return Cache::rememberForever($key, $callback);
    }

    protected function registerCacheKey(string $key): void
    {
        $keys = Cache::get('subscriptions:cache_keys', []);

        if (! in_array($key, $keys, true)) {
            $keys[] = $key;
            Cache::forever('subscriptions:cache_keys', $keys);
        }
    }
}
