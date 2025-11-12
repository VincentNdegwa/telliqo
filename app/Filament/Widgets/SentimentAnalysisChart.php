<?php

namespace App\Filament\Widgets;

use App\Models\Feedback;
use App\Models\Enums\Sentiments;
use Filament\Widgets\ChartWidget;

class SentimentAnalysisChart extends ChartWidget
{
    protected static ?string $heading = 'Feedback Sentiment Analysis';

    protected static ?int $sort = 6;

    protected int | string | array $columnSpan = 1;

    protected function getData(): array
    {
        $sentiments = [
            'Positive' => Feedback::where('sentiment', Sentiments::POSITIVE)->count(),
            'Neutral' => Feedback::where('sentiment', Sentiments::NEUTRAL)->count(),
            'Negative' => Feedback::where('sentiment', Sentiments::NEGATIVE)->count(),
            'Not Determined' => Feedback::where('sentiment', Sentiments::NOT_DETERMINED)->count(),
        ];

        return [
            'datasets' => [
                [
                    'label' => 'Sentiment Count',
                    'data' => array_values($sentiments),
                    'backgroundColor' => [
                        'rgba(34, 197, 94, 0.7)',
                        'rgba(156, 163, 175, 0.7)',
                        'rgba(239, 68, 68, 0.7)',
                        'rgba(107, 114, 128, 0.7)',
                    ],
                ],
            ],
            'labels' => array_keys($sentiments),
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
        ];
    }
}
