<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Feature;
use App\Models\PlanFeature;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'name',
        'description',
        'price_kes',
        'price_usd',
        'price_kes_yearly',
        'price_usd_yearly',
        'is_active',
        'sort_order',
    ];

    public function planFeatures(): HasMany
    {
        return $this->hasMany(PlanFeature::class);
    }

    public function features(): BelongsToMany
    {
        return $this->belongsToMany(Feature::class, 'plan_features')
            ->withPivot(['is_enabled', 'quota', 'is_unlimited', 'unit'])
            ->withTimestamps();
    }

    public function businesses(): HasMany
    {
        return $this->hasMany(Business::class);
    }

    public function syncFeatures(array $features): void
    {
        $allFeatures = Feature::orderBy('category')->orderBy('name')->get();
        $existing = $this->planFeatures()->get()->keyBy('feature_id');

        foreach ($allFeatures as $feature) {
            $key = $feature->key;
            $config = $features[$key] ?? null;

            $enabled = (bool) ($config['enabled'] ?? false);

            if (! $enabled) {
                if ($existing->has($feature->id)) {
                    $this->planFeatures()
                        ->where('feature_id', $feature->id)
                        ->delete();
                }

                continue;
            }

            $isQuota = $feature->type === 'quota';
            $isUnlimited = $isQuota && (bool) ($config['is_unlimited'] ?? false);
            $quota = null;
            $unit = null;

            if ($isQuota) {
                $unit = $config['unit'] ?? $feature->default_unit;

                if (! $isUnlimited) {
                    $quota = isset($config['quota']) ? (int) $config['quota'] : null;
                }
            }

            $this->planFeatures()->updateOrCreate(
                ['feature_id' => $feature->id],
                [
                    'is_enabled' => true,
                    'is_unlimited' => $isUnlimited,
                    'quota' => $quota,
                    'unit' => $unit,
                ]
            );
        }
    }

    public function getFeaturesFormState(): array
    {
        $state = [];

        $allFeatures = Feature::orderBy('category')->orderBy('name')->get();
        $byFeatureId = $this->planFeatures()->get()->keyBy('feature_id');

        foreach ($allFeatures as $feature) {
            $pivot = $byFeatureId->get($feature->id);

            $entry = [
                'enabled' => $pivot ? (bool) $pivot->is_enabled : false,
            ];

            if ($feature->type === 'quota') {
                $entry['is_unlimited'] = $pivot ? (bool) $pivot->is_unlimited : false;
                $entry['quota'] = $pivot ? $pivot->quota : null;
                $entry['unit'] = $pivot ? ($pivot->unit ?? $feature->default_unit) : $feature->default_unit;
            }

            $state[$feature->key] = $entry;
        }

        return $state;
    }
}
