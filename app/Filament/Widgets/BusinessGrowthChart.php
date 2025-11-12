<?php

namespace App\Filament\Widgets;

use App\Models\Business;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class BusinessGrowthChart extends ChartWidget
{
    protected static ?string $heading = 'Business Growth (Last 30 Days)';

    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 2;

    protected function getData(): array
    {
        $data = Business::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $labels = [];
        $values = [];

        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $labels[] = now()->subDays($i)->format('M d');
            $values[] = $data->where('date', $date)->first()->count ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'New Businesses',
                    'data' => $values,
                    'fill' => true,
                    'backgroundColor' => 'rgba(99, 102, 241, 0.2)',
                    'borderColor' => 'rgb(99, 102, 241)',
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
