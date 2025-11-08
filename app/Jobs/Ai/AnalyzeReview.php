<?php

namespace App\Jobs\Ai;

use App\AiAgents\FlagAgent;
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

        $flag_key = "review_moderation_{$this->review->business_id}";
        $moderation_agent = new FlagAgent($flag_key);

        try {

            //First check the flag
            $flagResult = $moderation_agent->analyze($comment, [
                'rating' => $this->review->rating,
                'customer_name' => $this->review->customer_name ?? 'Anonymous',
            ]);

            if ($flagResult['should_flag']) {
                $this->review->update(['moderation_status' => 'flagged', 'is_public' => false]);
                Log::info("Review flagged for moderation: {$this->review->id}");
                Log::info("Flagging reason: " . ($flagResult['reason'] ?? 'No reason provided'));
                return;
            }

            //Then analyze the review
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
