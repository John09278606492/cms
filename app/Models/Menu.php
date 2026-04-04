<?php

namespace App\Models;

use Datlechin\FilamentMenuBuilder\Models\Menu as BaseMenu;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Menu extends BaseMenu
{
    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_visible', true);
    }

    public function scopeForSite(Builder $query, Site $site): Builder
    {
        return $query->whereBelongsTo($site);
    }

    public function scopeWithNavigationRelations(Builder $query): Builder
    {
        return $query->with(['locations', 'menuItems.children']);
    }

    public static function navigationForSite(Site $site): Collection
    {
        return static::query()
            ->forSite($site)
            ->visible()
            ->withNavigationRelations()
            ->orderBy('id')
            ->get();
    }

    public function hasLocation(string $location): bool
    {
        if ($this->relationLoaded('locations')) {
            return $this->locations->contains('location', $location);
        }

        return $this->locations()->where('location', $location)->exists();
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
