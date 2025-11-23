<?php

namespace App\Models\Cashier;

use Laravel\Paddle\SubscriptionItem as CashierSubscriptionItem;

class SubscriptionItem extends CashierSubscriptionItem
{
    /**
     * The table associated with the model.
     *
     * This matches the `paddle_subscription_items` migration.
     */
    protected $table = 'paddle_subscription_items';
}
