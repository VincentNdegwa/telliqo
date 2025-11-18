<?php

namespace App\Filament\Resources\Businesses\RelationManagers;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Form as ComponentsForm;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CustomersRelationManager extends RelationManager
{
    protected static string $relationship = 'customers';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
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
            ->recordUrl(fn ($record) => route('filament.admin.resources.customers.edit', $record));
    }


}
