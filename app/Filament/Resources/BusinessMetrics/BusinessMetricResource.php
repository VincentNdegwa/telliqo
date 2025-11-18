<?php

namespace App\Filament\Resources\BusinessMetrics;

use App\Filament\Resources\BusinessMetrics\Pages\ListBusinessMetrics;
use App\Filament\Resources\BusinessMetrics\Pages\ViewBusinessMetric;
use App\Models\BusinessMetric;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Placeholder;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\ViewAction;

class BusinessMetricResource extends Resource
{
    protected static ?string $model = BusinessMetric::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?int $navigationSort = 40;
    
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Placeholder::make('business.name')
                ->label('Business'),
            Placeholder::make('metric_date')
                ->label('Date'),
            Placeholder::make('total_feedback')
                ->label('Total feedback'),
            Placeholder::make('avg_rating')
                ->label('Average rating'),
            Placeholder::make('nps')
                ->label('NPS'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('business.name')
                    ->label('Business')
                    ->searchable(),
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
            ->recordActions([
                ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBusinessMetrics::route('/'),
            'view' => ViewBusinessMetric::route('/{record}'),
        ];
    }
}
