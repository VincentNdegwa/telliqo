<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ApiKey extends Model
{
    protected $fillable = [
        'business_id',
        'name',
        'key_hash',
        'key_preview',
        'permissions',
        'last_used_at',
        'last_used_ip',
        'is_active',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'permissions' => 'array',
            'is_active' => 'boolean',
            'last_used_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Generate a new API key
     */
    public static function generate(Business $business, string $name, ?array $permissions = null, ?\DateTime $expiresAt = null): array
    {
        // Generate a secure random key
        $prefix = 'tk'; // telliqo key
        $randomPart = Str::random(64);
        $plainKey = $prefix . '_' . $randomPart;

        // Create hash for storage
        $keyHash = Hash::make($plainKey);

        // Create preview (first 8 and last 4 characters)
        $keyPreview = substr($plainKey, 0, 10) . '...' . substr($plainKey, -4);

        // Create the API key record
        $apiKey = self::create([
            'business_id' => $business->id,
            'name' => $name,
            'key_hash' => $keyHash,
            'key_preview' => $keyPreview,
            'permissions' => $permissions ?? ['*'], // Default to all permissions
            'expires_at' => $expiresAt,
            'is_active' => true,
        ]);

        // Return the plain key (only shown once)
        return [
            'api_key' => $apiKey,
            'plain_key' => $plainKey,
        ];
    }

    /**
     * Verify if a plain key matches this API key
     */
    public function verify(string $plainKey): bool
    {
        return Hash::check($plainKey, $this->key_hash);
    }

    /**
     * Check if the API key is valid
     */
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Check if the API key has a specific permission
     */
    public function hasPermission(string $permission): bool
    {
        if (!$this->permissions) {
            return false;
        }

        // Wildcard permission
        if (in_array('*', $this->permissions)) {
            return true;
        }

        return in_array($permission, $this->permissions);
    }

    /**
     * Update last used information
     */
    public function recordUsage(?string $ipAddress = null): void
    {
        $this->update([
            'last_used_at' => now(),
            'last_used_ip' => $ipAddress,
        ]);
    }

    /**
     * Revoke (deactivate) the API key
     */
    public function revoke(): void
    {
        $this->update(['is_active' => false]);
    }

    /**
     * Scope for active keys only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            });
    }

    /**
     * Scope for business
     */
    public function scopeForBusiness($query, int $businessId)
    {
        return $query->where('business_id', $businessId);
    }
}
