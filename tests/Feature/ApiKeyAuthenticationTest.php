<?php

namespace Tests\Feature;

use App\Models\ApiKey;
use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiKeyAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private string $apiKey;

    protected function setUp(): void
    {
        parent::setUp();

        $this->business = Business::factory()->create();

        $result = ApiKey::generate(
            $this->business,
            'Test API Key',
            ['review-requests.read', 'review-requests.create'],
            null
        );

        $this->apiKey = $result['plain_key'];
    }

    public function test_api_request_without_api_key_fails(): void
    {
        $response = $this->getJson('/api/review-requests');

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthorized'
            ]);
    }

    public function test_api_request_with_invalid_api_key_fails(): void
    {
        $response = $this->getJson('/api/review-requests', [
            'X-API-Key' => 'invalid_key'
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Invalid or expired API key'
            ]);
    }

    public function test_api_request_with_valid_api_key_succeeds(): void
    {
        $response = $this->getJson('/api/review-requests', [
            'X-API-Key' => $this->apiKey
        ]);

        $response->assertStatus(200);
    }

    public function test_api_request_with_bearer_token_succeeds(): void
    {
        $response = $this->getJson('/api/review-requests', [
            'Authorization' => 'Bearer ' . $this->apiKey
        ]);

        $response->assertStatus(200);
    }

    public function test_api_request_without_required_permission_fails(): void
    {
        $response = $this->postJson('/api/review-requests', [
            'customer' => [
                'name' => 'Test Customer',
                'email' => 'test@example.com',
            ],
            'subject' => 'Test Subject',
            'message' => 'Test Message',
            'send_mode' => 'manual',
        ], [
            'X-API-Key' => $this->apiKey
        ]);

        $response->assertStatus(201);

        $reviewRequest = $response->json('data');

        $deleteResponse = $this->deleteJson('/api/review-requests/' . $reviewRequest['id'], [], [
            'X-API-Key' => $this->apiKey
        ]);

        $deleteResponse->assertStatus(403)
            ->assertJson([
                'message' => 'API key does not have the required permission: review-requests.delete'
            ]);
    }

    public function test_api_request_with_required_permission_checks_correctly(): void
    {
        $result = ApiKey::generate(
            $this->business,
            'Full Access Key',
            ['review-requests.read', 'review-requests.create', 'review-requests.update', 'review-requests.delete'],
            null
        );

        $response = $this->getJson('/api/review-requests', [
            'X-API-Key' => $result['plain_key']
        ]);

        $response->assertStatus(200);
    }

    public function test_expired_api_key_is_rejected(): void
    {
        $expiredResult = ApiKey::generate(
            $this->business,
            'Expired Key',
            ['review-requests.read'],
            now()->subDay()
        );

        $response = $this->getJson('/api/review-requests', [
            'X-API-Key' => $expiredResult['plain_key']
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Invalid or expired API key'
            ]);
    }

    public function test_revoked_api_key_is_rejected(): void
    {
        $apiKeyModel = ApiKey::where('business_id', $this->business->id)->first();
        $apiKeyModel->revoke();

        $response = $this->getJson('/api/review-requests', [
            'X-API-Key' => $this->apiKey
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Invalid or expired API key'
            ]);
    }

    public function test_api_key_usage_is_recorded(): void
    {
        $apiKeyModel = ApiKey::where('business_id', $this->business->id)->first();
        
        $this->assertNull($apiKeyModel->last_used_at);
        $this->assertNull($apiKeyModel->last_used_ip);

        $this->getJson('/api/review-requests', [
            'X-API-Key' => $this->apiKey
        ]);

        $apiKeyModel->refresh();

        $this->assertNotNull($apiKeyModel->last_used_at);
        $this->assertNotNull($apiKeyModel->last_used_ip);
    }
}
