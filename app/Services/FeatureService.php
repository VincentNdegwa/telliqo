<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Feature;
use App\Models\FeatureUsage;
use Carbon\Carbon;

class FeatureService
{
    public function hasFeature(Business $business, string $featureKey): bool
    {
        $feature = Feature::where('key', $featureKey)->first();

        if (!$feature || !$business->plan) {
            return false;
        }

        return $business->plan
            ->planFeatures()
            ->where('feature_id', $feature->id)
            ->where('is_enabled', true)
            ->exists();
    }

    public function getEffectiveQuota(Business $business, string $featureKey, ?Carbon $periodStart = null): ?int
    {
        $feature = Feature::where('key', $featureKey)->first();

        if (!$feature || $feature->type !== 'quota' || !$business->plan) {
            return null;
        }

        $planFeature = $business->plan
            ->planFeatures()
            ->where('feature_id', $feature->id)
            ->where('is_enabled', true)
            ->first();

        if (!$planFeature) {
            return null;
        }

        if ($planFeature->is_unlimited) {
            return -1;
        }

        $baseQuota = $planFeature->quota ?? 0;

        $periodStart = $periodStart ? $periodStart->toDateString() : now()->startOfMonth()->toDateString();

        $addons = $business->featureAddons()
            ->where(function ($query) use ($periodStart) {
                $query->whereNull('period_start')
                    ->orWhere('period_start', $periodStart);
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
    }

    public function getUsage(Business $business, string $featureKey, ?Carbon $periodStart = null): int
    {
        $feature = Feature::where('key', $featureKey)->first();

        if (!$feature) {
            return 0;
        }

        $periodStart = $periodStart ? $periodStart->toDateString() : now()->startOfMonth()->toDateString();

        $usage = FeatureUsage::firstOrCreate([
            'business_id' => $business->id,
            'feature_id' => $feature->id,
            'period_start' => $periodStart,
        ], [
            'used' => 0,
        ]);

        return (int) $usage->used;
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

        if (!$feature) {
            return false;
        }

        $periodStart = $periodStart ? $periodStart->toDateString() : now()->startOfMonth()->toDateString();

        $usage = FeatureUsage::firstOrCreate([
            'business_id' => $business->id,
            'feature_id' => $feature->id,
            'period_start' => $periodStart,
        ], [
            'used' => 0,
        ]);

        $quota = $this->getEffectiveQuota($business, $featureKey, Carbon::parse($periodStart));

        if ($quota !== null && $quota !== -1 && ($usage->used + $amount) > $quota) {
            return false;
        }

        $usage->increment('used', $amount);

        return true;
    }

    public function getPricingJsonForWebsite(string $currency = 'KES'): array
    {
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

                if (!$feature || !$planFeature->is_enabled) {
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
                'features' => $features,
            ];
        }

        return [
            'currency' => $currency,
            'plans' => $plansArray,
        ];
    }
}
