<?php

namespace Tests\Feature;

use App\Enums\ContentStatus;
use App\Filament\Resources\ActivityResource;
use App\Filament\Resources\ActivityResource\Pages\ListActivities as ActivityListActivitiesPage;
use App\Filament\Widgets\CmsForgeAlertWidget;
use App\Filament\Widgets\ContentStatsOverview;
use App\Filament\Widgets\RecentContent;
use App\Filament\Widgets\SiteStatusAlertWidget;
use App\Layup\Widgets\ImageWidget;
use App\Policies\SitePolicy;
use App\Models\Menu;
use App\Models\Page;
use App\Models\Post;
use App\Models\Site;
use App\Support\CmsForgeBanner;
use App\Support\PageBuilderContent;
use App\Support\SiteStatusBanner;
use App\Models\User;
use App\Support\MasonContent;
use App\Providers\Filament\AdminPanelProvider;
use Datlechin\FilamentMenuBuilder\Models\MenuItem;
use Datlechin\FilamentMenuBuilder\Models\MenuLocation;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use MrAdder\FilamentLogger\Support\ActivityReviewLink;
use MrAdder\FilamentLogger\Support\ActivityEvents;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use ReflectionMethod;
use ReflectionProperty;
use Tests\TestCase;
use Slimani\MediaManager\Form\MediaPicker;

class TenancyTest extends TestCase
{
    use RefreshDatabase;

    public function test_platform_home_lists_only_active_sites(): void
    {
        $activeSite = Site::query()->create([
            'name' => 'Active Site',
            'slug' => 'active-site',
            'is_active' => true,
        ]);

        Site::query()->create([
            'name' => 'Inactive Site',
            'slug' => 'inactive-site',
            'is_active' => false,
        ]);

        $response = $this->get('/');

        $response
            ->assertOk()
            ->assertSee('CMS Forge alert')
            ->assertSee('Two panels, one CMS Forge')
            ->assertSee('Platform login')
            ->assertSee('Site owner login')
            ->assertSee('Create an account')
            ->assertSee($activeSite->name)
            ->assertDontSee('Inactive Site');
    }

    public function test_active_sites_have_a_public_starting_page(): void
    {
        $site = Site::query()->create([
            'name' => 'Tenant One',
            'slug' => 'tenant-one',
            'is_active' => true,
        ]);

        $response = $this->get("/sites/{$site->slug}");

        $response
            ->assertOk()
            ->assertSee('Tenant One')
            ->assertSee('Start with your own pages, posts, menus, and settings instead of placeholder content.');
    }

    public function test_mason_builder_content_renders_on_public_pages(): void
    {
        $site = Site::query()->create([
            'name' => 'Builder Site',
            'slug' => 'builder-site',
            'is_active' => true,
        ]);

        $homepage = Page::query()->create([
            'site_id' => $site->getKey(),
            'title' => 'Builder Home',
            'slug' => 'builder-home',
            'status' => ContentStatus::Published,
            'published_at' => now(),
            'is_homepage' => true,
            'show_in_menu' => false,
            'content' => MasonContent::blocks(
                MasonContent::hero(
                    heading: 'Build with blocks',
                    copy: 'This page now uses Mason bricks.',
                    eyebrow: 'Builder',
                    primaryLabel: 'Start here',
                    primaryUrl: '/start',
                ),
            ),
        ]);

        $this->get("/sites/{$site->slug}")
            ->assertOk()
            ->assertSee('Build with blocks')
            ->assertSee('This page now uses Mason bricks.')
            ->assertSee('Start here');
    }

    public function test_legacy_html_content_is_rendered_after_builder_upgrade(): void
    {
        $site = Site::query()->create([
            'name' => 'Legacy Site',
            'slug' => 'legacy-site',
            'is_active' => true,
        ]);

        $homepage = Page::query()->create([
            'site_id' => $site->getKey(),
            'title' => 'Legacy Home',
            'slug' => 'legacy-home',
            'status' => ContentStatus::Published,
            'published_at' => now(),
            'is_homepage' => true,
            'show_in_menu' => false,
            'content' => MasonContent::blocks(
                MasonContent::richText('<p>Original builder content.</p>'),
            ),
        ]);

        DB::table('pages')
            ->where('id', $homepage->getKey())
            ->update([
                'content' => '<p>Legacy builder-safe content.</p>',
            ]);

        $this->get("/sites/{$site->slug}")
            ->assertOk()
            ->assertSee('Legacy builder-safe content.');
    }

    public function test_layup_image_widget_keeps_media_state_inside_the_builder_payload(): void
    {
        $imagePicker = collect(ImageWidget::getFormSchema())
            ->first(fn ($component): bool => $component instanceof MediaPicker);

        $this->assertInstanceOf(MediaPicker::class, $imagePicker);
        $this->assertSame('src', $imagePicker->getName());

        $defaults = ImageWidget::getDefaultData();
        $this->assertArrayHasKey('src', $defaults);
        $this->assertArrayHasKey('image', $defaults);

        $reflection = new ReflectionProperty($imagePicker, 'saveRelationshipsUsing');
        $reflection->setAccessible(true);

        $this->assertNull($reflection->getValue($imagePicker));
    }

    public function test_layup_image_payloads_are_normalized_with_a_src_alias_for_preview(): void
    {
        $normalized = PageBuilderContent::normalize([
            'rows' => [
                [
                    'id' => 'row_1',
                    'settings' => [],
                    'columns' => [
                        [
                            'id' => 'col_1',
                            'span' => 12,
                            'settings' => [],
                            'widgets' => [
                                [
                                    'id' => 'widget_1',
                                    'type' => 'image',
                                    'data' => [
                                        'image' => 12,
                                        'alt' => 'Hero image',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $data = $normalized['rows'][0]['columns'][0]['widgets'][0]['data'];

        $this->assertSame(12, $data['src']);
        $this->assertSame(12, $data['image']);
        $this->assertSame('Hero image', $data['alt']);
    }

    public function test_public_tenant_sites_hide_platform_controls_from_guests(): void
    {
        $site = Site::query()->create([
            'name' => 'Client Facing Site',
            'slug' => 'client-facing-site',
            'is_active' => true,
        ]);

        $this->get("/sites/{$site->slug}")
            ->assertOk()
            ->assertDontSee('Platform')
            ->assertDontSee('Admin')
            ->assertDontSee('Dashboard');
    }

    public function test_site_managers_see_dashboard_shortcut_on_public_sites(): void
    {
        $owner = User::query()->create([
            'name' => 'Owner',
            'email' => 'site-owner@example.test',
            'password' => 'password',
        ]);

        $site = Site::query()->create([
            'name' => 'Managed Site',
            'slug' => 'managed-site',
            'owner_id' => $owner->getKey(),
            'is_active' => true,
        ]);

        $this->actingAs($owner)
            ->get("/sites/{$site->slug}")
            ->assertOk()
            ->assertSee('Dashboard')
            ->assertSee(route('filament.admin.pages.dashboard', ['tenant' => $site]), false)
            ->assertDontSee('Platform');
    }

    public function test_site_managers_see_tenant_aware_admin_shortcuts_on_public_sites(): void
    {
        $owner = User::query()->create([
            'name' => 'Owner',
            'email' => 'shortcut-owner@example.test',
            'password' => 'password',
        ]);

        $site = Site::query()->create([
            'name' => 'Shortcut Site',
            'slug' => 'shortcut-site',
            'owner_id' => $owner->getKey(),
            'is_active' => true,
        ]);

        $this->actingAs($owner)
            ->get("/sites/{$site->slug}")
            ->assertOk()
            ->assertSee(\App\Filament\Resources\Pages\PageResource::getUrl('create', panel: 'admin', tenant: $site), false)
            ->assertSee(\App\Filament\Resources\Posts\PostResource::getUrl('create', panel: 'admin', tenant: $site), false)
            ->assertSee(\Datlechin\FilamentMenuBuilder\Resources\MenuResource::getUrl('index', panel: 'admin', tenant: $site), false);
    }

    public function test_recent_content_widget_scopes_results_to_the_active_tenant(): void
    {
        $owner = User::query()->create([
            'name' => 'Widget Owner',
            'email' => 'widget-owner@example.test',
            'password' => 'password',
        ]);

        $site = Site::query()->create([
            'name' => 'Widget Site',
            'slug' => 'widget-site',
            'owner_id' => $owner->getKey(),
            'is_active' => true,
        ]);

        $otherSite = Site::query()->create([
            'name' => 'Other Widget Site',
            'slug' => 'other-widget-site',
            'is_active' => true,
        ]);

        $sitePost = Post::query()->create([
            'site_id' => $site->getKey(),
            'user_id' => $owner->getKey(),
            'title' => 'Tenant Post',
            'slug' => 'tenant-post',
            'status' => ContentStatus::Published,
            'published_at' => now(),
        ]);

        Post::query()->create([
            'site_id' => $otherSite->getKey(),
            'user_id' => $owner->getKey(),
            'title' => 'Other Tenant Post',
            'slug' => 'other-tenant-post',
            'status' => ContentStatus::Published,
            'published_at' => now(),
        ]);

        $previousPanel = Filament::getCurrentPanel();

        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Filament::setTenant($site, true);

        try {
            $widget = app(RecentContent::class);
            $method = new ReflectionMethod($widget, 'scopeToTenant');
            $method->setAccessible(true);

            $scopedQuery = $method->invoke($widget, Post::query()->withoutGlobalScopes());

            $this->assertSame([$sitePost->getKey()], $scopedQuery->pluck('id')->all());
        } finally {
            Filament::setTenant(null, true);
            Filament::setCurrentPanel($previousPanel);
        }
    }

    public function test_content_stats_widget_scopes_counts_to_the_active_tenant(): void
    {
        $owner = User::query()->create([
            'name' => 'Stats Owner',
            'email' => 'stats-owner@example.test',
            'password' => 'password',
        ]);

        $site = Site::query()->create([
            'name' => 'Stats Site',
            'slug' => 'stats-site',
            'owner_id' => $owner->getKey(),
            'is_active' => true,
        ]);

        $otherSite = Site::query()->create([
            'name' => 'Other Stats Site',
            'slug' => 'other-stats-site',
            'is_active' => true,
        ]);

        Post::query()->create([
            'site_id' => $site->getKey(),
            'user_id' => $owner->getKey(),
            'title' => 'Stats Tenant Post',
            'slug' => 'stats-tenant-post',
            'status' => ContentStatus::Published,
            'published_at' => now(),
        ]);

        Post::query()->create([
            'site_id' => $otherSite->getKey(),
            'user_id' => $owner->getKey(),
            'title' => 'Stats Other Tenant Post',
            'slug' => 'stats-other-tenant-post',
            'status' => ContentStatus::Published,
            'published_at' => now(),
        ]);

        $previousPanel = Filament::getCurrentPanel();

        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Filament::setTenant($site, true);

        try {
            $widget = app(ContentStatsOverview::class);
            $method = new ReflectionMethod($widget, 'scopeToTenant');
            $method->setAccessible(true);

            $scopedQuery = $method->invoke($widget, Post::query()->withoutGlobalScopes());

            $this->assertSame(1, $scopedQuery->count());
        } finally {
            Filament::setTenant(null, true);
            Filament::setCurrentPanel($previousPanel);
        }
    }

    public function test_activity_review_link_points_to_the_platform_activity_index(): void
    {
        $previousPanel = Filament::getCurrentPanel();

        Filament::setCurrentPanel(Filament::getPanel('admin'));

        try {
            $expectedUrl = ActivityResource::getUrl(
                'index',
                ['activeTab' => 'all'],
                panel: 'platform',
            );

            $this->assertSame($expectedUrl, ActivityReviewLink::toPlaybook('all_activity'));
        } finally {
            Filament::setCurrentPanel($previousPanel);
        }
    }

    public function test_super_admins_can_access_the_platform_panel(): void
    {
        $role = Role::query()->create([
            'name' => 'super_admin',
            'guard_name' => 'web',
        ]);

        $user = User::query()->create([
            'name' => 'Platform Admin',
            'email' => 'platform-admin@example.test',
            'password' => 'password',
        ]);

        $user->assignRole($role);

        $this->assertTrue($user->canAccessPanel(Filament::getPanel('platform')));

        $this->actingAs($user)
            ->get(Filament::getPanel('platform')->getUrl())
            ->assertOk();
    }

    public function test_site_managers_are_limited_to_the_tenant_panel(): void
    {
        $role = Role::query()->create([
            'name' => 'site_owner',
            'guard_name' => 'web',
        ]);

        $user = User::query()->create([
            'name' => 'Site Manager',
            'email' => 'site-manager@example.test',
            'password' => 'password',
        ]);

        $user->assignRole($role);

        $this->assertTrue($user->canAccessPanel(Filament::getPanel('admin')));
        $this->assertFalse($user->canAccessPanel(Filament::getPanel('platform')));
    }

    public function test_filament_panels_use_the_custom_login_page(): void
    {
        $this->assertSame(
            \App\Filament\Auth\Login::class,
            Filament::getPanel('admin')->getLoginRouteAction(),
        );

        $this->assertSame(
            \App\Filament\Auth\Login::class,
            Filament::getPanel('platform')->getLoginRouteAction(),
        );
    }

    public function test_cms_forge_alert_widget_builds_panel_specific_copy(): void
    {
        $widget = app(CmsForgeAlertWidget::class);
        $widget->panelId = 'platform';
        $widget->context = 'login';

        $method = new ReflectionMethod($widget, 'getAlertDefinition');
        $method->setAccessible(true);

        $loginDefinition = $method->invoke($widget);

        $this->assertSame('Platform sign in', $loginDefinition['title']);
        $this->assertSame('Switch to site owner login', $loginDefinition['action_label']);

        $widget->panelId = 'admin';
        $widget->context = 'dashboard';

        $dashboardDefinition = $method->invoke($widget);

        $this->assertSame('Site workspace', $dashboardDefinition['title']);
        $this->assertSame('Open CMS Forge home', $dashboardDefinition['action_label']);
    }

    public function test_admin_dashboard_alerts_render_in_a_single_sticky_stack(): void
    {
        $previousPanel = Filament::getCurrentPanel();
        $previousTenant = Filament::getTenant();

        $site = Site::query()->create([
            'name' => 'Paused Tenant',
            'slug' => 'paused-tenant-stack',
            'is_active' => false,
        ]);

        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Filament::setTenant($site, isQuiet: true);

        try {
            $provider = new AdminPanelProvider(app());
            $method = new ReflectionMethod($provider, 'renderAdminContentBanners');
            $method->setAccessible(true);

            $html = $method->invoke($provider);

            $this->assertStringContainsString('cms-forge-panel-alert-stack', $html);
            $this->assertStringContainsString('Site workspace', $html);
            $this->assertStringContainsString($site->name, $html);
        } finally {
            Filament::setCurrentPanel($previousPanel);
            Filament::setTenant($previousTenant, isQuiet: true);
        }
    }

    public function test_cms_forge_alert_widget_can_be_dismissed_temporarily_and_returns_after_the_ttl(): void
    {
        session()->start();

        $widget = app(CmsForgeAlertWidget::class);
        $widget->panelId = 'admin';
        $widget->context = 'dashboard';
        $widget->dismissCmsForgeAlert();

        $this->assertFalse($widget->shouldRenderAlert());

        $html = app('livewire')->mount(CmsForgeAlertWidget::class, [
            'panelId' => 'admin',
            'context' => 'dashboard',
        ]);

        $this->assertStringContainsString('fi-wi-cms-forge-alert', $html);
        $this->assertStringContainsString('hidden', $html);
        $this->assertStringContainsString('wire:poll.15s', $html);
        $this->assertStringNotContainsString('Site workspace', $html);

        $this->travel(61)->seconds();

        $htmlAfterExpiry = app('livewire')->mount(CmsForgeAlertWidget::class, [
            'panelId' => 'admin',
            'context' => 'dashboard',
        ]);

        $this->assertStringContainsString('Site workspace', $htmlAfterExpiry);
    }

    public function test_cms_forge_alert_widget_close_button_uses_filament_actions(): void
    {
        session()->start();

        app('livewire')
            ->test(CmsForgeAlertWidget::class, [
                'panelId' => 'admin',
                'context' => 'dashboard',
            ])
            ->call('mountAction', 'dismissCmsForgeAlert', [], [
                'schemaComponent' => 'content.cmsForgeAlert',
            ]);

        $html = app('livewire')->mount(CmsForgeAlertWidget::class, [
            'panelId' => 'admin',
            'context' => 'dashboard',
        ]);

        $this->assertStringContainsString('fi-wi-cms-forge-alert', $html);
        $this->assertStringContainsString('hidden', $html);
        $this->assertStringContainsString('wire:poll.15s', $html);
        $this->assertStringNotContainsString('CMS Forge notice paused', $html);
        $this->assertStringNotContainsString('Show again', $html);

        $widget = app(CmsForgeAlertWidget::class);
        $widget->panelId = 'admin';
        $widget->context = 'dashboard';

        $this->assertFalse($widget->shouldRenderAlert());
    }

    public function test_cms_forge_alert_widget_is_not_sticky_on_login(): void
    {
        $html = app('livewire')->mount(CmsForgeAlertWidget::class, [
            'panelId' => 'platform',
            'context' => 'login',
        ]);

        $this->assertStringNotContainsString('cms-forge-panel-alert-stack', $html);
        $this->assertStringContainsString('Platform sign in', $html);
    }

    public function test_activity_policy_is_registered_for_spatie_activity_records(): void
    {
        $this->assertSame(
            \App\Policies\ActivityPolicy::class,
            get_class(Gate::getPolicyFor(Activity::class)),
        );
    }

    public function test_platform_activity_log_page_is_accessible_to_super_admins(): void
    {
        $role = Role::query()->create([
            'name' => 'super_admin',
            'guard_name' => 'web',
        ]);

        $user = User::query()->create([
            'name' => 'Activity Viewer',
            'email' => 'activity-viewer@example.test',
            'password' => 'password',
        ]);

        $user->assignRole($role);

        $this->actingAs($user)
            ->get(ActivityResource::getUrl('index', [
                'activeTab' => 'all',
            ], isAbsolute: false, panel: 'platform'))
            ->assertOk();
    }

    public function test_activity_log_tabs_display_record_counts(): void
    {
        $previousPanel = Filament::getCurrentPanel();

        Filament::setCurrentPanel(Filament::getPanel('platform'));

        try {
            Activity::query()->create([
                'log_name' => config('filament-logger.access.log_name'),
                'description' => 'Failed login attempt',
                'event' => ActivityEvents::FAILED_LOGIN,
                'properties' => ['risk' => 'high'],
            ]);

            Activity::query()->create([
                'log_name' => config('filament-logger.resources.log_name'),
                'description' => 'Deleted page',
                'event' => 'Deleted',
                'properties' => ['risk' => 'high'],
            ]);

            Activity::query()->create([
                'log_name' => config('filament-logger.resources.log_name'),
                'description' => 'Updated page',
                'event' => 'Updated',
                'properties' => ['risk' => 'low'],
            ]);

            $tabs = app(ActivityListActivitiesPage::class)->getTabs();

            $this->assertSame(3, $tabs['all']->getBadge());
            $this->assertSame(2, $tabs['high_risk']->getBadge());
            $this->assertSame(1, $tabs['auth_issues']->getBadge());
            $this->assertSame(1, $tabs['failed_logins']->getBadge());
            $this->assertSame(1, $tabs['destructive']->getBadge());
            $this->assertSame(1, $tabs['destructive_recent']->getBadge());
            $this->assertSame(1, $tabs['auth_anomalies']->getBadge());
        } finally {
            Filament::setCurrentPanel($previousPanel);
        }
    }

    public function test_site_owners_can_access_owned_tenants_even_without_an_explicit_membership_pivot(): void
    {
        $owner = User::query()->create([
            'name' => 'Owner',
            'email' => 'owner-without-pivot@example.test',
            'password' => 'password',
        ]);

        $site = Site::query()->create([
            'name' => 'Owned Site',
            'slug' => 'owned-site',
            'owner_id' => $owner->getKey(),
            'is_active' => true,
        ]);

        DB::table('site_user')
            ->where('site_id', $site->getKey())
            ->where('user_id', $owner->getKey())
            ->delete();

        $this->assertTrue($owner->canAccessTenant($site));
        $this->assertContains($site->getKey(), $owner->getTenants(Filament::getPanel('admin'))->pluck('id')->all());
    }

    public function test_site_resource_list_access_follows_granted_permissions(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permission = Permission::query()->create([
            'name' => 'ViewAny:Site',
            'guard_name' => 'web',
        ]);

        $role = Role::query()->create([
            'name' => 'site_manager',
            'guard_name' => 'web',
        ]);

        $role->givePermissionTo($permission);

        $user = User::query()->create([
            'name' => 'Site Manager',
            'email' => 'site-manager@example.test',
            'password' => 'password',
        ]);

        $user->assignRole($role);

        $this->assertTrue((new SitePolicy())->viewAny($user));
    }

    public function test_inactive_sites_are_not_publicly_accessible(): void
    {
        $site = Site::query()->create([
            'name' => 'Hidden Tenant',
            'slug' => 'hidden-tenant',
            'is_active' => false,
        ]);

        $this->get("/sites/{$site->slug}")
            ->assertNotFound();
    }

    public function test_owners_can_preview_their_own_inactive_site(): void
    {
        $owner = User::query()->create([
            'name' => 'Preview Owner',
            'email' => 'preview-owner@example.test',
            'password' => 'password',
        ]);

        $site = Site::query()->create([
            'name' => 'Hidden Tenant',
            'slug' => 'hidden-preview-tenant',
            'owner_id' => $owner->getKey(),
            'is_active' => false,
        ]);

        // Guests get a 404 for inactive sites (see the test above); the owner
        // can preview the live site instead.
        $this->actingAs($owner)
            ->get("/sites/{$site->slug}")
            ->assertOk();
    }

    public function test_inactive_site_banner_is_built_for_the_panel(): void
    {
        $role = Role::query()->create([
            'name' => 'site_owner',
            'guard_name' => 'web',
        ]);

        $user = User::query()->create([
            'name' => 'Site Owner',
            'email' => 'site-owner-banner@example.test',
            'password' => 'password',
        ]);

        $user->assignRole($role);

        $site = Site::query()->create([
            'name' => 'Paused Tenant',
            'slug' => 'paused-tenant',
            'owner_id' => $user->getKey(),
            'is_active' => false,
        ]);

        $banner = SiteStatusBanner::forSite(
            $site,
            'https://example.test/admin/site/paused-tenant',
            'https://example.test/sites/paused-tenant',
        );

        $this->assertNotNull($banner);
        $this->assertSame('Workspace alert', $banner['eyebrow']);
        $this->assertSame('Inactive', $banner['badge']);
        $this->assertSame('Paused Tenant', $banner['title']);
        $this->assertSame('This site is currently inactive. Visitors see the custom error page, but you can still preview it until you reactivate it.', $banner['description']);
        $this->assertSame('Open site profile', $banner['primary_label']);
        $this->assertSame('https://example.test/admin/site/paused-tenant', $banner['primary_url']);
        $this->assertSame('Preview your site', $banner['secondary_label']);
        $this->assertSame('https://example.test/sites/paused-tenant', $banner['secondary_url']);
    }

    public function test_inactive_site_alert_widget_renders_the_banner_copy(): void
    {
        $banner = SiteStatusBanner::forSite(
            Site::query()->create([
                'name' => 'Paused Tenant',
                'slug' => 'paused-tenant',
                'is_active' => false,
            ]),
            'https://example.test/admin/site/paused-tenant',
            'https://example.test/sites/paused-tenant',
        );

        $html = app('livewire')->mount(SiteStatusAlertWidget::class, [
            'banner' => $banner,
        ]);

        $this->assertStringContainsString('Paused Tenant', $html);
        $this->assertStringContainsString('This site is currently inactive.', $html);
        $this->assertStringContainsString('Open site profile', $html);
        $this->assertStringContainsString('Preview your site', $html);
    }

    public function test_inactive_site_alert_widget_reappears_when_the_site_changes(): void
    {
        session()->start();

        $site = Site::query()->create([
            'name' => 'Paused Tenant',
            'slug' => 'paused-tenant-sticky',
            'is_active' => false,
        ]);

        $banner = SiteStatusBanner::forSite(
            $site,
            'https://example.test/admin/site/paused-tenant-sticky',
            'https://example.test/sites/paused-tenant-sticky',
        );

        $widget = app(SiteStatusAlertWidget::class);
        $widget->banner = $banner;
        $widget->dismissSiteStatusAlert();

        $this->assertFalse($widget->shouldRenderAlert());

        $this->travel(1)->minute();
        $site->touch();
        $site->refresh();

        $updatedBanner = SiteStatusBanner::forSite(
            $site,
            'https://example.test/admin/site/paused-tenant-sticky',
            'https://example.test/sites/paused-tenant-sticky',
        );

        $updatedWidget = app(SiteStatusAlertWidget::class);
        $updatedWidget->banner = $updatedBanner;

        $this->assertTrue($updatedWidget->shouldRenderAlert());

        $html = app('livewire')->mount(SiteStatusAlertWidget::class, [
            'banner' => $updatedBanner,
        ]);

        $this->assertStringContainsString('fi-wi-site-status-alert', $html);
        $this->assertStringContainsString($site->name, $html);
    }

    public function test_inactive_site_alert_widget_close_button_uses_filament_actions(): void
    {
        session()->start();

        $site = Site::query()->create([
            'name' => 'Paused Tenant',
            'slug' => 'paused-tenant-close',
            'is_active' => false,
        ]);

        $banner = SiteStatusBanner::forSite(
            $site,
            'https://example.test/admin/site/paused-tenant-close',
            'https://example.test/sites/paused-tenant-close',
        );

        app('livewire')
            ->test(SiteStatusAlertWidget::class, [
                'banner' => $banner,
            ])
            ->call('mountAction', 'dismissSiteStatusAlert', [], [
                'schemaComponent' => 'content.siteStatusAlert',
            ]);

        $html = app('livewire')->mount(SiteStatusAlertWidget::class, [
            'banner' => $banner,
        ]);

        $this->assertStringContainsString('fi-wi-site-status-alert', $html);
        $this->assertStringContainsString('hidden', $html);
        $this->assertStringContainsString('wire:poll.15s', $html);
        $this->assertStringNotContainsString('Site alert paused', $html);
        $this->assertStringNotContainsString('Show again', $html);

        $widget = app(SiteStatusAlertWidget::class);
        $widget->banner = $banner;

        $this->assertFalse($widget->shouldRenderAlert());
    }

    public function test_cms_forge_banner_is_built_for_landing_and_panel_contexts(): void
    {
        $landingBanner = CmsForgeBanner::forLanding('https://example.test/platform/login', 'https://example.test/admin/login');
        $platformBanner = CmsForgeBanner::forPanel('platform');
        $adminBanner = CmsForgeBanner::forPanel('admin');

        $this->assertSame('CMS Forge alert', $landingBanner['eyebrow']);
        $this->assertSame('Global notice', $landingBanner['badge']);
        $this->assertSame('Platform login', $landingBanner['primary_label']);
        $this->assertSame('Site owner login', $landingBanner['secondary_label']);

        $this->assertSame('Platform console', $platformBanner['title']);
        $this->assertSame('Open CMS Forge home', $platformBanner['primary_label']);

        $this->assertSame('Site workspace', $adminBanner['title']);
        $this->assertSame('Open CMS Forge home', $adminBanner['primary_label']);
    }

    public function test_default_site_navigation_shows_only_real_pages_without_placeholder_core_links(): void
    {
        $site = Site::query()->create([
            'name' => 'Navigation Site',
            'slug' => 'navigation-site',
            'is_active' => true,
        ]);

        $site->menus()->delete();

        Page::query()->create([
            'site_id' => $site->getKey(),
            'title' => 'About Us',
            'slug' => 'about-us',
            'status' => ContentStatus::Published,
            'published_at' => now(),
            'show_in_menu' => true,
            'sort_order' => 1,
        ]);

        Page::query()->create([
            'site_id' => $site->getKey(),
            'title' => 'Hidden Page',
            'slug' => 'hidden-page',
            'status' => ContentStatus::Published,
            'published_at' => now(),
            'show_in_menu' => false,
            'sort_order' => 2,
        ]);

        $this->get("/sites/{$site->slug}")
            ->assertOk()
            ->assertSee('About Us')
            ->assertDontSee('Blog')
            ->assertDontSee('Hidden Page');

        DB::table('posts')->insert([
            'site_id' => $site->getKey(),
            'title' => 'News',
            'slug' => 'news',
            'status' => ContentStatus::Published->value,
            'published_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->get("/sites/{$site->slug}")
            ->assertOk()
            ->assertSee('About Us')
            ->assertSee('View the blog')
            ->assertDontSee('Home');
    }

    public function test_new_sites_start_without_starter_pages_posts_or_navigation(): void
    {
        $owner = User::query()->create([
            'name' => 'Site Owner',
            'email' => 'owner@example.test',
            'password' => 'password',
        ]);

        $site = Site::query()->create([
            'name' => 'Starter Site',
            'slug' => 'starter-site',
            'owner_id' => $owner->getKey(),
            'is_active' => true,
        ]);

        $this->assertSame(0, $site->pages()->count());
        $this->assertSame(0, $site->posts()->count());
        $this->assertSame(0, $site->menus()->count());

        $this->get("/sites/{$site->slug}")
            ->assertOk()
            ->assertDontSee('Home')
            ->assertDontSee('Blog')
            ->assertDontSee('Post')
            ->assertDontSee('About')
            ->assertDontSee('Contact');
    }

    public function test_published_pages_are_automatically_synced_to_primary_navigation(): void
    {
        $site = Site::query()->create([
            'name' => 'Navigation Sync Site',
            'slug' => 'navigation-sync-site',
            'is_active' => true,
        ]);

        $page = Page::query()->create([
            'site_id' => $site->getKey(),
            'title' => 'Services',
            'slug' => 'services',
            'status' => ContentStatus::Published,
            'published_at' => now(),
            'show_in_menu' => true,
            'sort_order' => 30,
        ]);

        $menu = Menu::query()
            ->whereBelongsTo($site)
            ->where('name', 'Primary Navigation')
            ->firstOrFail();

        $this->assertDatabaseHas('menu_items', [
            'menu_id' => $menu->getKey(),
            'linkable_type' => $page->getMorphClass(),
            'linkable_id' => $page->getKey(),
            'title' => 'Services',
        ]);

        $this->get("/sites/{$site->slug}")
            ->assertOk()
            ->assertSee('Services');

        $page->update(['title' => 'Solutions']);

        $this->assertDatabaseHas('menu_items', [
            'menu_id' => $menu->getKey(),
            'linkable_type' => $page->getMorphClass(),
            'linkable_id' => $page->getKey(),
            'title' => 'Solutions',
        ]);

        $this->get("/sites/{$site->slug}")
            ->assertOk()
            ->assertSee('Solutions')
            ->assertDontSee('Services');
    }

    public function test_pages_are_removed_from_primary_navigation_when_hidden(): void
    {
        $site = Site::query()->create([
            'name' => 'Navigation Toggle Site',
            'slug' => 'navigation-toggle-site',
            'is_active' => true,
        ]);

        $page = Page::query()->create([
            'site_id' => $site->getKey(),
            'title' => 'Team',
            'slug' => 'team',
            'status' => ContentStatus::Published,
            'published_at' => now(),
            'show_in_menu' => true,
            'sort_order' => 40,
        ]);

        $page->update(['show_in_menu' => false]);

        $menu = Menu::query()
            ->whereBelongsTo($site)
            ->where('name', 'Primary Navigation')
            ->firstOrFail();

        $this->assertDatabaseMissing('menu_items', [
            'menu_id' => $menu->getKey(),
            'linkable_type' => $page->getMorphClass(),
            'linkable_id' => $page->getKey(),
        ]);

        $this->get("/sites/{$site->slug}")
            ->assertOk()
            ->assertDontSee('Team');
    }

    public function test_site_header_menu_is_rendered_on_the_public_site(): void
    {
        $site = Site::query()->create([
            'name' => 'Tenant One',
            'slug' => 'tenant-one',
            'is_active' => true,
        ]);

        $site->menus()->delete();

        $menu = Menu::query()->create([
            'site_id' => $site->getKey(),
            'name' => 'Primary Navigation',
            'is_visible' => true,
        ]);

        MenuLocation::query()->create([
            'menu_id' => $menu->getKey(),
            'location' => 'header',
        ]);

        MenuItem::query()->create([
            'menu_id' => $menu->getKey(),
            'title' => 'Cars',
            'url' => '/cars',
            'order' => 1,
        ]);

        $this->get("/sites/{$site->slug}")
            ->assertOk()
            ->assertSee('Cars');
    }

    public function test_single_visible_site_menu_is_used_as_a_header_fallback(): void
    {
        $site = Site::query()->create([
            'name' => 'Fallback Site',
            'slug' => 'fallback-site',
            'is_active' => true,
        ]);

        $site->menus()->delete();

        $menu = Menu::query()->create([
            'site_id' => $site->getKey(),
            'name' => 'Primary Navigation',
            'is_visible' => true,
        ]);

        MenuItem::query()->create([
            'menu_id' => $menu->getKey(),
            'title' => 'Inventory',
            'url' => '/inventory',
            'order' => 1,
        ]);

        $this->get("/sites/{$site->slug}")
            ->assertOk()
            ->assertSee('Inventory');
    }
}
