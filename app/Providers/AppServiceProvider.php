<?php

namespace App\Providers;

use App\Models\Menu;
use App\Models\Site;
use App\Support\SiteNavigation;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Slimani\MediaManager\Models\File as MediaFile;
use Slimani\MediaManager\Models\Folder as MediaFolder;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $resolveMediaSite = static function (): ?Site {
            if (app()->runningInConsole()) {
                return null;
            }

            $tenant = Filament::getTenant();

            return $tenant instanceof Site ? $tenant : null;
        };

        MediaFile::creating(function (MediaFile $file) use ($resolveMediaSite): void {
            if (filled($file->getAttribute('site_id'))) {
                return;
            }

            if ($site = $resolveMediaSite()) {
                $file->setAttribute('site_id', $site->getKey());
            }
        });

        MediaFolder::creating(function (MediaFolder $folder) use ($resolveMediaSite): void {
            if (filled($folder->getAttribute('site_id'))) {
                return;
            }

            if ($site = $resolveMediaSite()) {
                $folder->setAttribute('site_id', $site->getKey());
            }
        });

        MediaFile::addGlobalScope('tenant_media', function (Builder $query) use ($resolveMediaSite): void {
            if (! ($site = $resolveMediaSite())) {
                return;
            }

            $query->where($query->getModel()->qualifyColumn('site_id'), $site->getKey());
        });

        MediaFolder::addGlobalScope('tenant_media', function (Builder $query) use ($resolveMediaSite): void {
            if (! ($site = $resolveMediaSite())) {
                return;
            }

            $query->where($query->getModel()->qualifyColumn('site_id'), $site->getKey());
        });

        view()->composer('layouts.site', function (View $view): void {
            $site = $view->getData()['site'] ?? null;
            $user = auth()->user();

            if (! $site instanceof Site) {
                $view->with([
                    'headerMenuItems' => collect(),
                    'footerMenuItems' => collect(),
                    'canManageCurrentSite' => false,
                ]);

                return;
            }

            /** @var Collection<int, Menu> $menus */
            $menus = Menu::navigationForSite($site);

            $headerMenu = $menus->first(fn (Menu $menu): bool => $menu->hasLocation('header'));
            $footerMenu = $menus->first(fn (Menu $menu): bool => $menu->hasLocation('footer'));

            if (! $headerMenu && $menus->count() === 1) {
                $headerMenu = $menus->first();
            }

            $headerMenuItems = $headerMenu?->menuItems;

            if (($headerMenuItems?->isEmpty() ?? true) && $site instanceof Site) {
                $headerMenuItems = SiteNavigation::defaultHeaderForSite($site);
            }

            $view->with([
                'headerMenuItems' => $headerMenuItems ?? collect(),
                'footerMenuItems' => $footerMenu?->menuItems ?? collect(),
                'canManageCurrentSite' => $user?->canAccessTenant($site) ?? false,
            ]);
        });
    }
}
