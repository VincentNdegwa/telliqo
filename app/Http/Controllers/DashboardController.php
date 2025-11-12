<?php

namespace App\Http\Controllers;

use App\Jobs\ComputeDailyMetrics;
use App\Models\Enums\ModerationStatus;
use App\Models\Enums\Sentiments;
use App\Models\Feedback;
use App\Services\MetricsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __construct(
        protected MetricsService $metricsService
    ) {}

    /**
     * Display the dashboard with comprehensive statistics.
     */
    public function index()
    {
        $business = Auth::user()->getCurrentBusiness();

        if (!$business) {
            return redirect()->route('onboarding.show');
        }

        $this->ensureMetricsExist($business->id);

        $today = Carbon::today();
        $last7Days = Carbon::now()->subDays(7);
        $last30Days = Carbon::now()->subDays(30);
        $previousMonth = Carbon::now()->subDays(60);

        // Overall statistics
        $totalFeedback = $business->feedback()->count();
        $totalPublished = $business->feedback()->where('is_public', true)->count();
        $totalFlagged = $business->feedback()->where('moderation_status', ModerationStatus::FLAGGED)->count();
        
        $analyticsQuery = $business->feedback()->where('moderation_status', '!=', ModerationStatus::FLAGGED->value);
        
        $averageRating = round($analyticsQuery->avg('rating') ?? 0, 1);

        $recentFeedback = $business->feedback()
            ->where('moderation_status', '!=', ModerationStatus::FLAGGED->value)
            ->where('submitted_at', '>=', $last7Days)
            ->count();
        $previousWeekFeedback = $business->feedback()
            ->where('moderation_status', '!=', ModerationStatus::FLAGGED->value)
            ->whereBetween('submitted_at', [Carbon::now()->subDays(14), $last7Days])
            ->count();

        $ratingDistribution = [
            5 => $analyticsQuery->where('rating', 5)->count(),
            4 => $analyticsQuery->where('rating', 4)->count(),
            3 => $analyticsQuery->where('rating', 3)->count(),
            2 => $analyticsQuery->where('rating', 2)->count(),
            1 => $analyticsQuery->where('rating', 1)->count(),
        ];

        $sentimentDistribution = [
            'positive' => $analyticsQuery->where('sentiment', Sentiments::POSITIVE)->count(),
            'neutral' => $analyticsQuery->where('sentiment', Sentiments::NEUTRAL)->count(),
            'negative' => $analyticsQuery->where('sentiment', Sentiments::NEGATIVE)->count(),
            'not_determined' => $analyticsQuery->where('sentiment', Sentiments::NOT_DETERMINED)->count(),
        ];

        $feedbackTrend = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $count = $business->feedback()
                ->where('moderation_status', '!=', ModerationStatus::FLAGGED->value)
                ->whereDate('submitted_at', $date)
                ->count();
            $feedbackTrend[] = [
                'date' => Carbon::now()->subDays($i)->format('M d'),
                'count' => $count,
            ];
        }

        $currentMonthFeedback = $business->feedback()
            ->where('moderation_status', '!=', ModerationStatus::FLAGGED->value)
            ->where('submitted_at', '>=', $today->copy()->startOfMonth())
            ->count();
        $previousMonthFeedback = $business->feedback()
            ->where('moderation_status', '!=', ModerationStatus::FLAGGED->value)
            ->whereBetween('submitted_at', [
                $today->copy()->subMonth()->startOfMonth(),
                $today->copy()->subMonth()->endOfMonth()
            ])
            ->count();

        $recentFeedbackList = $business->feedback()
            ->orderBy('submitted_at', 'desc')
            ->limit(6)
            ->get()
            ->map(function ($feedback) {
                return [
                    'id' => $feedback->id,
                    'customer_name' => $feedback->customer_name,
                    'customer_email' => $feedback->customer_email,
                    'rating' => $feedback->rating,
                    'comment' => $feedback->comment,
                    'sentiment' => $feedback->sentiment?->serialize(),
                    'moderation_status' => $feedback->moderation_status->serialize(),
                    'is_public' => $feedback->is_public,
                    'submitted_at' => $feedback->submitted_at?->diffForHumans(),
                    'replied_at' => $feedback->replied_at,
                ];
            });

        $totalResponded = $business->feedback()->whereNotNull('replied_at')->count();
        $responseRate = $totalFeedback > 0 ? round(($totalResponded / $totalFeedback) * 100, 1) : 0;

        // Get metrics summary
        $metricsSummary = $this->metricsService->getSummary($business->id, 30);
        
        // Get daily metrics for charts (last 30 days)
        $dailyMetrics = $this->metricsService->getMetrics($business->id, 30);
        
        // Transform daily metrics for charts
        $npsChartData = [];
        $ratingChartData = [];
        $sentimentChartData = [];
        
        foreach ($dailyMetrics as $metric) {
            $date = Carbon::parse($metric['metric_date'])->format('M d');
            $npsChartData[] = [
                'date' => $date,
                'nps' => $metric['nps'] ?? 0,
                'promoters' => $metric['promoters'] ?? 0,
                'passives' => $metric['passives'] ?? 0,
                'detractors' => $metric['detractors'] ?? 0,
            ];
            
            $ratingChartData[] = [
                'date' => $date,
                'avg_rating' => $metric['avg_rating'] ?? 0,
                'total_feedback' => $metric['total_feedback'] ?? 0,
            ];
            
            $sentimentChartData[] = [
                'date' => $date,
                'positive' => $metric['positive_count'] ?? 0,
                'neutral' => $metric['neutral_count'] ?? 0,
                'negative' => $metric['negative_count'] ?? 0,
                'not_determined' => $metric['not_determined_count'] ?? 0,
            ];
        }
        
        $categoryAverage = $business->category_id 
            ? $this->metricsService->getCategoryAverage($business->category_id, 30)
            : null;

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
                'nps_trend' => $npsChartData,
                'rating_trend' => $ratingChartData,
                'sentiment_trend' => $sentimentChartData,
            ],
            'metrics' => $metricsSummary,
            'daily_metrics' => $dailyMetrics,
            'category_average' => $categoryAverage,
            'recent_feedback_list' => $recentFeedbackList,
            'quick_links' => $quickLinks,
        ]);
    }


    protected function ensureMetricsExist(int $businessId): void
    {
        $hasMetrics = \App\Models\BusinessMetric::where('business_id', $businessId)->exists();
        
        if (!$hasMetrics) {
            for ($i = 0; $i < 30; $i++) {
                $date = now()->subDays($i)->format('Y-m-d');
                (new ComputeDailyMetrics($businessId, $date))->handle();
            }
        }
    }
}
