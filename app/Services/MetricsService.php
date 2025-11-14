<?php

namespace App\Services;

use App\Models\Business;
use App\Models\BusinessMetric;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class MetricsService
{
    /**
     * Get metrics for a business within a date range
     */
    public function getMetrics(int $businessId, int $days = 30): array
    {
        $cacheKey = "metrics:business:{$businessId}:last{$days}";
        
        return Cache::remember($cacheKey, now()->addMinutes(15), function () use ($businessId, $days) {
            return BusinessMetric::forBusiness($businessId)
                ->lastDays($days)
                ->orderBy('metric_date')
                ->get()
                ->toArray();
        });
    }

    /**
     * Get summary metrics for a business
     */
    public function getSummary(int $businessId, int $days = 30): array
    {
        $cacheKey = "metrics:business:{$businessId}:summary:{$days}";
        
        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($businessId, $days) {
            $metrics = BusinessMetric::forBusiness($businessId)
                ->lastDays($days)
                ->get();
            if ($metrics->isEmpty()) {
                return $this->getEmptySummary();
            }

            $totalFeedback = $metrics->sum('total_feedback');
            $avgRating = $metrics->avg('avg_rating');
            $avgNps = $metrics->avg('nps');

            // Calculate trends (compare last 7 days vs previous 7 days)
            $recentMetrics = $metrics->where('metric_date', '>=', now()->subDays(7)->format('Y-m-d'));
            $previousMetrics = $metrics->whereBetween('metric_date', [
                now()->subDays(14)->format('Y-m-d'),
                now()->subDays(8)->format('Y-m-d')
            ]);

            $recentAvgRating = $recentMetrics->avg('avg_rating');
            $previousAvgRating = $previousMetrics->avg('avg_rating');
            $ratingTrend = $previousAvgRating > 0 
                ? round((($recentAvgRating - $previousAvgRating) / $previousAvgRating) * 100, 1)
                : 0;

            $recentNps = $recentMetrics->avg('nps');
            $previousNps = $previousMetrics->avg('nps');
            $npsTrend = $previousNps != 0
                ? round($recentNps - $previousNps, 1)
                : 0;

            // Sentiment distribution
            $sentimentDistribution = [
                'positive' => $metrics->sum('positive_count'),
                'neutral' => $metrics->sum('neutral_count'),
                'negative' => $metrics->sum('negative_count'),
                'not_determined' => $metrics->sum('not_determined_count'),
            ];

            // Top keywords aggregated
            $allKeywords = [];
            foreach ($metrics as $metric) {
                if ($metric->top_keywords) {
                    foreach ($metric->top_keywords as $keyword) {
                        $word = $keyword['word'] ?? '';
                        $count = $keyword['count'] ?? 0;
                        if ($word) {
                            $allKeywords[$word] = ($allKeywords[$word] ?? 0) + $count;
                        }
                    }
                }
            }
            arsort($allKeywords);
            $topKeywords = array_map(
                fn($word, $count) => ['word' => $word, 'count' => $count],
                array_keys(array_slice($allKeywords, 0, 20)),
                array_slice($allKeywords, 0, 20)
            );

            return [
                'total_feedback' => $totalFeedback,
                'avg_rating' => round($avgRating, 2),
                'avg_nps' => round($avgNps),
                'rating_trend' => $ratingTrend,
                'nps_trend' => $npsTrend,
                'sentiment_distribution' => $sentimentDistribution,
                'top_keywords' => $topKeywords,
                'nps_breakdown' => [
                    'promoters' => $metrics->sum('promoters'),
                    'passives' => $metrics->sum('passives'),
                    'detractors' => $metrics->sum('detractors'),
                ],
            ];
        });
    }

    /**
     * Get category average metrics for comparison
     */
    public function getCategoryAverage(int $categoryId, int $days = 30): array
    {
        $cacheKey = "metrics:category:{$categoryId}:avg:{$days}";
        
        return Cache::remember($cacheKey, now()->addHour(), function () use ($categoryId, $days) {
            $businessIds = Business::where('category_id', $categoryId)->pluck('id');

            if ($businessIds->isEmpty()) {
                return $this->getEmptySummary();
            }

            $metrics = BusinessMetric::whereIn('business_id', $businessIds)
                ->lastDays($days)
                ->get();

            if ($metrics->isEmpty()) {
                return $this->getEmptySummary();
            }

            return [
                'avg_rating' => round($metrics->avg('avg_rating'), 2),
                'avg_nps' => round($metrics->avg('nps')),
                'total_feedback' => $metrics->sum('total_feedback'),
                'sentiment_distribution' => [
                    'positive' => $metrics->sum('positive_count'),
                    'neutral' => $metrics->sum('neutral_count'),
                    'negative' => $metrics->sum('negative_count'),
                    'not_determined' => $metrics->sum('not_determined_count'),
                ],
            ];
        });
    }

    /**
     * Clear cache for a specific business
     */
    public function clearCache(int $businessId): void
    {
        $keys = [
            "metrics:business:{$businessId}:last7",
            "metrics:business:{$businessId}:last30",
            "metrics:business:{$businessId}:summary:7",
            "metrics:business:{$businessId}:summary:30",
        ];

        $business = Business::find($businessId);
        $categoryId = $business ? $business->category_id : null;

        if ($categoryId) {
            $keys[] = "metrics:category:{$categoryId}:avg:7";
            $keys[] = "metrics:category:{$categoryId}:avg:30";
        }

        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }

    /**
     * Get empty summary structure
     */
    protected function getEmptySummary(): array
    {
        return [
            'total_feedback' => 0,
            'avg_rating' => 0,
            'avg_nps' => 0,
            'rating_trend' => 0,
            'nps_trend' => 0,
            'sentiment_distribution' => [
                'positive' => 0,
                'neutral' => 0,
                'negative' => 0,
                'not_determined' => 0,
            ],
            'top_keywords' => [],
            'nps_breakdown' => [
                'promoters' => 0,
                'passives' => 0,
                'detractors' => 0,
            ],
        ];
    }
}
