<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessFeatureAddon extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'feature_addon_id',
        'quantity',
        'period_start',
    ];

    protected $casts = [
        'period_start' => 'date',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function addon(): BelongsTo
    {
        return $this->belongsTo(FeatureAddon::class, 'feature_addon_id');
    }
}
