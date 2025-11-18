<?php

namespace App\Filament\Widgets;

use App\Models\Feedback;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class ModerationQueueTable extends BaseWidget
{
    protected static ?int $sort = 6;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Pending Moderation')
            ->query(
                Feedback::query()
                    ->with(['business', 'customer'])
                    ->where('moderation_status', 'soft_flagged')
                    ->latest()
            )
            ->columns([
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable(),
                Tables\Columns\TextColumn::make('business.name')
                    ->label('Business')
                    ->searchable(),
                Tables\Columns\TextColumn::make('rating')
                    ->badge()
                    ->color(fn ($state) => match(true) {
                        $state >= 4 => 'success',
                        $state >= 3 => 'warning',
                        default => 'danger',
                    }),
                Tables\Columns\TextColumn::make('comment')
                    ->limit(50)
                    ->wrap(),
                Tables\Columns\TextColumn::make('sentiment')
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'positive' => 'success',
                        'neutral' => 'warning',
                        'negative' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                Action::make('approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Feedback $record) {
                        $record->update(['moderation_status' => 'approved']);
                        Notification::make()
                            ->success()
                            ->title('Feedback approved')
                            ->send();
                    }),
                Action::make('flag')
                    ->icon('heroicon-o-flag')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (Feedback $record) {
                        $record->update(['moderation_status' => 'hard_flagged']);
                        Notification::make()
                            ->success()
                            ->title('Feedback flagged')
                            ->send();
                    }),
            ])
            ->paginated([10, 25, 50]);
    }
}
