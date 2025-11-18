<?php

namespace App\Filament\Widgets;

use App\Models\BusinessCategory;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class CategoryPerformanceChart extends ChartWidget
{
    protected static ?int $sort = 7;
    protected int | string | array $columnSpan = 12;

    protected function getData(): array
    {
        $categoryData = BusinessCategory::select('business_categories.name')
            ->selectRaw('COUNT(DISTINCT businesses.id) as business_count')
            ->selectRaw('COUNT(feedback.id) as feedback_count')
            ->selectRaw('ROUND(AVG(feedback.rating), 1) as avg_rating')
            ->leftJoin('businesses', 'business_categories.id', '=', 'businesses.category_id')
            ->leftJoin('feedback', 'businesses.id', '=', 'feedback.business_id')
            ->groupBy('business_categories.id', 'business_categories.name')
            ->orderByDesc('business_count')
            ->limit(10)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Businesses',
                    'data' => $categoryData->pluck('business_count')->toArray(),
                    'backgroundColor' => 'rgba(59, 130, 246, 0.6)',
                ],
                [
                    'label' => 'Total Feedback',
                    'data' => $categoryData->pluck('feedback_count')->toArray(),
                    'backgroundColor' => 'rgba(16, 185, 129, 0.6)',
                ],
            ],
            'labels' => $categoryData->pluck('name')->toArray(),
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
                ],
            ],
        ];
    }
}
