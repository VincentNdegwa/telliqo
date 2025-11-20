<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeatureAddon extends Model
{
    use HasFactory;

    protected $fillable = [
        'feature_id',
        'name',
        'increment_quota',
        'price_kes',
        'price_usd',
        'is_active',
        'sort_order',
    ];

    public function feature(): BelongsTo
    {
        return $this->belongsTo(Feature::class);
    }

    public function businessAddons(): HasMany
    {
        return $this->hasMany(BusinessFeatureAddon::class);
    }
}
