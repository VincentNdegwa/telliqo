<?php

namespace App\Filament\Widgets;

use App\Models\Business;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class TopBusinessesChart extends ChartWidget
{
    // protected static ?string $heading = 'Top 10 Businesses by Feedback';
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 12;

    public ?string $filter = 'feedback';

    protected function getData(): array
    {
        $query = Business::select('businesses.name')
            ->leftJoin('feedback', 'businesses.id', '=', 'feedback.business_id');

        if ($this->filter === 'feedback') {
            $data = $query->selectRaw('COUNT(feedback.id) as value')
                ->groupBy('businesses.id', 'businesses.name')
                ->orderByDesc('value')
                ->limit(10)
                ->get();
        } else {
            $data = $query->selectRaw('ROUND(AVG(feedback.rating), 1) as value')
                ->groupBy('businesses.id', 'businesses.name')
                ->having('value', '>', 0)
                ->orderByDesc('value')
                ->limit(10)
                ->get();
        }

        return [
            'datasets' => [
                [
                    'label' => $this->filter === 'feedback' ? 'Feedback Count' : 'Average Rating',
                    'data' => $data->pluck('value')->toArray(),
                    'backgroundColor' => [
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(16, 185, 129, 0.8)',
                        'rgba(245, 158, 11, 0.8)',
                        'rgba(239, 68, 68, 0.8)',
                        'rgba(139, 92, 246, 0.8)',
                        'rgba(236, 72, 153, 0.8)',
                        'rgba(14, 165, 233, 0.8)',
                        'rgba(132, 204, 22, 0.8)',
                        'rgba(251, 146, 60, 0.8)',
                        'rgba(168, 85, 247, 0.8)',
                    ],
                ],
            ],
            'labels' => $data->pluck('name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getFilters(): ?array
    {
        return [
            'feedback' => 'By Feedback Count',
            'rating' => 'By Average Rating',
        ];
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
            'scales' => [
                'x' => [
                    'beginAtZero' => true,
                ],
            ],
        ];
    }
}
