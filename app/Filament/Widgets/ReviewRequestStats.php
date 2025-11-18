<?php

namespace App\Filament\Widgets;

use App\Models\ReviewRequest;
use App\Models\Customer;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class ReviewRequestStats extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 12;
    protected function getStats(): array
    {
        $totalRequests = ReviewRequest::count();
        $completedRequests = ReviewRequest::whereHas('feedback')->count();
        $pendingRequests = $totalRequests - $completedRequests;
        $conversionRate = $totalRequests > 0 
            ? round(($completedRequests / $totalRequests) * 100, 1) 
            : 0;

        $requestsThisMonth = ReviewRequest::whereMonth('created_at', now()->month)->count();
        $requestsLastMonth = ReviewRequest::whereMonth('created_at', now()->subMonth()->month)->count();
        $requestGrowth = $requestsLastMonth > 0 
            ? round((($requestsThisMonth - $requestsLastMonth) / $requestsLastMonth) * 100, 1) 
            : 0;

        $totalCustomers = Customer::count();
        $customersWithFeedback = Customer::whereHas('feedback')->count();
        $activeCustomerRate = $totalCustomers > 0 
            ? round(($customersWithFeedback / $totalCustomers) * 100, 1) 
            : 0;

        return [
            Stat::make('Total Review Requests', $totalRequests)
                ->description(
                    ($requestGrowth >= 0 ? '+' : '') . $requestGrowth . '% from last month'
                )
                ->descriptionIcon($requestGrowth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($requestGrowth >= 0 ? 'success' : 'danger'),
            
            Stat::make('Conversion Rate', $conversionRate . '%')
                ->description($completedRequests . ' of ' . $totalRequests . ' completed')
                ->descriptionIcon('heroicon-o-check-badge')
                ->color($conversionRate >= 50 ? 'success' : ($conversionRate >= 25 ? 'warning' : 'danger')),
            
            Stat::make('Pending Requests', $pendingRequests)
                ->description('Awaiting customer response')
                ->descriptionIcon('heroicon-o-clock')
                ->color('warning'),
            
            Stat::make('Total Customers', $totalCustomers)
                ->description($activeCustomerRate . '% have provided feedback')
                ->descriptionIcon('heroicon-o-users')
                ->color('info'),
        ];
    }
}
