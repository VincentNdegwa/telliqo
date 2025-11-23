<?php

namespace App\Filament\Resources\Plans\Actions;

use App\Models\Plan;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;

class EditPaddlePlanIdsAction extends Action
{
    public static function config(): static
    {
        return static::make('edit_paddle_plans')
            ->label('Set Paddle Plan IDs')
            ->icon('heroicon-o-flag')
            ->fillForm(function (Plan $record) {
                return [
                    'paddle_plan_id_monthly' => $record->paddle_plan_id_monthly,
                    'paddle_plan_id_yearly' => $record->paddle_plan_id_yearly,
                ];
            })
            ->form([
                TextInput::make('paddle_plan_id_monthly')
                    ->label('Paddle Plan ID (Monthly)')
                    ->maxLength(50),
                TextInput::make('paddle_plan_id_yearly')
                    ->label('Paddle Plan ID (Yearly)')
                    ->maxLength(50),
            ])
            ->action(function (array $data, Plan $record) {
                try {
                    $record->paddle_plan_id_monthly = $data['paddle_plan_id_monthly'] ?? null;
                    $record->paddle_plan_id_yearly = $data['paddle_plan_id_yearly'] ?? null;
                    $record->save();

                    Notification::make()
                        ->title('Paddle plan IDs updated')
                        ->success()
                        ->send();
                } catch (\Throwable $th) {
                    Notification::make()
                        ->title('Failed to update Paddle plan IDs')
                        ->body($th->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }
}
