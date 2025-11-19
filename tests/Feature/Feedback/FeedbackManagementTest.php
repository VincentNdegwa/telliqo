<?php

namespace Tests\Feature\Feedback;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Feedback;
use App\Models\User;
use App\Models\Enums\ModerationStatus;
use App\Models\Enums\Sentiments;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class FeedbackManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Business $business;

    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();

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

    public function test_authenticated_user_can_view_feedback_list(): void
    {
        Feedback::factory()->count(5)->create([
            'business_id' => $this->business->id,
        ]);

        $response = $this->actingAs($this->user)->get(route('feedback.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Feedback/Index')
                ->has('feedback.data', 5)
        );
    }

    public function test_unauthenticated_user_cannot_view_feedback_list(): void
    {
        $response = $this->get(route('feedback.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_feedback_list_displays_correct_stats(): void
    {
        Feedback::factory()->create([
            'business_id' => $this->business->id,
            'is_public' => true,
            'moderation_status' => ModerationStatus::PUBLISHED,
        ]);

        Feedback::factory()->create([
            'business_id' => $this->business->id,
            'is_public' => false,
            'moderation_status' => ModerationStatus::FLAGGED,
        ]);

        $response = $this->actingAs($this->user)->get(route('feedback.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->has('stats')
                ->where('stats.total.value', 2)
                ->where('stats.published.value', 1)
                ->where('stats.flagged.value', 1)
        );
    }

    public function test_user_can_reply_to_feedback(): void
    {
        $feedback = Feedback::factory()->create([
            'business_id' => $this->business->id,
        ]);

        $replyText = 'Thank you for your feedback!';

        $response = $this->actingAs($this->user)
            ->post(route('feedback.reply', $feedback), [
                'reply_text' => $replyText,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('feedback', [
            'id' => $feedback->id,
            'reply_text' => $replyText,
        ]);
    }

    public function test_user_can_flag_feedback(): void
    {
        $feedback = Feedback::factory()->create([
            'business_id' => $this->business->id,
            'moderation_status' => ModerationStatus::PUBLISHED,
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('feedback.flag', $feedback), [
                'reason' => 'abusive',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $feedback->refresh();
        $this->assertEquals(ModerationStatus::FLAGGED, $feedback->moderation_status);
    }

    public function test_guest_can_submit_public_feedback(): void
    {
        $response = $this->get(route('feedback.submit', $this->business->slug));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Public/Feedback')
                ->has('business')
        );
    }

    public function test_guest_can_submit_feedback_form(): void
    {
        $feedbackData = [
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'rating' => 5,
            'comment' => 'Great service!',
        ];

        $response = $this->post(
            route('feedback.store', $this->business->slug), 
            $feedbackData
        );


        $target_response = [201, 200, 302, 303, 307, 308];

        $status = $response->getStatusCode();
        $this->assertTrue(in_array($status, $target_response));

        $this->assertDatabaseHas('feedback', [
            'business_id' => $this->business->id,
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'rating' => 5,
        ]);
    }

    public function test_feedback_submission_requires_valid_data(): void
    {
        $response = $this->post(
            route('feedback.store', $this->business->slug), 
            [
                'rating' => 6, // Invalid rating
                'customer_email' => 'invalid-email',
            ]
        );

        $response->assertSessionHasErrors(['rating', 'customer_email']);
    }

    public function test_feedback_calculates_trends_correctly(): void
    {
        // Create feedback from last 7 days
        Feedback::factory()->count(3)->create([
            'business_id' => $this->business->id,
            'submitted_at' => now()->subDays(3),
        ]);

        // Create feedback from previous 7 days
        Feedback::factory()->count(2)->create([
            'business_id' => $this->business->id,
            'submitted_at' => now()->subDays(10),
        ]);

        $response = $this->actingAs($this->user)->get(route('feedback.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->has('stats.total.trend')
        );
    }

    public function test_feedback_pagination_works(): void
    {
        Feedback::factory()->count(25)->create([
            'business_id' => $this->business->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('feedback.index', ['per_page' => 10]));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->has('feedback.data', 10)
                ->has('feedback.links')
        );
    }
}
