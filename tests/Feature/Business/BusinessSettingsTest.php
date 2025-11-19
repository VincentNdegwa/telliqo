<?php

namespace Tests\Feature\Business;

use App\Models\Business;
use App\Models\BusinessCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BusinessSettingsTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Business $business;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

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

    public function test_user_can_view_business_settings(): void
    {
        $response = $this->actingAs($this->user)->get(route('business.settings'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('settings/business/Profile')
                ->has('business')
                ->has('categories')
        );
    }

    public function test_unauthenticated_user_cannot_view_business_settings(): void
    {
        $response = $this->get(route('business.settings'));

        $response->assertRedirect(route('login'));
    }

    public function test_user_can_update_business_settings(): void
    {
        $category = BusinessCategory::factory()->create();

        $updateData = [
            'name' => 'Updated Business Name',
            'slug' => 'updated-business-slug',
            'category_id' => $category->id,
            'description' => 'Updated description',
            'email' => 'updated@example.com',
            'phone' => '+1234567890',
            'website' => 'https://updated.example.com',
            'address' => '123 Updated St',
            'auto_approve_feedback' => true,
            'require_customer_name' => false,
            'feedback_email_notifications' => true,
        ];

        $response = $this->actingAs($this->user)
            ->post(route('business.settings.update'), $updateData);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('businesses', [
            'id' => $this->business->id,
            'name' => 'Updated Business Name',
            'email' => 'updated@example.com',
        ]);
    }

    public function test_business_slug_must_be_unique(): void
    {
        $otherBusiness = Business::factory()->create([
            'slug' => 'existing-slug',
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('business.settings.update'), [
                'name' => $this->business->name,
                'slug' => 'existing-slug',
                'category_id' => $this->business->category_id,
                'email' => $this->business->email,
                'auto_approve_feedback' => false,
                'require_customer_name' => false,
                'feedback_email_notifications' => false,
            ]);

        $response->assertSessionHasErrors('slug');
    }

    public function test_user_can_upload_business_logo(): void
    {
        $logo = UploadedFile::fake()->image('logo.png', 500, 500);

        $response = $this->actingAs($this->user)
            ->post(route('business.settings.update'), [
                'name' => $this->business->name,
                'slug' => $this->business->slug,
                'category_id' => $this->business->category_id,
                'email' => $this->business->email,
                'logo' => $logo,
                'auto_approve_feedback' => false,
                'require_customer_name' => false,
                'feedback_email_notifications' => false,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->business->refresh();
        $this->assertNotNull($this->business->logo);
        Storage::disk('public')->assertExists($this->business->logo);
    }

    public function test_user_can_remove_business_logo(): void
    {
        $logo = UploadedFile::fake()->image('logo.png');
        $logoPath = $logo->store('logos', 'public');
        
        $this->business->update(['logo' => $logoPath]);

        $response = $this->actingAs($this->user)
            ->delete(route('business.settings.remove-logo'));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->business->refresh();
        $this->assertNull($this->business->logo);
        Storage::disk('public')->assertMissing($logoPath);
    }

    public function test_business_settings_validation_works(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('business.settings.update'), [
                'name' => '',
                'slug' => '',
                'email' => 'invalid-email',
                'website' => 'not-a-url',
            ]);

        $response->assertSessionHasErrors([
            'name',
            'slug',
            'email',
            'website',
        ]);
    }

    public function test_user_can_view_notification_settings(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('business.settings.notifications'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('settings/business/Notifications')
        );
    }

    public function test_user_can_update_notification_settings(): void
    {
        $settings = [
            'new_feedback' => true,
            'feedback_reply' => false,
            'low_rating' => true,
            'weekly_summary' => true,
        ];

        $response = $this->actingAs($this->user)
            ->post(route('business.settings.notifications.update'), [
                'settings' => $settings,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    public function test_user_can_view_display_settings(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('business.settings.display'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('settings/business/Display')
        );
    }

    public function test_user_can_update_display_settings(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('business.settings.display.update'), [
                'show_business_profile' => false,
                'display_logo' => false,
                'show_total_reviews' => false,
                'show_average_rating' => false,
                'show_verified_badge' => false,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->business->refresh();

        $key = "display_settings";

        $this->assertFalse($this->business->getSettingValue($key, 'show_business_profile'));
        $this->assertFalse($this->business->getSettingValue($key, 'display_logo'));
        $this->assertFalse($this->business->getSettingValue($key, 'show_total_reviews'));
        $this->assertFalse($this->business->getSettingValue($key, 'show_average_rating'));
        $this->assertFalse($this->business->getSettingValue($key, 'show_verified_badge'));

    }

    public function test_user_can_view_moderation_settings(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('business.settings.moderation'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('settings/business/Moderation')
        );
    }

    public function test_user_can_update_moderation_settings(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('business.settings.moderation.update'), [
                'auto_approve_feedback' => true,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->business->refresh();
        $this->assertTrue($this->business->auto_approve_feedback);
    }

    public function test_user_can_view_feedback_settings(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('business.settings.feedback'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('settings/business/FeedBack')
        );
    }

    public function test_user_can_update_feedback_settings(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('business.settings.feedback.update'), [
                'require_customer_name' => true,
                'require_customer_email' => true,
                'allow_anonymous_feedback' => true,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->business->refresh();
        $key = "feedback_collection_settings";

        $this->assertTrue($this->business->getSettingValue($key, 'require_customer_name'));
        $this->assertTrue($this->business->getSettingValue($key, 'require_customer_email'));
        $this->assertTrue($this->business->getSettingValue($key, 'allow_anonymous_feedback'));
        
    }
}
