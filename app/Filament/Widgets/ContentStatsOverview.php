<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Pages\PageResource;
use App\Filament\Resources\Posts\PostResource;
use App\Models\Page;
use App\Models\Post;
use App\Models\Setting;
use App\Models\Site;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ContentStatsOverview extends StatsOverviewWidget
{
    protected ?string $heading = 'Publishing overview';

    protected function getStats(): array
    {
        $postsQuery = $this->scopeToTenant(Post::query());
        $pagesQuery = $this->scopeToTenant(Page::query());

        $publishedPostsUrl = $this->resourceUrl(PostResource::class, 'index');
        $pagesUrl = $this->resourceUrl(PageResource::class, 'index');

        return [
            Stat::make('Published posts', (string) (clone $postsQuery)->published()->count())
                ->description('Articles currently live')
                ->color('success')
                ->url($publishedPostsUrl),
            Stat::make('Editorial queue', (string) (clone $postsQuery)->whereIn('status', ['draft', 'scheduled'])->count())
                ->description('Draft and scheduled posts')
                ->color('warning')
                ->url($publishedPostsUrl),
            Stat::make('Published pages', (string) (clone $pagesQuery)->published()->count())
                ->description('Navigable site pages')
                ->color('primary')
                ->url($pagesUrl),
            Stat::make('Media assets', (string) $this->getTenantMediaCount())
                ->description('Images and attachments in the library')
                ->color('gray'),
        ];
    }

    protected function scopeToTenant(Builder $query): Builder
    {
        $tenant = Filament::getTenant();

        if (! $tenant instanceof Site) {
            return $query;
        }

        return $query->whereBelongsTo($tenant, 'site');
    }

    protected function resourceUrl(string $resource, string $page = 'index', array $parameters = []): ?string
    {
        $tenant = Filament::getTenant();

        if (! $tenant instanceof Site) {
            return null;
        }

        return $resource::getUrl(
            $page,
            $parameters,
            panel: Filament::getCurrentOrDefaultPanel()->getId(),
            tenant: $tenant,
        );
    }

    protected function getTenantMediaCount(): int
    {
        $tenant = Filament::getTenant();

        if (! $tenant instanceof Site) {
            return 0;
        }

        return Media::query()
            ->where(function (Builder $query) use ($tenant): void {
                $query
                    ->where(function (Builder $query) use ($tenant): void {
                        $query
                            ->where('model_type', Post::class)
                            ->whereIn('model_id', $tenant->posts()->select('posts.id'));
                    })
                    ->orWhere(function (Builder $query) use ($tenant): void {
                        $query
                            ->where('model_type', Page::class)
                            ->whereIn('model_id', $tenant->pages()->select('pages.id'));
                    })
                    ->orWhere(function (Builder $query) use ($tenant): void {
                        $query
                            ->where('model_type', Setting::class)
                            ->whereIn('model_id', $tenant->settings()->select('settings.id'));
                    });
            })
            ->count();
    }
}
