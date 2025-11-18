<?php

namespace App\Filament\Resources\Feedback;

use App\Filament\Resources\Feedback\Pages\EditFeedback;
use App\Filament\Resources\Feedback\Pages\ListFeedback;
use App\Models\Feedback as FeedbackModel;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Table;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;

class FeedbackResource extends Resource
{
    protected static ?string $model = FeedbackModel::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?int $navigationSort = 30;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Placeholder::make('business.name')
                ->label('Business'),
            Placeholder::make('customer.name')
                ->label('Customer'),
            Placeholder::make('rating')
                ->label('Rating'),
            Placeholder::make('sentiment')
                ->label('Sentiment'),
            Placeholder::make('submitted_at')
                ->label('Submitted at'),
            Textarea::make('comment')
                ->label('Comment')
                ->rows(4),
            Select::make('moderation_status')
                ->options([
                    'pending' => 'Pending',
                    'approved' => 'Approved',
                    'rejected' => 'Rejected',
                ])
                ->required(),
            Toggle::make('is_public')
                ->label('Public'),
            Textarea::make('reply_text')
                ->label('Reply')
                ->rows(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('business.name')
                    ->label('Business')
                    ->searchable(),
                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable(),
                TextColumn::make('rating')
                    ->sortable(),
                TextColumn::make('sentiment')
                    ->sortable(),
                TextColumn::make('moderation_status')
                    ->label('Status'),
                IconColumn::make('is_public')
                    ->boolean(),
                TextColumn::make('submitted_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                // add filters later if needed
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFeedback::route('/'),
            'edit' => EditFeedback::route('/{record}/edit'),
        ];
    }
}
