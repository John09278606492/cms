<?php

namespace Tests\Feature;

use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Filament\Resources\Templates\TemplateResource;
use App\Models\Template;
use App\Support\PageTemplates;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageTemplateTest extends TestCase
{
    use RefreshDatabase;

    public function test_merge_replaces_existing_blocks_and_rekeys_with_uuids(): void
    {
        $existing = [['type' => 'heading', 'data' => ['text' => 'Old']]];
        $template = [['type' => 'hero', 'data' => ['heading' => 'New']]];

        $merged = PageTemplates::merge($existing, $template, replace: true);

        $this->assertCount(1, $merged);
        $this->assertSame('hero', array_values($merged)[0]['type']);

        foreach (array_keys($merged) as $key) {
            $this->assertMatchesRegularExpression('/^[0-9a-f-]{36}$/', $key);
        }
    }

    public function test_merge_appends_template_blocks_after_existing_ones(): void
    {
        $existing = [['type' => 'heading', 'data' => ['text' => 'Keep me']]];
        $template = [['type' => 'paragraph', 'data' => ['content' => '<p>Added.</p>']]];

        $merged = array_values(PageTemplates::merge($existing, $template, replace: false));

        $this->assertCount(2, $merged);
        $this->assertSame('heading', $merged[0]['type']);
        $this->assertSame('paragraph', $merged[1]['type']);
    }

    public function test_a_template_round_trips_its_blocks_through_the_database(): void
    {
        $template = Template::query()->create([
            'site_id' => null,
            'name' => 'Reusable layout',
            'content' => [
                ['type' => 'hero', 'data' => ['heading' => 'Hello']],
                ['type' => 'paragraph', 'data' => ['content' => '<p>Body.</p>']],
            ],
        ]);

        $fresh = $template->fresh();

        $this->assertIsArray($fresh->content);
        $this->assertCount(2, $fresh->content);
        $this->assertSame('hero', $fresh->content[0]['type']);
    }

    public function test_template_and_submission_resources_are_tenant_scoped(): void
    {
        // Filament isolates tenants with a global scope on tenant-scoped
        // resources. A site must only ever see its own templates and form
        // submissions, so guard the invariant directly (mirrors the content
        // resource scoping test in TenancyTest).
        $this->assertTrue(TemplateResource::isScopedToTenant(), 'Templates must be tenant-scoped');
        $this->assertTrue(ContactMessageResource::isScopedToTenant(), 'Submissions must be tenant-scoped');
    }
}
