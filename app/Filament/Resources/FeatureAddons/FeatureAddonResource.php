<?php

namespace App\Filament\Resources\FeatureAddons;

use App\Filament\Resources\FeatureAddons\Pages\CreateFeatureAddon;
use App\Filament\Resources\FeatureAddons\Pages\EditFeatureAddon;
use App\Filament\Resources\FeatureAddons\Pages\ListFeatureAddons;
use App\Models\FeatureAddon;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Table;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;

class FeatureAddonResource extends Resource
{
    protected static ?string $model = FeatureAddon::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-plus-circle';

    protected static ?int $navigationSort = 65;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('feature_id')
            ->options(fn () => \App\Models\Feature::where('type', 'quota')->pluck('name', 'id'))
            ->label('Feature')
                ->required(),
            TextInput::make('name')
                ->required(),
            TextInput::make('increment_quota')
                ->numeric()
                ->required(),
            TextInput::make('price_kes')
                ->numeric()
                ->required(),
            TextInput::make('price_usd')
                ->numeric()
                ->required(),
            TextInput::make('sort_order')
                ->numeric()
                ->default(0),
            Toggle::make('is_active')
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('feature.name')
                    ->label('Feature')
                    ->searchable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('increment_quota')
                    ->label('Increment'),
                TextColumn::make('price_kes')
                    ->label('Price (KES)')
                    ->money('kes'),
                TextColumn::make('price_usd')
                    ->label('Price (USD)')
                    ->money('usd'),
                IconColumn::make('is_active')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFeatureAddons::route('/'),
            'create' => CreateFeatureAddon::route('/create'),
            'edit' => EditFeatureAddon::route('/{record}/edit'),
        ];
    }
}
