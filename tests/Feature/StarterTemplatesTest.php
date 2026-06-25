<?php

namespace Tests\Feature;

use App\Enums\ContentStatus;
use App\Models\Page;
use App\Models\Site;
use App\PageBuilder\StarterTemplates;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class StarterTemplatesTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_starter_references_a_renderable_block(): void
    {
        foreach (StarterTemplates::all() as $key => $template) {
            $this->assertNotEmpty($template['blocks'], "Starter {$key} has no blocks");

            foreach ($template['blocks'] as $block) {
                $this->assertTrue(
                    View::exists('page-builder.blocks.' . $block['type']),
                    "Starter {$key} references missing block: {$block['type']}",
                );
            }
        }
    }

    public function test_a_starter_layout_renders_on_a_public_page(): void
    {
        $site = Site::query()->create(['name' => 'Starter', 'slug' => 'starter-site', 'is_active' => true]);

        Page::query()->create([
            'site_id' => $site->getKey(),
            'title' => 'Home',
            'slug' => 'starter-home',
            'status' => ContentStatus::Published,
            'published_at' => now(),
            'is_homepage' => true,
            'show_in_menu' => false,
            'content' => StarterTemplates::blocks('landing'),
        ]);

        $this->get("/sites/{$site->slug}")
            ->assertOk()
            ->assertSee('A bold headline that sells the idea')
            ->assertSee('Everything you need');
    }
}
