<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Business extends Model
{
    use HasFactory, Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'category_id',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'website',
        'logo',
        'qr_code_path',
        'qr_code_url',
        'custom_thank_you_message',
        'brand_color_primary',
        'brand_color_secondary',
        'auto_approve_feedback',
        'require_customer_name',
        'feedback_email_notifications',
        'is_active',
        'onboarding_completed_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'auto_approve_feedback' => 'boolean',
            'require_customer_name' => 'boolean',
            'feedback_email_notifications' => 'boolean',
            'onboarding_completed_at' => 'datetime',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($business) {
            if (empty($business->slug)) {
                $business->slug = Str::slug($business->name);
            }
        });
    }

    /**
     * Get the category that owns the business.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(BusinessCategory::class);
    }

    /**
     * Get the users that belong to the business.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'business_users')
            ->withPivot(['role', 'is_active', 'invited_at', 'joined_at'])
            ->withTimestamps();
    }

    /**
     * Get the owners of the business.
     */
    public function owners(): BelongsToMany
    {
        return $this->users()->wherePivot('role', 'owner');
    }

    /**
     * Get the feedback for the business.
     */
    public function feedback(): HasMany
    {
        return $this->hasMany(Feedback::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function reviewRequests(): HasMany
    {
        return $this->hasMany(ReviewRequest::class);
    }

    public function settings(): HasMany
    {
        return $this->hasMany(BusinessSetting::class);
    }

    public function metrics(): HasMany
    {
        return $this->hasMany(BusinessMetric::class);
    }

    public function apiKeys(): HasMany
    {
        return $this->hasMany(ApiKey::class);
    }

    /**
     * Scope a query to only include businesses with completed onboarding.
     */
    public function scopeOnboarded($query)
    {
        return $query->whereNotNull('onboarding_completed_at');
    }

    /**
     * Mark the business onboarding as complete.
     */
    public function completeOnboarding(): void
    {
        $this->update([
            'onboarding_completed_at' => now(),
        ]);
    }

    /**
     * Check if the business onboarding is completed.
     */
    public function hasCompletedOnboarding(): bool
    {
        return ! is_null($this->onboarding_completed_at);
    }

    /**
     * Get a setting value by key.
     *
     * @param string $key The setting key (e.g., 'notification_settings')
     * @param mixed $default Default value if setting doesn't exist
     * @return mixed
     */
    public function getSetting(string $key, $default = null)
    {
        $setting = $this->settings()->forKey($key)->first();

        if (!$setting) {
            return $default;
        }

        return $setting->value ?? $default;
    }

    /**
     * Set a setting value by key (creates or updates).
     *
     * @param string $key The setting key
     * @param mixed $value The setting value (array will be JSON encoded)
     * @param bool $encrypted Whether to encrypt the value
     * @param string|null $description Optional description
     * @return BusinessSetting
     */
    public function setSetting(string $key, $value, bool $encrypted = false, ?string $description = null): BusinessSetting
    {
        return $this->settings()->updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'is_encrypted' => $encrypted,
                'description' => $description,
            ]
        );
    }

    /**
     * Get a nested value from a setting.
     *
     * @param string $key The setting key
     * @param string $nestedKey The nested key (dot notation)
     * @param mixed $default Default value
     * @return mixed
     */
    public function getSettingValue(string $key, string $nestedKey, $default = null)
    {
        $setting = $this->settings()->forKey($key)->first();

        if (!$setting) {
            return $default;
        }

        return $setting->get($nestedKey, $default);
    }

    /**
     * Update a nested value in a setting.
     *
     * @param string $key The setting key
     * @param string $nestedKey The nested key (dot notation)
     * @param mixed $value The value to set
     * @return BusinessSetting
     */
    public function updateSettingValue(string $key, string $nestedKey, $value): BusinessSetting
    {
        $setting = $this->settings()->firstOrCreate(
            ['key' => $key],
            ['value' => []]
        );

        $setting->set($nestedKey, $value)->save();

        return $setting;
    }

    /**
     * Update an entire setting group (merge with existing).
     *
     * @param string $key The setting key
     * @param array $data The data to merge
     * @return BusinessSetting
     */
    public function updateSettingGroup(string $key, array $data): BusinessSetting
    {
        $setting = $this->settings()->firstOrCreate(
            ['key' => $key],
            ['value' => []]
        );

        $setting->setMultiple($data)->save();

        return $setting;
    }

    /**
     * Replace an entire setting group (no merge).
     *
     * @param string $key The setting key
     * @param array $data The data to set
     * @return BusinessSetting
     */
    public function replaceSettingGroup(string $key, array $data): BusinessSetting
    {
        return $this->setSetting($key, $data);
    }

    /**
     * Check if a setting exists.
     *
     * @param string $key The setting key
     * @return bool
     */
    public function hasSetting(string $key): bool
    {
        return $this->settings()->forKey($key)->exists();
    }

    /**
     * Delete a setting.
     *
     * @param string $key The setting key
     * @return bool
     */
    public function deleteSetting(string $key): bool
    {
        return $this->settings()->forKey($key)->delete();
    }
}
