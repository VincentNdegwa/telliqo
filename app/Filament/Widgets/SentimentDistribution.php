<?php

namespace App\Filament\Widgets;

use App\Models\Feedback;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class SentimentDistribution extends ChartWidget
{
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 1;

    public ?string $filter = '30';

    protected function getData(): array
    {
        $days = (int) $this->filter;
        $startDate = now()->subDays($days);

        $sentimentData = Feedback::select('sentiment', DB::raw('COUNT(*) as count'))
            ->where('created_at', '>=', $startDate)
            ->whereNotNull('sentiment')
            ->groupBy('sentiment')
            ->get();

        $positiveCount = $sentimentData->firstWhere('sentiment', 'positive')?->count ?? 0;
        $neutralCount = $sentimentData->firstWhere('sentiment', 'neutral')?->count ?? 0;
        $negativeCount = $sentimentData->firstWhere('sentiment', 'negative')?->count ?? 0;

        return [
            'datasets' => [
                [
                    'label' => 'Sentiment Distribution',
                    'data' => [$positiveCount, $neutralCount, $negativeCount],
                    'backgroundColor' => [
                        'rgba(34, 197, 94, 0.8)',   // green
                        'rgba(234, 179, 8, 0.8)',   // yellow
                        'rgba(239, 68, 68, 0.8)',   // red
                    ],
                ],
            ],
            'labels' => ['Positive', 'Neutral', 'Negative'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getFilters(): ?array
    {
        return [
            '7' => 'Last 7 days',
            '30' => 'Last 30 days',
            '90' => 'Last 90 days',
            '365' => 'This year',
        ];
    }
}
