<?php

namespace App\Http\Controllers;

use App\Models\FeatureAddon;
use App\Models\LocalSubscription;
use App\Models\Plan;
use App\Services\FeatureService;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
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
            'effective_when' => ['nullable', 'in:immediately,end_of_cycle'],
            'duration_multiplier' => ['nullable', 'integer', 'min:1'],
            'meta' => ['nullable', 'array'],
        ]);

        $plan = null;

        if (! empty($validated['plan_id'])) {
            $plan = Plan::find($validated['plan_id']);
        }

        $effectiveWhen = $validated['effective_when'] ?? 'immediately';

        if ($effectiveWhen === 'end_of_cycle') {
            $this->subscriptionService->scheduleLocalPlanChange($business, $plan, [
                'provider' => $validated['provider'],
                'billing_period' => $validated['billing_period'] ?? null,
                'currency' => $validated['currency'] ?? null,
                'duration_multiplier' => $validated['duration_multiplier'] ?? 1,
                'meta' => $request->input('meta', []),
            ]);

            return back()->with('success', 'Your plan change has been scheduled for the end of the current billing period.');
        }

        $this->subscriptionService->createLocalSubscription($business, $plan, [
            'provider' => $validated['provider'],
            'billing_period' => $validated['billing_period'] ?? null,
            'amount' => $validated['amount'] ?? null,
            'currency' => $validated['currency'] ?? null,
            'external_id' => $validated['external_id'] ?? null,
            'effective_when' => $effectiveWhen,
            'duration_multiplier' => $validated['duration_multiplier'] ?? 1,
            'meta' => $request->input('meta', []),
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
        try {
            $business = Auth::user()->getCurrentBusiness();

            if (! $business) {
                return response()->json([
                    'success' => false,
                    'message' => 'No business found',
                    'redirect' => route('onboarding.show'),
                ], 403);
            }

            $validated = $request->validate([
                'plan_id' => ['required', 'integer', 'exists:plans,id'],
                'billing_period' => ['required', 'in:monthly,yearly'],
            ]);

            $plan = Plan::findOrFail($validated['plan_id']);

            $result = $this->subscriptionService->startPaddleSubscription($business, $plan, [
                'billing_period' => $validated['billing_period'],
            ]);

            if ($result['error']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                ], 400);
            }

            return response()->json([
                'success' => true,
                'options' => $result['options'],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Paddle subscription error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to start Paddle subscription: '.$e->getMessage(),
            ], 500);
        }
    }

    public function startPaypalSubscription(Request $request)
    {
        try {
            $business = Auth::user()->getCurrentBusiness();

            if (! $business) {
                return response()->json([
                    'success' => false,
                    'message' => 'No business found',
                    'redirect' => route('onboarding.show')
                ], 403);
            }

            $validated = $request->validate([
                'plan_id' => ['required', 'integer', 'exists:plans,id'],
                'billing_period' => ['required', 'in:monthly,yearly'],
            ]);

            $plan = Plan::findOrFail($validated['plan_id']);
            $currency = env('PAYPAL_CURRENCY', 'USD');

            $paypalResult = $this->subscriptionService->startPaypalSubscription($business, $plan, [
                'billing_period' => $validated['billing_period'],
                'currency' => $currency,
            ]);

            if ($paypalResult['error']) {
                return response()->json([
                    'success' => false,
                    'message' => $paypalResult['message']
                ], 400);
            }

            Log::info('Returning PayPal approval URL', ['url' => $paypalResult['url']]);

            return response()->json([
                'success' => true,
                'redirect_url' => $paypalResult['url']
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('PayPal subscription error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to process PayPal subscription: ' . $e->getMessage()
            ], 500);
        }
    }

    public function revisePaypalSubscription(Request $request)
    {
        try {
            $business = Auth::user()->getCurrentBusiness();

            if (! $business) {
                return response()->json([
                    'success' => false,
                    'message' => 'No business found',
                    'redirect' => route('onboarding.show'),
                ], 403);
            }

            $validated = $request->validate([
                'plan_id' => ['required', 'integer', 'exists:plans,id'],
                'billing_period' => ['required', 'in:monthly,yearly'],
            ]);

            $currentSubscription = $this->subscriptionService->getCurrentLocalSubscription($business);

            if (! $currentSubscription || $currentSubscription->provider !== 'paypal') {
                return response()->json([
                    'success' => false,
                    'message' => 'No active PayPal subscription to revise.',
                ], 400);
            }

            $plan = Plan::findOrFail($validated['plan_id']);
            $currency = env('PAYPAL_CURRENCY', 'USD');

            $revisionResult = $this->subscriptionService->revisePaypalSubscriptionPlan($business, $currentSubscription, $plan, [
                'billing_period' => $validated['billing_period'],
                'currency' => $currency,
            ]);

            return response()->json([
                'success' => true,
                'redirect_url' => $revisionResult['url'] ?? null,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        } catch (\Throwable $e) {
            Log::error('PayPal subscription revision error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to revise PayPal subscription: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function handlePaypalCallback(Request $request)
    {
        try {
            DB::beginTransaction();
            $business = Auth::user()->getCurrentBusiness();

            if (! $business) {
                return redirect()->route('onboarding.show');
            }

            $subscriptionId = $request->query('subscription_id');

            if (! $subscriptionId) {
                return redirect()->route('billing.index')->with('error', 'Missing PayPal subscription ID.');
            }

            $paypalSubscription = app(\App\Services\PaypalService::class)->getSubscription($subscriptionId);

            Log::info('PayPal subscription callback result', [
                'subscription_id' => $subscriptionId,
                'subscription' => $paypalSubscription,
            ]);

            if (! is_array($paypalSubscription)) {
                return redirect()->route('billing.index')->with('error', 'Unable to load PayPal subscription details.');
            }

            $status = $paypalSubscription['status'] ?? 'unknown';

            if ($status !== 'ACTIVE' && $status !== 'APPROVAL_PENDING') {
                return redirect()->route('billing.index')->with('error', 'Subscription status is: ' . $status);
            }

            $customId = $paypalSubscription['custom_id'] ?? '';
            $reference = [];

            foreach (explode(';', $customId) as $part) {
                $segments = explode(':', $part, 2);

                if (count($segments) === 2) {
                    $reference[$segments[0]] = $segments[1];
                }
            }

            $businessId = isset($reference['business']) ? (int) $reference['business'] : null;
            $planId = isset($reference['plan']) ? (int) $reference['plan'] : null;
            $billingPeriod = $reference['period'] ?? 'monthly';
            $currencyCode = $reference['currency'] ?? 'USD';

            if (! $businessId || $businessId !== $business->id || ! $planId) {
                return redirect()->route('billing.index')->with('error', 'Subscription reference does not match your current workspace.');
            }

            $plan = Plan::find($planId);

            if (! $plan) {
                return redirect()->route('billing.index')->with('error', 'Plan linked to this subscription no longer exists.');
            }

            $billingPeriodNormalized = $billingPeriod === 'yearly' ? 'yearly' : 'monthly';
            $currencyUpper = strtoupper($currencyCode);

            if ($billingPeriodNormalized === 'yearly') {
                if ($currencyUpper === 'USD') {
                    $amountValue = (float) $plan->price_usd_yearly;
                } else {
                    $amountValue = (float) $plan->price_kes_yearly;
                }
            } else {
                if ($currencyUpper === 'USD') {
                    $amountValue = (float) $plan->price_usd;
                } else {
                    $amountValue = (float) $plan->price_kes;
                }
            }

            $amountCurrency = $currencyUpper;

            $billingInfo = $paypalSubscription['billing_info'] ?? [];
            $lastPayment = $billingInfo['last_payment'] ?? null;
            $paidAt = now();

            if ($lastPayment && ! empty($lastPayment['time'])) {
                $paidAt = \Carbon\Carbon::parse($lastPayment['time']);
            }

            // Check if this is a revision of an existing local PayPal subscription
            $existingLocal = LocalSubscription::where('business_id', $business->id)
                ->where('provider', 'paypal')
                ->where('external_id', $subscriptionId)
                ->first();

            if ($existingLocal && is_array($existingLocal->meta ?? null) && isset($existingLocal->meta['pending_revision'])) {
                $pending = $existingLocal->meta['pending_revision'];
                $newPlanId = $pending['new_plan_id'] ?? null;
                $newBillingPeriod = $pending['billing_period'] ?? $billingPeriodNormalized;
                $newCurrency = $pending['currency'] ?? $amountCurrency;

                $newPlan = $newPlanId ? Plan::find($newPlanId) : null;

                if (! $newPlan) {
                    DB::rollBack();
                    return redirect()->route('billing.index')->with('error', 'Plan linked to this subscription revision no longer exists.');
                }

                $newBillingPeriodNormalized = $newBillingPeriod === 'yearly' ? 'yearly' : 'monthly';
                $newCurrencyUpper = strtoupper($newCurrency);

                if ($newBillingPeriodNormalized === 'yearly') {
                    if ($newCurrencyUpper === 'USD') {
                        $newAmountValue = (float) $newPlan->price_usd_yearly;
                    } else {
                        $newAmountValue = (float) $newPlan->price_kes_yearly;
                    }
                } else {
                    if ($newCurrencyUpper === 'USD') {
                        $newAmountValue = (float) $newPlan->price_usd;
                    } else {
                        $newAmountValue = (float) $newPlan->price_kes;
                    }
                }

                $meta = $existingLocal->meta ?? [];
                $meta['paypal_subscription_id'] = $subscriptionId;
                $meta['paypal_status'] = $status;
                $meta['paypal_plan_id'] = $paypalSubscription['plan_id'] ?? null;
                $meta['payer_email'] = $paypalSubscription['subscriber']['email_address'] ?? null;
                $meta['paypal_billing_info'] = $billingInfo;
                unset($meta['pending_revision']);

                $existingLocal->plan_id = $newPlan->id;
                $existingLocal->billing_period = $newBillingPeriodNormalized;
                $existingLocal->amount = $newAmountValue;
                $existingLocal->currency = $newCurrencyUpper;
                $existingLocal->meta = $meta;
                $existingLocal->status = 'active';
                $existingLocal->save();

                $business->plan()->associate($newPlan);
                $business->save();

                DB::commit();

                return redirect()->route('billing.index')->with('success', 'Subscription plan updated successfully via PayPal. Changes will apply from the next billing period.');
            }

            $durationMultiplier = 1;
            $endsAt = $billingPeriodNormalized === 'yearly'
                ? now()->copy()->addYear()
                : now()->copy()->addMonth();

            $meta = [
                'paypal_subscription_id' => $subscriptionId,
                'paypal_status' => $status,
                'paypal_plan_id' => $paypalSubscription['plan_id'] ?? null,
                'payer_email' => $paypalSubscription['subscriber']['email_address'] ?? null,
                'paypal_billing_info' => $billingInfo,
            ];

            $this->subscriptionService->createLocalSubscription($business, $plan, [
                'provider' => 'paypal',
                'billing_period' => $billingPeriodNormalized,
                'amount' => $amountValue,
                'currency' => $amountCurrency,
                'external_id' => $subscriptionId,
                'effective_when' => 'immediately',
                'duration_multiplier' => $durationMultiplier,
                'ends_at' => $endsAt,
                'transaction_status' => 'COMPLETED',
                'transaction_meta' => $meta,
                'paid_at' => $paidAt,
            ]);
            DB::commit();
            return redirect()->route('billing.index')->with('success', 'Subscription created successfully via PayPal.');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::info("Error creating subscription via PayPal", [
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
            ]);
            return redirect()->route('billing.index')->with('error', 'Failed to create subscription via PayPal.');
        }
    }

    public function handlePaypalCancel()
    {
        return redirect()->route('billing.index')->with('error', 'Payment cancelled by user.');
    }

    public function suspendPaypalSubscription(LocalSubscription $subscription, Request $request)
    {
        $business = Auth::user()->getCurrentBusiness();

        if (! $business) {
            return response()->json([
                'success' => false,
                'message' => 'No business found',
                'redirect' => route('onboarding.show'),
            ], 403);
        }

        if ($subscription->business_id !== $business->id) {
            abort(403);
        }

        $data = $request->validate([
            'reason' => ['required', 'string', 'min:1', 'max:128'],
        ]);

        $this->subscriptionService->suspendPaypalSubscription($subscription, $business, $data['reason']);

        return response()->json([
            'success' => true,
            'message' => 'Subscription suspended successfully.',
        ]);
    }

    public function cancelPaypalSubscription(LocalSubscription $subscription, Request $request)
    {
        $business = Auth::user()->getCurrentBusiness();

        if (! $business) {
            return response()->json([
                'success' => false,
                'message' => 'No business found',
                'redirect' => route('onboarding.show'),
            ], 403);
        }

        if ($subscription->business_id !== $business->id) {
            abort(403);
        }

        $data = $request->validate([
            'reason' => ['required', 'string', 'min:1', 'max:128'],
        ]);

        $this->subscriptionService->cancelPaypalSubscription($subscription, $business, $data['reason']);

        return response()->json([
            'success' => true,
            'message' => 'Subscription canceled successfully.',
        ]);
    }

    public function reactivatePaypalSubscription(LocalSubscription $subscription, Request $request)
    {
        $business = Auth::user()->getCurrentBusiness();

        if (! $business) {
            return response()->json([
                'success' => false,
                'message' => 'No business found',
                'redirect' => route('onboarding.show'),
            ], 403);
        }

        if ($subscription->business_id !== $business->id) {
            abort(403);
        }

        $data = $request->validate([
            'reason' => ['nullable', 'string', 'min:1', 'max:128'],
        ]);

        $reason = $data['reason'] ?? 'Reactivating the subscription';

        $this->subscriptionService->reactivatePaypalSubscription($subscription, $business, $reason);

        return response()->json([
            'success' => true,
            'message' => 'Subscription reactivated successfully.',
        ]);
    }


    public function cancelPaddleSubscription(Request $request)
    {
        try {
            $business = Auth::user()->getCurrentBusiness();

            if (! $business) {
                return response()->json([
                    'success' => false,
                    'message' => 'No business found',
                    'redirect' => route('onboarding.show'),
                ], 403);
            }

            $immediately = (bool) $request->input('immediately', false);

            $this->subscriptionService->cancelPaddleSubscription($business, $immediately);

            return response()->json([
                'success' => true,
                'message' => $immediately
                    ? 'Paddle subscription canceled immediately.'
                    : 'Paddle subscription canceled at end of billing period.',
            ]);
        } catch (\Throwable $e) {
            Log::error('Paddle cancel error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }


    public function pausePaddleSubscription(Request $request)
    {
        try {
            $business = Auth::user()->getCurrentBusiness();

            if (! $business) {
                return response()->json([
                    'success' => false,
                    'message' => 'No business found',
                    'redirect' => route('onboarding.show'),
                ], 403);
            }

            $immediately = (bool) $request->input('immediately', false);

            $this->subscriptionService->pausePaddleSubscription($business, $immediately);

            return response()->json([
                'success' => true,
                'message' => $immediately
                    ? 'Paddle subscription paused immediately.'
                    : 'Paddle subscription will be paused at the end of the billing period.',
            ]);
        } catch (\Throwable $e) {
            Log::error('Paddle pause error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }


    public function resumePaddleSubscription(Request $request)
    {
        try {
            $business = Auth::user()->getCurrentBusiness();

            if (! $business) {
                return response()->json([
                    'success' => false,
                    'message' => 'No business found',
                    'redirect' => route('onboarding.show'),
                ], 403);
            }

            $this->subscriptionService->resumePaddleSubscription($business);

            return response()->json([
                'success' => true,
                'message' => 'Paddle subscription resumed.',
            ]);
        } catch (\Throwable $e) {
            Log::error('Paddle resume error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }


    public function swapPaddleSubscription(Request $request)
    {
        try {
            $business = Auth::user()->getCurrentBusiness();

            if (! $business) {
                return response()->json([
                    'success' => false,
                    'message' => 'No business found',
                    'redirect' => route('onboarding.show'),
                ], 403);
            }

            $validated = $request->validate([
                'plan_id' => ['required', 'integer', 'exists:plans,id'],
                'billing_period' => ['required', 'in:monthly,yearly'],
            ]);

            $plan = Plan::findOrFail($validated['plan_id']);

            $this->subscriptionService->swapPaddleSubscription(
                $business,
                $plan,
                $validated['billing_period']
            );

            return response()->json([
                'success' => true,
                'message' => 'Paddle subscription swapped. New plan will take effect from the next billing period without proration.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Paddle swap error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
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
