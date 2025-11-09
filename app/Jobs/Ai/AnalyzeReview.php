<?php

namespace App\Jobs\Ai;

use App\AiAgents\FlagAgent;
use App\AiAgents\ReviewAgent;
use App\Models\Enums\ModerationStatus;
use App\Models\Enums\Sentiments;
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
        $rating = $this->review->rating;

        $review_key = "review_analysis_{$this->review->business_id}";
        $review_agent = new ReviewAgent($review_key);

        $flag_key = "review_moderation_{$this->review->business_id}";
        $moderation_agent = new FlagAgent($flag_key);

        try {
            // Check moderation status
            $flagResult = $moderation_agent->analyze($comment, [
                'rating' => $rating,
                'customer_name' => $this->review->customer_name ?? 'Anonymous',
            ]);

            $moderationStatus = $flagResult['moderation_status'] ?? 'published';
            $reason = $flagResult['reason'] ?? 'No reason provided';
            $confidence = $flagResult['confidence'] ?? 0.0;

            // Log the decision
            Log::info("Moderation check for Review ID {$this->review->id}", [
                'review_text' => $comment,
                'rating' => $rating,
                'moderation_status' => $moderationStatus,
                'confidence' => $confidence,
                'reason' => $reason,
            ]);

            // Apply moderation status
            if ($moderationStatus === 'flagged') {
                $this->review->update([
                    'moderation_status' => ModerationStatus::FLAGGED,
                    'is_public' => false
                ]);
                Log::warning("Review flagged for moderation: {$this->review->id}", [
                    'confidence' => $confidence,
                    'reason' => $reason,
                ]);
            } elseif ($moderationStatus === 'soft_flagged') {
                $this->review->update([
                    'moderation_status' => ModerationStatus::SOFT_FLAGGED,
                    'is_public' => true 
                ]);
                Log::info("Review soft-flagged for review: {$this->review->id}", [
                    'confidence' => $confidence,
                    'reason' => $reason,
                ]);
            }

            // Analyze the review sentiment
            $analysisData = $review_agent->analyze($comment);

            Log::info("Review analysis data for Review ID {$this->review->id}", [
                'analysis' => $analysisData
            ]);

            $sentimentValue = $analysisData['sentiment'] ?? null;
            $sentiment = $sentimentValue ? Sentiments::from($sentimentValue) : null;
            
            Log::info("Updating Review ID {$this->review->id} with sentiment: {$sentiment?->value}");

            $this->review->update([
                'sentiment' => $sentiment,
            ]);

        } catch (\Exception $e) {
            Log::error("Review analysis failed for Review ID {$this->review->id}: " . $e->getMessage());
        }
    }
}
