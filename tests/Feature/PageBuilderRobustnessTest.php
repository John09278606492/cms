<?php

namespace Tests\Feature;

use App\Enums\ContentStatus;
use App\Models\Page;
use App\Models\Site;
use App\PageBuilder\PageBuilder;
use App\Support\RichText;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageBuilderRobustnessTest extends TestCase
{
    use RefreshDatabase;

    public function test_rich_text_helper_is_safe_on_empty_and_malformed_input(): void
    {
        $this->assertSame('', RichText::render(null));
        $this->assertSame('', RichText::render(''));
        $this->assertSame('', RichText::render([]));
        $this->assertStringContainsString('Hello', RichText::render('<p>Hello</p>'));
    }

    public function test_text_widgets_render_when_their_content_is_empty(): void
    {
        $site = Site::query()->create(['name' => 'Empty', 'slug' => 'empty-site', 'is_active' => true]);

        Page::query()->create([
            'site_id' => $site->getKey(),
            'title' => 'Home',
            'slug' => 'empty-home',
            'status' => ContentStatus::Published,
            'published_at' => now(),
            'is_homepage' => true,
            'show_in_menu' => false,
            'content' => [
                ['type' => 'paragraph', 'data' => []],                                   // no content key
                ['type' => 'media_text', 'data' => ['heading' => 'Hi']],                 // no body
                ['type' => 'accordion', 'data' => ['items' => [['question' => 'Q?']]]],  // no answer
                ['type' => 'tabs', 'data' => ['items' => [['label' => 'One']]]],         // no content
            ],
        ]);

        $this->get("/sites/{$site->slug}")->assertOk();
    }

    /**
     * Every widget, rendered with its default data and with a background design
     * wrapper, must not throw — this is what an owner sees when they drop one in.
     */
    public function test_every_widget_renders_with_default_data(): void
    {
        foreach (array_keys(PageBuilder::paletteMeta()) as $name) {
            $data = PageBuilder::defaultData($name);

            $html = view('components.page-builder', [
                'blocks' => [['type' => $name, 'data' => $data + ['_bg' => '#eeeeee', '_pad' => 'lg', '_radius' => 'none']]],
                'page' => null,
            ])->render();

            $this->assertIsString($html, "Widget {$name} failed to render");
        }
    }
}
