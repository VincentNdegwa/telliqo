<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\BusinessSetting;
use Illuminate\Database\Seeder;

class BusinessSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $businesses = Business::all();

        foreach ($businesses as $business) {
            $this->createDefaultSettings($business);
        }

        $this->command->info("Default settings created for {$businesses->count()} businesses.");
    }

    /**
     * Create default settings for a business.
     */
    public function createDefaultSettings(Business $business): void
    {
        $defaultSettings = $this->getDefaultSettings();

        foreach ($defaultSettings as $key => $data) {
            if ($business->hasSetting($key)) {
                continue;
            }

            BusinessSetting::create([
                'business_id' => $business->id,
                'key' => $key,
                'value' => $data['value'],
                'type' => $data['type'] ?? 'json',
                'is_encrypted' => $data['is_encrypted'] ?? false,
                'description' => $data['description'] ?? null,
            ]);
        }
    }

    /**
     * Get default settings structure.
     */
    private function getDefaultSettings(): array
    {
        return [
            'notification_settings' => [
                'description' => 'Email and push notification preferences',
                'value' => [
                    'email_notifications_enabled' => true,
                    'new_feedback_email' => true,
                    'weekly_summary' => true,
                    'monthly_report' => false,
                    'low_rating_alert' => true,
                    'low_rating_threshold' => 2,
                ],
            ],

            'display_settings' => [
                'description' => 'Public-facing display and privacy settings',
                'value' => [
                    'show_business_profile' => true,
                    'display_logo' => true,
                    'show_total_reviews' => true,
                    'show_average_rating' => true,
                    'show_verified_badge' => true,
                ],
            ],

            'moderation_settings' => [
                'description' => 'Review moderation and filtering settings',
                'value' => [
                    'enable_ai_moderation' => false,
                    'block_duplicate_reviews' => true,
                ],
            ],

            'feedback_collection_settings' => [
                'description' => 'Customer feedback collection requirements and options',
                'value' => [
                    'require_customer_name' => false,
                    'require_customer_email' => false,
                    'allow_anonymous_feedback' => true,
                ],
            ],
        ];
    }
}

