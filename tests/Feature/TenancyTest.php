<?php

namespace Tests\Feature;

use App\Enums\ContentStatus;
use App\Models\Menu;
use App\Models\Page;
use App\Models\Site;
use App\Models\User;
use App\Support\MasonContent;
use Datlechin\FilamentMenuBuilder\Models\MenuItem;
use Datlechin\FilamentMenuBuilder\Models\MenuLocation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

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
            ->assertDontSee('Platform');
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
