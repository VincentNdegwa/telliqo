<?php

namespace App\Services;

use App\Models\Business;
use App\Models\LocalSubscription;
use App\Models\LocalTransaction;
use App\Models\Plan;
use Illuminate\Support\Facades\Cache;

class SubscriptionService
{
    public function __construct(
        protected FeatureService $features,
    ) {
        //
    }

    public function createLocalSubscription(Business $business, ?Plan $plan, array $data): LocalSubscription
    {
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


    public function startPaddleSubscription(Business $business, ?Plan $plan = null, array $options = []): ?string
    {
        throw new \LogicException('Paddle subscription integration is not implemented yet. Please wire this method using your installed Laravel Paddle package API.');
    }


    public function startPaypalSubscription(Business $business, ?Plan $plan = null, array $options = []): ?string
    {
        throw new \LogicException('PayPal subscription integration is not implemented yet. Please wire this method using your preferred PayPal SDK or package.');
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
