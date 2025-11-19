<?php

namespace Tests\Feature\ReviewRequests;

use App\Jobs\SendReviewRequestEmail;
use App\Mail\ReviewRequestEmail;
use App\Models\Business;
use App\Models\Customer;
use App\Models\ReviewRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ReviewRequestManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Business $business;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed permissions and roles
        $this->seed(\Database\Seeders\LaratrustSeeder::class);

        $this->user = User::factory()->create();
        $this->business = Business::factory()->create([
            'onboarding_completed_at' => now(),
        ]);
        
        $this->business->users()->attach($this->user->id, [
            'role' => 'owner',
            'is_active' => true,
            'joined_at' => now(),
        ]);

        // Assign owner role via Laratrust
        $ownerRole = \App\Models\Role::where('name', 'owner')->first();
        if ($ownerRole) {
            $this->user->addRole($ownerRole, $this->business);
        }
    }

    public function test_authenticated_user_can_view_review_requests(): void
    {
        $customer = Customer::factory()->create(['business_id' => $this->business->id]);
        
        ReviewRequest::factory()->count(5)->create([
            'business_id' => $this->business->id,
            'customer_id' => $customer->id,
        ]);

        $response = $this->actingAs($this->user)->get(route('review-requests.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('ReviewRequests/Index')
                ->has('reviewRequests.data', 5)
        );
    }

    public function test_unauthenticated_user_cannot_view_review_requests(): void
    {
        $response = $this->get(route('review-requests.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_user_can_view_create_review_request_form(): void
    {
        $response = $this->actingAs($this->user)->get(route('review-requests.create'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('ReviewRequests/Create')
                ->has('customers')
        );
    }

    public function test_user_can_create_review_request(): void
    {
        Queue::fake();
        $customer = Customer::factory()->create(['business_id' => $this->business->id]);

        $requestData = [
            'customer_id' => $customer->id,
            'subject' => 'We value your feedback',
            'message' => 'Please share your experience with us.',
            'send_mode' => 'now',
        ];

        $response = $this->actingAs($this->user)
            ->post(route('review-requests.store'), $requestData);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('review_requests', [
            'business_id' => $this->business->id,
            'customer_id' => $customer->id,
            'subject' => 'We value your feedback',
        ]);
    }

    public function test_review_request_sends_email_when_send_mode_is_immediate(): void
    {
        Queue::fake();
        $customer = Customer::factory()->create(['business_id' => $this->business->id]);

        $requestData = [
            'customer_id' => $customer->id,
            'subject' => 'We value your feedback',
            'message' => 'Please share your experience with us.',
            'send_mode' => 'now',
        ];

        $this->actingAs($this->user)
            ->post(route('review-requests.store'), $requestData);

        Queue::assertPushed(SendReviewRequestEmail::class);
    }

    public function test_review_request_does_not_send_email_when_send_mode_is_manual(): void
    {
        Queue::fake();
        $customer = Customer::factory()->create(['business_id' => $this->business->id]);

        $requestData = [
            'customer_id' => $customer->id,
            'subject' => 'We value your feedback',
            'message' => 'Please share your experience with us.',
            'send_mode' => 'manual',
        ];

        $this->actingAs($this->user)
            ->post(route('review-requests.store'), $requestData);

        Queue::assertNotPushed(SendReviewRequestEmail::class);
    }

    public function test_review_request_creation_requires_valid_customer(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('review-requests.store'), [
                'customer_id' => 9999,
                'subject' => 'Test',
                'message' => 'Test message',
                'send_mode' => 'manual',
            ]);

        $response->assertSessionHasErrors('customer_id');
    }

    public function test_user_can_filter_review_requests_by_status(): void
    {
        $customer = Customer::factory()->create(['business_id' => $this->business->id]);
        
        ReviewRequest::factory()->create([
            'business_id' => $this->business->id,
            'customer_id' => $customer->id,
            'status' => 'pending',
        ]);

        ReviewRequest::factory()->create([
            'business_id' => $this->business->id,
            'customer_id' => $customer->id,
            'status' => 'completed',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('review-requests.index', ['status' => 'pending']));

        $response->assertStatus(200);
    }

    public function test_user_can_search_review_requests_by_customer(): void
    {
        $searchCustomer = Customer::factory()->create([
            'business_id' => $this->business->id,
            'name' => 'John Smith',
            'email' => 'john@example.com',
        ]);

        ReviewRequest::factory()->create([
            'business_id' => $this->business->id,
            'customer_id' => $searchCustomer->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('review-requests.index', ['search' => 'John']));

        $response->assertStatus(200);
    }

    public function test_user_can_send_reminder_for_review_request(): void
    {
        Queue::fake();
        Mail::fake();
        $customer = Customer::factory()->create(['business_id' => $this->business->id]);

        $reviewRequest = ReviewRequest::factory()->create([
            'business_id' => $this->business->id,
            'customer_id' => $customer->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('review-requests.remind', $reviewRequest));
        
        Mail::assertSent(ReviewRequestEmail::class);

        $response->assertRedirect();
        $response->assertSessionHas('success');

    }

    public function test_guest_can_view_review_request_via_token(): void
    {
        $customer = Customer::factory()->create(['business_id' => $this->business->id]);
        
        $reviewRequest = ReviewRequest::factory()->create([
            'business_id' => $this->business->id,
            'customer_id' => $customer->id,
            'status' => 'pending',
        ]);

        $response = $this->get(route('review-request.show', $reviewRequest->unique_token));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('ReviewRequest/Submit')
                ->has('reviewRequest')
        );
    }

    public function test_guest_can_submit_review_via_token(): void
    {
        $customer = Customer::factory()->create(['business_id' => $this->business->id]);
        
        $reviewRequest = ReviewRequest::factory()->create([
            'business_id' => $this->business->id,
            'customer_id' => $customer->id,
            'status' => 'pending',
        ]);

        $response = $this->post(route('review-request.submit', $reviewRequest->unique_token), [
            'rating' => 5,
            'comment' => 'Excellent service!',
        ]);

        $reviewRequest->refresh();
        $this->assertEquals('completed', $reviewRequest->status);
    }

    public function test_guest_can_opt_out_of_review_request(): void
    {
        $customer = Customer::factory()->create(['business_id' => $this->business->id]);
        
        $reviewRequest = ReviewRequest::factory()->create([
            'business_id' => $this->business->id,
            'customer_id' => $customer->id,
        ]);

        $response = $this->get(route('review-request.opt-out', $reviewRequest->unique_token));

        $response->assertStatus(200);

        $customer->refresh();
        $this->assertTrue($customer->opted_out);
    }

    public function test_review_request_stats_are_calculated_correctly(): void
    {
        $customer = Customer::factory()->create(['business_id' => $this->business->id]);
        
        ReviewRequest::factory()->create([
            'business_id' => $this->business->id,
            'customer_id' => $customer->id,
            'status' => 'pending',
        ]);

        ReviewRequest::factory()->create([
            'business_id' => $this->business->id,
            'customer_id' => $customer->id,
            'status' => 'completed',
        ]);

        $response = $this->actingAs($this->user)->get(route('review-requests.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->where('stats.total', 2)
                ->where('stats.pending', 1)
                ->where('stats.completed', 1)
        );
    }
}
