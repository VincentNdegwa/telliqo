<?php

namespace App\Filament\Resources\Businesses\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Model;

class CustomerEngagementStats extends StatsOverviewWidget
{
    public ?Model $record = null;
    
    protected int | string | array $columnSpan = 12;

    protected function getStats(): array
    {
        if (!$this->record) {
            return [];
        }

        $totalCustomers = $this->record->customers()->count();
        $customersWithFeedback = $this->record->customers()->has('feedback')->count();
        $repeatCustomers = $this->record->customers()
            ->has('feedback', '>', 1)
            ->count();
        
        $engagementRate = $totalCustomers > 0 
            ? round(($customersWithFeedback / $totalCustomers) * 100, 1) 
            : 0;
        
        $repeatRate = $customersWithFeedback > 0 
            ? round(($repeatCustomers / $customersWithFeedback) * 100, 1) 
            : 0;

        // Review requests stats
        $totalRequests = $this->record->reviewRequests()->count();
        $completedRequests = $this->record->reviewRequests()
            ->whereNotNull('completed_at')
            ->count();
        $conversionRate = $totalRequests > 0 
            ? round(($completedRequests / $totalRequests) * 100, 1) 
            : 0;

        return [
            Stat::make('Customer Engagement', "{$engagementRate}%")
                ->description("{$customersWithFeedback} of {$totalCustomers} customers provided feedback")
                ->icon('heroicon-o-user-group')
                ->color($engagementRate >= 50 ? 'success' : ($engagementRate >= 25 ? 'warning' : 'danger')),
            
            Stat::make('Repeat Customers', "{$repeatRate}%")
                ->description("{$repeatCustomers} customers gave multiple feedback")
                ->icon('heroicon-o-arrow-path')
                ->color($repeatRate >= 30 ? 'success' : 'info'),
            
            Stat::make('Review Requests', $totalRequests)
                ->description("{$completedRequests} completed ({$conversionRate}% conversion)")
                ->icon('heroicon-o-envelope')
                ->color('primary'),
            
            Stat::make('Avg Feedback/Customer', 
                $customersWithFeedback > 0 
                    ? number_format($this->record->feedback()->count() / $customersWithFeedback, 2)
                    : '0'
            )
                ->description('Feedback per engaged customer')
                ->icon('heroicon-o-chart-bar')
                ->color('info'),
        ];
    }
}
