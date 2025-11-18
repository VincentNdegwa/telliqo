<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\Auditable;
use BackedEnum;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laratrust\Contracts\LaratrustUser;
use Laratrust\Traits\HasRolesAndPermissions;
use Filament\Models\Contracts\FilamentUser as FilamentUserContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\UuidInterface;

class User extends Authenticatable implements LaratrustUser, FilamentUserContract
{
    use HasFactory, Notifiable, TwoFactorAuthenticatable, Auditable, HasRolesAndPermissions;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_super_admin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];


    protected $auditSensitive = [
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'is_super_admin' => 'boolean',
        ];
    }

    /**
     * Get the businesses that belong to the user.
     */
    public function businesses(): BelongsToMany
    {
        return $this->belongsToMany(Business::class, 'business_users')
            ->withPivot(['role', 'is_active', 'invited_at', 'joined_at'])
            ->withTimestamps();
    }


    public function ownedBusinesses(): BelongsToMany
    {
        return $this->businesses()->wherePivot('role', 'owner');
    }

 
    public function hasCompletedOnboarding(): bool
    {
        return $this->businesses()->whereNotNull('onboarding_completed_at')->exists();
    }


    public function getCurrentBusiness(): ?Business
    {
        return $this->businesses()->first();
    }


    public function isOwnerOf(Business $business): bool
    {
        return $this->businesses()
            ->wherePivot('business_id', $business->id)
            ->wherePivot('role', 'owner')
            ->exists();
    }

    /**
     * Check if the user is an admin of the given business.
     */
    public function isAdminOf(Business $business): bool
    {
        return $this->businesses()
            ->wherePivot('business_id', $business->id)
            ->wherePivot('role', 'admin')
            ->exists();
    }

    /**
     * Check if the user belongs to the given business.
     */
    public function belongsToBusiness(Business $business): bool
    {
        return $this->businesses()
            ->where('business_id', $business->id)
            ->exists();
    }

    public function isSuperAdmin(): bool
    {
        return $this->is_super_admin === true;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return  $this->isSuperAdmin();
    }

    public function addRole(array|string|int|Model|UuidInterface|BackedEnum $role, mixed $team = null): static
    {
        DB::table('role_user')->insert([
            'user_id' => $this->id,
            'role_id' => is_object($role) ? $role->id : $role,
            'team_id' => is_object($team) ? $team->id : $team,
            'user_type' => get_class($this),
        ]);
        return $this;
    }
}
