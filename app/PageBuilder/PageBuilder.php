<?php

namespace App\PageBuilder;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;

/**
 * The custom, Filament-native page builder. Each "block" is a Filament Builder
 * block with its own form schema and a Blade preview view; the same Blade view
 * renders the block on the public site (see resources/views/page-builder).
 *
 * Built entirely on Laravel + Filament — no third-party page-builder package.
 */
class PageBuilder
{
    /**
     * @return array<int, Block>
     */
    public static function blocks(): array
    {
        return [
            self::hero(),
            self::heading(),
            self::paragraph(),
            self::image(),
            self::mediaText(),
            self::button(),
            self::featureGrid(),
            self::stats(),
            self::accordion(),
            self::testimonial(),
            self::callToAction(),
            self::gallery(),
            self::video(),
            self::divider(),
            self::spacer(),
        ];
    }

    protected static function alignOptions(): array
    {
        return ['left' => 'Left', 'center' => 'Center', 'right' => 'Right'];
    }

    protected static function widthOptions(): array
    {
        return ['content' => 'Content', 'wide' => 'Wide', 'full' => 'Full width'];
    }

    protected static function hero(): Block
    {
        return Block::make('hero')
            ->label('Hero')
            ->icon('heroicon-o-rectangle-group')
            ->preview('page-builder.blocks.hero')
            ->schema([
                TextInput::make('eyebrow')->maxLength(80),
                TextInput::make('heading')->required()->maxLength(160),
                Textarea::make('copy')->rows(3)->columnSpanFull(),
                TextInput::make('primary_label')->label('Primary button label')->maxLength(40),
                TextInput::make('primary_url')->label('Primary button URL')->maxLength(255),
                TextInput::make('secondary_label')->label('Secondary button label')->maxLength(40),
                TextInput::make('secondary_url')->label('Secondary button URL')->maxLength(255),
                Select::make('surface')->options(['soft' => 'Soft', 'contrast' => 'Contrast (dark)', 'minimal' => 'Minimal'])->default('contrast'),
                Select::make('align')->options(self::alignOptions())->default('center'),
            ])
            ->columns(2);
    }

    protected static function heading(): Block
    {
        return Block::make('heading')
            ->label('Heading')
            ->icon('heroicon-o-bars-3-bottom-left')
            ->preview('page-builder.blocks.heading')
            ->schema([
                TextInput::make('text')->required()->maxLength(200)->columnSpanFull(),
                Select::make('level')->options(['h1' => 'H1', 'h2' => 'H2', 'h3' => 'H3', 'h4' => 'H4'])->default('h2'),
                Select::make('align')->options(self::alignOptions())->default('left'),
                ColorPicker::make('color'),
            ])
            ->columns(3);
    }

    protected static function paragraph(): Block
    {
        return Block::make('paragraph')
            ->label('Text')
            ->icon('heroicon-o-document-text')
            ->preview('page-builder.blocks.paragraph')
            ->schema([
                RichEditor::make('content')->required()->columnSpanFull(),
                Select::make('width')->options(self::widthOptions())->default('content'),
            ]);
    }

    protected static function image(): Block
    {
        return Block::make('image')
            ->label('Image')
            ->icon('heroicon-o-photo')
            ->preview('page-builder.blocks.image')
            ->schema([
                FileUpload::make('image')->image()->disk('public')->directory('page-builder')->imageEditor()->columnSpanFull(),
                TextInput::make('alt')->label('Alt text')->maxLength(255),
                TextInput::make('caption')->maxLength(255),
                Select::make('width')->options(self::widthOptions())->default('content'),
                Toggle::make('rounded')->default(true),
            ])
            ->columns(2);
    }

    protected static function mediaText(): Block
    {
        return Block::make('media_text')
            ->label('Image + text')
            ->icon('heroicon-o-view-columns')
            ->preview('page-builder.blocks.media_text')
            ->schema([
                FileUpload::make('image')->image()->disk('public')->directory('page-builder')->imageEditor()->columnSpanFull(),
                Select::make('image_side')->options(['left' => 'Image left', 'right' => 'Image right'])->default('left'),
                TextInput::make('heading')->maxLength(160),
                RichEditor::make('body')->columnSpanFull(),
                TextInput::make('button_label')->maxLength(40),
                TextInput::make('button_url')->maxLength(255),
            ])
            ->columns(2);
    }

    protected static function stats(): Block
    {
        return Block::make('stats')
            ->label('Stats')
            ->icon('heroicon-o-chart-bar')
            ->preview('page-builder.blocks.stats')
            ->schema([
                TextInput::make('heading')->maxLength(160),
                Select::make('columns')->options(['2' => '2', '3' => '3', '4' => '4'])->default('3'),
                Repeater::make('items')
                    ->schema([
                        TextInput::make('value')->required()->maxLength(20),
                        TextInput::make('label')->required()->maxLength(60),
                    ])
                    ->defaultItems(3)
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    protected static function accordion(): Block
    {
        return Block::make('accordion')
            ->label('Accordion / FAQ')
            ->icon('heroicon-o-queue-list')
            ->preview('page-builder.blocks.accordion')
            ->schema([
                TextInput::make('heading')->maxLength(160)->columnSpanFull(),
                Repeater::make('items')
                    ->schema([
                        TextInput::make('question')->required()->maxLength(200),
                        RichEditor::make('answer'),
                    ])
                    ->defaultItems(3)
                    ->columnSpanFull(),
            ]);
    }

    protected static function testimonial(): Block
    {
        return Block::make('testimonial')
            ->label('Testimonial')
            ->icon('heroicon-o-chat-bubble-bottom-center-text')
            ->preview('page-builder.blocks.testimonial')
            ->schema([
                Textarea::make('quote')->required()->rows(3)->columnSpanFull(),
                TextInput::make('author')->maxLength(80),
                TextInput::make('role')->maxLength(80),
                FileUpload::make('avatar')->image()->avatar()->disk('public')->directory('page-builder'),
            ])
            ->columns(2);
    }

    protected static function button(): Block
    {
        return Block::make('button')
            ->label('Button')
            ->icon('heroicon-o-cursor-arrow-rays')
            ->preview('page-builder.blocks.button')
            ->schema([
                TextInput::make('label')->required()->maxLength(60),
                TextInput::make('url')->required()->maxLength(255),
                Select::make('style')->options(['primary' => 'Primary', 'secondary' => 'Secondary', 'outline' => 'Outline'])->default('primary'),
                Select::make('align')->options(self::alignOptions())->default('left'),
                Toggle::make('new_tab')->label('Open in new tab'),
            ])
            ->columns(2);
    }

    protected static function featureGrid(): Block
    {
        return Block::make('feature_grid')
            ->label('Feature grid')
            ->icon('heroicon-o-squares-2x2')
            ->preview('page-builder.blocks.feature_grid')
            ->schema([
                TextInput::make('eyebrow')->maxLength(80),
                TextInput::make('heading')->maxLength(160),
                Textarea::make('intro')->rows(2)->columnSpanFull(),
                Select::make('columns')->options(['2' => '2 columns', '3' => '3 columns', '4' => '4 columns'])->default('3'),
                Repeater::make('items')
                    ->schema([
                        TextInput::make('emoji')->label('Icon / emoji')->maxLength(8),
                        TextInput::make('title')->required()->maxLength(80),
                        Textarea::make('description')->rows(2),
                    ])
                    ->defaultItems(3)
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    protected static function callToAction(): Block
    {
        return Block::make('call_to_action')
            ->label('Call to action')
            ->icon('heroicon-o-megaphone')
            ->preview('page-builder.blocks.call_to_action')
            ->schema([
                TextInput::make('eyebrow')->maxLength(80),
                TextInput::make('heading')->required()->maxLength(160),
                Textarea::make('copy')->rows(3)->columnSpanFull(),
                TextInput::make('button_label')->maxLength(40),
                TextInput::make('button_url')->maxLength(255),
                Select::make('theme')->options(['amber' => 'Amber', 'stone' => 'Dark'])->default('amber'),
            ])
            ->columns(2);
    }

    protected static function gallery(): Block
    {
        return Block::make('gallery')
            ->label('Gallery')
            ->icon('heroicon-o-photo')
            ->preview('page-builder.blocks.gallery')
            ->schema([
                FileUpload::make('images')->image()->multiple()->reorderable()->disk('public')->directory('page-builder')->columnSpanFull(),
                Select::make('columns')->options(['2' => '2', '3' => '3', '4' => '4'])->default('3'),
            ]);
    }

    protected static function video(): Block
    {
        return Block::make('video')
            ->label('Video')
            ->icon('heroicon-o-play-circle')
            ->preview('page-builder.blocks.video')
            ->schema([
                TextInput::make('url')->label('YouTube or Vimeo URL')->required()->maxLength(255)->columnSpanFull(),
                Select::make('width')->options(self::widthOptions())->default('content'),
            ]);
    }

    protected static function divider(): Block
    {
        return Block::make('divider')
            ->label('Divider')
            ->icon('heroicon-o-minus')
            ->preview('page-builder.blocks.divider')
            ->schema([]);
    }

    protected static function spacer(): Block
    {
        return Block::make('spacer')
            ->label('Spacer')
            ->icon('heroicon-o-arrows-up-down')
            ->preview('page-builder.blocks.spacer')
            ->schema([
                Select::make('height')->options(['sm' => 'Small', 'md' => 'Medium', 'lg' => 'Large', 'xl' => 'Extra large'])->default('md'),
            ]);
    }
}
