<?php

namespace Tests\Feature;

use App\Enums\ContentStatus;
use App\Models\Page;
use App\Models\Post;
use App\Models\Site;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageBuilderWidgetsTest extends TestCase
{
    use RefreshDatabase;

    protected function publish(Site $site, array $content): string
    {
        Page::query()->create([
            'site_id' => $site->getKey(),
            'title' => 'Widgets',
            'slug' => 'widgets-home',
            'status' => ContentStatus::Published,
            'published_at' => now(),
            'is_homepage' => true,
            'show_in_menu' => false,
            'content' => $content,
        ]);

        return $this->get("/sites/{$site->slug}")->assertOk()->getContent();
    }

    public function test_interactive_widgets_render_their_markup(): void
    {
        $site = Site::query()->create(['name' => 'W', 'slug' => 'w-site', 'is_active' => true]);

        $html = $this->publish($site, [
            ['type' => 'icon_box', 'data' => ['icon' => '★', 'title' => 'Reliable', 'text' => 'Always on']],
            ['type' => 'counter', 'data' => ['items' => [['value' => 1200, 'suffix' => '+', 'label' => 'Users']]]],
            ['type' => 'progress_bars', 'data' => ['items' => [['label' => 'Design', 'percent' => 90]]]],
            ['type' => 'star_rating', 'data' => ['rating' => '4']],
            ['type' => 'social_icons', 'data' => ['links' => [['network' => 'github', 'url' => 'https://github.com']]]],
            ['type' => 'carousel', 'data' => ['images' => ['page-builder/a.jpg', 'page-builder/b.jpg']]],
            ['type' => 'countdown', 'data' => ['heading' => 'Launch', 'until' => now()->addDay()->toDateTimeString()]],
            ['type' => 'map', 'data' => ['query' => 'Eiffel Tower, Paris']],
        ]);

        $this->assertStringContainsString('Reliable', $html);
        $this->assertStringContainsString('data-target="1200"', $html);
        $this->assertStringContainsString('width: 90%', $html);
        $this->assertStringContainsString('github.com', $html);
        $this->assertStringContainsString('pb-carousel', $html);
        $this->assertStringContainsString('pb-countdown', $html);
        $this->assertStringContainsString('google.com/maps?q=Eiffel', $html);
    }

    public function test_container_renders_its_nested_widgets(): void
    {
        $site = Site::query()->create(['name' => 'Box', 'slug' => 'box-site', 'is_active' => true]);

        $html = $this->publish($site, [
            ['type' => 'container', 'data' => [
                'direction' => 'row',
                'gap' => 'md',
                'blocks' => [
                    ['type' => 'heading', 'data' => ['text' => 'Inside the box', 'level' => 'h3']],
                    ['type' => 'button', 'data' => ['label' => 'Nested button', 'url' => '#', 'style' => 'primary']],
                ],
            ]],
        ]);

        $this->assertStringContainsString('Inside the box', $html);
        $this->assertStringContainsString('Nested button', $html);
        $this->assertStringContainsString('flex-row', $html);
    }

    public function test_posts_grid_pulls_published_posts_for_the_site(): void
    {
        $site = Site::query()->create(['name' => 'Blog', 'slug' => 'blog-site', 'is_active' => true]);
        $other = Site::query()->create(['name' => 'Other', 'slug' => 'other-site', 'is_active' => true]);

        Post::query()->create([
            'site_id' => $site->getKey(),
            'title' => 'Our First Story',
            'slug' => 'our-first-story',
            'status' => ContentStatus::Published,
            'published_at' => now()->subDay(),
        ]);

        Post::query()->create([
            'site_id' => $other->getKey(),
            'title' => 'Another Sites Post',
            'slug' => 'another-sites-post',
            'status' => ContentStatus::Published,
            'published_at' => now(),
        ]);

        $html = $this->publish($site, [
            ['type' => 'posts_grid', 'data' => ['heading' => 'Latest', 'count' => '3', 'columns' => '3']],
        ]);

        $this->assertStringContainsString('Our First Story', $html);
        $this->assertStringNotContainsString('Another Sites Post', $html);
    }
}
