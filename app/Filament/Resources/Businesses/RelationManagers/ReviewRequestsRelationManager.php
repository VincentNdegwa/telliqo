<?php

namespace App\Filament\Resources\Businesses\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ReviewRequestsRelationManager extends RelationManager
{
    protected static string $relationship = 'reviewRequests';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable(),
                TextColumn::make('status')
                    ->sortable(),
                TextColumn::make('send_mode')
                    ->label('Mode'),
                TextColumn::make('sent_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('expires_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->recordUrl(fn ($record) => route('filament.admin.resources.review-requests.edit', $record));
    }
}
