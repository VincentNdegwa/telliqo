<?php

namespace App\Filament\Resources\Plans;

use App\Filament\Resources\Plans\Pages\CreatePlan;
use App\Filament\Resources\Plans\Pages\EditPlan;
use App\Filament\Resources\Plans\Pages\ListPlans;
use App\Filament\Resources\Plans\RelationManagers\PlanFeaturesRelationManager;
use App\Models\Plan;
use App\Models\Feature;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Table;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;

class PlanResource extends Resource
{
    protected static ?string $model = Plan::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-credit-card';

    protected static ?int $navigationSort = 60;

    public static function form(Schema $schema): Schema
    {
        $featureSections = [];

        $features = Feature::orderBy('category')->orderBy('name')->get();

        foreach ($features as $feature) {
            $baseKey = 'features.' . $feature->key;

            $components = [
                Toggle::make($baseKey . '.enabled')
                    ->label($feature->name . ' (' . $feature->key . ')')
                    ->helperText(trim(($feature->category ? $feature->category . ' · ' : '') . ($feature->description ?? ''))),
            ];

            if ($feature->type === 'quota') {
                $components[] = Toggle::make($baseKey . '.is_unlimited')
                    ->label('Unlimited');

                $components[] = TextInput::make($baseKey . '.quota')
                    ->numeric()
                    ->label('Quota');

                $components[] = TextInput::make($baseKey . '.unit')
                    ->label('Unit')
                    ->default($feature->default_unit);
            }

            $featureSections[] = Section::make($feature->name)
                ->schema($components)
                ->columns(4);
        }

        return $schema->components([
            Section::make('Plan details')
                ->columnSpanFull()
                ->schema([
                    TextInput::make('key')
                        ->required()
                        ->unique(ignoreRecord: true),
                    TextInput::make('name')
                        ->required(),
                    Textarea::make('description')
                        ->columnSpanFull(),
                    TextInput::make('price_kes')
                        ->numeric()
                        ->required()
                        ->label('Price (KES / month)'),
                    TextInput::make('price_usd')
                        ->numeric()
                        ->required()
                        ->label('Price (USD / month)'),
                    TextInput::make('price_kes_yearly')
                        ->numeric()
                        ->label('Price (KES / year)'),
                    TextInput::make('price_usd_yearly')
                        ->numeric()
                        ->label('Price (USD / year)'),
                    TextInput::make('sort_order')
                        ->numeric()
                        ->default(0),
                    Toggle::make('is_active')
                        ->required(),
                ]),
            Section::make('Features')
                ->columnSpanFull()
                ->collapsible()
                ->collapsed()
                ->schema($featureSections)
                ->columns(1),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('key')
                    ->label('Key')
                    ->searchable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('price_kes')
                    ->label('Price (KES / month)')
                    ->money('kes'),
                TextColumn::make('price_usd')
                    ->label('Price (USD / month)')
                    ->money('usd'),
                TextColumn::make('price_kes_yearly')
                    ->label('Price (KES / year)')
                    ->money('kes'),
                TextColumn::make('price_usd_yearly')
                    ->label('Price (USD / year)')
                    ->money('usd'),
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

    public static function getRelations(): array
    {
        return [
            PlanFeaturesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPlans::route('/'),
            'create' => CreatePlan::route('/create'),
            'edit' => EditPlan::route('/{record}/edit'),
        ];
    }
}
