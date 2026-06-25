<?php

namespace Tests\Feature;

use App\Enums\ContentStatus;
use App\Models\Page;
use App\Models\Site;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageBuilderDesignTest extends TestCase
{
    use RefreshDatabase;

    protected function renderPageWith(array $design): string
    {
        $site = Site::query()->create([
            'name' => 'Design Site',
            'slug' => 'design-site-'.uniqid(),
            'is_active' => true,
        ]);

        Page::query()->create([
            'site_id' => $site->getKey(),
            'title' => 'Design Home',
            'slug' => 'design-home',
            'status' => ContentStatus::Published,
            'published_at' => now(),
            'is_homepage' => true,
            'show_in_menu' => false,
            'content' => [
                ['type' => 'heading', 'data' => array_merge(['text' => 'Styled heading', 'level' => 'h2'], $design)],
            ],
        ]);

        return $this->get("/sites/{$site->slug}")->assertOk()->getContent();
    }

    public function test_gradient_background_renders(): void
    {
        $html = $this->renderPageWith(['_bg' => '#0f172a', '_grad_to' => '#7c3aed', '_pad' => 'lg']);

        $this->assertStringContainsString('linear-gradient(135deg, #0f172a, #7c3aed)', $html);
        $this->assertStringContainsString('py-16', $html);
    }

    public function test_border_shadow_radius_and_margins_render(): void
    {
        $html = $this->renderPageWith([
            '_border_width' => '2',
            '_border_color' => '#f59e0b',
            '_shadow' => 'lg',
            '_radius' => 'lg',
            '_mt' => 'md',
            '_mb' => 'xl',
        ]);

        $this->assertStringContainsString('border: 2px solid #f59e0b', $html);
        $this->assertStringContainsString('shadow-lg', $html);
        $this->assertStringContainsString('rounded-2xl', $html);
        $this->assertStringContainsString('mt-10', $html);
        $this->assertStringContainsString('mb-24', $html);
    }

    public function test_text_colour_overrides_descendant_block_colours(): void
    {
        $html = $this->renderPageWith(['_text_color' => '#ffffff']);

        $this->assertStringContainsString('color: #ffffff', $html);
        // Descendants are forced to inherit so the wrapper colour wins. (The
        // `&` in the arbitrary variant is HTML-escaped in the source, but the
        // browser decodes it back — hence asserting on the suffix.)
        $this->assertStringContainsString('_h2]:text-inherit', $html);
    }

    public function test_blocks_without_design_render_without_a_wrapper(): void
    {
        $html = $this->renderPageWith([]);

        $this->assertStringContainsString('Styled heading', $html);
        $this->assertStringNotContainsString('background-image: linear-gradient(135deg', $html);
    }

    public function test_entrance_animation_adds_its_classes(): void
    {
        $html = $this->renderPageWith(['_anim' => 'fade-up']);

        $this->assertStringContainsString('pb-anim', $html);
        $this->assertStringContainsString('pb-anim-fade-up', $html);
    }

    public function test_responsive_visibility_classes_render(): void
    {
        $html = $this->renderPageWith([
            '_hide_mobile' => true,
            '_hide_tablet' => true,
            '_hide_desktop' => true,
        ]);

        $this->assertStringContainsString('max-md:hidden', $html);
        $this->assertStringContainsString('md:max-lg:hidden', $html);
        $this->assertStringContainsString('lg:hidden', $html);
    }
}
