<?php

namespace App\Filament\Resources\Plans\Actions;

use App\Models\Plan;
use App\Services\PaypalService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;

class SyncPaypalPlanAction extends Action
{
    public static function config(): static
    {
        return static::make('sync_paypal_plan')
            ->label('Sync PayPal Plan')
            ->icon('heroicon-o-arrow-path')
            ->form([
                Select::make('billing_period')
                    ->label('Billing Period')
                    ->options([
                        'monthly' => 'Monthly',
                        'yearly' => 'Yearly',
                    ])
                    ->required(),
            ])
            ->action(function (array $data, Plan $record) {
                try {
                    /** @var PaypalService $paypal */
                    $paypal = app(PaypalService::class);

                    $billingPeriod = $data['billing_period'] ?? 'monthly';
                    $currency = env('PAYPAL_CURRENCY', 'USD');

                    $result = $paypal->syncPaypalBillingPlanForLocalPlan($record, $billingPeriod, $currency);

                    $created = $result['created'] ?? false;
                    $remoteId = $result['id'] ?? null;

                    Notification::make()
                        ->title($created ? 'PayPal plan created and linked' : 'PayPal plan already linked')
                        ->body($remoteId ? ('PayPal Plan ID: '.$remoteId) : null)
                        ->success()
                        ->send();
                } catch (\Throwable $th) {
                    Notification::make()
                        ->title('Failed to sync PayPal plan')
                        ->body($th->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }
}
