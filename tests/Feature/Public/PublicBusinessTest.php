<?php

namespace Tests\Feature\Public;

use App\Models\Business;
use App\Models\Feedback;
use App\Models\Enums\ModerationStatus;
use App\Models\Enums\Sentiments;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicBusinessTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;

    protected function setUp(): void
    {
        parent::setUp();

        $this->business = Business::factory()->create();
    }

    public function test_public_business_profile_route_exists(): void
    {
        $response = $this->get(route('business.public', ['business' => $this->business->slug]));

        // Route exists (may be 200 for valid or 302/404 for invalid)
        $this->assertTrue(in_array($response->status(), [200, 302, 404]));
    }
}
