<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LocalTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'local_subscription_id',
        'provider',
        'status',
        'amount',
        'currency',
        'external_id',
        'meta',
        'paid_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'paid_at' => 'datetime',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function localSubscription()
    {
        return $this->belongsTo(LocalSubscription::class);
    }
}
