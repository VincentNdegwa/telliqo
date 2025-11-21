<?php

namespace App\Filament\Resources\Businesses\Actions;

use App\Models\LocalSubscription;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AssignPlanAction extends Action
{
    public static function config(): static
    {
        return static::make('assign_plan')
            ->label('Assign Plan')
            ->icon('heroicon-o-currency-dollar')
            ->fillForm(function ($record) {
                $plan = null;
                if ($record->plan_id) {
                    $plan = \App\Models\Plan::find($record->plan_id);
                }

                $defaultCurrency = 'KES';
                $defaultPeriod = 'monthly';
                $defaultAmount = null;

                if ($plan) {
                    $defaultAmount = $plan->price_kes;
                }

                return [
                    'name' => $record->name,
                    'plan' => $record->plan_id ?? null,
                    'period' => $defaultPeriod,
                    'currency' => $defaultCurrency,
                    'amount' => $defaultAmount,
                ];
            })
            ->action(function ($data, $record) {
                try {

                    $subscriptionService = app()->make(\App\Services\SubscriptionService::class);
                

                    $startsAt = Carbon::now();
                    $subscription = [
                        'business_id' => $record->id,
                        'plan_id' => $data['plan'],
                        'provider' => $data['provider'] ?? 'manual',
                        'external_id' => $data['external_id'] ?? null,
                        'status' => $data['status'] ?? 'active',
                        'billing_period' => $data['period'] ?? 'monthly',
                        'starts_at' => $startsAt,
                        'trial_ends_at' => $data['trial_ends_at'] ?? null,
                        'ends_at' => $data['ends_at'] ?? null,
                        'amount' => $data['amount'] ?? null,
                        'currency' => $data['currency'] ?? 'KES',
                        'meta' => json_encode($data['meta'] ?? null),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ];

                    $plan = \App\Models\Plan::find($data['plan']);

                    $subscriptionService->createLocalSubscription($record, $plan, $subscription); 

                    Notification::make()
                        ->title('Plan assigned and subscription created')
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
                TextInput::make('name')->label('Business Name')->readOnly(),

                Select::make('plan')
                    ->label('Select Plan')
                    ->options(\App\Models\Plan::all()->pluck('name', 'id'))
                    ->required()
                    ->searchable()
                    ->native(false)
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        $period = $get('period') ?? 'monthly';
                        $currency = $get('currency') ?? 'KES';
                        $plan = \App\Models\Plan::find($state);
                        if ($plan) {
                            $price = ($period === 'yearly')
                                ? ($currency === 'USD' ? $plan->price_usd_yearly : $plan->price_kes_yearly)
                                : ($currency === 'USD' ? $plan->price_usd : $plan->price_kes);
                            $set('amount', $price);
                        }
                    }),

                Select::make('period')
                    ->label('Billing Period')
                    ->options([
                        'monthly' => 'Monthly',
                        'yearly' => 'Yearly',
                    ])
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        $planId = $get('plan');
                        $currency = $get('currency') ?? 'KES';
                        if ($planId) {
                            $plan = \App\Models\Plan::find($planId);
                            if ($plan) {
                                $price = ($state === 'yearly')
                                    ? ($currency === 'USD' ? $plan->price_usd_yearly : $plan->price_kes_yearly)
                                    : ($currency === 'USD' ? $plan->price_usd : $plan->price_kes);
                                $set('amount', $price);
                            }
                        }
                    }),

                Select::make('currency')
                    ->label('Currency')
                    ->options([
                        'KES' => 'KES',
                        'USD' => 'USD',
                    ])
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        $planId = $get('plan');
                        $period = $get('period') ?? 'monthly';
                        if ($planId) {
                            $plan = \App\Models\Plan::find($planId);
                            if ($plan) {
                                $price = ($period === 'yearly')
                                    ? ($state === 'USD' ? $plan->price_usd_yearly : $plan->price_kes_yearly)
                                    : ($state === 'USD' ? $plan->price_usd : $plan->price_kes);
                                $set('amount', $price);
                            }
                        }
                    }),

                TextInput::make('amount')
                    ->label('Amount')
                    ->numeric()
                    ->required()
                    ->readOnly(),
            ]);
    }
}

