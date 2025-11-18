<?php

namespace App\Filament\Widgets;

use App\Models\Business;
use App\Models\User;
use App\Models\Feedback;
use App\Models\BusinessCategory;
use App\Models\ReviewRequest;
use App\Models\Customer;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        // Business Stats
        $totalBusinesses = Business::count();
        $activeBusinesses = Business::where('is_active', true)->count();
        $businessesThisMonth = Business::whereMonth('created_at', now()->month)->count();
        $businessesLastMonth = Business::whereMonth('created_at', now()->subMonth()->month)->count();
        $businessGrowth = $businessesLastMonth > 0 
            ? round((($businessesThisMonth - $businessesLastMonth) / $businessesLastMonth) * 100, 1) 
            : 0;

        // User Stats
        $totalUsers = User::count();
        $usersThisMonth = User::whereMonth('created_at', now()->month)->count();
        $usersLastMonth = User::whereMonth('created_at', now()->subMonth()->month)->count();
        $userGrowth = $usersLastMonth > 0 
            ? round((($usersThisMonth - $usersLastMonth) / $usersLastMonth) * 100, 1) 
            : 0;

        // Feedback Stats
        $totalFeedback = Feedback::count();
        $approvedFeedback = Feedback::where('moderation_status', 'approved')->count();
        $pendingFeedback = Feedback::where('moderation_status', 'soft_flagged')->count();
        $flaggedFeedback = Feedback::where('moderation_status', 'hard_flagged')->count();
        $feedbackThisMonth = Feedback::whereMonth('created_at', now()->month)->count();
        $feedbackLastMonth = Feedback::whereMonth('created_at', now()->subMonth()->month)->count();
        $feedbackGrowth = $feedbackLastMonth > 0 
            ? round((($feedbackThisMonth - $feedbackLastMonth) / $feedbackLastMonth) * 100, 1) 
            : 0;

        // Rating Stats
        $averageRating = round(Feedback::avg('rating') ?? 0, 1);
        $ratingThisMonth = round(Feedback::whereMonth('created_at', now()->month)->avg('rating') ?? 0, 1);
        $ratingLastMonth = round(Feedback::whereMonth('created_at', now()->subMonth()->month)->avg('rating') ?? 0, 1);
        $ratingChange = round($ratingThisMonth - $ratingLastMonth, 1);

        // Category Stats
        $totalCategories = BusinessCategory::count();
        $categoriesWithBusinesses = BusinessCategory::has('businesses')->count();

        // Customer Stats
        $totalCustomers = Customer::count();
        $customersWithFeedback = Customer::has('feedback')->count();

        return [
            Stat::make('Total Businesses', $totalBusinesses)
                ->description(
                    ($businessGrowth >= 0 ? '+' : '') . $businessGrowth . '% from last month'
                )
                ->descriptionIcon($businessGrowth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->chart($this->getBusinessTrend())
                ->color($businessGrowth >= 0 ? 'success' : 'danger'),
            
            Stat::make('Active Businesses', $activeBusinesses)
                ->description('Inactive: ' . ($totalBusinesses - $activeBusinesses))
                ->descriptionIcon('heroicon-o-building-office')
                ->color('info'),
            
            Stat::make('Total Users', $totalUsers)
                ->description(
                    ($userGrowth >= 0 ? '+' : '') . $userGrowth . '% from last month'
                )
                ->descriptionIcon($userGrowth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->chart($this->getUserTrend())
                ->color($userGrowth >= 0 ? 'success' : 'danger'),
            
            Stat::make('Total Feedback', $totalFeedback)
                ->description(
                    ($feedbackGrowth >= 0 ? '+' : '') . $feedbackGrowth . '% from last month'
                )
                ->descriptionIcon($feedbackGrowth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->chart($this->getFeedbackTrend())
                ->color($feedbackGrowth >= 0 ? 'success' : 'danger'),
            
            Stat::make('Pending Review', $pendingFeedback)
                ->description('Flagged: ' . $flaggedFeedback . ' | Approved: ' . $approvedFeedback)
                ->descriptionIcon('heroicon-o-flag')
                ->color('warning'),
            
            Stat::make('Average Rating', $averageRating . ' / 5')
                ->description(
                    ($ratingChange >= 0 ? '+' : '') . $ratingChange . ' from last month'
                )
                ->descriptionIcon($ratingChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($averageRating >= 4 ? 'success' : ($averageRating >= 3 ? 'warning' : 'danger')),
            
            Stat::make('Total Customers', $totalCustomers)
                ->description($customersWithFeedback . ' have provided feedback')
                ->descriptionIcon('heroicon-o-users')
                ->color('info'),
            
            Stat::make('Categories', $totalCategories)
                ->description($categoriesWithBusinesses . ' have businesses')
                ->descriptionIcon('heroicon-o-tag')
                ->color('gray'),
        ];
    }

    private function getBusinessTrend(): array
    {
        return Business::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count')
            ->toArray();
    }

    private function getUserTrend(): array
    {
        return User::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count')
            ->toArray();
    }

    private function getFeedbackTrend(): array
    {
        return Feedback::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count')
            ->toArray();
    }
}
