<?php

namespace App\Filament\Widgets;

use App\Models\Feedback;
use App\Models\Enums\ModerationStatus;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentFeedback extends BaseWidget
{
    protected static ?string $heading = 'Recent Feedback';

    protected static ?int $sort = 8;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Feedback::with('business')->latest()->limit(15)
            )
            ->columns([
                Tables\Columns\TextColumn::make('business.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer_name')
                    ->searchable()
                    ->default('Anonymous'),
                Tables\Columns\TextColumn::make('rating')
                    ->sortable()
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state >= 4 => 'success',
                        $state === 3 => 'warning',
                        default => 'danger',
                    }),
                Tables\Columns\TextColumn::make('sentiment')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state?->label() ?? 'N/A')
                    ->color(fn ($state): string => match ($state) {
                        \App\Models\Enums\Sentiments::POSITIVE => 'success',
                        \App\Models\Enums\Sentiments::NEUTRAL => 'gray',
                        \App\Models\Enums\Sentiments::NEGATIVE => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('moderation_status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state?->label() ?? 'N/A')
                    ->color(fn ($state): string => match ($state) {
                        ModerationStatus::PUBLISHED => 'success',
                        ModerationStatus::SOFT_FLAGGED => 'info',
                        ModerationStatus::FLAGGED => 'warning',
                        ModerationStatus::HIDDEN => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('submitted_at')
                    ->dateTime()
                    ->sortable()
                    ->since(),
            ]);
    }
}
