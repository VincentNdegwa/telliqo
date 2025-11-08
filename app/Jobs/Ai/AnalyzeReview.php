<?php

namespace App\Jobs\Ai;

use App\AiAgents\ReviewAgent;
use App\Models\Feedback;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class AnalyzeReview implements ShouldQueue
{
    use Queueable;

    protected $review;

    /**
     * Create a new job instance.
     */
    public function __construct(Feedback $review)
    {
        $this->review = $review;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $comment = $this->review->comment;

        $review_key = "review_analysis_{$this->review->business_id}";
        $review_agent = new ReviewAgent($review_key);

        try {

            $analysisData = $review_agent->analyze($comment);
            $sentiment = $analysisData['sentiment'] ?? null;
            $this->review->update([
                'sentiment' => $sentiment,
            ]);

            Log::info("Review analysis completed for Review ID {$this->review->id}: Sentiment - {$sentiment}");

        } catch (\Exception $e) {
            Log::error("Review analysis failed for Review ID {$this->review->id}: " . $e->getMessage());
        }
    }
}
