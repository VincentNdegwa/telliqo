<?php

namespace App\Filament\Widgets;

use App\Models\Business;
use App\Models\User;
use App\Models\Feedback;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Businesses', Business::count())
                ->description('Active: ' . Business::where('is_active', true)->count())
                ->descriptionIcon('heroicon-o-building-office')
                ->color('success'),
            
            Stat::make('Total Users', User::count())
                ->description('Super Admins: ' . User::where('is_super_admin', true)->count())
                ->descriptionIcon('heroicon-o-users')
                ->color('primary'),
            
            Stat::make('Total Feedback', Feedback::count())
                ->description('Pending: ' . Feedback::where('moderation_status', 'pending')->count())
                ->descriptionIcon('heroicon-o-chat-bubble-left-right')
                ->color('warning'),
        ];
    }
}
