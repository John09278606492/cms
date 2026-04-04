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
        return [
            Stat::make('Published posts', (string) Post::query()->published()->count())
                ->description('Articles currently live')
                ->color('success')
                ->url(PostResource::getUrl('index')),
            Stat::make('Editorial queue', (string) Post::query()->whereIn('status', ['draft', 'scheduled'])->count())
                ->description('Draft and scheduled posts')
                ->color('warning')
                ->url(PostResource::getUrl('index')),
            Stat::make('Published pages', (string) Page::query()->published()->count())
                ->description('Navigable site pages')
                ->color('primary')
                ->url(PageResource::getUrl('index')),
            Stat::make('Media assets', (string) $this->getTenantMediaCount())
                ->description('Images and attachments in the library')
                ->color('gray'),
        ];
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
