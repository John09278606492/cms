<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\Site;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThemeStylesTest extends TestCase
{
    use RefreshDatabase;

    public function test_theme_fonts_and_brand_colour_are_injected_into_the_site(): void
    {
        $site = Site::query()->create(['name' => 'Theme', 'slug' => 'theme-site', 'is_active' => true]);

        Setting::query()->updateOrCreate(
            ['site_id' => $site->getKey()],
            [
                'site_name' => 'Theme',
                'heading_font' => 'Poppins',
                'body_font' => 'Lora',
                'brand_primary' => '#7c3aed',
                'brand_accent' => '#db2777',
            ],
        );

        $html = $this->get("/sites/{$site->slug}")->assertOk()->getContent();

        $this->assertStringContainsString('fonts.googleapis.com/css2', $html);
        $this->assertStringContainsString('family=Poppins', $html);
        $this->assertStringContainsString('family=Lora', $html);
        $this->assertStringContainsString("--font-heading: 'Poppins'", $html);
        $this->assertStringContainsString('--brand-primary: #7c3aed', $html);
        // The derived tokens the blocks read are overridden too.
        $this->assertStringContainsString('--brand-primary-hover: #7c3aed', $html);
        $this->assertStringContainsString('--brand-accent: #db2777', $html);
        $this->assertStringContainsString('--brand-accent-on-dark: #db2777', $html);
        $this->assertStringContainsString('var(--brand-primary)', $html);
    }

    public function test_blocks_use_brand_token_classes_so_the_theme_reaches_them(): void
    {
        $site = Site::query()->create(['name' => 'Brand', 'slug' => 'brand-site', 'is_active' => true]);

        \App\Models\Page::query()->create([
            'site_id' => $site->getKey(),
            'title' => 'Home',
            'slug' => 'brand-home',
            'status' => \App\Enums\ContentStatus::Published,
            'published_at' => now(),
            'is_homepage' => true,
            'show_in_menu' => false,
            'content' => [
                ['type' => 'hero', 'data' => ['eyebrow' => 'Hi', 'heading' => 'Welcome', 'surface' => 'minimal', 'primary_label' => 'Go', 'primary_url' => '#']],
                ['type' => 'feature_grid', 'data' => ['eyebrow' => 'Why', 'heading' => 'Features', 'items' => [['title' => 'A']]]],
            ],
        ]);

        $html = $this->get("/sites/{$site->slug}")->assertOk()->getContent();

        $this->assertStringContainsString('pb-btn-primary', $html);
        $this->assertStringContainsString('pb-eyebrow', $html);
    }

    public function test_sites_without_a_theme_get_no_extra_styles(): void
    {
        $site = Site::query()->create(['name' => 'Plain', 'slug' => 'plain-site', 'is_active' => true]);

        $html = $this->get("/sites/{$site->slug}")->assertOk()->getContent();

        $this->assertStringNotContainsString('fonts.googleapis.com', $html);
        $this->assertStringNotContainsString('--brand-primary', $html);
    }

    public function test_google_fonts_url_only_includes_selected_known_fonts(): void
    {
        $setting = new Setting(['heading_font' => 'Poppins', 'body_font' => 'Not A Real Font']);

        $url = $setting->googleFontsUrl();

        $this->assertStringContainsString('family=Poppins', $url);
        $this->assertStringNotContainsString('Not+A+Real+Font', $url);
    }
}
