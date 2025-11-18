<?php

namespace App\Filament\Resources\Businesses\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MetricsRelationManager extends RelationManager
{
    protected static string $relationship = 'metrics';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('metric_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('total_feedback')
                    ->label('Feedback')
                    ->sortable(),
                TextColumn::make('avg_rating')
                    ->label('Avg rating')
                    ->sortable(),
                TextColumn::make('nps')
                    ->label('NPS')
                    ->sortable(),
            ])
            ->recordUrl(fn ($record) => route('filament.admin.resources.business-metrics.view', $record));
    }
}
