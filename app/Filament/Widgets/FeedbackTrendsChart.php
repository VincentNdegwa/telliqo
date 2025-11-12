<?php

namespace App\Filament\Widgets;

use App\Models\Feedback;
use Filament\Widgets\ChartWidget;

class FeedbackTrendsChart extends ChartWidget
{
    protected static ?string $heading = 'Feedback Trends (Last 30 Days)';

    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = 2;

    protected function getData(): array
    {
        $data = Feedback::selectRaw('DATE(created_at) as date, COUNT(*) as count')
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
                    'label' => 'Feedback Received',
                    'data' => $values,
                    'fill' => true,
                    'backgroundColor' => 'rgba(234, 179, 8, 0.2)',
                    'borderColor' => 'rgb(234, 179, 8)',
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
