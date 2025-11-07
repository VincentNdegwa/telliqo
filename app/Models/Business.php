<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Business extends Model
{
    use HasFactory;

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
}
