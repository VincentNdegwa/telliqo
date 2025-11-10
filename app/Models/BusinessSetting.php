<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class BusinessSetting extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'business_id',
        'key',
        'value',
        'type',
        'is_encrypted',
        'description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'value' => 'array',
        'is_encrypted' => 'boolean',
    ];

    /**
     * Get the business that owns the setting.
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Scope a query to filter by key.
     */
    public function scopeForKey($query, string $key)
    {
        return $query->where('key', $key);
    }

    /**
     * Scope a query to filter encrypted settings.
     */
    public function scopeEncrypted($query, bool $encrypted = true)
    {
        return $query->where('is_encrypted', $encrypted);
    }

    /**
     * Get the value attribute with decryption if needed.
     */
    public function getValueAttribute($value)
    {
        if ($this->is_encrypted && $value) {
            try {
                $decrypted = Crypt::decryptString($value);
                return json_decode($decrypted, true);
            } catch (\Exception $e) {
                // If decryption fails, return null or handle error
                return null;
            }
        }

        return json_decode($value, true);
    }

    /**
     * Set the value attribute with encryption if needed.
     */
    public function setValueAttribute($value)
    {
        $jsonValue = is_array($value) ? json_encode($value) : $value;

        if ($this->is_encrypted) {
            $this->attributes['value'] = Crypt::encryptString($jsonValue);
        } else {
            $this->attributes['value'] = $jsonValue;
        }
    }

    /**
     * Get a specific nested value from the settings.
     */
    public function get(string $nestedKey, $default = null)
    {
        $value = $this->value;

        if (!is_array($value)) {
            return $default;
        }

        return data_get($value, $nestedKey, $default);
    }

    /**
     * Set a specific nested value in the settings.
     */
    public function set(string $nestedKey, $value): self
    {
        $currentValue = $this->value ?? [];

        data_set($currentValue, $nestedKey, $value);

        $this->value = $currentValue;

        return $this;
    }

    /**
     * Get all settings values.
     */
    public function getAll(): array
    {
        return $this->value ?? [];
    }

    /**
     * Set multiple values at once (merge with existing).
     */
    public function setMultiple(array $data): self
    {
        $currentValue = $this->value ?? [];

        $this->value = array_merge($currentValue, $data);

        return $this;
    }

    /**
     * Replace all values (not merge).
     */
    public function replaceAll(array $data): self
    {
        $this->value = $data;

        return $this;
    }
}
