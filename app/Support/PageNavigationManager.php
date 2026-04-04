<?php

namespace App\Support;

use App\Models\Menu;
use App\Models\Page;
use App\Models\Site;
use Datlechin\FilamentMenuBuilder\Models\MenuItem;
use Datlechin\FilamentMenuBuilder\Models\MenuLocation;
use Illuminate\Database\Eloquent\Builder;

class PageNavigationManager
{
    public function sync(Page $page): void
    {
        if (blank($page->site_id)) {
            return;
        }

        $page->loadMissing('site');

        if (! $page->site instanceof Site) {
            return;
        }

        if (! $this->shouldBePresentInNavigation($page)) {
            $this->removeFromPrimaryHeaderMenu($page);
            $this->syncDescendants($page);

            return;
        }

        $menu = $this->ensurePrimaryHeaderMenu($page->site);
        $menuItem = $this->menuItemQuery($menu, $page)->first();

        $updates = [
            'panel' => $page->getMorphClass(),
        ];

        if ($this->shouldSyncTitle($menuItem, $page)) {
            $updates['title'] = $page->title;
        }

        if ($this->shouldSyncParent($menuItem, $menu, $page)) {
            $updates['parent_id'] = $this->resolveParentMenuItem($menu, $page)?->getKey();
        }

        if ($menuItem) {
            $menuItem->fill($updates);
            $menuItem->save();
        } else {
            $updates['menu_id'] = $menu->getKey();
            $updates['linkable_type'] = $page->getMorphClass();
            $updates['linkable_id'] = $page->getKey();
            $updates['order'] = $this->nextOrder($menu, $page);

            MenuItem::query()->create($updates);
        }

        $this->syncDescendants($page);
    }

    public function ensurePrimaryHeaderMenu(Site $site): Menu
    {
        $menu = $this->findPrimaryHeaderMenu($site);

        if (! $menu) {
            $menu = Menu::query()->create([
                'site_id' => $site->getKey(),
                'name' => 'Primary Navigation',
                'is_visible' => true,
            ]);
        } elseif (! $menu->is_visible) {
            $menu->forceFill(['is_visible' => true])->save();
        }

        MenuLocation::query()->firstOrCreate([
            'menu_id' => $menu->getKey(),
            'location' => 'header',
        ]);

        return $menu;
    }

    protected function findPrimaryHeaderMenu(Site $site): ?Menu
    {
        return Menu::query()
            ->forSite($site)
            ->whereRelation('locations', 'location', 'header')
            ->orderBy('id')
            ->first()
            ?? Menu::query()
                ->forSite($site)
            ->where('name', 'Primary Navigation')
            ->orderBy('id')
                ->first();
    }

    protected function removeFromPrimaryHeaderMenu(Page $page): void
    {
        $page->loadMissing('site');

        if (! $page->site instanceof Site) {
            return;
        }

        $menu = $this->findPrimaryHeaderMenu($page->site);

        if (! $menu) {
            return;
        }

        $this->menuItemQuery($menu, $page)
            ->get()
            ->each(fn (MenuItem $menuItem): bool => $menuItem->delete());
    }

    protected function syncDescendants(Page $page): void
    {
        $page->children()
            ->get()
            ->each(fn (Page $child) => $this->sync($child));
    }

    protected function shouldBePresentInNavigation(Page $page): bool
    {
        return $page->show_in_menu
            && ! $page->is_homepage
            && ! $page->trashed()
            && $page->isPublished();
    }

    protected function shouldSyncTitle(?MenuItem $menuItem, Page $page): bool
    {
        if (! $menuItem) {
            return true;
        }

        $originalTitle = (string) ($page->getOriginal('title') ?: $page->title);

        return blank($menuItem->title) || $menuItem->title === $originalTitle;
    }

    protected function shouldSyncParent(?MenuItem $menuItem, Menu $menu, Page $page): bool
    {
        if (! $menuItem) {
            return true;
        }

        $originalParentMenuItem = $this->resolveParentMenuItemByPageId(
            $menu,
            $page->getOriginal('parent_id'),
        );

        return $menuItem->parent_id === $originalParentMenuItem?->getKey();
    }

    protected function resolveParentMenuItem(Menu $menu, Page $page): ?MenuItem
    {
        return $this->resolveParentMenuItemByPageId($menu, $page->parent_id);
    }

    protected function resolveParentMenuItemByPageId(Menu $menu, mixed $pageId): ?MenuItem
    {
        if (blank($pageId)) {
            return null;
        }

        return MenuItem::query()
            ->where('menu_id', $menu->getKey())
            ->where('linkable_type', (new Page())->getMorphClass())
            ->where('linkable_id', $pageId)
            ->first();
    }

    protected function nextOrder(Menu $menu, Page $page): int
    {
        return ((int) MenuItem::query()
            ->where('menu_id', $menu->getKey())
            ->where('parent_id', $this->resolveParentMenuItem($menu, $page)?->getKey())
            ->max('order')) + 1;
    }

    protected function menuItemQuery(Menu $menu, Page $page): Builder
    {
        return MenuItem::query()
            ->where('menu_id', $menu->getKey())
            ->where('linkable_type', $page->getMorphClass())
            ->where('linkable_id', $page->getKey());
    }
}
