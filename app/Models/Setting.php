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
        'brand_primary',
        'brand_accent',
        'heading_font',
        'body_font',
    ];

    /**
     * Curated Google Fonts owners can choose for their theme.
     *
     * @var array<string, string> family => CSS weights to load
     */
    public const FONTS = [
        'Inter' => 'wght@400;500;600;700',
        'Poppins' => 'wght@400;500;600;700',
        'Montserrat' => 'wght@400;500;600;700',
        'Roboto' => 'wght@400;500;700',
        'Work Sans' => 'wght@400;500;600;700',
        'DM Sans' => 'wght@400;500;700',
        'Source Sans 3' => 'wght@400;600;700',
        'Lora' => 'wght@400;500;600;700',
        'Playfair Display' => 'wght@400;500;600;700',
        'Merriweather' => 'wght@400;700',
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

    /**
     * @return array<string, string> family => label, for a Filament Select.
     */
    public static function fontOptions(): array
    {
        return array_combine(array_keys(self::FONTS), array_keys(self::FONTS));
    }

    /**
     * Build the Google Fonts stylesheet URL for the selected theme fonts.
     */
    public function googleFontsUrl(): ?string
    {
        $families = collect([$this->heading_font, $this->body_font])
            ->filter(fn ($font): bool => filled($font) && isset(self::FONTS[$font]))
            ->unique();

        if ($families->isEmpty()) {
            return null;
        }

        $params = $families
            ->map(fn (string $font): string => 'family=' . str_replace(' ', '+', $font) . ':' . self::FONTS[$font])
            ->implode('&');

        return 'https://fonts.googleapis.com/css2?' . $params . '&display=swap';
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
