<?php

namespace App\Filament\Resources\Businesses\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Model;

class SentimentDonutChart extends ChartWidget
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

        $positive = $this->record->feedback()->where('sentiment', 'positive')->count();
        $neutral = $this->record->feedback()->where('sentiment', 'neutral')->count();
        $negative = $this->record->feedback()->where('sentiment', 'negative')->count();

        return [
            'datasets' => [
                [
                    'data' => [$positive, $neutral, $negative],
                    'backgroundColor' => [
                        '#22c55e', // green for positive
                        '#eab308', // yellow for neutral
                        '#ef4444', // red for negative
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
