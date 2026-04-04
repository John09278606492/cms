<?php

namespace App\Models;

use App\Casts\PageBuilderContentCast;
use App\Enums\ContentStatus;
use App\Models\Concerns\HasPageBuilderContent;
use App\Support\PageNavigationManager;
use Datlechin\FilamentMenuBuilder\Contracts\MenuPanelable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\URL;
use Slimani\MediaManager\Concerns\InteractsWithMediaFiles;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Page extends Model implements HasMedia, MenuPanelable
{
    use HasFactory;
    use HasPageBuilderContent;
    use InteractsWithMediaFiles;
    use InteractsWithMedia;
    use SoftDeletes;

    protected $fillable = [
        'site_id',
        'user_id',
        'parent_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'status',
        'published_at',
        'is_homepage',
        'show_in_menu',
        'sort_order',
        'featured_image_id',
        'meta_title',
        'meta_description',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'content' => PageBuilderContentCast::class,
            'status' => ContentStatus::class,
            'published_at' => 'datetime',
            'is_homepage' => 'boolean',
            'show_in_menu' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saved(function (self $page): void {
            app(PageNavigationManager::class)->sync($page);
        });

        static::deleted(function (self $page): void {
            app(PageNavigationManager::class)->sync($page);
        });

        static::restored(function (self $page): void {
            app(PageNavigationManager::class)->sync($page);
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function featuredImage(): BelongsTo
    {
        return $this->mediaFile('featured_image_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', ContentStatus::Published->value)
            ->where(function (Builder $query): void {
                $query
                    ->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }

    public function isPublished(): bool
    {
        return $this->status === ContentStatus::Published
            && ($this->published_at === null || $this->published_at->isPast());
    }

    public function publicUrl(): string
    {
        if ($this->is_homepage && $this->isPublished()) {
            return route('sites.home', $this->site);
        }

        return route('sites.pages.show', [
            'site' => $this->site,
            'slug' => $this->slug,
        ]);
    }

    public function previewUrl(): string
    {
        if ($this->isPublished() && ! $this->trashed()) {
            return $this->publicUrl();
        }

        return URL::temporarySignedRoute(
            'sites.preview.pages.show',
            now()->addHours(12),
            [
                'site' => $this->site,
                'slug' => $this->slug,
            ],
        );
    }

    public function featuredImageUrl(string $conversion = ''): ?string
    {
        $url = $this->featuredImage?->getUrl($conversion);

        if (filled($url)) {
            return $url;
        }

        $legacyUrl = blank($conversion)
            ? $this->getFirstMediaUrl('featured-images')
            : $this->getFirstMediaUrl('featured-images', $conversion);

        return filled($legacyUrl)
            ? $legacyUrl
            : $this->getFirstMediaUrl('featured-images');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('featured-images')
            ->useDisk('public')
            ->singleFile();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->fit(Fit::Crop, 600, 400)
            ->nonQueued();
    }

    public function getMenuPanelName(): string
    {
        return 'Pages';
    }

    public function getMenuPanelTitleColumn(): string
    {
        return 'title';
    }

    public function getMenuPanelUrlUsing(): callable
    {
        return fn (): string => $this->publicUrl();
    }

    public function getMenuPanelModifyQueryUsing(): callable
    {
        return fn (Builder $query): Builder => $query
            ->published()
            ->orderBy('title');
    }
}
