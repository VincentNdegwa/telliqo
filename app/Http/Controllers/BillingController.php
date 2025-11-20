<?php

namespace App\Http\Controllers;

use App\Models\FeatureAddon;
use App\Models\LocalSubscription;
use App\Models\Plan;
use App\Services\FeatureService;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class BillingController extends Controller
{
    public function __construct(
        protected FeatureService $featureService,
        protected SubscriptionService $subscriptionService,
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

        $availablePlans = Plan::with(['planFeatures.feature'])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(function (Plan $plan) use ($business) {
                $featureSummary = [];

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

                return [
                    'id' => $plan->id,
                    'key' => $plan->key,
                    'name' => $plan->name,
                    'description' => $plan->description,
                    'price_kes' => (float) $plan->price_kes,
                    'price_kes_yearly' => (float) $plan->price_kes_yearly,
                    'is_current' => $business->plan_id === $plan->id,
                    'features' => $featureSummary,
                ];
            });

        $localSubscriptions = $this->subscriptionService
            ->getLocalSubscriptions($business)
            ->map(function ($subscription) {
                return [
                    'id' => $subscription->id,
                    'provider' => $subscription->provider,
                    'status' => $subscription->status,
                    'billing_period' => $subscription->billing_period,
                    'amount' => $subscription->amount !== null ? (float) $subscription->amount : null,
                    'currency' => $subscription->currency,
                    'starts_at' => optional($subscription->starts_at)->toIso8601String(),
                    'trial_ends_at' => optional($subscription->trial_ends_at)->toIso8601String(),
                    'ends_at' => optional($subscription->ends_at)->toIso8601String(),
                ];
            });

        $localTransactions = $this->subscriptionService
            ->getLocalTransactions($business)
            ->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'local_subscription_id' => $transaction->local_subscription_id,
                    'provider' => $transaction->provider,
                    'status' => $transaction->status,
                    'amount' => $transaction->amount !== null ? (float) $transaction->amount : null,
                    'currency' => $transaction->currency,
                    'paid_at' => optional($transaction->paid_at)->toIso8601String(),
                ];
            });

        $currentLocalSubscription = $this->subscriptionService
            ->getCurrentLocalSubscription($business);

        $currentSubscription = null;

        if ($currentLocalSubscription) {
            $currentSubscription = [
                'id' => $currentLocalSubscription->id,
                'plan_name' => optional($currentLocalSubscription->plan)->name,
                'provider' => $currentLocalSubscription->provider,
                'status' => $currentLocalSubscription->status,
                'billing_period' => $currentLocalSubscription->billing_period,
                'amount' => $currentLocalSubscription->amount !== null ? (float) $currentLocalSubscription->amount : null,
                'currency' => $currentLocalSubscription->currency,
                'starts_at' => optional($currentLocalSubscription->starts_at)->toIso8601String(),
                'ends_at' => optional($currentLocalSubscription->ends_at)->toIso8601String(),
            ];
        }

        return Inertia::render('Billing/Index', [
            'plan' => $planData,
            'addons' => $addons,
            'usage' => $usage,
            'localSubscriptions' => $localSubscriptions,
            'localTransactions' => $localTransactions,
            'availablePlans' => $availablePlans,
            'currentSubscription' => $currentSubscription,
            'hasAnyActiveSubscription' => $business->hasAnyActiveSubscription(),
        ]);
    }

    public function createLocalSubscription(Request $request)
    {
        $business = Auth::user()->getCurrentBusiness();

        if (! $business) {
            return redirect()->route('onboarding.show');
        }

        $validated = $request->validate([
            'plan_id' => ['nullable', 'integer', 'exists:plans,id'],
            'provider' => ['required', 'string', 'max:50'],
            'billing_period' => ['nullable', 'string', 'max:20'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:3'],
            'external_id' => ['nullable', 'string', 'max:191'],
        ]);

        $plan = null;

        if (! empty($validated['plan_id'])) {
            $plan = Plan::find($validated['plan_id']);
        }

        $this->subscriptionService->createLocalSubscription($business, $plan, [
            'provider' => $validated['provider'],
            'billing_period' => $validated['billing_period'] ?? null,
            'amount' => $validated['amount'] ?? null,
            'currency' => $validated['currency'] ?? null,
            'external_id' => $validated['external_id'] ?? null,
        ]);

        return back()->with('success', 'Local subscription has been recorded.');
    }

    public function cancelLocalSubscription(LocalSubscription $subscription)
    {
        $business = Auth::user()->getCurrentBusiness();

        if (! $business) {
            return redirect()->route('onboarding.show');
        }

        if ($subscription->business_id !== $business->id) {
            abort(403);
        }

        $this->subscriptionService->cancelLocalSubscription($subscription, $business);

        return back()->with('success', 'Local subscription has been canceled.');
    }

    public function startPaddleSubscription(Request $request)
    {
        $business = Auth::user()->getCurrentBusiness();

        if (! $business) {
            return redirect()->route('onboarding.show');
        }

        $validated = $request->validate([
            'plan_id' => ['nullable', 'integer', 'exists:plans,id'],
        ]);

        $plan = null;

        if (! empty($validated['plan_id'])) {
            $plan = Plan::find($validated['plan_id']);
        }

        try {
            $checkoutUrl = $this->subscriptionService->startPaddleSubscription($business, $plan, []);
        } catch (\LogicException $e) {
            return back()->with('error', 'Paddle subscription integration is not configured yet.');
        }

        if ($checkoutUrl) {
            return redirect()->away($checkoutUrl);
        }

        return back()->with('error', 'Unable to start Paddle subscription.');
    }

    public function startPaypalSubscription(Request $request)
    {
        $business = Auth::user()->getCurrentBusiness();

        if (! $business) {
            return redirect()->route('onboarding.show');
        }

        $validated = $request->validate([
            'plan_id' => ['nullable', 'integer', 'exists:plans,id'],
        ]);

        $plan = null;

        if (! empty($validated['plan_id'])) {
            $plan = Plan::find($validated['plan_id']);
        }

        try {
            $checkoutUrl = $this->subscriptionService->startPaypalSubscription($business, $plan, []);
        } catch (\LogicException $e) {
            return back()->with('error', 'PayPal subscription integration is not configured yet.');
        }

        if ($checkoutUrl) {
            return redirect()->away($checkoutUrl);
        }

        return back()->with('error', 'Unable to start PayPal subscription.');
    }

    public function requestAddon(Request $request)
    {
        $business = Auth::user()->getCurrentBusiness();

        if (! $business) {
            return redirect()->route('onboarding.show');
        }

        // if (! user_can('billing.manage', $business)) {
        //     abort(403, 'You do not have permission to request addons.');
        // }

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
