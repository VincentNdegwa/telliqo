<?php

namespace App\Filament\Resources\Businesses\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Model;

class NPSBreakdownChart extends ChartWidget
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

        // Promoters: 4-5 stars
        $promoters = $this->record->feedback()->whereIn('rating', [4, 5])->count();
        
        // Passives: 3 stars
        $passives = $this->record->feedback()->where('rating', 3)->count();
        
        // Detractors: 1-2 stars
        $detractors = $this->record->feedback()->whereIn('rating', [1, 2])->count();

        return [
            'datasets' => [
                [
                    'data' => [$promoters, $passives, $detractors],
                    'backgroundColor' => [
                        '#22c55e', // green for promoters
                        '#eab308', // yellow for passives
                        '#ef4444', // red for detractors
                    ],
                ],
            ],
            'labels' => [
                'Promoters (4-5★)',
                'Passives (3★)',
                'Detractors (1-2★)',
            ],
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
