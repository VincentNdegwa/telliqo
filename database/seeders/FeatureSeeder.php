<?php

namespace Database\Seeders;

use App\Models\Feature;
use Illuminate\Database\Seeder;

class FeatureSeeder extends Seeder
{
    public function run(): void
    {
        $features = [
            // Feedback System
            [
                'key' => 'feedback_submissions',
                'name' => 'Feedback submissions',
                'category' => 'Feedback System',
                'type' => 'quota',
                'default_unit' => 'per_month',
                'description' => 'Total number of feedback entries a business can receive per month.',
                'public_name' => 'Monthly feedback submissions',
                'public_description' => 'Maximum number of feedback entries you can collect each month.',
            ],
            [
                'key' => 'manual_feedback_reply',
                'name' => 'Manual feedback reply',
                'category' => 'Feedback System',
                'type' => 'boolean',
                'default_unit' => null,
                'description' => 'User can manually write replies to customer feedback.',
                'public_name' => 'Manual reply to feedback',
                'public_description' => 'Write and publish your own replies to customer feedback.',
            ],
            [
                'key' => 'ai_reply_generator',
                'name' => 'AI reply generator',
                'category' => 'Feedback System',
                'type' => 'boolean',
                'default_unit' => null,
                'description' => 'System automatically generates AI-powered reply suggestions.',
                'public_name' => 'AI reply suggestions',
                'public_description' => 'Get AI-generated suggestions for replying to customer feedback.',
            ],
            [
                'key' => 'ai_sentiment',
                'name' => 'AI sentiment analysis',
                'category' => 'Feedback System',
                'type' => 'boolean',
                'default_unit' => null,
                'description' => 'AI analyzes feedback sentiment (positive/neutral/negative).',
                'public_name' => 'AI sentiment analysis',
                'public_description' => 'Automatically detect if feedback is positive, neutral, or negative.',
            ],
            [
                'key' => 'manual_moderation',
                'name' => 'Manual moderation',
                'category' => 'Feedback System',
                'type' => 'boolean',
                'default_unit' => null,
                'description' => 'User manually flags and reviews policy-violating feedback.',
                'public_name' => 'Manual moderation',
                'public_description' => 'Manually review and flag inappropriate or policy-violating feedback.',
            ],
            [
                'key' => 'verified_customer_badge',
                'name' => 'Verified customer badge',
                'category' => 'Feedback System',
                'type' => 'boolean',
                'default_unit' => null,
                'description' => 'Assign a “Verified Customer” tag on feedback left by customers in system.',
                'public_name' => 'Verified customer badge',
                'public_description' => 'Show a badge on reviews from verified customers.',
            ],
            [
                'key' => 'verified_customer_insights',
                'name' => 'Verified customer insights',
                'category' => 'Feedback System',
                'type' => 'boolean',
                'default_unit' => null,
                'description' => 'Analytics showing the ratio of verified vs non-verified reviews.',
                'public_name' => 'Verified vs non-verified analytics',
                'public_description' => 'See analytics comparing reviews from verified vs non-verified customers.',
            ],

            // Dashboard Analytics
            [
                'key' => 'dashboard_basic_analytics',
                'name' => 'Basic analytics dashboard',
                'category' => 'Dashboard Analytics',
                'type' => 'boolean',
                'default_unit' => null,
                'description' => 'Shows rating trend, feedback trend, rating distribution.',
                'public_name' => 'Basic analytics',
                'public_description' => 'View rating trends, feedback volume, and rating distribution.',
            ],
            [
                'key' => 'dashboard_nps_analytics',
                'name' => 'NPS analytics dashboard',
                'category' => 'Dashboard Analytics',
                'type' => 'boolean',
                'default_unit' => null,
                'description' => 'Access full analytics including NPS score, category comparison, sentiment trend, and metrics.',
                'public_name' => 'Advanced NPS analytics',
                'public_description' => 'Access NPS score, sentiment trends, and advanced analytics.',
            ],
            [
                'key' => 'summary_reports',
                'name' => 'Summary reports',
                'category' => 'Dashboard Analytics',
                'type' => 'boolean',
                'default_unit' => null,
                'description' => 'Weekly and monthly performance summary delivered via email.',
                'public_name' => 'Email summary reports',
                'public_description' => 'Receive weekly and monthly performance reports via email.',
            ],

            // Customer Management
            [
                'key' => 'public_profile',
                'name' => 'Public profile',
                'category' => 'Customer Management',
                'type' => 'boolean',
                'default_unit' => null,
                'description' => 'Business has a public review profile page.',
                'public_name' => 'Public review page',
                'public_description' => 'Get a public page where customers can see and leave reviews.',
            ],
            [
                'key' => 'qr_code',
                'name' => 'QR code',
                'category' => 'Customer Management',
                'type' => 'boolean',
                'default_unit' => null,
                'description' => 'Can generate QR codes for feedback collection.',
                'public_name' => 'Feedback QR codes',
                'public_description' => 'Generate QR codes that link directly to your feedback form.',
            ],
            [
                'key' => 'review_link',
                'name' => 'Review link',
                'category' => 'Customer Management',
                'type' => 'boolean',
                'default_unit' => null,
                'description' => 'Shareable feedback link for customers.',
                'public_name' => 'Shareable review link',
                'public_description' => 'Share a link that customers can use to leave feedback.',
            ],
            [
                'key' => 'csv_export',
                'name' => 'CSV export',
                'category' => 'Customer Management',
                'type' => 'boolean',
                'default_unit' => null,
                'description' => 'Export customer feedback & stats as CSV.',
                'public_name' => 'CSV export',
                'public_description' => 'Export feedback and analytics data as CSV files.',
            ],

            // Review Requests
            [
                'key' => 'review_request_emails',
                'name' => 'Review request emails',
                'category' => 'Review Requests',
                'type' => 'quota',
                'default_unit' => 'per_month',
                'description' => 'Number of email review requests that can be sent per month.',
                'public_name' => 'Monthly review request emails',
                'public_description' => 'How many review request emails you can send each month.',
            ],
            [
                'key' => 'scheduled_review_requests',
                'name' => 'Scheduled review requests',
                'category' => 'Review Requests',
                'type' => 'boolean',
                'default_unit' => null,
                'description' => 'Schedule feedback request emails for a future date/time.',
                'public_name' => 'Schedule review requests',
                'public_description' => 'Schedule review request emails to send at a later time.',
            ],

            // Team & Roles
            [
                'key' => 'team_users',
                'name' => 'Team users',
                'category' => 'Team & Roles',
                'type' => 'quota',
                'default_unit' => 'users',
                'description' => 'Number of team members allowed.',
                'public_name' => 'Team members',
                'public_description' => 'How many team members can be added to your account.',
            ],
            [
                'key' => 'user_roles',
                'name' => 'User roles',
                'category' => 'Team & Roles',
                'type' => 'boolean',
                'default_unit' => null,
                'description' => 'Ability to create custom roles and assign permissions.',
                'public_name' => 'Custom user roles',
                'public_description' => 'Create custom roles and manage permissions for your team.',
            ],

            // API Integration
            [
                'key' => 'review_verification_api',
                'name' => 'Review verification API',
                'category' => 'API Integration',
                'type' => 'boolean',
                'default_unit' => null,
                'description' => 'API that verifies whether a customer is real and allowed to submit feedback.',
                'public_name' => 'Review verification API',
                'public_description' => 'Use an API to verify whether a customer is allowed to submit feedback.',
            ],

            // Media & Branding Tools
            [
                'key' => 'poster_generator_premium',
                'name' => 'Poster generator premium',
                'category' => 'Media & Branding Tools',
                'type' => 'boolean',
                'default_unit' => null,
                'description' => 'Access to premium design templates for posters and QR flyers.',
                'public_name' => 'Premium poster templates',
                'public_description' => 'Unlock premium poster and QR flyer design templates.',
            ],
        ];

        foreach ($features as $data) {
            Feature::updateOrCreate(
                ['key' => $data['key']],
                $data
            );
        }
    }
}
