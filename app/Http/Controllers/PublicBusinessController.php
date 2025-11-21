<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Enums\ModerationStatus;
use App\Models\Enums\Sentiments;
use App\Services\FeatureService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PublicBusinessController extends Controller
{
    public function __construct(
        protected FeatureService $features,
    ) {}

    /**
     * Show the public business profile page.
     */
    public function show(Business $business)
    {
        $displaySettings = $business->getSetting('display_settings', []);
        $feedbackSettings = $business->getSetting('feedback_collection_settings', []);

        
        $hasVerifiedBadge = $this->features->hasFeature($business, 'verified_customer_badge');

        $acceptingFeedbackSubmissions = $this->features->canUseFeature($business, 'feedback_submissions');
        
        $canDisplayProfile = $displaySettings['show_business_profile'] ?? true;

        if (!$canDisplayProfile) {
            return redirect()->route('home');
        }

        $business->load(['category', 'feedback' => function ($query) {
            $query->where('is_public', true)
                ->where('moderation_status', '!=', ModerationStatus::FLAGGED)
                ->orderBy('submitted_at', 'desc');
        }]);

        $publicFeedback = $business->feedback;

        $stats = [
            'total' => $publicFeedback->count(),
            'average_rating' => round($publicFeedback->avg('rating') ?? 0, 1),
            'rating_distribution' => [
                5 => $publicFeedback->where('rating', 5)->count(),
                4 => $publicFeedback->where('rating', 4)->count(),
                3 => $publicFeedback->where('rating', 3)->count(),
                2 => $publicFeedback->where('rating', 2)->count(),
                1 => $publicFeedback->where('rating', 1)->count(),
            ],
            'sentiment_distribution' => [
                'positive' => $publicFeedback->where('sentiment', Sentiments::POSITIVE)->count(),
                'neutral' => $publicFeedback->where('sentiment', Sentiments::NEUTRAL)->count(),
                'negative' => $publicFeedback->where('sentiment', Sentiments::NEGATIVE)->count(),
            ],
        ];

        $total = $stats['total'];
        foreach ($stats['rating_distribution'] as $rating => $count) {
            $stats['rating_distribution'][$rating] = [
                'count' => $count,
                'percentage' => $total > 0 ? round(($count / $total) * 100, 1) : 0,
            ];
        }

        // Calculate percentages for sentiment distribution
        foreach ($stats['sentiment_distribution'] as $sentiment => $count) {
            $stats['sentiment_distribution'][$sentiment] = [
                'count' => $count,
                'percentage' => $total > 0 ? round(($count / $total) * 100, 1) : 0,
            ];
        }

        $feedbackFeed = $business->feedback()
            ->where('is_public', true)
            ->where('moderation_status', '!=', ModerationStatus::FLAGGED)
            ->orderBy('submitted_at', 'desc')
            ->paginate(10)
            ->through(function ($feedback) use ($hasVerifiedBadge) {
                return [
                    'id' => $feedback->id,
                    'business_id' => $feedback->business_id,
                    'customer_name' => $feedback->customer_name,
                    'rating' => $feedback->rating,
                    'comment' => $feedback->comment,
                    'reply_text' => $feedback->reply_text,
                    'sentiment' => $feedback->sentiment?->serialize(),
                    'moderation_status' => $feedback->moderation_status->serialize(),
                    'is_public' => $feedback->is_public,
                    'submitted_at' => $feedback->submitted_at,
                    'submitted_at_human' => $feedback->submitted_at?->diffForHumans(),
                    'replied_at' => $feedback->replied_at,
                    'replied_at_human' => $feedback->replied_at?->diffForHumans(),
                    'verified_customer' => $hasVerifiedBadge && $feedback->customer_id != null
                ];
            });

        return Inertia::render('Public/BusinessProfile', [
            'business' => $business,
            'stats' => $stats,
            'feedbackFeed' => $feedbackFeed,
            'displaySettings' => $displaySettings,
            'feedbackSettings' => $feedbackSettings,
            'acceptingFeedbackSubmissions' => $acceptingFeedbackSubmissions,
        ]);
    }
}
