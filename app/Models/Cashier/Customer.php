<?php

namespace App\Models\Cashier;

use Laravel\Paddle\Customer as CashierCustomer;

class Customer extends CashierCustomer
{
    /**
     * The table associated with the model.
     *
     * This matches the `paddle_customers` migration.
     */
    protected $table = 'paddle_customers';
}
