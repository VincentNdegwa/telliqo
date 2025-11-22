<?php

namespace Database\Seeders\Load;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Feedback;
use App\Models\ReviewRequest;
use Illuminate\Database\Seeder;

class RandomBusinessActivitySeeder extends Seeder
{
    /**
     * Seed random review requests and feedback for existing businesses.
     */
    public function run(): void
    {
        $businesses = Business::with('customers')->get();

        if ($businesses->isEmpty()) {
            if (method_exists($this, 'command') && $this->command) {
                $this->command->warn('No businesses found. Skipping RandomBusinessActivitySeeder.');
            }

            return;
        }

        foreach ($businesses as $business) {
            // Ensure the business has some customers
            $customers = $business->customers;

            if ($customers->isEmpty()) {
                $customers = Customer::factory()
                    ->count(10)
                    ->create([
                        'business_id' => $business->id,
                    ]);
            }

            foreach ($customers as $customer) {
                // For each customer, create between 1 and 3 review requests
                $requestsCount = random_int(1, 3);

                $reviewRequests = ReviewRequest::factory()
                    ->count($requestsCount)
                    ->create([
                        'business_id' => $business->id,
                        'customer_id' => $customer->id,
                    ]);

                // Create 0-2 feedback entries for this customer
                $feedbackTarget = random_int(0, 2);
                $feedbackCreated = 0;

                // Attach some feedback to review requests
                foreach ($reviewRequests as $request) {
                    if ($feedbackCreated >= $feedbackTarget) {
                        break;
                    }

                    Feedback::factory()->create([
                        'business_id' => $business->id,
                        'customer_id' => $customer->id,
                        'review_request_id' => $request->id,
                    ]);

                    $feedbackCreated++;
                }

                // Optionally add extra feedback without a review request
                while ($feedbackCreated < $feedbackTarget) {
                    Feedback::factory()->create([
                        'business_id' => $business->id,
                        'customer_id' => $customer->id,
                    ]);

                    $feedbackCreated++;
                }

                // Update simple customer stats based on what we just created
                $customer->refresh();

                $customer->update([
                    'total_requests_sent' => $customer->total_requests_sent + $reviewRequests->count(),
                    'total_feedbacks' => $customer->total_feedbacks + $feedbackCreated,
                    'last_request_sent_at' => $reviewRequests->max('sent_at') ?? $customer->last_request_sent_at,
                    'last_feedback_at' => Feedback::where('customer_id', $customer->id)->max('submitted_at') ?? $customer->last_feedback_at,
                ]);
            }
        }
    }
}
