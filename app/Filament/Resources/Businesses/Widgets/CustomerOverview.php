<?php

namespace App\Filament\Resources\Businesses\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Model;
use App\Models\Business;
use App\Services\MetricsService;

class CustomerOverview extends StatsOverviewWidget
{
    public ?Model $record = null;
    public array $summaryMetrics = [];
    protected MetricsService $metricsService;

    public function mount(?Model $record = null, array $summaryMetrics = []): void
    {
        $this->metricsService =new MetricsService();
        $this->record = $record;
        $this->summaryMetrics = $this->metricsService->getSummary($this->record->id, 30);
;
    }

    protected function getStats(): array
    {
        if (!$this->record || empty($this->summaryMetrics)) {
            return [
                Stat::make('Total Feedback', 'N/A')->icon('heroicon-o-chat-bubble-left-right')->color('gray'),
                Stat::make('Average Rating', 'N/A')->icon('heroicon-o-star')->color('gray'),
                Stat::make('NPS Score', 'N/A')->icon('heroicon-o-bolt')->color('gray'),
                Stat::make('Total Customers', 'N/A')->icon('heroicon-o-users')->color('gray'),
            ];
        }

        $business = $this->record;
        $totalFeedback = $this->summaryMetrics['total_feedback'] ?? 0;
        $avgRating = $this->summaryMetrics['avg_rating'] ?? 0;
        $npsScore = $this->summaryMetrics['avg_nps'] ?? 0;

        return [
            Stat::make('Total Feedback', $totalFeedback)
                ->description('Feedback in last 30 days')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->color('info'),

            Stat::make('Average Rating', $avgRating ? number_format($avgRating, 2) . ' / 5.00' : 'N/A')
                ->description('30-day average')
                ->icon('heroicon-o-star')
                ->color($avgRating >= 4.5 ? 'success' : ($avgRating >= 3.5 ? 'warning' : 'danger')),

            Stat::make('NPS Score', "{$npsScore}%")
                ->description('Net Promoter Score')
                ->icon('heroicon-o-bolt')
                ->color($npsScore >= 50 ? 'success' : ($npsScore >= 0 ? 'warning' : 'danger')),

            Stat::make('Total Customers', $business->customers()->count())
                ->description('Total unique customers')
                ->icon('heroicon-o-users')
                ->color('primary'),
        ];
    }
}