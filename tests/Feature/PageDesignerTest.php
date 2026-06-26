<?php

namespace Tests\Feature;

use App\Enums\ContentStatus;
use App\Livewire\PageDesigner;
use App\Models\Page;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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
            ->assertSet('selectedPath', '1')
            ->assertSet('dirty', true)
            ->assertSet('blocks.1.type', 'button')
            ->assertSet('blocks.1.data.label', 'Click me');
    }

    public function test_dragging_a_widget_inserts_it_at_a_position(): void
    {
        $this->actingAs($this->owner);

        Livewire::test(PageDesigner::class, ['site' => $this->site, 'page' => $this->page])
            ->call('insertAt', 'button', 0)   // drop before the existing heading
            ->assertCount('blocks', 2)
            ->assertSet('blocks.0.type', 'button')
            ->assertSet('blocks.1.type', 'heading')
            ->assertSet('selectedPath', '0')
            ->assertSet('dirty', true);
    }

    public function test_drag_reorder_indices_place_blocks_correctly(): void
    {
        $this->actingAs($this->owner);

        // Build [heading, button, feature_grid].
        $component = Livewire::test(PageDesigner::class, ['site' => $this->site, 'page' => $this->page])
            ->call('addBlock', 'button')
            ->call('addBlock', 'feature_grid');

        // Drag the heading (from 0) onto the end drop-zone (target 3). The view
        // computes to = from < target ? target - 1 : target = 2.
        $component->call('move', 0, 2)
            ->assertSet('blocks.0.type', 'button')
            ->assertSet('blocks.1.type', 'feature_grid')
            ->assertSet('blocks.2.type', 'heading');

        // Drag feature_grid (now at 1) onto the first drop-zone (target 0): to = 0.
        $component->call('move', 1, 0)
            ->assertSet('blocks.0.type', 'feature_grid')
            ->assertSet('blocks.1.type', 'button');
    }

    public function test_blocks_can_be_reordered_and_removed(): void
    {
        $this->actingAs($this->owner);

        Livewire::test(PageDesigner::class, ['site' => $this->site, 'page' => $this->page])
            ->call('addBlock', 'button')   // index 1
            ->call('moveUp', '1')          // button now index 0
            ->assertSet('blocks.0.type', 'button')
            ->assertSet('blocks.1.type', 'heading')
            ->call('duplicate', '0')
            ->assertCount('blocks', 3)
            ->call('remove', '0')
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

    public function test_editing_a_field_updates_the_block_and_marks_dirty(): void
    {
        $this->actingAs($this->owner);

        Livewire::test(PageDesigner::class, ['site' => $this->site, 'page' => $this->page])
            ->set('blocks.0.data.text', 'Updated heading')
            ->assertSet('blocks.0.data.text', 'Updated heading')
            ->assertSet('dirty', true);
    }

    public function test_inspector_renders_the_selected_blocks_fields(): void
    {
        $this->actingAs($this->owner);

        Livewire::test(PageDesigner::class, ['site' => $this->site, 'page' => $this->page])
            ->call('select', '0')
            ->assertSee('Content')
            ->assertSee('Level')   // a heading field label
            ->assertSee('Design');
    }

    public function test_repeater_items_can_be_added_and_removed(): void
    {
        $this->actingAs($this->owner);

        Livewire::test(PageDesigner::class, ['site' => $this->site, 'page' => $this->page])
            ->call('addBlock', 'feature_grid')   // selected = 1, default has 3 items
            ->assertCount('blocks.1.data.items', 3)
            ->call('addItem', 'items')
            ->assertCount('blocks.1.data.items', 4)
            ->set('blocks.1.data.items.3.title', 'Fourth feature')
            ->assertSet('blocks.1.data.items.3.title', 'Fourth feature')
            ->call('removeItem', 'items', 0)
            ->assertCount('blocks.1.data.items', 3);
    }

    public function test_inline_edits_persist_on_save(): void
    {
        $this->actingAs($this->owner);

        Livewire::test(PageDesigner::class, ['site' => $this->site, 'page' => $this->page])
            ->set('blocks.0.data.text', 'Saved via inspector')
            ->set('blocks.0.data._bg', '#101820')
            ->call('save');

        $content = $this->page->fresh()->content;

        $this->assertSame('Saved via inspector', $content[0]['data']['text']);
        $this->assertSame('#101820', $content[0]['data']['_bg']);
    }

    public function test_every_widget_has_an_inspector_schema(): void
    {
        foreach (\App\PageBuilder\PageBuilder::palette() as $widget) {
            $fields = \App\PageBuilder\BlockFields::for($widget['name']);
            $this->assertIsArray($fields, "Missing inspector schema for {$widget['name']}");
        }

        $this->assertSame('text', \App\PageBuilder\BlockFields::for('heading')[0]['key']);
        $this->assertNotEmpty(\App\PageBuilder\BlockFields::design());
    }

    public function test_a_widget_can_be_nested_inside_a_column_and_edited(): void
    {
        $this->actingAs($this->owner);

        $component = Livewire::test(PageDesigner::class, ['site' => $this->site, 'page' => $this->page])
            ->call('addBlock', 'columns')   // becomes block index 1, default 2 columns
            ->assertSet('selectedPath', '1');

        // Drop a button into the first column.
        $component->call('addInto', '1.data.columns.0.blocks', 'button')
            ->assertSet('selectedPath', '1.data.columns.0.blocks.0')
            ->assertSet('blocks.1.data.columns.0.blocks.0.type', 'button')
            ->assertSet('blocks.1.data.columns.0.blocks.0.data.label', 'Click me');

        // Edit the nested button's label via its deep path.
        $component->set('blocks.1.data.columns.0.blocks.0.data.label', 'Buy now')
            ->assertSet('blocks.1.data.columns.0.blocks.0.data.label', 'Buy now')
            ->assertSet('dirty', true);

        // Persisted, the nesting survives the round-trip.
        $component->call('save');
        $saved = $this->page->fresh()->content;
        $this->assertSame('button', $saved[1]['data']['columns'][0]['blocks'][0]['type']);
        $this->assertSame('Buy now', $saved[1]['data']['columns'][0]['blocks'][0]['data']['label']);
    }

    public function test_nested_widgets_and_columns_can_be_removed(): void
    {
        $this->actingAs($this->owner);

        $component = Livewire::test(PageDesigner::class, ['site' => $this->site, 'page' => $this->page])
            ->call('addBlock', 'columns')
            ->call('addInto', '1.data.columns.0.blocks', 'heading')
            ->call('addInto', '1.data.columns.0.blocks', 'button')
            ->assertCount('blocks.1.data.columns.0.blocks', 2);

        // Remove the first nested widget.
        $component->call('remove', '1.data.columns.0.blocks.0')
            ->assertCount('blocks.1.data.columns.0.blocks', 1)
            ->assertSet('blocks.1.data.columns.0.blocks.0.type', 'button');

        // Add then remove a column.
        $component->call('addColumn', '1')
            ->assertCount('blocks.1.data.columns', 3)
            ->call('removeColumn', '1', 2)
            ->assertCount('blocks.1.data.columns', 2);
    }

    public function test_an_image_can_be_uploaded_to_a_widget(): void
    {
        Storage::fake('public');
        $this->actingAs($this->owner);

        $component = Livewire::test(PageDesigner::class, ['site' => $this->site, 'page' => $this->page])
            ->call('addBlock', 'image')   // selectedPath '1'
            ->set('pendingUploads.image', UploadedFile::fake()->image('photo.jpg'));

        $path = $component->get('blocks.1.data.image');

        $this->assertNotEmpty($path);
        Storage::disk('public')->assertExists($path);
        $component->assertSet('dirty', true);

        // Clearing removes it.
        $component->call('clearImage', 'image')
            ->assertSet('blocks.1.data.image', null);
    }

    public function test_multiple_images_append_to_a_gallery_and_can_be_removed(): void
    {
        Storage::fake('public');
        $this->actingAs($this->owner);

        $component = Livewire::test(PageDesigner::class, ['site' => $this->site, 'page' => $this->page])
            ->call('addBlock', 'gallery')   // selectedPath '1'
            ->set('pendingUploads.images', [
                UploadedFile::fake()->image('a.jpg'),
                UploadedFile::fake()->image('b.jpg'),
            ]);

        $this->assertCount(2, $component->get('blocks.1.data.images'));

        $component->call('removeImageAt', 'images', 0);
        $this->assertCount(1, $component->get('blocks.1.data.images'));
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
