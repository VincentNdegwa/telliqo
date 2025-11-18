<?php

namespace App\Filament\Resources\Customers;

use App\Filament\Resources\Customers\Pages\CreateCustomer;
use App\Filament\Resources\Customers\Pages\EditCustomer;
use App\Filament\Resources\Customers\Pages\ListCustomers;
use App\Filament\Resources\Customers\RelationManagers;
use App\Models\Customer;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Table;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('business_id')
                ->relationship('business', 'name')
                ->searchable()
                ->required(),
            TextInput::make('name')
                ->required(),
            TextInput::make('email')
                ->email()
                ->required(),
            TextInput::make('phone')
                ->tel(),
            TextInput::make('company_name'),
            TextInput::make('tags')
                ->placeholder('Comma separated'),
            Textarea::make('notes')
                ->rows(3),
            Toggle::make('opted_out')
                ->label('Opted out'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('business.name')
                    ->label('Business')
                    ->searchable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('phone')
                    ->searchable(),
                TextColumn::make('total_requests_sent')
                    ->label('Requests')
                    ->sortable(),
                TextColumn::make('total_feedbacks')
                    ->label('Feedbacks')
                    ->sortable(),
                IconColumn::make('opted_out')
                    ->boolean(),
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
            RelationManagers\FeedbackRelationManager::class,
            RelationManagers\ReviewRequestsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCustomers::route('/'),
            'create' => CreateCustomer::route('/create'),
            'edit' => EditCustomer::route('/{record}/edit'),
        ];
    }
}
