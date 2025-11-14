<?php

namespace App\Jobs;

use App\Mail\ReviewRequestEmail;
use App\Models\ReviewRequest;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendReviewRequestEmail implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public ReviewRequest $reviewRequest,
        public bool $isReminder = false
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Check if review request is still valid
        if ($this->reviewRequest->status === 'expired' || $this->reviewRequest->status === 'completed') {
            return;
        }

        // Send the email
        Mail::to($this->reviewRequest->customer->email)
            ->send(new ReviewRequestEmail($this->reviewRequest, $this->isReminder));

        // Update sent_at if not already set (for scheduled emails)
        if (!$this->reviewRequest->sent_at) {
            $this->reviewRequest->update([
                'sent_at' => now(),
            ]);
        }
    }
}
