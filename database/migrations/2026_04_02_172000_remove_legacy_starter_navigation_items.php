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
                'menus.menuItems:id,menu_id,linkable_type,linkable_id,title,url',
                'pages:id,site_id,title,slug',
                'posts:id,site_id,title,slug',
            ])
            ->get()
            ->each(function (Site $site): void {
                $starterPageIds = $site->pages
                    ->filter(fn (Page $page): bool => ($page->slug === 'about' && $page->title === 'About')
                        || ($page->slug === 'contact' && $page->title === 'Contact'))
                    ->pluck('id')
                    ->all();

                $starterPostIds = $site->posts
                    ->filter(fn (Post $post): bool => $post->slug === 'first-post' && $post->title === 'First Post')
                    ->pluck('id')
                    ->all();

                $homeUrl = route('sites.home', $site, false);
                $blogUrl = route('sites.blog.index', $site, false);

                $starterMenuItemIds = $site->menus
                    ->flatMap->menuItems
                    ->filter(function (MenuItem $item) use ($blogUrl, $homeUrl, $starterPageIds, $starterPostIds): bool {
                        if (
                            blank($item->linkable_type)
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
            });
    }

    public function down(): void
    {
        //
    }
};
