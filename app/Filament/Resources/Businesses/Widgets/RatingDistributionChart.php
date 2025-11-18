<?php

namespace App\Filament\Resources\Businesses\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Model;

class RatingDistributionChart extends ChartWidget
{
    
    public ?Model $record = null;
    
    protected int | string | array $columnSpan = 1;


    protected function getData(): array
    {
        if (!$this->record) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        $ratings = [];
        $colors = [
            '#ef4444', // 1 star - red
            '#f97316', // 2 stars - orange
            '#eab308', // 3 stars - yellow
            '#3b82f6', // 4 stars - blue
            '#22c55e', // 5 stars - green
        ];

        for ($i = 1; $i <= 5; $i++) {
            $count = $this->record->feedback()
                ->where('rating', $i)
                ->count();
            $ratings[] = $count;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Number of Reviews',
                    'data' => $ratings,
                    'backgroundColor' => $colors,
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
                        'stepSize' => 1,
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
