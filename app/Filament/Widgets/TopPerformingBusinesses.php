<?php

namespace App\Filament\Widgets;

use App\Models\Business;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\DB;

class TopPerformingBusinesses extends BaseWidget
{
    protected static ?string $heading = 'Top Performing Businesses';

    protected static ?int $sort = 7;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Business::query()
                    ->select('businesses.*')
                    ->selectSub(function ($query) {
                        $query->selectRaw('COUNT(*)')
                            ->from('feedback')
                            ->whereColumn('businesses.id', 'feedback.business_id');
                    }, 'feedback_count')
                    ->selectSub(function ($query) {
                        $query->selectRaw('AVG(rating)')
                            ->from('feedback')
                            ->whereColumn('businesses.id', 'feedback.business_id');
                    }, 'feedback_avg_rating')
                    ->whereExists(function ($query) {
                        $query->select(DB::raw(1))
                            ->from('feedback')
                            ->whereColumn('businesses.id', 'feedback.business_id');
                    })
                    ->orderByDesc('feedback_avg_rating')
                    ->orderByDesc('feedback_count')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('category.name')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('feedback_count')
                    ->label('Total Feedback')
                    ->sortable()
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('feedback_avg_rating')
                    ->label('Avg Rating')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 2) . ' / 5' : 'N/A')
                    ->badge()
                    ->color(fn ($state): string => match (true) {
                        $state >= 4 => 'success',
                        $state >= 3 => 'warning',
                        $state > 0 => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->since(),
            ]);
    }
}
