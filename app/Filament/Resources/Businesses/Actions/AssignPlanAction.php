<?php

namespace App\Filament\Resources\Businesses\Actions;

use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;

class AssignPlanAction extends Action
{
    public static function config(): static
    {
        return static::make("assign_plan")
            ->label('Assign Plan')
            ->icon('heroicon-o-currency-dollar')
            ->fillForm(function ($record) {
                return [
                    'name' => $record->name,
                    'plan' => $record->plan_id ?? null,
                ];
            })
            ->action(function ($data, $record) {
                try {
                    $record->update(['plan_id' => $data['plan']]);
                    
                    Notification::make()
                        ->title('Plan assigned successfully')
                        ->success()
                        ->send();
                        
                } catch (\Throwable $th) {
                    $message = $th->getMessage();
                    Notification::make()
                        ->title('Error assigning plan')
                        ->body($message)
                        ->danger()
                        ->send();
                }
            })
            ->form([
                TextInput::make("name")->label("Business Name")->readOnly(),
                Select::make("plan")->options(
                    \App\Models\Plan::all()->pluck('name', 'id')
                )->required()->label('Select Plan')->searchable()->native(false)->placeholder('Choose a plan'),
            ]);
    }
}

