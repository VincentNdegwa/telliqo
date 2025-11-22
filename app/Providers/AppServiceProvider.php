<?php

namespace App\Providers;

use App\Models\Business;
use App\Models\Feedback;
use App\Models\User;
use App\Observers\BusinessObserver;
use App\Observers\FeedbackObserver;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

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
