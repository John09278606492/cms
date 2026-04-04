<?php

namespace App\Support;

use App\Models\Page;
use App\Models\Site;
use Illuminate\Support\Collection;

class SiteNavigation
{
    public static function defaultHeaderForSite(Site $site): Collection
    {
        $pages = $site->pages()
            ->published()
            ->where('show_in_menu', true)
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();

        $items = static::pageItems($pages);

        if ($site->posts()->published()->exists()) {
            $items->push(
                NavigationItem::make('Blog', route('sites.blog.index', $site))
                    ->activeWhen(fn (): bool => request()->routeIs('sites.blog.*')),
            );
        }

        return $items->values();
    }

    /**
     * @param  Collection<int, Page>  $pages
     * @return Collection<int, NavigationItem>
     */
    protected static function pageItems(Collection $pages): Collection
    {
        $visiblePages = $pages
            ->reject(fn (Page $page): bool => $page->is_homepage)
            ->values();

        $visiblePageIds = $visiblePages->pluck('id');
        $pagesByParent = $visiblePages->groupBy(
            fn (Page $page): string => $page->parent_id === null ? 'root' : (string) $page->parent_id,
        );

        return $visiblePages
            ->filter(
                fn (Page $page): bool => $page->parent_id === null || ! $visiblePageIds->contains($page->parent_id),
            )
            ->map(fn (Page $page): NavigationItem => static::mapPageToItem($page, $pagesByParent))
            ->values();
    }

    /**
     * @param  Collection<string, Collection<int, Page>>  $pagesByParent
     */
    protected static function mapPageToItem(Page $page, Collection $pagesByParent): NavigationItem
    {
        $children = collect($pagesByParent->get((string) $page->getKey(), collect()))
            ->map(fn (Page $child): NavigationItem => static::mapPageToItem($child, $pagesByParent))
            ->values();

        return NavigationItem::make($page->title, $page->publicUrl())
            ->children($children);
    }
}
