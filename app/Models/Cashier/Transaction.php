<?php

namespace App\Models\Cashier;

use Laravel\Paddle\Transaction as CashierTransaction;

class Transaction extends CashierTransaction
{
    /**
     * The table associated with the model.
     *
     * This matches the `paddle_transactions` migration.
     */
    protected $table = 'paddle_transactions';
}
