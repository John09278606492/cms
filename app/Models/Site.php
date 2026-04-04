<?php

namespace App\Models;

use App\Support\SiteProvisioner;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Site extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'name',
        'slug',
        'description',
        'domain',
        'subdomain',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::created(function (self $site): void {
            app(SiteProvisioner::class)->provision($site);
        });

        static::saved(function (self $site): void {
            if (blank($site->owner_id)) {
                return;
            }

            $site->users()->syncWithoutDetaching([
                $site->owner_id => ['role' => 'owner'],
            ]);
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function pages(): HasMany
    {
        return $this->hasMany(Page::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function tags(): HasMany
    {
        return $this->hasMany(Tag::class);
    }

    public function settings(): HasMany
    {
        return $this->hasMany(Setting::class);
    }

    public function setting(): HasOne
    {
        return $this->hasOne(Setting::class);
    }

    public function menus(): HasMany
    {
        return $this->hasMany(Menu::class);
    }
}
