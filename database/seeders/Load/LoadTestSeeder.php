<?php

namespace Database\Seeders\Load;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Feedback;
use App\Models\ReviewRequest;
use App\Models\User;
use Database\Seeders\BusinessSettingsSeeder;
use Illuminate\Database\Seeder;

class LoadTestSeeder extends Seeder
{
    /**
     * Run the database seeds for load testing.
     */
    public function run(): void
    {
        $businessCount = (int) env('LOADTEST_BUSINESSES', 2);
        $customersPerBusiness = (int) env('LOADTEST_CUSTOMERS_PER_BUSINESS', 10);
        $requestsPerCustomer = (int) env('LOADTEST_REQUESTS_PER_CUSTOMER', 3);
        $feedbackPerCustomer = (int) env('LOADTEST_FEEDBACK_PER_CUSTOMER', 2);

        $businesses = Business::factory()
            ->onboarded()
            ->count($businessCount)
            ->create();

        $settingsSeeder = new BusinessSettingsSeeder();

        foreach ($businesses as $business) {
            // Create an owner user for this business and attach via pivot
            $owner = User::factory()->create();

            $business->users()->attach($owner->id, [
                'role' => 'owner',
                'is_active' => true,
                'invited_at' => now()->subDays(1),
                'joined_at' => now(),
            ]);

            // Ensure Laratrust owner role is attached
            $business->attachOwnerRole($owner);

            // Ensure default business settings exist
            $settingsSeeder->createDefaultSettings($business);

            // Create customers for this business
            $customers = Customer::factory()
                ->count($customersPerBusiness)
                ->create([
                    'business_id' => $business->id,
                ]);

            foreach ($customers as $customer) {
                // Create review requests for this customer
                $reviewRequests = ReviewRequest::factory()
                    ->count($requestsPerCustomer)
                    ->create([
                        'business_id' => $business->id,
                        'customer_id' => $customer->id,
                    ]);

                // Create feedback entries for this customer
                $feedbackCreated = 0;

                // Link some feedback to review requests
                foreach ($reviewRequests as $request) {
                    if ($feedbackCreated >= $feedbackPerCustomer) {
                        break;
                    }

                    Feedback::factory()->create([
                        'business_id' => $business->id,
                        'customer_id' => $customer->id,
                        'review_request_id' => $request->id,
                    ]);

                    $feedbackCreated++;
                }

                // Optionally create extra feedback not tied to a review request
                while ($feedbackCreated < $feedbackPerCustomer) {
                    Feedback::factory()->create([
                        'business_id' => $business->id,
                        'customer_id' => $customer->id,
                    ]);

                    $feedbackCreated++;
                }

                // Update simple customer stats
                $customer->update([
                    'total_requests_sent' => $requestsPerCustomer,
                    'total_feedbacks' => $feedbackPerCustomer,
                    'last_request_sent_at' => $reviewRequests->max('sent_at'),
                    'last_feedback_at' => Feedback::where('customer_id', $customer->id)->max('submitted_at'),
                ]);
            }
        }
    }
}
