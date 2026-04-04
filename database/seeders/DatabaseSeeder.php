<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\Site;
use App\Models\User;
use App\Support\PageNavigationManager;
use App\Support\SiteProvisioner;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::query()->updateOrCreate(
            ['email' => env('CMS_ADMIN_EMAIL', 'admin@cms.test')],
            [
                'name' => env('CMS_ADMIN_NAME', 'CMS Administrator'),
                'password' => env('CMS_ADMIN_PASSWORD', 'password'),
            ],
        );

        $superAdminRole = Role::query()->firstOrCreate([
            'name' => 'super_admin',
            'guard_name' => 'web',
        ]);

        Role::query()->firstOrCreate([
            'name' => 'panel_user',
            'guard_name' => 'web',
        ]);

        $siteOwnerRole = Role::query()->firstOrCreate([
            'name' => 'site_owner',
            'guard_name' => 'web',
        ]);

        $superAdminRole->syncPermissions(Permission::query()->pluck('name')->all());
        $admin->syncRoles([$superAdminRole]);

        $tenantPermissionModels = ['Post', 'Page', 'Category', 'Tag', 'Setting', 'Menu'];

        $siteOwnerRole->syncPermissions(
            Permission::query()
                ->where(function ($query) use ($tenantPermissionModels): void {
                    foreach ($tenantPermissionModels as $index => $model) {
                        $method = $index === 0 ? 'where' : 'orWhere';
                        $query->{$method}('name', 'like', "%:{$model}");
                    }
                })
                ->pluck('name')
                ->all(),
        );

        $defaultSite = Site::query()->updateOrCreate(
            ['slug' => env('CMS_SITE_SLUG', Str::slug(config('app.name')) ?: 'main-site')],
            [
                'name' => env('CMS_SITE_NAME', config('app.name')),
                'description' => 'The primary platform-managed website for this CMS instance.',
                'owner_id' => $admin->getKey(),
                'is_active' => true,
            ],
        );

        foreach (['categories', 'tags', 'posts', 'pages', 'settings', 'menus'] as $table) {
            DB::table($table)
                ->whereNull('site_id')
                ->update(['site_id' => $defaultSite->getKey()]);
        }

        Site::query()
            ->whereNotNull('owner_id')
            ->get()
            ->each(function (Site $site): void {
                $site->users()->syncWithoutDetaching([
                    $site->owner_id => ['role' => 'owner'],
                ]);

                $owner = $site->owner;

                if ($owner && ! $owner->hasRole('super_admin')) {
                    $owner->assignRole('site_owner');
                }
            });

        Setting::query()->updateOrCreate(
            ['site_id' => $defaultSite->getKey()],
            [
                'site_name' => $defaultSite->name,
                'site_tagline' => 'A Laravel and Filament powered CMS',
                'site_description' => 'A modern multi-tenant content management system foundation built with Laravel and Filament.',
                'site_email' => env('CMS_ADMIN_EMAIL', 'admin@cms.test'),
                'posts_per_page' => 10,
                'meta_title' => $defaultSite->name,
                'meta_description' => 'Manage tenant websites, pages, posts, menus, media, roles, and site settings from a single admin platform.',
            ],
        );

        Site::query()->get()->each(function (Site $site): void {
            Setting::query()->firstOrCreate(
                ['site_id' => $site->getKey()],
                [
                    'site_name' => $site->name,
                    'site_description' => $site->description,
                    'posts_per_page' => 10,
                    'meta_title' => $site->name,
                ],
            );
        });

        $provisioner = app(SiteProvisioner::class);
        $pageNavigationManager = app(PageNavigationManager::class);

        Site::query()->get()->each(function (Site $site) use ($provisioner): void {
            $provisioner->provision($site);
        });

        Site::query()
            ->with([
                'pages' => fn ($query) => $query
                    ->whereNull('parent_id')
                    ->orderBy('sort_order')
                    ->orderBy('title'),
            ])
            ->get()
            ->each(function (Site $site) use ($pageNavigationManager): void {
                $site->pages->each(fn ($page) => $pageNavigationManager->sync($page));
            });
    }
}
