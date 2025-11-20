<?php

namespace App\Http\Controllers;

use App\Models\FeatureAddon;
use App\Services\FeatureService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class BillingController extends Controller
{
    public function __construct(
        protected FeatureService $featureService
    ) {}

    public function index(Request $request)
    {
        $business = Auth::user()->getCurrentBusiness();

        // if (! user_can('billing.manage', $business)) {
        //     abort(403, 'You do not have permission to access billing.');
        // }

        $plan = $business->plan;

        $planData = null;
        $featureSummary = [];

        if ($plan) {
            $plan->load(['planFeatures.feature']);

            foreach ($plan->planFeatures as $planFeature) {
                $feature = $planFeature->feature;

                if (! $feature) {
                    continue;
                }

                $featureSummary[] = [
                    'key' => $feature->key,
                    'name' => $feature->name,
                    'category' => $feature->category,
                    'type' => $feature->type,
                    'description' => $feature->public_description ?? $feature->description,
                    'is_enabled' => (bool) $planFeature->is_enabled,
                    'is_unlimited' => (bool) $planFeature->is_unlimited,
                    'quota' => $planFeature->quota,
                    'unit' => $planFeature->unit ?? $feature->default_unit,
                ];
            }

            $planData = [
                'id' => $plan->id,
                'key' => $plan->key,
                'name' => $plan->name,
                'description' => $plan->description,
                'price_kes' => (float) $plan->price_kes,
                'price_usd' => (float) $plan->price_usd,
                'price_kes_yearly' => (float) $plan->price_kes_yearly,
                'price_usd_yearly' => (float) $plan->price_usd_yearly,
                'features' => $featureSummary,
            ];
        }

        $addons = FeatureAddon::with('feature')
            ->where('is_active', true)
            ->orderBy('feature_id')
            ->orderBy('sort_order')
            ->get()
            ->map(function (FeatureAddon $addon) {
                return [
                    'id' => $addon->id,
                    'feature_key' => $addon->feature?->key,
                    'feature_name' => $addon->feature?->name,
                    'name' => $addon->name,
                    'increment_quota' => $addon->increment_quota,
                    'price_kes' => (float) $addon->price_kes,
                    'price_usd' => (float) $addon->price_usd,
                ];
            });

        $usage = [];

        if ($plan) {
            foreach ($featureSummary as $feature) {
                if ($feature['type'] !== 'quota' || ! $feature['is_enabled']) {
                    continue;
                }

                $remaining = $this->featureService->remainingQuota($business, $feature['key']);
                $used = $this->featureService->getUsage($business, $feature['key']);

                $usage[] = [
                    'key' => $feature['key'],
                    'name' => $feature['name'],
                    'unit' => $feature['unit'],
                    'used' => $used,
                    'quota' => $feature['is_unlimited'] ? -1 : $feature['quota'],
                    'remaining' => $remaining,
                    'is_unlimited' => $feature['is_unlimited'],
                ];
            }
        }

        return Inertia::render('Billing/Index', [
            'plan' => $planData,
            'addons' => $addons,
            'usage' => $usage,
        ]);
    }

    public function requestAddon(Request $request)
    {
        $business = Auth::user()->getCurrentBusiness();

        if (! $business) {
            return redirect()->route('onboarding.show');
        }

        if (! user_can('billing.manage', $business)) {
            abort(403, 'You do not have permission to request addons.');
        }

        $validated = $request->validate([
            'addon_id' => ['required', 'integer', 'exists:feature_addons,id'],
        ]);

        // For now we just log the request and flash a message.
        \Log::info('Addon request', [
            'business_id' => $business->id,
            'addon_id' => $validated['addon_id'],
        ]);

        return back()->with('success', 'Your addon request has been received. Our team will contact you soon.');
    }
}
