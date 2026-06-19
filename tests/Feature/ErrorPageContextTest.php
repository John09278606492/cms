<?php

namespace Tests\Feature;

use App\Enums\ContentStatus;
use App\Models\Post;
use App\Models\Site;
use App\Models\User;
use App\Support\ErrorPageContext;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class ErrorPageContextTest extends TestCase
{
    use RefreshDatabase;

    public function test_missing_site_paths_render_a_site_not_found_message(): void
    {
        $context = app(ErrorPageContext::class)->resolve404(
            Request::create('/sites/missing-site', 'GET'),
        );

        $this->assertSame('Site not found', $context['title']);
        $this->assertSame(route('platform.home'), $context['primaryUrl']);
    }

    public function test_inactive_sites_render_a_site_unavailable_message(): void
    {
        $site = Site::query()->create([
            'name' => 'Archived Site',
            'slug' => 'archived-site',
            'is_active' => false,
        ]);

        $context = app(ErrorPageContext::class)->resolve404(
            Request::create("/sites/{$site->slug}", 'GET'),
        );

        $this->assertSame('Site unavailable', $context['title']);
        $this->assertSame(route('sites.home', $site), $context['secondaryUrl']);
    }

    public function test_missing_blog_posts_render_a_blog_not_found_message(): void
    {
        $owner = User::query()->create([
            'name' => 'Owner',
            'email' => 'owner@example.test',
            'password' => 'password',
        ]);

        $site = Site::query()->create([
            'name' => 'Tenant Site',
            'slug' => 'tenant-site',
            'owner_id' => $owner->getKey(),
            'is_active' => true,
        ]);

        $context = app(ErrorPageContext::class)->resolve404(
            Request::create("/sites/{$site->slug}/blog/missing-post", 'GET'),
        );

        $this->assertSame('Blog post not found', $context['title']);
        $this->assertSame(route('sites.blog.index', $site), $context['primaryUrl']);
    }

    public function test_unpublished_blog_posts_render_a_not_published_message(): void
    {
        $owner = User::query()->create([
            'name' => 'Owner',
            'email' => 'owner2@example.test',
            'password' => 'password',
        ]);

        $site = Site::query()->create([
            'name' => 'Tenant Site',
            'slug' => 'tenant-site-2',
            'owner_id' => $owner->getKey(),
            'is_active' => true,
        ]);

        Post::query()->create([
            'site_id' => $site->getKey(),
            'user_id' => $owner->getKey(),
            'title' => 'Draft Article',
            'slug' => 'draft-article',
            'status' => ContentStatus::Draft,
        ]);

        $context = app(ErrorPageContext::class)->resolve404(
            Request::create("/sites/{$site->slug}/blog/draft-article", 'GET'),
        );

        $this->assertSame('Blog post not published', $context['title']);
    }

    public function test_admin_forbidden_routes_render_an_admin_access_denied_message(): void
    {
        $context = app(ErrorPageContext::class)->resolve403(
            Request::create('/admin/posts', 'GET'),
        );

        $this->assertSame('Admin access denied', $context['title']);
        $this->assertSame(Filament::getLoginUrl(), $context['secondaryUrl']);
    }
}
