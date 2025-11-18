<?php

namespace App\Filament\Widgets;

use App\Models\Feedback;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class RatingDistributionChart extends ChartWidget
{
    // protected static ?string $heading = 'Rating Distribution';
    protected static ?int $sort = 8;

    public ?string $filter = '30';

    protected function getData(): array
    {
        $days = (int) $this->filter;
        $startDate = now()->subDays($days);

        $ratingData = Feedback::select('rating', DB::raw('COUNT(*) as count'))
            ->where('created_at', '>=', $startDate)
            ->groupBy('rating')
            ->orderBy('rating', 'desc')
            ->get();

        $ratings = [5, 4, 3, 2, 1];
        $counts = [];

        foreach ($ratings as $rating) {
            $counts[] = $ratingData->firstWhere('rating', $rating)?->count ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Number of Reviews',
                    'data' => $counts,
                    'backgroundColor' => [
                        'rgba(34, 197, 94, 0.8)',
                        'rgba(132, 204, 22, 0.8)',
                        'rgba(234, 179, 8, 0.8)',
                        'rgba(249, 115, 22, 0.8)',
                        'rgba(239, 68, 68, 0.8)',
                    ],
                ],
            ],
            'labels' => ['5 Stars', '4 Stars', '3 Stars', '2 Stars', '1 Star'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getFilters(): ?array
    {
        return [
            '7' => 'Last 7 days',
            '30' => 'Last 30 days',
            '90' => 'Last 90 days',
            '365' => 'This year',
        ];
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
