<?php

namespace App\Filament\Resources\ReviewRequests;

use App\Filament\Resources\ReviewRequests\Pages\EditReviewRequest;
use App\Filament\Resources\ReviewRequests\Pages\ListReviewRequests;
use App\Models\ReviewRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;

class ReviewRequestResource extends Resource
{
    protected static ?string $model = ReviewRequest::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-envelope';

    protected static ?int $navigationSort = 20;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Placeholder::make('business.name')
                ->label('Business'),
            Placeholder::make('customer.name')
                ->label('Customer'),
            Placeholder::make('status')
                ->label('Status'),
            Placeholder::make('send_mode')
                ->label('Mode'),
            Placeholder::make('sent_at')
                ->label('Sent at'),
            Placeholder::make('opened_at')
                ->label('Opened at'),
            Placeholder::make('completed_at')
                ->label('Completed at'),
            Placeholder::make('expires_at')
                ->label('Expires at'),
            Textarea::make('message')
                ->rows(4)
                ->label('Message'),
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
                TextColumn::make('status')
                    ->sortable(),
                TextColumn::make('send_mode')
                    ->label('Mode'),
                TextColumn::make('sent_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('expires_at')
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
            'index' => ListReviewRequests::route('/'),
            'edit' => EditReviewRequest::route('/{record}/edit'),
        ];
    }
}
