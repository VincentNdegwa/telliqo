<?php

namespace App\Filament\Resources\Businesses\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Model;

class MonthlyPerformanceChart extends ChartWidget
{
    
    public ?Model $record = null;
    

    protected int | string | array $columnSpan = 12;

    protected function getData(): array
    {
        if (!$this->record) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        $feedbackData = [];
        $avgRatingData = [];
        $labels = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $labels[] = $date->format('M Y');
            
            // Feedback count
            $feedbackCount = $this->record->feedback()
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
            $feedbackData[] = $feedbackCount;
            
            // Average rating
            $avgRating = $this->record->feedback()
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->avg('rating');
            $avgRatingData[] = $avgRating ? round($avgRating, 2) : 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Feedback Count',
                    'data' => $feedbackData,
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'yAxisID' => 'y',
                ],
                [
                    'label' => 'Average Rating',
                    'data' => $avgRatingData,
                    'borderColor' => '#22c55e',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                    'yAxisID' => 'y1',
                ],
            ],
            'labels' => $labels,
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
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'left',
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Feedback Count',
                    ],
                ],
                'y1' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'right',
                    'min' => 0,
                    'max' => 5,
                    'title' => [
                        'display' => true,
                        'text' => 'Average Rating',
                    ],
                    'grid' => [
                        'drawOnChartArea' => false,
                    ],
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
