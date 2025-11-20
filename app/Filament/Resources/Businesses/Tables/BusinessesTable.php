<?php

namespace App\Filament\Resources\Businesses\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class BusinessesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('category.name')
                    ->label('Category')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(),
                TextColumn::make("owner.name")
                    ->label('Owner')
                    ->getStateUsing(function(Model $record) {
                        $owner = $record->owners()->first();
                        return $owner ? $owner->name : '-';
                    }),
                TextColumn::make('total_users')
                    ->label('Users')
                    ->getStateUsing(fn (Model $record) => $record->users()->count()),
                TextColumn::make('plan.name')
                    ->label('Plan'),
                IconColumn::make('is_active')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // you can add filters later (e.g. by is_active, category)
            ])
            ->recordActions([
                
                ViewAction::make(),
                EditAction::make(),
                \App\Filament\Resources\Businesses\Actions\AssignPlanAction::config(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
