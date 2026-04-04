<?php

use App\Models\Page;
use App\Models\Post;
use App\Models\Site;
use Datlechin\FilamentMenuBuilder\Models\MenuItem;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Site::query()
            ->with([
                'menus:id,site_id',
                'menus.menuItems:id,menu_id,linkable_type,linkable_id,title,url,created_at',
                'pages:id,site_id,title,slug,excerpt,is_homepage,created_at,updated_at,deleted_at',
                'posts:id,site_id,title,slug,excerpt,created_at,updated_at,deleted_at',
            ])
            ->get()
            ->each(function (Site $site): void {
                $this->cleanupStarterMenuItems($site);
                $this->softDeleteStarterPages($site);
                $this->softDeleteStarterPosts($site);
            });
    }

    public function down(): void
    {
        //
    }

    protected function cleanupStarterMenuItems(Site $site): void
    {
        $starterPageIds = $this->starterPageIds($site);
        $starterPostIds = $this->starterPostIds($site);
        $homeUrl = route('sites.home', $site, false);
        $blogUrl = route('sites.blog.index', $site, false);
        $starterMenuItemIds = $site->menus
            ->flatMap->menuItems
            ->filter(function (MenuItem $item) use ($blogUrl, $homeUrl, $site, $starterPageIds, $starterPostIds): bool {
                if (
                    $this->wasCreatedNearSiteProvisioning($site, $item)
                    && blank($item->linkable_type)
                    && in_array($item->title, ['Home', 'Blog'], true)
                    && in_array($item->url, [$homeUrl, $blogUrl], true)
                ) {
                    return true;
                }

                return ($item->linkable_type === (new Page())->getMorphClass() && in_array($item->linkable_id, $starterPageIds))
                    || ($item->linkable_type === (new Post())->getMorphClass() && in_array($item->linkable_id, $starterPostIds));
            })
            ->pluck('id')
            ->all();

        if ($starterMenuItemIds === []) {
            return;
        }

        MenuItem::query()
            ->whereKey($starterMenuItemIds)
            ->delete();
    }

    protected function softDeleteStarterPages(Site $site): void
    {
        $starterPageIds = $this->starterPageIds($site);

        if ($starterPageIds === []) {
            return;
        }

        Page::query()
            ->whereKey($starterPageIds)
            ->delete();
    }

    protected function softDeleteStarterPosts(Site $site): void
    {
        $starterPostIds = $this->starterPostIds($site);

        if ($starterPostIds === []) {
            return;
        }

        Post::query()
            ->whereKey($starterPostIds)
            ->delete();
    }

    /**
     * @return array<int, int>
     */
    protected function starterPageIds(Site $site): array
    {
        return $site->pages
            ->filter(fn (Page $page): bool => $this->isStarterPage($site, $page))
            ->pluck('id')
            ->all();
    }

    /**
     * @return array<int, int>
     */
    protected function starterPostIds(Site $site): array
    {
        return $site->posts
            ->filter(fn (Post $post): bool => $this->isStarterPost($site, $post))
            ->pluck('id')
            ->all();
    }

    protected function isStarterPage(Site $site, Page $page): bool
    {
        if ($page->trashed() || ! $this->wasLeftUntouchedAfterProvisioning($site, $page)) {
            return false;
        }

        return match ([$page->slug, $page->title, $page->excerpt, $page->is_homepage]) {
            ['home', 'Home', 'Introduce the site and tell visitors what they should do next.', true] => true,
            ['about', 'About', 'Share the story, mission, and personality behind this site.', false] => true,
            ['contact', 'Contact', 'Give visitors a clear way to reach you.', false] => true,
            default => false,
        };
    }

    protected function isStarterPost(Site $site, Post $post): bool
    {
        if ($post->trashed() || ! $this->wasLeftUntouchedAfterProvisioning($site, $post)) {
            return false;
        }

        return $post->slug === 'first-post'
            && $post->title === 'First Post'
            && $post->excerpt === 'A starter article you can edit, replace, or delete.';
    }

    protected function wasLeftUntouchedAfterProvisioning(Site $site, Page|Post $record): bool
    {
        if (! $site->created_at || ! $record->created_at || ! $record->updated_at) {
            return false;
        }

        return $this->wasCreatedNearSiteProvisioning($site, $record)
            && abs($record->created_at->diffInMinutes($record->updated_at, false)) <= 10;
    }

    protected function wasCreatedNearSiteProvisioning(Site $site, object $record): bool
    {
        if (! $site->created_at || ! $record->created_at) {
            return false;
        }

        return abs($site->created_at->diffInMinutes($record->created_at, false)) <= 10;
    }
};
