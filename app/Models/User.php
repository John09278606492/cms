<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Models\Contracts\HasDefaultTenant;
use Filament\Models\Contracts\HasName;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Jeffgreco13\FilamentBreezy\Traits\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser, HasAvatar, HasName, HasTenants, HasDefaultTenant
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;
    use HasRoles;
    use Notifiable;
    use TwoFactorAuthenticatable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar_url',
    ];

    protected $hidden = [
        'password',
        'remember_token',
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
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return match ($panel->getId()) {
            'platform' => $this->hasRole('super_admin'),
            'admin' => $this->hasAnyRole(['super_admin', 'panel_user', 'site_owner']),
            default => false,
        };
    }

    public function getFilamentAvatarUrl(): ?string
    {
        if (blank($this->avatar_url)) {
            return null;
        }

        if (filter_var($this->avatar_url, FILTER_VALIDATE_URL)) {
            return $this->avatar_url;
        }

        return Storage::disk('public')->url($this->avatar_url);
    }

    public function getFilamentName(): string
    {
        return $this->name;
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function ownedSites(): HasMany
    {
        return $this->hasMany(Site::class, 'owner_id');
    }

    public function sites(): BelongsToMany
    {
        return $this->belongsToMany(Site::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    public function pages(): HasMany
    {
        return $this->hasMany(Page::class);
    }

    public function canAccessTenant(Model $tenant): bool
    {
        if (! $tenant instanceof Site) {
            return false;
        }

        return $this->hasRole('super_admin')
            || $this->ownedSites()->whereKey($tenant->getKey())->exists()
            || $this->sites()->whereKey($tenant->getKey())->exists();
    }

    public function getTenants(Panel $panel): array|Collection
    {
        if ($this->hasRole('super_admin')) {
            return Site::query()
                ->orderBy('name')
                ->get();
        }

        return $this->ownedSites()
            ->orderBy('name')
            ->get()
            ->concat(
                $this->sites()
                    ->orderBy('name')
                    ->get(),
            )
            ->unique('id')
            ->values();
    }

    public function getDefaultTenant(Panel $panel): ?Model
    {
        if ($this->hasRole('super_admin')) {
            return Site::query()->orderBy('name')->first();
        }

        return $this->ownedSites()->orderBy('name')->first()
            ?? $this->sites()->orderBy('name')->first();
    }
}
