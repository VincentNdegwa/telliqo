<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Plan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
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

        Log::info('Creating subscription checkout for business: '.$business->id.' and price: '.$priceId.' and period: '.$periodKey);

        try {
            $type = $options['type'] ?? 'default';

            $checkout = $business->subscribe($priceId, $type);

            $verificationToken = Str::random(64);
            
            Cache::put(
                'paddle_payment_token:' . $verificationToken,
                [
                    'business_id' => $business->id,
                    'plan_id' => $plan->id,
                    'billing_period' => $periodKey,
                    'currency' => $options['currency'] ?? null,
                    'created_at' => now()->toIso8601String(),
                    'used' => false,
                ],
                now()->addMinutes(30)
            );

            $checkout->customData([
                'business_id' => $business->id,
                'plan_id' => $plan->id,
                'billing_period' => $periodKey,
                'currency' => $options['currency'] ?? null,
                'verification_token' => $verificationToken,
            ]);

            $checkout->returnTo(route('billing.index', ['payment_token' => $verificationToken]));

            return $checkout;
        } catch (\Throwable $e) {
            Log::error('Failed to create Paddle subscription checkout via Cashier', [
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

    public function verifyPaymentToken(string $token): ?array
    {
        $cacheKey = 'paddle_payment_token:' . $token;
        
        $data = Cache::get($cacheKey);
        
        if (!$data || !is_array($data)) {
            Log::warning('Payment token verification failed: token not found or invalid', [
                'token' => substr($token, 0, 8) . '...', // Log partial token for debugging
            ]);
            return null;
        }
        
        if ($data['used'] ?? false) {
            Log::warning('Payment token verification failed: token already used', [
                'token' => substr($token, 0, 8) . '...',
            ]);
            return null;
        }
        
        Cache::forget($cacheKey);
        
        Log::info('Payment token verified and consumed', [
            'token' => substr($token, 0, 8) . '...',
            'business_id' => $data['business_id'] ?? null,
            'plan_id' => $data['plan_id'] ?? null,
        ]);
        
        return $data;
    }
}
