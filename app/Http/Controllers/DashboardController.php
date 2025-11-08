<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with comprehensive statistics.
     */
    public function index()
    {
        $business = Auth::user()->getCurrentBusiness();

        if (!$business) {
            return redirect()->route('onboarding.show');
        }

        // Date ranges
        $today = Carbon::today();
        $last7Days = Carbon::now()->subDays(7);
        $last30Days = Carbon::now()->subDays(30);
        $previousMonth = Carbon::now()->subDays(60);

        // Overall statistics
        $totalFeedback = $business->feedback()->count();
        $totalPublished = $business->feedback()->where('is_public', true)->count();
        $totalFlagged = $business->feedback()->where('moderation_status', 'flagged')->count();
        $averageRating = round($business->feedback()->avg('rating') ?? 0, 1);

        // Recent trends (last 7 days vs previous 7 days)
        $recentFeedback = $business->feedback()->where('submitted_at', '>=', $last7Days)->count();
        $previousWeekFeedback = $business->feedback()
            ->whereBetween('submitted_at', [Carbon::now()->subDays(14), $last7Days])
            ->count();

        // Rating distribution
        $ratingDistribution = [
            5 => $business->feedback()->where('rating', 5)->count(),
            4 => $business->feedback()->where('rating', 4)->count(),
            3 => $business->feedback()->where('rating', 3)->count(),
            2 => $business->feedback()->where('rating', 2)->count(),
            1 => $business->feedback()->where('rating', 1)->count(),
        ];

        // Sentiment distribution
        $sentimentDistribution = [
            'positive' => $business->feedback()->where('sentiment', 'positive')->count(),
            'neutral' => $business->feedback()->where('sentiment', 'neutral')->count(),
            'negative' => $business->feedback()->where('sentiment', 'negative')->count(),
        ];

        // Feedback trend over last 30 days (daily)
        $feedbackTrend = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $count = $business->feedback()
                ->whereDate('submitted_at', $date)
                ->count();
            $feedbackTrend[] = [
                'date' => Carbon::now()->subDays($i)->format('M d'),
                'count' => $count,
            ];
        }

        // Monthly comparison (current month vs previous month)
        $currentMonthFeedback = $business->feedback()
            ->where('submitted_at', '>=', $today->copy()->startOfMonth())
            ->count();
        $previousMonthFeedback = $business->feedback()
            ->whereBetween('submitted_at', [
                $today->copy()->subMonth()->startOfMonth(),
                $today->copy()->subMonth()->endOfMonth()
            ])
            ->count();

        // Recent feedback (paginated)
        $recentFeedbackList = $business->feedback()
            ->orderBy('submitted_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($feedback) {
                return [
                    'id' => $feedback->id,
                    'customer_name' => $feedback->customer_name,
                    'customer_email' => $feedback->customer_email,
                    'rating' => $feedback->rating,
                    'comment' => $feedback->comment,
                    'sentiment' => $feedback->sentiment,
                    'moderation_status' => $feedback->moderation_status,
                    'is_public' => $feedback->is_public,
                    'submitted_at' => $feedback->submitted_at?->diffForHumans(),
                    'replied_at' => $feedback->replied_at,
                ];
            });

        // Response rate
        $totalResponded = $business->feedback()->whereNotNull('replied_at')->count();
        $responseRate = $totalFeedback > 0 ? round(($totalResponded / $totalFeedback) * 100, 1) : 0;

        // Quick Links
        $quickLinks = [
            'public_profile_url' => route('business.public', $business->slug),
            'review_url' => route('feedback.submit', $business->slug),
            'qr_code_path' => $business->qr_code_path,
            'business_slug' => $business->slug,
        ];

        return Inertia::render('Dashboard', [
            'stats' => [
                'total_feedback' => $totalFeedback,
                'total_published' => $totalPublished,
                'total_flagged' => $totalFlagged,
                'average_rating' => $averageRating,
                'response_rate' => $responseRate,
                'recent_feedback' => $recentFeedback,
                'previous_week_feedback' => $previousWeekFeedback,
                'current_month_feedback' => $currentMonthFeedback,
                'previous_month_feedback' => $previousMonthFeedback,
            ],
            'charts' => [
                'rating_distribution' => $ratingDistribution,
                'sentiment_distribution' => $sentimentDistribution,
                'feedback_trend' => $feedbackTrend,
            ],
            'recent_feedback_list' => $recentFeedbackList,
            'quick_links' => $quickLinks,
        ]);
    }
}
