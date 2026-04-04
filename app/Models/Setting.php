<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Slimani\MediaManager\Concerns\InteractsWithMediaFiles;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Setting extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMediaFiles;
    use InteractsWithMedia;

    protected $fillable = [
        'site_id',
        'site_logo_id',
        'site_name',
        'site_tagline',
        'site_description',
        'site_email',
        'site_phone',
        'site_address',
        'site_favicon_id',
        'default_og_image_id',
        'meta_title',
        'meta_description',
        'posts_per_page',
        'social_links',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'posts_per_page' => 'integer',
            'social_links' => 'array',
        ];
    }

    public static function current(): self
    {
        return static::forSite();
    }

    public static function forSite(?Site $site = null): self
    {
        if (! Schema::hasTable((new static())->getTable())) {
            return static::make(static::defaultAttributes($site));
        }

        $query = static::query()
            ->with(['siteLogo', 'siteFavicon', 'defaultOgImage']);

        if ($site) {
            $query->whereBelongsTo($site);
        }

        return $query->first() ?? static::make(static::defaultAttributes($site));
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function siteLogo(): BelongsTo
    {
        return $this->mediaFile('site_logo_id');
    }

    public function siteFavicon(): BelongsTo
    {
        return $this->mediaFile('site_favicon_id');
    }

    public function defaultOgImage(): BelongsTo
    {
        return $this->mediaFile('default_og_image_id');
    }

    public function siteLogoUrl(string $conversion = ''): ?string
    {
        return $this->managedImageUrl(
            $this->siteLogo,
            'site-logo',
            $conversion,
        );
    }

    public function siteFaviconUrl(string $conversion = ''): ?string
    {
        return $this->managedImageUrl(
            $this->siteFavicon,
            'site-favicon',
            $conversion,
        );
    }

    public function defaultOgImageUrl(string $conversion = ''): ?string
    {
        return $this->managedImageUrl(
            $this->defaultOgImage,
            'default-og-image',
            $conversion,
        );
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('site-logo')
            ->useDisk('public')
            ->singleFile();

        $this->addMediaCollection('site-favicon')
            ->useDisk('public')
            ->singleFile();

        $this->addMediaCollection('default-og-image')
            ->useDisk('public')
            ->singleFile();
    }

    /**
     * @return array<string, mixed>
     */
    protected static function defaultAttributes(?Site $site = null): array
    {
        return [
            'site_name' => $site?->name ?? config('app.name'),
            'posts_per_page' => 10,
        ];
    }

    protected function managedImageUrl(mixed $file, string $legacyCollection, string $conversion = ''): ?string
    {
        $url = $file?->getUrl($conversion);

        if (filled($url)) {
            return $url;
        }

        $legacyUrl = blank($conversion)
            ? $this->getFirstMediaUrl($legacyCollection)
            : $this->getFirstMediaUrl($legacyCollection, $conversion);

        return filled($legacyUrl)
            ? $legacyUrl
            : $this->getFirstMediaUrl($legacyCollection);
    }
}
