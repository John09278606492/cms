<?php

namespace App\Livewire;

use App\Models\Page;
use App\Models\Site;
use App\PageBuilder\BlockFields;
use App\PageBuilder\PageBuilder;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * A full-screen, Elementor-style visual designer. It edits the very same
 * `pages.content` block JSON the form editor and public site use, so the three
 * stay fully compatible. Stage 1: palette + live canvas + select/reorder/save.
 */
#[Layout('layouts.designer')]
class PageDesigner extends Component
{
    public Site $site;

    public int $pageId;

    public string $pageTitle;

    /** @var array<int, array{type: string, data: array<string, mixed>}> */
    public array $blocks = [];

    public ?int $selected = null;

    public string $device = 'desktop';

    public bool $dirty = false;

    public function mount(Site $site, Page $page): void
    {
        abort_unless(auth()->check() && auth()->user()->canAccessTenant($site), 403);
        abort_unless($page->site_id === $site->getKey(), 404);

        $this->site = $site;
        $this->pageId = $page->getKey();
        $this->pageTitle = $page->title;
        $this->blocks = is_array($page->content) ? array_values($page->content) : [];
    }

    public function select(int $index): void
    {
        $this->selected = isset($this->blocks[$index]) ? $index : null;
    }

    /**
     * Any inline edit to a block's data (via wire:model) marks the page dirty.
     */
    public function updated(string $name): void
    {
        if (str_starts_with($name, 'blocks.')) {
            $this->dirty = true;
        }
    }

    public function addItem(string $key): void
    {
        if ($this->selected === null || ! isset($this->blocks[$this->selected])) {
            return;
        }

        $items = $this->blocks[$this->selected]['data'][$key] ?? [];
        $items[] = [];
        $this->blocks[$this->selected]['data'][$key] = array_values($items);
        $this->dirty = true;
    }

    public function removeItem(string $key, int $index): void
    {
        if ($this->selected === null) {
            return;
        }

        $items = $this->blocks[$this->selected]['data'][$key] ?? [];

        if (! isset($items[$index])) {
            return;
        }

        array_splice($items, $index, 1);
        $this->blocks[$this->selected]['data'][$key] = array_values($items);
        $this->dirty = true;
    }

    public function addBlock(string $name): void
    {
        $this->blocks[] = ['type' => $name, 'data' => PageBuilder::defaultData($name)];
        $this->selected = array_key_last($this->blocks);
        $this->dirty = true;
    }

    public function insertAt(string $name, int $index): void
    {
        $index = max(0, min($index, count($this->blocks)));
        $block = ['type' => $name, 'data' => PageBuilder::defaultData($name)];

        array_splice($this->blocks, $index, 0, [$block]);
        $this->selected = $index;
        $this->dirty = true;
    }

    public function move(int $from, int $to): void
    {
        if (! isset($this->blocks[$from]) || $to < 0 || $to >= count($this->blocks)) {
            return;
        }

        $blocks = $this->blocks;
        $item = array_splice($blocks, $from, 1)[0];
        array_splice($blocks, $to, 0, [$item]);

        $this->blocks = $blocks;
        $this->selected = $to;
        $this->dirty = true;
    }

    public function moveUp(int $index): void
    {
        $this->move($index, $index - 1);
    }

    public function moveDown(int $index): void
    {
        $this->move($index, $index + 1);
    }

    public function duplicate(int $index): void
    {
        if (! isset($this->blocks[$index])) {
            return;
        }

        $blocks = $this->blocks;
        array_splice($blocks, $index + 1, 0, [$this->blocks[$index]]);

        $this->blocks = $blocks;
        $this->selected = $index + 1;
        $this->dirty = true;
    }

    public function remove(int $index): void
    {
        if (! isset($this->blocks[$index])) {
            return;
        }

        array_splice($this->blocks, $index, 1);
        $this->selected = null;
        $this->dirty = true;
    }

    public function save(): void
    {
        $page = Page::query()->whereKey($this->pageId)->firstOrFail();
        abort_unless($page->site_id === $this->site->getKey(), 404);

        $page->forceFill(['content' => array_values($this->blocks)])->save();

        $this->dirty = false;
        $this->dispatch('designer-saved');
    }

    /**
     * @return array<int, array{name: string, label: string, icon: string, group: string}>
     */
    public function getPaletteProperty(): array
    {
        return PageBuilder::palette();
    }

    public function render()
    {
        return view('livewire.page-designer')->layoutData(['pageTitle' => $this->pageTitle]);
    }
}
