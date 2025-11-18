<?php

namespace App\Filament\Resources\BusinessCategories;

use App\Filament\Resources\BusinessCategories\Pages\CreateBusinessCategory;
use App\Filament\Resources\BusinessCategories\Pages\EditBusinessCategory;
use App\Filament\Resources\BusinessCategories\Pages\ListBusinessCategories;
use App\Models\BusinessCategory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Table;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;

class BusinessCategoryResource extends Resource
{
    protected static ?string $model = BusinessCategory::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-tag';
    
    protected static ?int $navigationSort = 50;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->required(),
            TextInput::make('slug')
                ->required(),
            TextInput::make('icon'),
            Textarea::make('description')
                ->columnSpanFull(),
            Toggle::make('is_active')
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('slug')
                    ->searchable(),
                TextColumn::make('icon')
                    ->searchable(),
                IconColumn::make('is_active')
                    ->boolean(),
                TextColumn::make('businesses_count')
                    ->counts('businesses')
                    ->label('Businesses'),
            ])
            ->filters([
                // add filters later if needed
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
            'index' => ListBusinessCategories::route('/'),
            'create' => CreateBusinessCategory::route('/create'),
            'edit' => EditBusinessCategory::route('/{record}/edit'),
        ];
    }
}
