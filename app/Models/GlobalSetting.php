<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class GlobalSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'array',
        ];
    }

    public function scopeForKey($query, string $key)
    {
        return $query->where('key', $key);
    }

    public static function getValue(string $key, $default = null)
    {
        $setting = static::forKey($key)->first();
        return $setting ? $setting->value : $default;
    }

    public static function setValue(string $key, $value, ?string $description = null): self
    {
        return static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'description' => $description,
            ]
        );
    }

    public function get(string $nestedKey, $default = null)
    {
        return Arr::get($this->value, $nestedKey, $default);
    }

    public function set(string $nestedKey, $value): self
    {
        $currentValue = is_array($this->value) ? $this->value : [];
        Arr::set($currentValue, $nestedKey, $value);
        $this->value = $currentValue;
        return $this;
    }

    public function setMultiple(array $data): self
    {
        $currentValue = is_array($this->value) ? $this->value : [];
        foreach ($data as $key => $value) {
            Arr::set($currentValue, $key, $value);
        }
        $this->value = $currentValue;
        return $this;
    }
}
