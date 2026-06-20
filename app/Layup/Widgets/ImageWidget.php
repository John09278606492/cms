<?php

declare(strict_types=1);

namespace App\Layup\Widgets;

use Crumbls\Layup\View\BaseWidget;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\ToggleButtons;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Slimani\MediaManager\Form\MediaPicker;
use Slimani\MediaManager\Models\File as MediaFile;

class ImageWidget extends BaseWidget
{
    public static function getType(): string
    {
        return 'image';
    }

    public static function getLabel(): string
    {
        return 'Image';
    }

    public static function getIcon(): string
    {
        return 'heroicon-o-photo';
    }

    public static function getCategory(): string
    {
        return 'media';
    }

    public static function getFormSchema(): array
    {
        return [
            MediaPicker::make('src')
                ->label('Image')
                ->required()
                ->conversion('')
                ->image()
                ->imageEditor()
                ->directory(fn (): string => 'Builder/Images')
                ->saveRelationshipsUsing(null)
                ->helperText('Upload a new image or reuse one from this site\'s asset library.'),
            TextInput::make('alt')
                ->label('Alt text')
                ->maxLength(255),
            Textarea::make('caption')
                ->label('Caption')
                ->rows(3)
                ->columnSpanFull(),
            ToggleButtons::make('width')
                ->label('Width')
                ->options([
                    'content' => 'Content',
                    'wide' => 'Wide',
                    'full' => 'Full',
                ])
                ->default('content')
                ->grouped(),
        ];
    }

    public static function getDefaultData(): array
    {
        return [
            'src' => null,
            'image' => null,
            'alt' => '',
            'caption' => '',
            'width' => 'content',
        ];
    }

    public static function getPreview(array $data): string
    {
        $label = static::describeImage($data['src'] ?? $data['image'] ?? null);

        return $label !== null ? 'Image: ' . $label : 'Image block';
    }

    public static function onDelete(array $data, ?\Crumbls\Layup\Support\WidgetContext $context = null): void
    {
        $image = $data['image'] ?? $data['src'] ?? null;

        if (is_numeric($image) || ! is_string($image) || $image === '' || filter_var($image, FILTER_VALIDATE_URL)) {
            return;
        }

        Storage::disk('public')->delete(ltrim($image, '/'));
    }

    public function render(): View
    {
        $data = $this->data;
        $image = $data['src'] ?? $data['image'] ?? null;

        return view('mason.bricks.image', [
            'alt' => $data['alt'] ?? null,
            'caption' => $data['caption'] ?? null,
            'imageUrl' => static::resolveImageUrl($image),
            'width' => $data['width'] ?? 'content',
            'styles' => is_array($data['styles'] ?? null) ? $data['styles'] : [],
        ]);
    }

    protected static function resolveImageUrl(mixed $image): ?string
    {
        if (blank($image)) {
            return null;
        }

        if (is_numeric($image)) {
            $file = MediaFile::find((int) $image);

            return $file?->getUrl();
        }

        if (! is_string($image)) {
            return null;
        }

        if (filter_var($image, FILTER_VALIDATE_URL)) {
            return $image;
        }

        return Storage::disk('public')->url(ltrim($image, '/'));
    }

    protected static function describeImage(mixed $image): ?string
    {
        $url = static::resolveImageUrl($image);

        if (filled($url)) {
            $path = parse_url($url, PHP_URL_PATH) ?: $url;

            return basename($path);
        }

        if (is_numeric($image)) {
            return 'media #' . (int) $image;
        }

        return null;
    }
}
