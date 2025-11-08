<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PublicBusinessController extends Controller
{
    /**
     * Show the public business profile page.
     */
    public function show(Business $business)
    {
        $business->load(['category', 'feedback' => function ($query) {
            $query->where('is_public', true)
                ->where('moderation_status', '!=', 'flagged')
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
                'positive' => $publicFeedback->filter(fn($f) => is_string($f->sentiment) && strcasecmp($f->sentiment, 'positive') === 0)->count(),
                'neutral' => $publicFeedback->filter(fn($f) => is_string($f->sentiment) && strcasecmp($f->sentiment, 'neutral') === 0)->count(),
                'negative' => $publicFeedback->filter(fn($f) => is_string($f->sentiment) && strcasecmp($f->sentiment, 'negative') === 0)->count(),
            ],
        ];

        // Calculate percentages for rating distribution
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
            ->where('moderation_status', '!=', 'flagged')
            ->orderBy('submitted_at', 'desc')
            ->paginate(10);

        $feedbackFeed->getCollection()->transform(function ($feedback) {
            $feedback->submitted_at_human = $feedback->submitted_at?->diffForHumans();
            $feedback->replied_at_human = $feedback->replied_at?->diffForHumans();
            return $feedback;
        });

        return Inertia::render('Public/BusinessProfile', [
            'business' => $business,
            'stats' => $stats,
            'feedbackFeed' => $feedbackFeed,
        ]);
    }
}
