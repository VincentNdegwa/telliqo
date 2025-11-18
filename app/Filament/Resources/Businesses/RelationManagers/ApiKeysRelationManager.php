<?php

namespace App\Filament\Resources\Businesses\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ApiKeysRelationManager extends RelationManager
{
    protected static string $relationship = 'apiKeys';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('key')
                    ->label('Key')
                    ->limit(20),
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable(),
                TextColumn::make('last_used_at')
                    ->dateTime()
                    ->label('Last used')
                    ->sortable(),
            ]);
    }
}
