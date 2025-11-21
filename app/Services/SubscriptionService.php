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
        $subscription->status = 'active';
        $subscription->billing_period = $data['billing_period'] ?? null;
        $subscription->starts_at = $data['starts_at'] ?? now();
        $subscription->trial_ends_at = $data['trial_ends_at'] ?? null;
        $subscription->ends_at = $data['ends_at'] ?? null;
        $subscription->amount = $data['amount'] ?? null;
        $subscription->currency = $data['currency'] ?? null;
        $subscription->meta = $data['meta'] ?? null;

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
