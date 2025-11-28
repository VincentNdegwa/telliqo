<?php

namespace App\Listeners;

use App\Models\Business;
use App\Models\Plan;
use App\Services\FeatureService;
use Illuminate\Support\Facades\Log;
use Laravel\Paddle\Events\SubscriptionCanceled;
use Laravel\Paddle\Events\SubscriptionCreated;
use Laravel\Paddle\Events\SubscriptionPaused;
use Laravel\Paddle\Events\SubscriptionUpdated;

class PaddleSubscriptionEventListener
{
    public function __construct(
        protected FeatureService $featureService
    ) {}

    public function handleSubscriptionCreated(SubscriptionCreated $event): void
    {
        $business = $this->getBusinessFromEvent($event);
        
        if ($business) {
            Log::info('Paddle subscription created - syncing features', [
                'business_id' => $business->id,
                'subscription_id' => $event->subscription->id,
            ]);
            
            $this->updateBusinessPlan($business, $event->payload);
            
            $this->featureService->syncBusinessFeatures($business);
        }
    }

    /**
     * Handle subscription updated event.
     * This event is fired for: activated, resumed, past_due, trialing, and other status changes.
     */
    public function handleSubscriptionUpdated(SubscriptionUpdated $event): void
    {
        $business = $this->getBusinessFromEvent($event);
        
        if ($business) {
            Log::info('Paddle subscription updated - syncing features', [
                'business_id' => $business->id,
                'subscription_id' => $event->subscription->id,
                'status' => $event->subscription->status,
            ]);
            
            $this->updateBusinessPlan($business, $event->payload);
            
            $this->featureService->syncBusinessFeatures($business);
        }
    }

    public function handleSubscriptionPaused(SubscriptionPaused $event): void
    {
        $business = $this->getBusinessFromEvent($event);
        
        if ($business) {
            Log::info('Paddle subscription paused - syncing features', [
                'business_id' => $business->id,
                'subscription_id' => $event->subscription->id,
            ]);
            
            $this->featureService->syncBusinessFeatures($business);
        }
    }

    public function handleSubscriptionCanceled(SubscriptionCanceled $event): void
    {
        $business = $this->getBusinessFromEvent($event);
        
        if ($business) {
            Log::info('Paddle subscription canceled - syncing features', [
                'business_id' => $business->id,
                'subscription_id' => $event->subscription->id,
            ]);
            
            if (!$business->hasActiveLocalSubscription()) {
                $business->plan_id = null;
                $business->save();
                
                Log::info('Removed plan_id from business after Paddle cancellation', [
                    'business_id' => $business->id,
                ]);
            }
            
            $this->featureService->syncBusinessFeatures($business);
        }
    }

    /**
     * Update business plan based on subscription data
     */
    protected function updateBusinessPlan(Business $business, array $payload): void
    {
        Log::info("Paddle payload data", ['data' => $payload['data'] ?? null]);
        
        $customData = $payload['data']['custom_data'] ?? null;
        
        if (!$customData || !is_array($customData)) {
            Log::warning('No custom_data found in Paddle webhook payload', [
                'business_id' => $business->id,
                'subscription_id' => $payload['data']['id'] ?? null,
            ]);
            return;
        }

        $planId = $customData['plan_id'] ?? null;
        
        if (!$planId) {
            Log::warning('No plan_id found in Paddle webhook custom_data', [
                'business_id' => $business->id,
                'custom_data' => $customData,
            ]);
            return;
        }

        $plan = Plan::find($planId);
        
        if (!$plan) {
            Log::error('Plan not found from Paddle webhook custom_data', [
                'business_id' => $business->id,
                'plan_id' => $planId,
            ]);
            return;
        }

        if ($business->plan_id !== $planId) {
            $business->plan_id = $planId;
            $business->save();
            
            Log::info('Updated business plan from Paddle webhook', [
                'business_id' => $business->id,
                'old_plan_id' => $business->plan_id,
                'new_plan_id' => $planId,
            ]);
        }
    }

    protected function getBusinessFromEvent($event): ?Business
    {
        $subscription = $event->subscription;
        
        if (!$subscription) {
            Log::warning('Paddle event received without subscription');
            return null;
        }

        $billableId = $subscription->billable_id;
        $billableType = $subscription->billable_type;

        if (!$billableId || $billableType !== Business::class) {
            Log::warning('Paddle subscription does not belong to a Business', [
                'billable_id' => $billableId,
                'billable_type' => $billableType,
            ]);
            return null;
        }

        $business = Business::find($billableId);

        if (!$business) {
            Log::error('Business not found for Paddle subscription', [
                'business_id' => $billableId,
                'subscription_id' => $subscription->id,
            ]);
            return null;
        }

        return $business;
    }
}
