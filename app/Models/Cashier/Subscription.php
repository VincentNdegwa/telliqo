<?php

namespace App\Models\Cashier;

use Laravel\Paddle\Subscription as CashierSubscription;

class Subscription extends CashierSubscription
{
    /**
     * The table associated with the model.
     *
     * This matches the `paddle_subscriptions` migration.
     */
    protected $table = 'paddle_subscriptions';
}
