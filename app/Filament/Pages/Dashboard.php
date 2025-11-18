<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    /**
     * Filament v4 expects navigationIcon to be string|BackedEnum|Htmlable|null.
     */
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-home';

    // Use Filament's default dashboard view and widgets; override only if needed.

    public function getColumns(): int | array
    {
        return 12;
    }
}
