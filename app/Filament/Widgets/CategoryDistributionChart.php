<?php

namespace App\Filament\Widgets;

use App\Models\BusinessCategory;
use Filament\Widgets\ChartWidget;

class CategoryDistributionChart extends ChartWidget
{
    protected static ?string $heading = 'Businesses by Category';

    protected static ?int $sort = 5;

    protected int | string | array $columnSpan = 1;

    protected function getData(): array
    {
        $categories = BusinessCategory::query()
            ->select('business_categories.*')
            ->selectSub(function ($query) {
                $query->selectRaw('COUNT(*)')
                    ->from('businesses')
                    ->whereColumn('business_categories.id', 'businesses.category_id');
            }, 'businesses_count')
            ->whereExists(function ($query) {
                $query->selectRaw('1')
                    ->from('businesses')
                    ->whereColumn('business_categories.id', 'businesses.category_id');
            })
            ->orderByDesc('businesses_count')
            ->limit(10)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Businesses',
                    'data' => $categories->pluck('businesses_count')->toArray(),
                    'backgroundColor' => [
                        'rgba(99, 102, 241, 0.7)',
                        'rgba(139, 92, 246, 0.7)',
                        'rgba(168, 85, 247, 0.7)',
                        'rgba(236, 72, 153, 0.7)',
                        'rgba(239, 68, 68, 0.7)',
                        'rgba(251, 146, 60, 0.7)',
                        'rgba(234, 179, 8, 0.7)',
                        'rgba(34, 197, 94, 0.7)',
                        'rgba(16, 185, 129, 0.7)',
                        'rgba(6, 182, 212, 0.7)',
                    ],
                ],
            ],
            'labels' => $categories->pluck('name')->toArray(),
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
