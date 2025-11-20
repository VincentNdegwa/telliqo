<?php

namespace App\Filament\Resources\Plans\RelationManagers;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

class PlanFeaturesRelationManager extends RelationManager
{
    protected static string $relationship = 'planFeatures';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('feature_id')
                ->relationship('feature', 'name')
                ->required(),
            Toggle::make('is_enabled')
                ->required(),
            Toggle::make('is_unlimited'),
            TextInput::make('quota')
                ->numeric()
                ->label('Quota'),
            TextInput::make('unit')
                ->label('Unit'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('feature.name')
                    ->label('Feature')
                    ->searchable(),
                IconColumn::make('is_enabled')
                    ->boolean()
                    ->label('Enabled'),
                IconColumn::make('is_unlimited')
                    ->boolean()
                    ->label('Unlimited'),
                TextColumn::make('quota')
                    ->label('Quota'),
                TextColumn::make('unit')
                    ->label('Unit'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                \Filament\Actions\CreateAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
