<?php

namespace App\Filament\Resources\Plans\RelationManagers;

use App\Models\Feature;
use Filament\Forms\Components\Placeholder;
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
use Filament\Actions\EditAction;
use Filament\Schemas\Components\Utilities\Get;

class PlanFeaturesRelationManager extends RelationManager
{
    protected static string $relationship = 'planFeatures';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('feature_id')
                ->relationship('feature', 'name')
                ->required()
                ->searchable()
                ->reactive()
                ->afterStateUpdated(function ($state, callable $set) {
                    $feature = $state ? Feature::find($state) : null;

                    if ($feature && $feature->type === 'quota' && $feature->default_unit) {
                        $set('unit', $feature->default_unit);
                    }
                }),
            Placeholder::make('feature_details')
                ->label('Feature details')
                ->content(function (Get $get) {
                    $featureId = $get('feature_id');
                    $feature = $featureId ? Feature::find($featureId) : null;

                    if (! $feature) {
                        return null;
                    }

                    $lines = [
                        'Key: ' . $feature->key,
                        'Category: ' . ($feature->category ?? '-'),
                        'Type: ' . $feature->type,
                    ];

                    if ($feature->type === 'quota') {
                        $lines[] = 'Default unit: ' . ($feature->default_unit ?? '-');
                    }

                    if ($feature->description) {
                        $lines[] = $feature->description;
                    }

                    return implode("\n", $lines);
                })
                ->columnSpanFull(),
            Toggle::make('is_enabled')
                ->required()
                ->reactive(),
            Toggle::make('is_unlimited')
                ->label('Unlimited')
                ->hidden(function (Get $get) {
                    $featureId = $get('feature_id');
                    $feature = $featureId ? Feature::find($featureId) : null;

                    if (! $feature || $feature->type !== 'quota') {
                        return true;
                    }

                    return ! $get('is_enabled');
                }),
            TextInput::make('quota')
                ->numeric()
                ->label('Quota')
                ->hidden(function (Get $get) {
                    $featureId = $get('feature_id');
                    $feature = $featureId ? Feature::find($featureId) : null;

                    if (! $feature || $feature->type !== 'quota') {
                        return true;
                    }

                    if (! $get('is_enabled')) {
                        return true;
                    }

                    return (bool) $get('is_unlimited');
                })
                ->required(function (Get $get) {
                    $featureId = $get('feature_id');
                    $feature = $featureId ? Feature::find($featureId) : null;

                    return $feature && $feature->type === 'quota' && $get('is_enabled') && ! $get('is_unlimited');
                }),
            TextInput::make('unit')
                ->label('Unit')
                ->hidden(function (Get $get) {
                    $featureId = $get('feature_id');
                    $feature = $featureId ? Feature::find($featureId) : null;

                    if (! $feature || $feature->type !== 'quota') {
                        return true;
                    }

                    return ! $get('is_enabled');
                }),
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
                // \Filament\Actions\CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make()->modalHeading('Edit Plan Feature'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
