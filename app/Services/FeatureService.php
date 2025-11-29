<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Feature;
use App\Models\FeatureUsage;
use App\Models\Plan;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;

class FeatureService
{
    public function hasFeature(Business $business, string $featureKey): bool
    {
        if (App::environment('testing')) {
            return true;
        }

        $cacheKey = sprintf('features:has:%d:%s', $business->id, $featureKey);

        return $this->rememberForever($cacheKey, function () use ($business, $featureKey) {
            $feature = Feature::where('key', $featureKey)->first();

            if (! $feature || ! $business->plan) {
                return false;
            }

            return $business->plan
                ->planFeatures()
                ->where('feature_id', $feature->id)
                ->where('is_enabled', true)
                ->exists();
        });
    }

    public function getEffectiveQuota(Business $business, string $featureKey, ?Carbon $periodStart = null): ?int
    {
        $periodStartDate = $this->getPeriodStartDate($periodStart);

        $cacheKey = $this->featureCacheKey($business, $featureKey, $periodStartDate, 'quota');

        return $this->rememberForever($cacheKey, function () use ($business, $featureKey, $periodStartDate) {
            $feature = Feature::where('key', $featureKey)->first();

            if (! $feature || $feature->type !== 'quota' || ! $business->plan) {
                return null;
            }

            $planFeature = $business->plan
                ->planFeatures()
                ->where('feature_id', $feature->id)
                ->where('is_enabled', true)
                ->first();

            if (! $planFeature) {
                return null;
            }

            if ($planFeature->is_unlimited) {
                return -1;
            }

            $baseQuota = $planFeature->quota ?? 0;

            $addons = $business->featureAddons()
                ->where(function ($query) use ($periodStartDate) {
                    $query->whereNull('period_start')
                        ->orWhereDate('period_start', $periodStartDate);
                })
                ->whereHas('addon', function ($query) use ($feature) {
                    $query->where('feature_id', $feature->id)
                        ->where('is_active', true);
                })
                ->with('addon')
                ->get();

            $addonQuota = $addons->sum(function ($businessAddon) {
                return $businessAddon->quantity * ($businessAddon->addon->increment_quota ?? 0);
            });

            return $baseQuota + $addonQuota;
        });
    }

    public function getUsage(Business $business, string $featureKey, ?Carbon $periodStart = null): int
    {
        $periodStartDate = $this->getPeriodStartDate($periodStart);

        $cacheKey = $this->featureCacheKey($business, $featureKey, $periodStartDate, 'usage');

        return $this->rememberForever($cacheKey, function () use ($business, $featureKey, $periodStartDate) {
            $feature = Feature::where('key', $featureKey)->first();

            if (! $feature) {
                return 0;
            }

            $usage = FeatureUsage::where('business_id', $business->id)
                ->where('feature_id', $feature->id)
                ->whereDate('period_start', $periodStartDate)
                ->first();

            if (! $usage) {
                $usage = FeatureUsage::create([
                    'business_id' => $business->id,
                    'feature_id' => $feature->id,
                    'period_start' => $periodStartDate,
                    'used' => 0,
                ]);
            }

            return (int) $usage->used;
        });
    }

    public function remainingQuota(Business $business, string $featureKey, ?Carbon $periodStart = null): ?int
    {
        $quota = $this->getEffectiveQuota($business, $featureKey, $periodStart);

        if ($quota === null || $quota === -1) {
            return $quota;
        }

        $used = $this->getUsage($business, $featureKey, $periodStart);

        return max(0, $quota - $used);
    }

    public function canUseFeature(Business $business, string $featureKey, int $amount = 1, ?Carbon $periodStart = null): bool
    {
        if (App::environment('testing')) {
            return true;
        }
        
        if (!$this->hasFeature($business, $featureKey)) {
            return false;
        }

        $quota = $this->getEffectiveQuota($business, $featureKey, $periodStart);

        if ($quota === null || $quota === -1) {
            return true;
        }

        $remaining = $this->remainingQuota($business, $featureKey, $periodStart);

        return $remaining >= $amount;
    }

    public function recordUsage(Business $business, string $featureKey, int $amount = 1, ?Carbon $periodStart = null): bool
    {
        $feature = Feature::where('key', $featureKey)->first();

        if (! $feature) {
            return false;
        }

        $periodStartDate = $this->getPeriodStartDate($periodStart);

        $usage = FeatureUsage::where('business_id', $business->id)
            ->where('feature_id', $feature->id)
            ->whereDate('period_start', $periodStartDate)
            ->first();

        if (! $usage) {
            $usage = FeatureUsage::create([
                'business_id' => $business->id,
                'feature_id' => $feature->id,
                'period_start' => $periodStartDate,
                'used' => 0,
            ]);
        }

        $quota = $this->getEffectiveQuota($business, $featureKey, $periodStartDate);

        if ($quota !== null && $quota !== -1 && ($usage->used + $amount) > $quota) {
            return false;
        }

        $usage->increment('used', $amount);

        $usageCacheKey = $this->featureCacheKey($business, $featureKey, $periodStartDate, 'usage');
        Cache::forget($usageCacheKey);

        return true;
    }

    public function getPricingJsonForWebsite(string $currency = 'KES'): array
    {
        $cacheKey = $this->globalCacheKey('pricing_json_'.$currency);

        return $this->rememberForever($cacheKey, function () use ($currency) {
            $plans = \App\Models\Plan::with(['planFeatures.feature' => function ($query) {
                $query->orderBy('category')->orderBy('name');
            }])
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get();

            $plansArray = [];

            foreach ($plans as $plan) {
                $features = [];

                foreach ($plan->planFeatures as $planFeature) {
                    $feature = $planFeature->feature;

                    if (! $feature || ! $planFeature->is_enabled) {
                        continue;
                    }

                    if ($feature->type === 'boolean') {
                        $features[$feature->key] = true;
                        continue;
                    }

                    if ($feature->type === 'quota') {
                        $features[$feature->key] = [
                            'quota' => $planFeature->is_unlimited ? -1 : ($planFeature->quota ?? 0),
                            'unit' => $planFeature->unit ?? $feature->default_unit,
                        ];
                    }
                }

                $plansArray[] = [
                    'id' => $plan->key,
                    'name' => $plan->name,
                    'price_kes' => (float) $plan->price_kes,
                    'price_usd' => (float) $plan->price_usd,
                    'price_kes_yearly' => (float) $plan->price_kes_yearly,
                    'price_usd_yearly' => (float) $plan->price_usd_yearly,
                    'features' => $features,
                ];
            }

            return [
                'currency' => $currency,
                'plans' => $plansArray,
            ];
        });
    }

    public function getBusinessFeatures(Business $business)
    {
        $cacheKey = sprintf('features:business:%d', $business->id);

        return $this->rememberForever($cacheKey, function () use ($business) {
            $plan = $business->plan;

            if (! $plan) {
                return [];
            }

            $plan->load('planFeatures.feature');

            $features = [];

            foreach ($plan->planFeatures as $planFeature) {
                $feature = $planFeature->feature;

                if (! $feature) {
                    continue;
                }

                // if (! $this->canUseFeature($business, $feature->key)) {
                //     continue;
                // }

                $features[] = $feature->key;
            }

            return $features;
        });
    }

    public function clearAllCache(): void
    {
        $keys = Cache::get('features:cache_keys', []);

        foreach ($keys as $key) {
            Cache::forget($key);
        }

        Cache::forget('features:cache_keys');
    }


    public function clearBusinessCache(Business $business): void
    {
        $keys = Cache::get('features:cache_keys', []);

        if (empty($keys)) {
            return;
        }

        $remaining = [];

        $businessMarker = ':' . $business->id . ':';
        $businessListKey = sprintf('features:business:%d', $business->id);

        foreach ($keys as $key) {
            // keys that include the business id marker should be forgotten
            if (strpos($key, $businessMarker) !== false || $key === $businessListKey) {
                Cache::forget($key);
            } else {
                $remaining[] = $key;
            }
        }

        Cache::forever('features:cache_keys', $remaining);
    }


    public function syncBusinessFeatures(Business $business): array
    {
        $this->clearBusinessCache($business);

        $features = $this->getBusinessFeatures($business);

        foreach ($features as $featureKey) {
            $this->hasFeature($business, $featureKey);
        }

        return $features;
    }

    protected function getPeriodStartDate(?Carbon $periodStart = null): Carbon
    {
        return ($periodStart ? $periodStart->copy() : now())
            ->startOfMonth()
            ->startOfDay();
    }

    protected function featureCacheKey(Business $business, string $featureKey, Carbon $periodStartDate, string $suffix): string
    {
        return sprintf(
            'features:%s:%d:%s:%s',
            $suffix,
            $business->id,
            $featureKey,
            $periodStartDate->toDateString(),
        );
    }

    protected function globalCacheKey(string $suffix): string
    {
        return 'features:global:'.$suffix;
    }

    protected function rememberForever(string $key, \Closure $callback)
    {
        $this->registerCacheKey($key);

        return Cache::rememberForever($key, $callback);
    }

    protected function registerCacheKey(string $key): void
    {
        $keys = Cache::get('features:cache_keys', []);

        if (! in_array($key, $keys, true)) {
            $keys[] = $key;
            Cache::forever('features:cache_keys', $keys);
        }
    }


}
