<?php

namespace App\Mason\Bricks;

use Awcodes\Mason\Brick;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\ToggleButtons;
use Illuminate\Support\Facades\Storage;
use Slimani\MediaManager\Form\MediaPicker;
use Slimani\MediaManager\Models\File as MediaFile;

class Image extends Brick
{
    public static function getId(): string
    {
        return 'image';
    }

    public static function getIcon(): string|null
    {
        return 'heroicon-o-photo';
    }

    public static function toHtml(array $config, ?array $data = null): ?string
    {
        $image = $config['image'] ?? null;
        $managedImage = filled($image) && is_numeric((string) $image)
            ? MediaFile::find((int) $image)
            : null;

        return view('mason.bricks.image', [
            'alt' => $config['alt'] ?? null,
            'caption' => $config['caption'] ?? null,
            'imageUrl' => $managedImage
                ? $managedImage->getUrl()
                : (filled($image)
                    ? (filter_var($image, FILTER_VALIDATE_URL) ? $image : Storage::disk('public')->url($image))
                    : null),
            'width' => $config['width'] ?? 'content',
        ])->render();
    }

    public static function configureBrickAction(Action $action): Action
    {
        return $action
            ->slideOver()
            ->schema([
                MediaPicker::make('image')
                    ->required()
                    ->conversion('')
                    ->image()
                    ->imageEditor()
                    ->directory(fn (): string => 'Builder/Images')
                    ->helperText('Upload a new image or reuse one from this site\'s asset library.')
                    ->saveRelationshipsUsing(null),
                TextInput::make('alt')
                    ->label('Alt text')
                    ->maxLength(255),
                Textarea::make('caption')
                    ->rows(3)
                    ->columnSpanFull(),
                ToggleButtons::make('width')
                    ->options([
                        'content' => 'Content',
                        'wide' => 'Wide',
                        'full' => 'Full',
                    ])
                    ->default('content')
                    ->grouped(),
            ]);
    }
}
