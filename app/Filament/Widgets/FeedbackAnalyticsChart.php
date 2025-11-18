<?php

namespace App\Filament\Widgets;

use App\Models\Feedback;
use App\Models\Enums\Sentiments;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class FeedbackAnalyticsChart extends ChartWidget
{
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 12;



    protected function getData(): array
    {
        $last30Days = now()->subDays(30);
        
        // Get daily feedback counts for the last 30 days
        $feedbackData = Feedback::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', $last30Days)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $dates = [];
        $counts = [];
        
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $dates[] = now()->subDays($i)->format('M d');
            $counts[] = $feedbackData->firstWhere('date', $date)?->count ?? 0;
        }

        // Get sentiment distribution
        $sentimentData = Feedback::select('sentiment', DB::raw('COUNT(*) as count'))
            ->whereNotNull('sentiment')
            ->groupBy('sentiment')
            ->get();

        $positiveCount = $sentimentData->firstWhere('sentiment', 'positive')?->count ?? 0;
        $neutralCount = $sentimentData->firstWhere('sentiment', 'neutral')?->count ?? 0;
        $negativeCount = $sentimentData->firstWhere('sentiment', 'negative')?->count ?? 0;

        return [
            'datasets' => [
                [
                    'label' => 'Daily Feedback',
                    'data' => $counts,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'fill' => true,
                ],
            ],
            'labels' => $dates,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                ],
            ],
        ];
    }
}
