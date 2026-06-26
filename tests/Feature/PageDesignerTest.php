<?php

namespace Tests\Feature;

use App\Enums\ContentStatus;
use App\Livewire\PageDesigner;
use App\Models\Page;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PageDesignerTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;

    protected Site $site;

    protected Page $page;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::query()->create([
            'name' => 'Designer Owner',
            'email' => 'designer-owner@example.test',
            'password' => 'password',
        ]);

        $this->site = Site::query()->create([
            'name' => 'Designer Site',
            'slug' => 'designer-site',
            'owner_id' => $this->owner->getKey(),
            'is_active' => true,
        ]);

        $this->page = Page::query()->create([
            'site_id' => $this->site->getKey(),
            'title' => 'Home',
            'slug' => 'designer-home',
            'status' => ContentStatus::Draft,
            'is_homepage' => false,
            'show_in_menu' => false,
            'content' => [
                ['type' => 'heading', 'data' => ['text' => 'Existing', 'level' => 'h2']],
            ],
        ]);
    }

    public function test_owner_can_open_the_designer_with_existing_blocks(): void
    {
        $this->actingAs($this->owner);

        Livewire::test(PageDesigner::class, ['site' => $this->site, 'page' => $this->page])
            ->assertSet('pageTitle', 'Home')
            ->assertCount('blocks', 1);
    }

    public function test_adding_a_widget_appends_it_with_default_content(): void
    {
        $this->actingAs($this->owner);

        Livewire::test(PageDesigner::class, ['site' => $this->site, 'page' => $this->page])
            ->call('addBlock', 'button')
            ->assertCount('blocks', 2)
            ->assertSet('selected', 1)
            ->assertSet('dirty', true)
            ->assertSet('blocks.1.type', 'button')
            ->assertSet('blocks.1.data.label', 'Click me');
    }

    public function test_blocks_can_be_reordered_and_removed(): void
    {
        $this->actingAs($this->owner);

        Livewire::test(PageDesigner::class, ['site' => $this->site, 'page' => $this->page])
            ->call('addBlock', 'button')   // index 1
            ->call('moveUp', 1)            // button now index 0
            ->assertSet('blocks.0.type', 'button')
            ->assertSet('blocks.1.type', 'heading')
            ->call('duplicate', 0)
            ->assertCount('blocks', 3)
            ->call('remove', 0)
            ->assertCount('blocks', 2);
    }

    public function test_saving_persists_the_blocks_with_their_data(): void
    {
        $this->actingAs($this->owner);

        Livewire::test(PageDesigner::class, ['site' => $this->site, 'page' => $this->page])
            ->call('addBlock', 'feature_grid')
            ->call('save')
            ->assertSet('dirty', false);

        $content = $this->page->fresh()->content;

        $this->assertCount(2, $content);
        $this->assertSame('feature_grid', $content[1]['type']);
        // Default data survived the round-trip to the database.
        $this->assertSame('Features', $content[1]['data']['heading']);
        $this->assertNotEmpty($content[1]['data']['items']);
    }

    public function test_users_without_access_to_the_site_are_forbidden(): void
    {
        $stranger = User::query()->create([
            'name' => 'Stranger',
            'email' => 'stranger@example.test',
            'password' => 'password',
        ]);

        $this->actingAs($stranger)
            ->get(route('pages.designer', ['site' => $this->site, 'page' => $this->page]))
            ->assertForbidden();
    }

    public function test_a_page_from_another_site_cannot_be_opened(): void
    {
        $otherSite = Site::query()->create([
            'name' => 'Other',
            'slug' => 'other-designer-site',
            'owner_id' => $this->owner->getKey(),
            'is_active' => true,
        ]);

        $this->actingAs($this->owner)
            ->get(route('pages.designer', ['site' => $otherSite, 'page' => $this->page]))
            ->assertNotFound();
    }
}
