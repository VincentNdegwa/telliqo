<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Plan;
use Laravel\Paddle\Checkout;

class PaddleService
{
    public function createSubscriptionCheckout(Business $business, Plan $plan, string $billingPeriod, array $options = []): Checkout
    {
        $periodKey = $billingPeriod === 'yearly' ? 'yearly' : 'monthly';

        $priceId = $periodKey === 'yearly'
            ? $plan->paddle_plan_id_yearly
            : $plan->paddle_plan_id_monthly;

        if (! $priceId) {
            throw new \RuntimeException('Missing Paddle price id on plan for period: '.$periodKey);
        }

        \Log::info('Creating subscription checkout for business: '.$business->id.' and price: '.$priceId.' and period: '.$periodKey);

        try {
            $type = $options['type'] ?? 'default';

            $checkout = $business->subscribe($priceId, $type);

            $checkout->customData([
                'business_id' => $business->id,
                'plan_id' => $plan->id,
                'billing_period' => $periodKey,
                'currency' => $options['currency'] ?? null,
            ]);

            $checkout->returnTo(route('billing.index'));

            return $checkout;
        } catch (\Throwable $e) {
            \Log::error('Failed to create Paddle subscription checkout via Cashier', [
                'business_id' => $business->id,
                'plan_id' => $plan->id,
                'price_id' => $priceId,
                'error' => $e->getMessage(),
            ]);

            throw new \RuntimeException(
                'Failed to create Paddle subscription checkout: '.$e->getMessage(),
                0,
                $e
            );
        }
    }
}
