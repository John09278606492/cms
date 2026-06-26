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

    /**
     * Dot-path of the selected block: "3" for a top-level block, or a nested
     * path like "3.data.columns.0.blocks.1" for a widget inside a container.
     */
    public ?string $selectedPath = null;

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

    // --- Path helpers ------------------------------------------------------

    /** The block (array) at a dot-path, or null. */
    public function blockAt(?string $path): ?array
    {
        if ($path === null || $path === '') {
            return null;
        }

        $block = data_get($this->blocks, $path);

        return is_array($block) && isset($block['type']) ? $block : null;
    }

    /** Split a block path into [listPath, index]. listPath '' means top level. */
    protected function splitPath(string $path): array
    {
        $pos = strrpos($path, '.');

        return $pos === false
            ? ['', (int) $path]
            : [substr($path, 0, $pos), (int) substr($path, $pos + 1)];
    }

    /** @return array<int, mixed> the list of blocks at a list-path. */
    protected function getList(string $listPath): array
    {
        if ($listPath === '') {
            return $this->blocks;
        }

        $list = data_get($this->blocks, $listPath);

        return is_array($list) ? array_values($list) : [];
    }

    protected function putList(string $listPath, array $list): void
    {
        $list = array_values($list);

        if ($listPath === '') {
            $this->blocks = $list;
        } else {
            data_set($this->blocks, $listPath, $list);
        }

        $this->dirty = true;
    }

    protected function pathInList(string $listPath, int $index): string
    {
        return $listPath === '' ? (string) $index : $listPath . '.' . $index;
    }

    // --- Selection & inline edits -----------------------------------------

    public function select(string $path): void
    {
        $this->selectedPath = $this->blockAt($path) !== null ? $path : null;
    }

    /** Any inline edit to a block's data (via wire:model) marks the page dirty. */
    public function updated(string $name): void
    {
        if (str_starts_with($name, 'blocks.')) {
            $this->dirty = true;
        }
    }

    /** Add/remove rows inside a repeater field of the selected block. */
    public function addItem(string $key): void
    {
        if ($this->selectedPath === null) {
            return;
        }

        $path = $this->selectedPath . '.data.' . $key;
        $items = data_get($this->blocks, $path, []);
        $items = is_array($items) ? array_values($items) : [];
        $items[] = [];

        data_set($this->blocks, $path, $items);
        $this->dirty = true;
    }

    public function removeItem(string $key, int $index): void
    {
        if ($this->selectedPath === null) {
            return;
        }

        $path = $this->selectedPath . '.data.' . $key;
        $items = data_get($this->blocks, $path, []);

        if (! is_array($items) || ! isset($items[$index])) {
            return;
        }

        array_splice($items, $index, 1);
        data_set($this->blocks, $path, array_values($items));
        $this->dirty = true;
    }

    // --- Block operations (work at any nesting level) ----------------------

    public function addBlock(string $name): void
    {
        $this->blocks[] = ['type' => $name, 'data' => PageBuilder::defaultData($name)];
        $this->selectedPath = (string) array_key_last($this->blocks);
        $this->dirty = true;
    }

    /** Insert a new widget into a list (top level, or a container's child list). */
    public function insertInto(string $listPath, string $name, int $index): void
    {
        $list = $this->getList($listPath);
        $index = max(0, min($index, count($list)));

        array_splice($list, $index, 0, [['type' => $name, 'data' => PageBuilder::defaultData($name)]]);
        $this->putList($listPath, $list);
        $this->selectedPath = $this->pathInList($listPath, $index);
    }

    public function addInto(string $listPath, string $name): void
    {
        $this->insertInto($listPath, $name, count($this->getList($listPath)));
    }

    /** Top-level insert used by palette drag-and-drop. */
    public function insertAt(string $name, int $index): void
    {
        $this->insertInto('', $name, $index);
    }

    public function moveBlock(string $listPath, int $from, int $to): void
    {
        $list = $this->getList($listPath);

        if (! isset($list[$from]) || $to < 0 || $to >= count($list)) {
            return;
        }

        $item = array_splice($list, $from, 1)[0];
        array_splice($list, $to, 0, [$item]);

        $this->putList($listPath, $list);
        $this->selectedPath = $this->pathInList($listPath, $to);
    }

    /** Top-level reorder used by canvas drag-and-drop. */
    public function move(int $from, int $to): void
    {
        $this->moveBlock('', $from, $to);
    }

    public function moveUp(string $path): void
    {
        [$listPath, $index] = $this->splitPath($path);
        $this->moveBlock($listPath, $index, $index - 1);
    }

    public function moveDown(string $path): void
    {
        [$listPath, $index] = $this->splitPath($path);
        $this->moveBlock($listPath, $index, $index + 1);
    }

    public function duplicate(string $path): void
    {
        [$listPath, $index] = $this->splitPath($path);
        $list = $this->getList($listPath);

        if (! isset($list[$index])) {
            return;
        }

        array_splice($list, $index + 1, 0, [$list[$index]]);
        $this->putList($listPath, $list);
        $this->selectedPath = $this->pathInList($listPath, $index + 1);
    }

    public function remove(string $path): void
    {
        [$listPath, $index] = $this->splitPath($path);
        $list = $this->getList($listPath);

        if (! isset($list[$index])) {
            return;
        }

        array_splice($list, $index, 1);
        $this->putList($listPath, $list);
        $this->selectedPath = null;
    }

    // --- Columns container --------------------------------------------------

    public function addColumn(string $path): void
    {
        if (($block = $this->blockAt($path)) === null || $block['type'] !== 'columns') {
            return;
        }

        $columns = data_get($this->blocks, $path . '.data.columns', []);
        $columns = is_array($columns) ? array_values($columns) : [];

        if (count($columns) >= 4) {
            return;
        }

        $columns[] = ['blocks' => []];
        data_set($this->blocks, $path . '.data.columns', $columns);
        $this->dirty = true;
    }

    public function removeColumn(string $path, int $column): void
    {
        $columns = data_get($this->blocks, $path . '.data.columns', []);

        if (! is_array($columns) || ! isset($columns[$column]) || count($columns) <= 1) {
            return;
        }

        array_splice($columns, $column, 1);
        data_set($this->blocks, $path . '.data.columns', array_values($columns));
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
