<?php

namespace App\Filament\Resources\Businesses\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SettingsRelationManager extends RelationManager
{
    protected static string $relationship = 'settings';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('key')
                    ->label('Key')
                    ->searchable(),
                TextColumn::make('value')
                    ->label('Value'),
                TextColumn::make('description')
                    ->label('Description')
                    ->limit(50),
            ]);
    }
}
