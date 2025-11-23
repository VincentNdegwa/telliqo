<?php

namespace App\Providers;

use App\Models\Business;
use App\Models\Feedback;
use App\Models\User;
use App\Models\Cashier\Customer as CashierCustomerModel;
use App\Models\Cashier\Subscription as CashierSubscriptionModel;
use App\Models\Cashier\SubscriptionItem as CashierSubscriptionItemModel;
use App\Models\Cashier\Transaction as CashierTransactionModel;
use App\Observers\BusinessObserver;
use App\Observers\FeedbackObserver;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Laravel\Paddle\Cashier;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Feedback::observe(FeedbackObserver::class);
        Business::observe(BusinessObserver::class);

        Cashier::useCustomerModel(CashierCustomerModel::class);
        Cashier::useSubscriptionModel(CashierSubscriptionModel::class);
        Cashier::useSubscriptionItemModel(CashierSubscriptionItemModel::class);
        Cashier::useTransactionModel(CashierTransactionModel::class);

        Gate::before(function (?User $user, string $ability) {
            if (! $user) {
                return null;
            }

            return $user->isSuperAdmin() ? true : null;
        });

        Gate::define('viewScalar', function (?User $user) {
            return app()->environment('local') || in_array($user?->email, [
            ]);
        });
    }
}
