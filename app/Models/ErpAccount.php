<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class ErpAccount extends Authenticatable implements FilamentUser, HasName
{
    use HasFactory, HasRoles, Notifiable, SoftDeletes;

    protected $table = 'erp_accounts';

    protected $fillable = [
        'username',
        'full_name',
        'email',
        'phone',
        'password',
        'is_active',
        'must_change_password',
    ];

    /**
     * Single registration (backward compat — returns the latest).
     */
    public function registration(): HasOne
    {
        return $this->hasOne(PpdbRegistration::class, 'erp_account_id')->latestOfMany();
    }

    /**
     * All registrations (1 account can have multiple children/santri).
     */
    public function registrations(): HasMany
    {
        return $this->hasMany(PpdbRegistration::class, 'erp_account_id');
    }

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
            'two_factor_enabled' => 'boolean',
            'locked_until' => 'datetime',
            'last_login_at' => 'datetime',
            'must_change_password' => 'boolean',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_active;
    }

    public function getFilamentName(): string
    {
        return $this->full_name;
    }

    public function getGuardName(): string
    {
        return 'erp';
    }
}
