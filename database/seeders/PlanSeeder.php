<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            'starter' => [
                'name' => 'Starter',
                'description' => 'Perfect for getting started with collecting feedback.',
                'price_kes' => 0,
                'price_usd' => 0,
                'price_kes_yearly' => 0,
                'price_usd_yearly' => 0,
                'sort_order' => 1,
                'features' => [
                    'feedback_submissions' => [
                        'enabled' => true,
                        'quota' => 100,
                        'unit' => 'per_month',
                    ],
                    'manual_feedback_reply' => [
                        'enabled' => true,
                    ],
                    'manual_moderation' => [
                        'enabled' => true,
                    ],
                    'verified_customer_badge' => [
                        'enabled' => true,
                    ],
                    'dashboard_basic_analytics' => [
                        'enabled' => true,
                    ],
                    'review_request_emails' => [
                        'enabled' => true,
                        'quota' => 100,
                        'unit' => 'per_month',
                    ],
                    'team_users' => [
                        'enabled' => true,
                        'quota' => 3,
                        'unit' => 'users',
                    ],
                    'user_roles' => [
                        'enabled' => false,
                    ],
                    'ai_reply_generator' => [
                        'enabled' => false,
                    ],
                    'ai_sentiment' => [
                        'enabled' => false,
                    ],
                    'ai_moderation' => [
                        'enabled' => false,
                    ],
                    'dashboard_nps_analytics' => [
                        'enabled' => false,
                    ],
                    'summary_reports' => [
                        'enabled' => false,
                    ],
                    'api_intergration' => [
                        'enabled' => false,
                    ],
                ],
            ],
            'growth' => [
                'name' => 'Growth',
                'description' => 'For growing businesses that need more volume and analytics.',
                'price_kes' => 1999,
                'price_usd' => 19,
                'price_kes_yearly' => 1999 * 10,
                'price_usd_yearly' => 19 * 10,
                'sort_order' => 2,
                'features' => [
                    'feedback_submissions' => [
                        'enabled' => true,
                        'quota' => 1000,
                        'unit' => 'per_month',
                    ],
                    'manual_feedback_reply' => [
                        'enabled' => true,
                    ],
                    'manual_moderation' => [
                        'enabled' => true,
                    ],
                    'verified_customer_badge' => [
                        'enabled' => true,
                    ],
                    'dashboard_basic_analytics' => [
                        'enabled' => true,
                    ],
                    'dashboard_nps_analytics' => [
                        'enabled' => true,
                    ],
                    'review_request_emails' => [
                        'enabled' => true,
                        'quota' => 1000,
                        'unit' => 'per_month',
                    ],
                    'team_users' => [
                        'enabled' => true,
                        'quota' => 10,
                        'unit' => 'users',
                    ],
                    'user_roles' => [
                        'enabled' => true,
                    ],
                    'ai_reply_generator' => [
                        'enabled' => true,
                    ],
                    'ai_sentiment' => [
                        'enabled' => true,
                    ],
                    'ai_moderation' => [
                        'enabled' => false,
                    ],
                    'summary_reports' => [
                        'enabled' => true,
                    ],
                    'api_intergration' => [
                        'enabled' => false,
                    ],
                ],
            ],
            'pro' => [
                'name' => 'Pro',
                'description' => 'Full power for teams that need everything unlimited.',
                'price_kes' => 4999,
                'price_usd' => 49,
                'price_kes_yearly' => 4999 * 10,
                'price_usd_yearly' => 49 * 10,
                'sort_order' => 3,
                'features' => [
                    'feedback_submissions' => [
                        'enabled' => true,
                        'is_unlimited' => true,
                        'unit' => 'per_month',
                    ],
                    'manual_feedback_reply' => [
                        'enabled' => true,
                    ],
                    'manual_moderation' => [
                        'enabled' => true,
                    ],
                    'verified_customer_badge' => [
                        'enabled' => true,
                    ],
                    'dashboard_basic_analytics' => [
                        'enabled' => true,
                    ],
                    'dashboard_nps_analytics' => [
                        'enabled' => true,
                    ],
                    'summary_reports' => [
                        'enabled' => true,
                    ],
                    'review_request_emails' => [
                        'enabled' => true,
                        'is_unlimited' => true,
                        'unit' => 'per_month',
                    ],
                    'team_users' => [
                        'enabled' => true,
                        'is_unlimited' => true,
                        'unit' => 'users',
                    ],
                    'user_roles' => [
                        'enabled' => true,
                    ],
                    'ai_reply_generator' => [
                        'enabled' => true,
                    ],
                    'ai_sentiment' => [
                        'enabled' => true,
                    ],
                    'ai_moderation' => [
                        'enabled' => true,
                    ],
                    'api_intergration' => [
                        'enabled' => true,
                    ],
                ],
            ],
        ];

        foreach ($plans as $key => $data) {
            $plan = Plan::updateOrCreate(
                ['key' => $key],
                [
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'price_kes' => $data['price_kes'],
                    'price_usd' => $data['price_usd'],
                    'price_kes_yearly' => $data['price_kes_yearly'],
                    'price_usd_yearly' => $data['price_usd_yearly'],
                    'is_active' => true,
                    'sort_order' => $data['sort_order'],
                ]
            );

            $plan->syncFeatures($data['features']);
        }
    }
}
