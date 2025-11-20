<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Feature extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'name',
        'category',
        'description',
        'type',
        'default_unit',
        'public_name',
        'public_description',
    ];

    public function planFeatures(): HasMany
    {
        return $this->hasMany(PlanFeature::class);
    }

    public function plans(): BelongsToMany
    {
        return $this->belongsToMany(Plan::class, 'plan_features')
            ->withPivot(['is_enabled', 'quota', 'is_unlimited', 'unit'])
            ->withTimestamps();
    }

    public function usages(): HasMany
    {
        return $this->hasMany(FeatureUsage::class);
    }

    public function addons(): HasMany
    {
        return $this->hasMany(FeatureAddon::class);
    }
}
