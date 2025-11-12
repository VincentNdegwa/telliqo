<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static string $view = 'filament.pages.dashboard';

    public function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\StatsOverview::class,
            \App\Filament\Widgets\FeedbackRatingsChart::class,
            \App\Filament\Widgets\BusinessGrowthChart::class,
            \App\Filament\Widgets\FeedbackTrendsChart::class,
            \App\Filament\Widgets\CategoryDistributionChart::class,
            \App\Filament\Widgets\SentimentAnalysisChart::class,
            \App\Filament\Widgets\TopPerformingBusinesses::class,
            \App\Filament\Widgets\LatestBusinesses::class,
            \App\Filament\Widgets\RecentFeedback::class,
        ];
    }

    public function getColumns(): int | string | array
    {
        return [
            'default' => 1,
            'sm' => 2,
            'md' => 2,
            'lg' => 2,
            'xl' => 2,
            '2xl' => 3,
        ];
    }
}
