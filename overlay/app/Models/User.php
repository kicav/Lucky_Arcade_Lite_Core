<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ADMIN_ROLES = [
        'super_admin' => 'Super admin',
        'operator' => 'Operator',
    ];

    private const ADMIN_AREA_ACCESS = [
        'super_admin' => ['*'],
        'operator' => ['dashboard', 'games', 'users', 'user_actions', 'entries', 'audit', 'system'],
    ];

    protected $fillable = [
        'name', 'email', 'password', 'is_admin', 'admin_role', 'daily_stake_limit',
        'self_excluded_until', 'suspended_at', 'suspension_reason', 'two_factor_secret',
        'two_factor_recovery_codes', 'two_factor_confirmed_at',
    ];

    protected $hidden = ['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'daily_stake_limit' => 'integer',
            'self_excluded_until' => 'datetime',
            'suspended_at' => 'datetime',
            'two_factor_secret' => 'encrypted',
            'two_factor_recovery_codes' => 'encrypted:array',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    public function wallet(): HasOne { return $this->hasOne(Wallet::class); }
    public function fairnessSeeds(): HasMany { return $this->hasMany(FairnessSeed::class); }
    public function gameEntries(): HasMany { return $this->hasMany(GameEntry::class); }
    public function ledgerEntries(): HasMany { return $this->hasMany(LedgerEntry::class); }
    public function securityEvents(): HasMany { return $this->hasMany(SecurityEvent::class); }

    public function hasTwoFactorEnabled(): bool
    {
        return $this->two_factor_confirmed_at !== null && filled($this->two_factor_secret);
    }

    public function resolvedAdminRole(): ?string
    {
        if (! $this->is_admin) return null;
        return array_key_exists((string) $this->admin_role, self::ADMIN_ROLES)
            ? $this->admin_role
            : 'operator';
    }

    public function canAccessAdminArea(string $area): bool
    {
        $role = $this->resolvedAdminRole();
        if ($role === null) return false;
        $areas = self::ADMIN_AREA_ACCESS[$role] ?? [];
        return in_array('*', $areas, true) || in_array($area, $areas, true);
    }

    public function isSelfExcluded(): bool
    {
        return $this->self_excluded_until !== null && $this->self_excluded_until->isFuture();
    }

    public function isSuspended(): bool { return $this->suspended_at !== null; }
}
