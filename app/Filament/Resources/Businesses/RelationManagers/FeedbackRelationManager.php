<?php

namespace App\Filament\Resources\Businesses\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FeedbackRelationManager extends RelationManager
{
    protected static string $relationship = 'feedback';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable(),
                TextColumn::make('rating')
                    ->sortable(),
                TextColumn::make('moderation_status')
                    ->label('Status'),
                IconColumn::make('is_public')
                    ->boolean(),
                TextColumn::make('submitted_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->recordUrl(fn ($record) => route('filament.admin.resources.feedback.edit', $record));
    }
}
