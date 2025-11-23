<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Plan;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class PaypalService
{
    /**
     * @var Client|null
     */
    protected $client = null;

    protected $clientId;
    protected $clientSecret;
    protected $apiBaseUrl;

    protected function getClient(): Client
    {
        if ($this->client) {
            return $this->client;
        }

        $mode = env('PAYPAL_MODE', 'sandbox');

        if ($mode === 'sandbox') {
            $this->clientId = env('PAYPAL_SANDBOX_CLIENT_ID', env('PAYPAL_CLIENT_ID'));
            $this->clientSecret = env('PAYPAL_SANDBOX_CLIENT_SECRET', env('PAYPAL_CLIENT_SECRET'));
            $this->apiBaseUrl = 'https://api-m.sandbox.paypal.com';
        } else {
            $this->clientId = env('PAYPAL_CLIENT_ID');
            $this->clientSecret = env('PAYPAL_CLIENT_SECRET');
            $this->apiBaseUrl = 'https://api-m.paypal.com';
        }

        $this->client = new Client([
            'base_uri' => $this->apiBaseUrl,
            'timeout' => 30,
        ]);

        return $this->client;
    }

    protected function getAccessToken(): string
    {
        $client = $this->getClient();

        try {
            $response = $client->request('POST', '/v1/oauth2/token', [
                'auth' => [$this->clientId, $this->clientSecret],
                'form_params' => [
                    'grant_type' => 'client_credentials',
                ],
            ]);

            $body = json_decode((string) $response->getBody(), true);

            if (! is_array($body) || empty($body['access_token'])) {
                throw new Exception('PayPal access token missing in response');
            }

            return $body['access_token'];
        } catch (RequestException $e) {
            Log::error('PayPal getAccessToken error', [
                'message' => $e->getMessage(),
                'response' => $e->hasResponse() ? (string) $e->getResponse()->getBody() : null,
            ]);

            throw new Exception('Unable to get PayPal access token');
        }
    }

    public function createSubscriptionOrder(Business $business, Plan $plan, string $billingPeriod, string $currencyCode, array $options = []): ?string
    {
        try {
            $amountValue = $this->resolvePlanAmount($plan, $billingPeriod, $currencyCode);

            Log::info("Amount: {$amountValue}");

            if ($amountValue <= 0) {
                throw new Exception('Invalid amount for plan');
            }

            $accessToken = $this->getAccessToken();
            $client = $this->getClient();

            $referenceId = sprintf(
                'business:%d;plan:%d;period:%s;currency:%s',
                $business->id,
                $plan->id,
                $billingPeriod,
                $currencyCode
            );

            $body = [
                'intent' => 'CAPTURE',
                'application_context' => [
                    'brand_name' => config('app.name', 'Telliqo'),
                    'return_url' => route('billing.subscriptions.paypal.callback'),
                    'cancel_url' => route('billing.subscriptions.paypal.cancel'),
                    'user_action' => 'PAY_NOW',
                    'shipping_preference' => 'NO_SHIPPING',
                ],
                'purchase_units' => [[
                    'reference_id' => $referenceId,
                    'amount' => [
                        'currency_code' => $currencyCode,
                        'value' => number_format($amountValue, 2, '.', ''),
                    ],
                ]],
            ];

            Log::info('PayPal create order request', [
                'body' => $body,
            ]);

            $response = $client->request('POST', '/v2/checkout/orders', [
                'headers' => [
                    'Authorization' => 'Bearer '.$accessToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => $body,
            ]);

            $orderData = json_decode((string) $response->getBody(), true);

            Log::info('PayPal create order response', [
                'order' => $orderData,
            ]);

            if (! is_array($orderData) || empty($orderData['links'])) {
                throw new Exception('Unexpected PayPal create order response');
            }

            foreach ($orderData['links'] as $link) {
                if (($link['rel'] ?? null) === 'approve' && ! empty($link['href'])) {
                    Log::info('Found PayPal approval URL', ['url' => $link['href']]);
                    return $link['href'];
                }
            }

            throw new Exception('No approval URL found in PayPal response');
        } catch (RequestException $e) {
            Log::error('PayPal order creation failed (HTTP)', [
                'message' => $e->getMessage(),
                'response' => $e->hasResponse() ? (string) $e->getResponse()->getBody() : null,
            ]);

            throw new Exception('PayPal order creation failed');
        } catch (\Throwable $e) {
            Log::error('PayPal order creation failed', [
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function captureOrder(string $orderId): ?array
    {
        try {
            $accessToken = $this->getAccessToken();
            $client = $this->getClient();

            $response = $client->request('POST', "/v2/checkout/orders/{$orderId}/capture", [
                'headers' => [
                    'Authorization' => 'Bearer '.$accessToken,
                    'Content-Type' => 'application/json',
                ],
            ]);

            $orderData = json_decode((string) $response->getBody(), true);

            Log::info('PayPal capture order response', [
                'order' => $orderData,
            ]);

            return $orderData;
        } catch (RequestException $e) {
            Log::error('PayPal capture order failed (HTTP)', [
                'message' => $e->getMessage(),
                'response' => $e->hasResponse() ? (string) $e->getResponse()->getBody() : null,
            ]);

            return null;
        } catch (\Throwable $e) {
            Log::error('PayPal capture order failed', [
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function reviseSubscriptionPlan(string $subscriptionId, string $planId, array $options = []): array
    {
        try {
            $accessToken = $this->getAccessToken();
            $client = $this->getClient();

            $applicationContext = [
                'brand_name' => config('app.name', 'Telliqo'),
                'return_url' => route('billing.subscriptions.paypal.callback'),
                'cancel_url' => route('billing.subscriptions.paypal.cancel'),
            ];

            if (! empty($options['application_context']) && is_array($options['application_context'])) {
                $applicationContext = array_merge($applicationContext, $options['application_context']);
            }

            $body = [
                'plan_id' => $planId,
                'application_context' => $applicationContext,
            ];

            Log::info('PayPal revise subscription request', [
                'subscription_id' => $subscriptionId,
                'body' => $body,
            ]);

            $response = $client->request('POST', '/v1/billing/subscriptions/'.$subscriptionId.'/revise', [
                'headers' => [
                    'Authorization' => 'Bearer '.$accessToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => $body,
            ]);

            $subscriptionData = json_decode((string) $response->getBody(), true);

            Log::info('PayPal revise subscription response', [
                'subscription_id' => $subscriptionId,
                'subscription' => $subscriptionData,
            ]);

            $approvalUrl = null;

            if (is_array($subscriptionData) && ! empty($subscriptionData['links'])) {
                foreach ($subscriptionData['links'] as $link) {
                    if (($link['rel'] ?? null) === 'approve' && ! empty($link['href'])) {
                        $approvalUrl = $link['href'];
                        break;
                    }
                }
            }

            return [
                'id' => $subscriptionData['id'] ?? $subscriptionId,
                'status' => $subscriptionData['status'] ?? null,
                'approval_url' => $approvalUrl,
                'raw' => $subscriptionData,
            ];
        } catch (RequestException $e) {
            Log::error('PayPal revise subscription failed (HTTP)', [
                'subscription_id' => $subscriptionId,
                'message' => $e->getMessage(),
                'response' => $e->hasResponse() ? (string) $e->getResponse()->getBody() : null,
            ]);

            throw new Exception('PayPal subscription revision failed');
        } catch (\Throwable $e) {
            Log::error('PayPal revise subscription failed', [
                'subscription_id' => $subscriptionId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function syncPaypalBillingPlanForLocalPlan(Plan $plan, string $billingPeriod, string $currencyCode = 'USD'): array
    {
        $billingPeriod = $billingPeriod === 'yearly' ? 'yearly' : 'monthly';
        $currencyCode = strtoupper($currencyCode);

        $accessToken = $this->getAccessToken();
        $client = $this->getClient();

        $existingId = $billingPeriod === 'yearly'
            ? $plan->paypal_plan_id_yearly
            : $plan->paypal_plan_id_monthly;

        if ($existingId) {
            try {
                $response = $client->request('GET', '/v1/billing/plans/'.$existingId, [
                    'headers' => [
                        'Authorization' => 'Bearer '.$accessToken,
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ],
                ]);

                $planData = json_decode((string) $response->getBody(), true);

                Log::info('PayPal existing billing plan fetched', [
                    'plan_id' => $existingId,
                    'plan' => $planData,
                ]);

                return [
                    'id' => $existingId,
                    'status' => $planData['status'] ?? null,
                    'raw' => $planData,
                    'created' => false,
                ];
            } catch (RequestException $e) {
                Log::warning('Failed to fetch existing PayPal billing plan, will create new', [
                    'plan_id' => $existingId,
                    'message' => $e->getMessage(),
                ]);
                // fall through to create
            }
        }

        $productId = $this->getOrCreatePaypalProductId();

        $amountValue = $this->resolvePlanAmount($plan, $billingPeriod, $currencyCode);
        Log::info("Amount for plan {$plan->name} ({$billingPeriod}) is {$amountValue}");

        if ($amountValue <= 0) {
            throw new Exception('Invalid amount for PayPal billing plan');
        }

        $intervalUnit = $billingPeriod === 'yearly' ? 'YEAR' : 'MONTH';

        $body = [
            'product_id' => $productId,
            'name' => $plan->name.' ('.strtoupper($billingPeriod).')',
            'description' => $plan->description ?? $plan->name,
            'status' => 'ACTIVE',
            'billing_cycles' => [
                [
                    'frequency' => [
                        'interval_unit' => $intervalUnit,
                        'interval_count' => 1,
                    ],
                    'tenure_type' => 'REGULAR',
                    'sequence' => 1,
                    'total_cycles' => 0, // 0 for infinite billing cycles
                    'pricing_scheme' => [
                        'fixed_price' => [
                            'value' => number_format($amountValue, 2, '.', ''),
                            'currency_code' => $currencyCode,
                        ],
                    ],
                ],
            ],
            'payment_preferences' => [
                'auto_bill_outstanding' => true,
                'setup_fee' => [
                    'value' => '0',
                    'currency_code' => $currencyCode,
                ],
                'setup_fee_failure_action' => 'CONTINUE',
                'payment_failure_threshold' => 3,
            ],
        ];

        Log::info('PayPal create billing plan request', [
            'body' => $body,
        ]);

        try {
            $response = $client->request('POST', '/v1/billing/plans', [
                'headers' => [
                    'Authorization' => 'Bearer '.$accessToken,
                    'Content-Type' => 'application/json',
                    'Prefer' => 'return=representation',
                ],
                'json' => $body,
            ]);

            $planData = json_decode((string) $response->getBody(), true);

            Log::info('PayPal create billing plan response', [
                'plan' => $planData,
            ]);

            $remoteId = $planData['id'] ?? null;

            if (! $remoteId) {
                throw new Exception('PayPal billing plan id missing in response');
            }

            if ($billingPeriod === 'yearly') {
                $plan->paypal_plan_id_yearly = $remoteId;
            } else {
                $plan->paypal_plan_id_monthly = $remoteId;
            }

            $plan->save();

            return [
                'id' => $remoteId,
                'status' => $planData['status'] ?? null,
                'raw' => $planData,
                'created' => true,
            ];
        } catch (RequestException $e) {
            Log::error('PayPal create billing plan failed (HTTP)', [
                'message' => $e->getMessage(),
                'response' => $e->hasResponse() ? (string) $e->getResponse()->getBody() : null,
            ]);

            throw new Exception('PayPal billing plan creation failed');
        }
    }

    protected function getOrCreatePaypalProductId(): string
    {

        $accessToken = $this->getAccessToken();
        $client = $this->getClient();

        $productName = config('app.name', 'Telliqo');

        // Try to find existing product by name
        try {
            $response = $client->request('GET', '/v1/catalogs/products', [
                'headers' => [
                    'Authorization' => 'Bearer '.$accessToken,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'query' => [
                    'page_size' => 20,
                    'total_required' => 'false',
                ],
            ]);

            $data = json_decode((string) $response->getBody(), true);

            foreach ($data['products'] ?? [] as $product) {
                if (($product['name'] ?? null) === $productName && ! empty($product['id'])) {
                    Log::info('Using existing PayPal product', [
                        'product_id' => $product['id'],
                        'name' => $productName,
                    ]);

                    return $product['id'];
                }
            }
        } catch (RequestException $e) {
            Log::warning('Failed to list PayPal catalog products', [
                'message' => $e->getMessage(),
                'response' => $e->hasResponse() ? (string) $e->getResponse()->getBody() : null,
            ]);
        }

        // Create a new product if none found
        $body = [
            'name' => $productName,
            'description' => $productName.' subscription',
            'type' => 'SERVICE',
            'category' => 'SOFTWARE',
        ];

        Log::info('PayPal create product request', [
            'body' => $body,
        ]);

        try {
            $response = $client->request('POST', '/v1/catalogs/products', [
                'headers' => [
                    'Authorization' => 'Bearer '.$accessToken,
                    'Content-Type' => 'application/json',
                    'Prefer' => 'return=representation',
                ],
                'json' => $body,
            ]);

            $productData = json_decode((string) $response->getBody(), true);

            Log::info('PayPal create product response', [
                'product' => $productData,
            ]);

            $productId = $productData['id'] ?? null;

            if (! $productId) {
                throw new Exception('PayPal product id missing in response');
            }

            return $productId;
        } catch (RequestException $e) {
            Log::error('PayPal create product failed (HTTP)', [
                'message' => $e->getMessage(),
                'response' => $e->hasResponse() ? (string) $e->getResponse()->getBody() : null,
            ]);

            throw new Exception('PayPal product creation failed');
        }
    }

    public function createBillingSubscription(Business $business, Plan $plan, string $billingPeriod, string $currencyCode, array $options = []): array
    {
        try {
            $accessToken = $this->getAccessToken();
            $client = $this->getClient();

            $periodKey = $billingPeriod === 'yearly' ? 'yearly' : 'monthly';
            $paypalPlanId = $periodKey === 'yearly'
                ? $plan->paypal_plan_id_yearly
                : $plan->paypal_plan_id_monthly;

            if (! $paypalPlanId) {
                throw new Exception('Missing PayPal plan id on plan record for period: '.$periodKey);
            }

            $customId = sprintf(
                'business:%d;plan:%d;period:%s;currency:%s',
                $business->id,
                $plan->id,
                $billingPeriod,
                $currencyCode
            );

            $body = [
                'plan_id' => $paypalPlanId,
                'custom_id' => $customId,
                'application_context' => [
                    'brand_name' => config('app.name', 'Telliqo'),
                    'user_action' => 'SUBSCRIBE_NOW',
                    'return_url' => route('billing.subscriptions.paypal.callback'),
                    'cancel_url' => route('billing.subscriptions.paypal.cancel'),
                ],
            ];

            Log::info('PayPal create subscription request', [
                'body' => $body,
            ]);

            $response = $client->request('POST', '/v1/billing/subscriptions', [
                'headers' => [
                    'Authorization' => 'Bearer '.$accessToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => $body,
            ]);

            $subscriptionData = json_decode((string) $response->getBody(), true);

            Log::info('PayPal create subscription response', [
                'subscription' => $subscriptionData,
            ]);

            if (! is_array($subscriptionData) || empty($subscriptionData['links'])) {
                throw new Exception('Unexpected PayPal subscription response');
            }

            $approvalUrl = null;

            foreach ($subscriptionData['links'] as $link) {
                if (($link['rel'] ?? null) === 'approve' && ! empty($link['href'])) {
                    $approvalUrl = $link['href'];
                    break;
                }
            }

            if (! $approvalUrl) {
                throw new Exception('No approval URL found in PayPal subscription response');
            }

            return [
                'id' => $subscriptionData['id'] ?? null,
                'status' => $subscriptionData['status'] ?? null,
                'approval_url' => $approvalUrl,
                'raw' => $subscriptionData,
            ];
        } catch (RequestException $e) {
            Log::error('PayPal create subscription failed (HTTP)', [
                'message' => $e->getMessage(),
                'response' => $e->hasResponse() ? (string) $e->getResponse()->getBody() : null,
            ]);

            throw new Exception('PayPal subscription creation failed');
        } catch (\Throwable $e) {
            Log::error('PayPal create subscription failed', [
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function getSubscription(string $subscriptionId): ?array
    {
        try {
            $accessToken = $this->getAccessToken();
            $client = $this->getClient();

            $response = $client->request('GET', '/v1/billing/subscriptions/'.$subscriptionId, [
                'headers' => [
                    'Authorization' => 'Bearer '.$accessToken,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
            ]);

            $subscriptionData = json_decode((string) $response->getBody(), true);

            Log::info('PayPal get subscription response', [
                'subscription' => $subscriptionData,
            ]);

            return $subscriptionData;
        } catch (RequestException $e) {
            Log::error('PayPal get subscription failed (HTTP)', [
                'message' => $e->getMessage(),
                'response' => $e->hasResponse() ? (string) $e->getResponse()->getBody() : null,
            ]);

            return null;
        } catch (\Throwable $e) {
            Log::error('PayPal get subscription failed', [
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function suspendSubscription(string $subscriptionId, string $reason): void
    {
        try {
            $accessToken = $this->getAccessToken();
            $client = $this->getClient();

            $response = $client->request('POST', '/v1/billing/subscriptions/'.$subscriptionId.'/suspend', [
                'headers' => [
                    'Authorization' => 'Bearer '.$accessToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'reason' => $reason,
                ],
            ]);

            Log::info('PayPal suspend subscription response', [
                'subscription_id' => $subscriptionId,
                'status_code' => $response->getStatusCode(),
            ]);
        } catch (RequestException $e) {
            Log::error('PayPal suspend subscription failed (HTTP)', [
                'subscription_id' => $subscriptionId,
                'message' => $e->getMessage(),
                'response' => $e->hasResponse() ? (string) $e->getResponse()->getBody() : null,
            ]);

            throw new Exception('PayPal subscription suspension failed');
        }
    }

    public function cancelSubscription(string $subscriptionId, string $reason): void
    {
        try {
            $accessToken = $this->getAccessToken();
            $client = $this->getClient();

            $response = $client->request('POST', '/v1/billing/subscriptions/'.$subscriptionId.'/cancel', [
                'headers' => [
                    'Authorization' => 'Bearer '.$accessToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'reason' => $reason,
                ],
            ]);

            Log::info('PayPal cancel subscription response', [
                'subscription_id' => $subscriptionId,
                'status_code' => $response->getStatusCode(),
            ]);
        } catch (RequestException $e) {
            Log::error('PayPal cancel subscription failed (HTTP)', [
                'subscription_id' => $subscriptionId,
                'message' => $e->getMessage(),
                'response' => $e->hasResponse() ? (string) $e->getResponse()->getBody() : null,
            ]);

            throw new Exception('PayPal subscription cancellation failed');
        }
    }

    public function activateSubscription(string $subscriptionId, string $reason): void
    {
        try {
            $accessToken = $this->getAccessToken();
            $client = $this->getClient();

            $response = $client->request('POST', '/v1/billing/subscriptions/'.$subscriptionId.'/activate', [
                'headers' => [
                    'Authorization' => 'Bearer '.$accessToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'reason' => $reason,
                ],
            ]);

            Log::info('PayPal activate subscription response', [
                'subscription_id' => $subscriptionId,
                'status_code' => $response->getStatusCode(),
            ]);
        } catch (RequestException $e) {
            Log::error('PayPal activate subscription failed (HTTP)', [
                'subscription_id' => $subscriptionId,
                'message' => $e->getMessage(),
                'response' => $e->hasResponse() ? (string) $e->getResponse()->getBody() : null,
            ]);

            throw new Exception('PayPal subscription activation failed');
        }
    }

    protected function resolvePlanAmount(Plan $plan, string $billingPeriod, string $currencyCode): float
    {
        $billingPeriod = $billingPeriod === 'yearly' ? 'yearly' : 'monthly';
        $currencyCode = strtoupper($currencyCode);

        if ($billingPeriod === 'yearly') {
            if ($currencyCode === 'USD') {
                return (float) $plan->price_usd_yearly;
            }

            return (float) $plan->price_kes_yearly;
        }

        if ($currencyCode === 'USD') {
            return (float) $plan->price_usd;
        }

        return (float) $plan->price_kes;
    }
}
