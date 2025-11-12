<?php

namespace App\Filament\Widgets;

use App\Models\Feedback;
use Filament\Widgets\ChartWidget;

class FeedbackRatingsChart extends ChartWidget
{
    protected static ?string $heading = 'Feedback Ratings Distribution';

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 1;

    protected function getData(): array
    {
        $ratings = Feedback::selectRaw('rating, COUNT(*) as count')
            ->groupBy('rating')
            ->orderBy('rating')
            ->pluck('count', 'rating')
            ->toArray();

        $data = [];
        for ($i = 1; $i <= 5; $i++) {
            $data[] = $ratings[$i] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Number of Feedbacks',
                    'data' => $data,
                    'backgroundColor' => [
                        'rgba(239, 68, 68, 0.7)',
                        'rgba(251, 146, 60, 0.7)',
                        'rgba(234, 179, 8, 0.7)',
                        'rgba(34, 197, 94, 0.7)',
                        'rgba(16, 185, 129, 0.7)',
                    ],
                    'borderColor' => [
                        'rgb(239, 68, 68)',
                        'rgb(251, 146, 60)',
                        'rgb(234, 179, 8)',
                        'rgb(34, 197, 94)',
                        'rgb(16, 185, 129)',
                    ],
                    'borderWidth' => 1,
                ],
            ],
            'labels' => ['1 Star', '2 Stars', '3 Stars', '4 Stars', '5 Stars'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'precision' => 0,
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
        ];
    }
}
