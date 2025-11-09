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

    public $timeout = 180;
    public $tries = 2;

    public function __construct(protected Feedback $review)
    {
    }

    public function handle(): void
    {
        $reviewAgent = new ReviewAgent("review_analysis_{$this->review->business_id}");
        $flagAgent = new FlagAgent("moderation_{$this->review->business_id}");

        try {
            $moderationResult = $flagAgent->analyze($this->review->comment, [
                'rating' => $this->review->rating,
                'customer_name' => $this->review->customer_name ?? 'Anonymous',
            ]);

            $status = $moderationResult['moderation_status'] ?? 'published';
            $confidence = $moderationResult['confidence'] ?? 0.0;
            $reason = $moderationResult['reason'] ?? 'No reason provided';

            Log::info("Review {$this->review->id} moderation: {$status}", [
                'confidence' => $confidence,
                'reason' => $reason,
            ]);

            if ($status === 'flagged') {
                $this->review->update([
                    'moderation_status' => ModerationStatus::FLAGGED,
                    'is_public' => false,
                ]);
            } elseif ($status === 'soft_flagged') {
                $this->review->update([
                    'moderation_status' => ModerationStatus::SOFT_FLAGGED,
                    'is_public' => true,
                ]);
            }

            $sentimentData = $reviewAgent->analyze($this->review->comment);
            $sentiment = isset($sentimentData['sentiment']) 
                ? Sentiments::from($sentimentData['sentiment']) 
                : null;

            $this->review->update(['sentiment' => $sentiment]);

            Log::info("Review {$this->review->id} analyzed: {$sentiment?->value}");

        } catch (\Exception $e) {
            Log::error("Review {$this->review->id} analysis failed: {$e->getMessage()}");
            throw $e;
        }
    }
}
