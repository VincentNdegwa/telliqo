<?php

namespace App\Filament\Resources\Businesses;

use App\Filament\Resources\Businesses\Pages\CreateBusiness;
use App\Filament\Resources\Businesses\Pages\EditBusiness;
use App\Filament\Resources\Businesses\Pages\ListBusinesses;
use App\Filament\Resources\Businesses\Pages\ViewBusiness;
use App\Filament\Resources\Businesses\RelationManagers;
use App\Filament\Resources\Businesses\Schemas\BusinessForm;
use App\Filament\Resources\Businesses\Tables\BusinessesTable;
use App\Filament\Resources\Businesses\Widgets\CustomerOverview;
use App\Models\Business;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BusinessResource extends Resource
{
    protected static ?string $model = Business::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?int $navigationSort = 0;

    public static function form(Schema $schema): Schema
    {
        return BusinessForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BusinessesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\UsersRelationManager::class,
            RelationManagers\RolesRelationManager::class,
            RelationManagers\FeedbackRelationManager::class,
            RelationManagers\CustomersRelationManager::class,
            RelationManagers\ReviewRequestsRelationManager::class,
            RelationManagers\MetricsRelationManager::class,
            RelationManagers\SettingsRelationManager::class,
            RelationManagers\ApiKeysRelationManager::class,
        ];
    }

    public static function getWidgets(): array
    {
        return [
            CustomerOverview::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBusinesses::route('/'),
            'create' => CreateBusiness::route('/create'),
            'view' => ViewBusiness::route('/{record}'),
            'edit' => EditBusiness::route('/{record}/edit'),
        ];
    }
}