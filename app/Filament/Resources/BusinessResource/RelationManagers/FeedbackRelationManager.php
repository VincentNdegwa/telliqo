<?php

namespace App\Filament\Resources\BusinessResource\RelationManagers;

use App\Models\Enums\ModerationStatus;
use App\Models\Enums\Sentiments;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class FeedbackRelationManager extends RelationManager
{
    protected static string $relationship = 'feedback';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('customer_name')
                    ->maxLength(255),
                Forms\Components\TextInput::make('customer_email')
                    ->email()
                    ->maxLength(255),
                Forms\Components\TextInput::make('rating')
                    ->numeric()
                    ->required()
                    ->minValue(1)
                    ->maxValue(5),
                Forms\Components\Textarea::make('comment')
                    ->columnSpanFull(),
                Forms\Components\Select::make('sentiment')
                    ->options([
                        Sentiments::POSITIVE->value => 'Positive',
                        Sentiments::NEUTRAL->value => 'Neutral',
                        Sentiments::NEGATIVE->value => 'Negative',
                        Sentiments::NOT_DETERMINED->value => 'Not Determined',
                    ]),
                Forms\Components\Select::make('moderation_status')
                    ->options([
                        ModerationStatus::PUBLISHED->value => 'Published',
                        ModerationStatus::SOFT_FLAGGED->value => 'Needs Review',
                        ModerationStatus::FLAGGED->value => 'Flagged',
                        ModerationStatus::HIDDEN->value => 'Hidden',
                    ])
                    ->required(),
                Forms\Components\Toggle::make('is_public')
                    ->default(false),
                Forms\Components\Textarea::make('reply_text')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('customer_name')
            ->columns([
                Tables\Columns\TextColumn::make('customer_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer_email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('rating')
                    ->sortable()
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state >= 4 => 'success',
                        $state === 3 => 'warning',
                        default => 'danger',
                    }),
                Tables\Columns\TextColumn::make('sentiment')
                    ->badge()
                    ->formatStateUsing(fn (?Sentiments $state): string => $state?->label() ?? 'N/A')
                    ->color(fn (?Sentiments $state): string => match ($state) {
                        Sentiments::POSITIVE => 'success',
                        Sentiments::NEUTRAL => 'gray',
                        Sentiments::NEGATIVE => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('moderation_status')
                    ->badge()
                    ->formatStateUsing(fn (?ModerationStatus $state): string => $state?->label() ?? 'N/A')
                    ->color(fn (?ModerationStatus $state): string => match ($state) {
                        ModerationStatus::PUBLISHED => 'success',
                        ModerationStatus::SOFT_FLAGGED => 'info',
                        ModerationStatus::FLAGGED => 'warning',
                        ModerationStatus::HIDDEN => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('is_public')
                    ->boolean(),
                Tables\Columns\TextColumn::make('submitted_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('rating')
                    ->options([
                        1 => '1 Star',
                        2 => '2 Stars',
                        3 => '3 Stars',
                        4 => '4 Stars',
                        5 => '5 Stars',
                    ]),
                Tables\Filters\SelectFilter::make('moderation_status')
                    ->options([
                        ModerationStatus::PUBLISHED->value => 'Published',
                        ModerationStatus::SOFT_FLAGGED->value => 'Needs Review',
                        ModerationStatus::FLAGGED->value => 'Flagged',
                        ModerationStatus::HIDDEN->value => 'Hidden',
                    ]),
                Tables\Filters\SelectFilter::make('sentiment')
                    ->options([
                        Sentiments::POSITIVE->value => 'Positive',
                        Sentiments::NEUTRAL->value => 'Neutral',
                        Sentiments::NEGATIVE->value => 'Negative',
                        Sentiments::NOT_DETERMINED->value => 'Not Determined',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('submitted_at', 'desc');
    }
}
