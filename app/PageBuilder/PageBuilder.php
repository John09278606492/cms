<?php

namespace App\PageBuilder;

use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;

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
            ...self::contentBlocks(),
            self::columns(),
        ];
    }

    /**
     * Blocks that can live inside a column. Excludes the Columns block itself to
     * avoid infinite nesting.
     *
     * @return array<int, Block>
     */
    protected static function contentBlocks(): array
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
            self::pricingTable(),
            self::logoCloud(),
            self::tabs(),
            self::contactForm(),
            self::gallery(),
            self::video(),
            self::divider(),
            self::spacer(),
        ];
    }

    protected static function columns(): Block
    {
        return Block::make('columns')
            ->label('Columns')
            ->icon('heroicon-o-view-columns')
            ->preview('page-builder.blocks.columns')
            ->schema(self::withDesign([
                Select::make('gap')->options(['sm' => 'Small', 'md' => 'Medium', 'lg' => 'Large'])->default('md'),
                Repeater::make('columns')
                    ->label('Columns')
                    ->schema([
                        Builder::make('blocks')
                            ->label('Column content')
                            ->blocks(self::contentBlocks())
                            ->blockPreviews()
                            ->addActionLabel('Add a block')
                            ->columnSpanFull(),
                    ])
                    ->minItems(1)
                    ->maxItems(4)
                    ->defaultItems(2)
                    ->grid(2)
                    ->columnSpanFull(),
            ]));
    }

    protected static function alignOptions(): array
    {
        return ['left' => 'Left', 'center' => 'Center', 'right' => 'Right'];
    }

    protected static function widthOptions(): array
    {
        return ['content' => 'Content', 'wide' => 'Wide', 'full' => 'Full width'];
    }

    /**
     * Universal "Design" controls appended to every block. Keys are underscore-
     * prefixed so they never collide with a block's content fields; the front-end
     * renderer (<x-page-builder>) wraps each block with these styles.
     */
    protected static function spacingOptions(): array
    {
        return ['none' => 'None', 'sm' => 'Small', 'md' => 'Medium', 'lg' => 'Large', 'xl' => 'Extra large'];
    }

    protected static function designSection(): Section
    {
        return Section::make('Design')
            ->icon('heroicon-o-paint-brush')
            ->collapsed()
            ->schema([
                Fieldset::make('Background')
                    ->columns(2)
                    ->schema([
                        ColorPicker::make('_bg')->label('Background colour'),
                        ColorPicker::make('_grad_to')->label('Gradient to')
                            ->helperText('Set with a background colour for a gradient.'),
                        FileUpload::make('_bg_image')->label('Background image')
                            ->image()->disk('public')->directory('page-builder')->imageEditor(),
                        Select::make('_overlay')->label('Image overlay')
                            ->options(['none' => 'None', 'light' => 'Light', 'dark' => 'Dark'])
                            ->default('none')
                            ->helperText('Improves text readability over an image.'),
                    ]),
                Fieldset::make('Spacing & size')
                    ->columns(2)
                    ->schema([
                        Select::make('_pad')->label('Vertical padding')->options(self::spacingOptions())->default('none'),
                        Select::make('_padx')->label('Horizontal padding')->options(self::spacingOptions())->default('none'),
                        Select::make('_mt')->label('Margin top')->options(self::spacingOptions())->default('none'),
                        Select::make('_mb')->label('Margin bottom')->options(self::spacingOptions())->default('none'),
                        Select::make('_width')->label('Container width')
                            ->options(['default' => 'Default', 'narrow' => 'Narrow', 'wide' => 'Wide', 'full' => 'Full width'])
                            ->default('default'),
                        Select::make('_align')->label('Text alignment')->options(self::alignOptions())->placeholder('Inherit'),
                    ]),
                Fieldset::make('Typography')
                    ->columns(3)
                    ->schema([
                        ColorPicker::make('_text_color')->label('Text colour'),
                        Select::make('_font_size')->label('Text size')
                            ->options(['default' => 'Default', 'sm' => 'Small', 'base' => 'Base', 'lg' => 'Large', 'xl' => 'Extra large'])
                            ->default('default'),
                        Select::make('_font_weight')->label('Text weight')
                            ->options(['default' => 'Default', 'normal' => 'Normal', 'medium' => 'Medium', 'semibold' => 'Semibold', 'bold' => 'Bold'])
                            ->default('default'),
                    ]),
                Fieldset::make('Border & shadow')
                    ->columns(2)
                    ->schema([
                        Select::make('_radius')->label('Rounded corners')
                            ->options(['none' => 'None', 'sm' => 'Small', 'md' => 'Medium', 'lg' => 'Large', 'xl' => 'Extra large', 'full' => 'Pill / circle'])
                            ->default('none'),
                        Select::make('_shadow')->label('Shadow')
                            ->options(['none' => 'None', 'sm' => 'Small', 'md' => 'Medium', 'lg' => 'Large', 'xl' => 'Extra large'])
                            ->default('none'),
                        Select::make('_border_width')->label('Border width')
                            ->options(['none' => 'None', '1' => '1px', '2' => '2px', '4' => '4px'])
                            ->default('none'),
                        ColorPicker::make('_border_color')->label('Border colour'),
                    ]),
            ]);
    }

    /**
     * Append the shared Design section to a block's content schema.
     *
     * @param  array<int, mixed>  $content
     * @return array<int, mixed>
     */
    protected static function withDesign(array $content): array
    {
        return [...$content, self::designSection()];
    }

    protected static function hero(): Block
    {
        return Block::make('hero')
            ->label('Hero')
            ->icon('heroicon-o-rectangle-group')
            ->preview('page-builder.blocks.hero')
            ->columns(2)
            ->schema(self::withDesign([
                TextInput::make('eyebrow')->maxLength(80),
                TextInput::make('heading')->required()->maxLength(160),
                Textarea::make('copy')->rows(3)->columnSpanFull(),
                TextInput::make('primary_label')->label('Primary button label')->maxLength(40),
                TextInput::make('primary_url')->label('Primary button URL')->maxLength(255),
                TextInput::make('secondary_label')->label('Secondary button label')->maxLength(40),
                TextInput::make('secondary_url')->label('Secondary button URL')->maxLength(255),
                Select::make('surface')->options(['soft' => 'Soft', 'contrast' => 'Contrast (dark)', 'minimal' => 'Minimal'])->default('contrast'),
                Select::make('align')->options(self::alignOptions())->default('center'),
            ]));
    }

    protected static function heading(): Block
    {
        return Block::make('heading')
            ->label('Heading')
            ->icon('heroicon-o-bars-3-bottom-left')
            ->preview('page-builder.blocks.heading')
            ->columns(3)
            ->schema(self::withDesign([
                TextInput::make('text')->required()->maxLength(200)->columnSpanFull(),
                Select::make('level')->options(['h1' => 'H1', 'h2' => 'H2', 'h3' => 'H3', 'h4' => 'H4'])->default('h2'),
                Select::make('align')->options(self::alignOptions())->default('left'),
                ColorPicker::make('color'),
            ]));
    }

    protected static function paragraph(): Block
    {
        return Block::make('paragraph')
            ->label('Text')
            ->icon('heroicon-o-document-text')
            ->preview('page-builder.blocks.paragraph')
            ->schema(self::withDesign([
                RichEditor::make('content')->required()->columnSpanFull(),
                Select::make('width')->options(self::widthOptions())->default('content'),
            ]));
    }

    protected static function image(): Block
    {
        return Block::make('image')
            ->label('Image')
            ->icon('heroicon-o-photo')
            ->preview('page-builder.blocks.image')
            ->columns(2)
            ->schema(self::withDesign([
                FileUpload::make('image')->image()->disk('public')->directory('page-builder')->imageEditor()->columnSpanFull(),
                TextInput::make('alt')->label('Alt text')->maxLength(255),
                TextInput::make('caption')->maxLength(255),
                Select::make('width')->options(self::widthOptions())->default('content'),
                Toggle::make('rounded')->default(true),
            ]));
    }

    protected static function mediaText(): Block
    {
        return Block::make('media_text')
            ->label('Image + text')
            ->icon('heroicon-o-view-columns')
            ->preview('page-builder.blocks.media_text')
            ->columns(2)
            ->schema(self::withDesign([
                FileUpload::make('image')->image()->disk('public')->directory('page-builder')->imageEditor()->columnSpanFull(),
                Select::make('image_side')->options(['left' => 'Image left', 'right' => 'Image right'])->default('left'),
                TextInput::make('heading')->maxLength(160),
                RichEditor::make('body')->columnSpanFull(),
                TextInput::make('button_label')->maxLength(40),
                TextInput::make('button_url')->maxLength(255),
            ]));
    }

    protected static function button(): Block
    {
        return Block::make('button')
            ->label('Button')
            ->icon('heroicon-o-cursor-arrow-rays')
            ->preview('page-builder.blocks.button')
            ->columns(2)
            ->schema(self::withDesign([
                TextInput::make('label')->required()->maxLength(60),
                TextInput::make('url')->required()->maxLength(255),
                Select::make('style')->options(['primary' => 'Primary', 'secondary' => 'Secondary', 'outline' => 'Outline'])->default('primary'),
                Select::make('align')->options(self::alignOptions())->default('left'),
                Toggle::make('new_tab')->label('Open in new tab'),
            ]));
    }

    protected static function featureGrid(): Block
    {
        return Block::make('feature_grid')
            ->label('Feature grid')
            ->icon('heroicon-o-squares-2x2')
            ->preview('page-builder.blocks.feature_grid')
            ->columns(2)
            ->schema(self::withDesign([
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
            ]));
    }

    protected static function stats(): Block
    {
        return Block::make('stats')
            ->label('Stats')
            ->icon('heroicon-o-chart-bar')
            ->preview('page-builder.blocks.stats')
            ->columns(2)
            ->schema(self::withDesign([
                TextInput::make('heading')->maxLength(160),
                Select::make('columns')->options(['2' => '2', '3' => '3', '4' => '4'])->default('3'),
                Repeater::make('items')
                    ->schema([
                        TextInput::make('value')->required()->maxLength(20),
                        TextInput::make('label')->required()->maxLength(60),
                    ])
                    ->defaultItems(3)
                    ->columnSpanFull(),
            ]));
    }

    protected static function accordion(): Block
    {
        return Block::make('accordion')
            ->label('Accordion / FAQ')
            ->icon('heroicon-o-queue-list')
            ->preview('page-builder.blocks.accordion')
            ->schema(self::withDesign([
                TextInput::make('heading')->maxLength(160)->columnSpanFull(),
                Repeater::make('items')
                    ->schema([
                        TextInput::make('question')->required()->maxLength(200),
                        RichEditor::make('answer'),
                    ])
                    ->defaultItems(3)
                    ->columnSpanFull(),
            ]));
    }

    protected static function testimonial(): Block
    {
        return Block::make('testimonial')
            ->label('Testimonial')
            ->icon('heroicon-o-chat-bubble-bottom-center-text')
            ->preview('page-builder.blocks.testimonial')
            ->columns(2)
            ->schema(self::withDesign([
                Textarea::make('quote')->required()->rows(3)->columnSpanFull(),
                TextInput::make('author')->maxLength(80),
                TextInput::make('role')->maxLength(80),
                FileUpload::make('avatar')->image()->avatar()->disk('public')->directory('page-builder'),
            ]));
    }

    protected static function callToAction(): Block
    {
        return Block::make('call_to_action')
            ->label('Call to action')
            ->icon('heroicon-o-megaphone')
            ->preview('page-builder.blocks.call_to_action')
            ->columns(2)
            ->schema(self::withDesign([
                TextInput::make('eyebrow')->maxLength(80),
                TextInput::make('heading')->required()->maxLength(160),
                Textarea::make('copy')->rows(3)->columnSpanFull(),
                TextInput::make('button_label')->maxLength(40),
                TextInput::make('button_url')->maxLength(255),
                Select::make('theme')->options(['amber' => 'Amber', 'stone' => 'Dark'])->default('amber'),
            ]));
    }

    protected static function pricingTable(): Block
    {
        return Block::make('pricing_table')
            ->label('Pricing table')
            ->icon('heroicon-o-currency-dollar')
            ->preview('page-builder.blocks.pricing_table')
            ->columns(2)
            ->schema(self::withDesign([
                TextInput::make('eyebrow')->maxLength(80),
                TextInput::make('heading')->maxLength(160),
                Textarea::make('intro')->rows(2)->columnSpanFull(),
                Select::make('columns')->options(['2' => '2 plans', '3' => '3 plans', '4' => '4 plans'])->default('3'),
                Repeater::make('plans')
                    ->schema([
                        TextInput::make('name')->required()->maxLength(60),
                        TextInput::make('price')->required()->maxLength(20)->helperText('e.g. $29'),
                        TextInput::make('period')->maxLength(20)->helperText('e.g. /month'),
                        Textarea::make('description')->rows(2)->columnSpanFull(),
                        Textarea::make('features')->rows(4)->helperText('One feature per line')->columnSpanFull(),
                        TextInput::make('button_label')->maxLength(40),
                        TextInput::make('button_url')->maxLength(255),
                        Toggle::make('featured')->label('Highlight this plan'),
                    ])
                    ->defaultItems(3)
                    ->columnSpanFull(),
            ]));
    }

    protected static function logoCloud(): Block
    {
        return Block::make('logo_cloud')
            ->label('Logo cloud')
            ->icon('heroicon-o-building-office-2')
            ->preview('page-builder.blocks.logo_cloud')
            ->columns(2)
            ->schema(self::withDesign([
                TextInput::make('heading')->maxLength(160)->columnSpanFull(),
                FileUpload::make('logos')->image()->multiple()->reorderable()->disk('public')->directory('page-builder')->columnSpanFull(),
                Toggle::make('grayscale')->label('Greyscale logos')->default(true),
            ]));
    }

    protected static function tabs(): Block
    {
        return Block::make('tabs')
            ->label('Tabs')
            ->icon('heroicon-o-folder')
            ->preview('page-builder.blocks.tabs')
            ->schema(self::withDesign([
                Repeater::make('items')
                    ->label('Tabs')
                    ->schema([
                        TextInput::make('label')->required()->maxLength(60),
                        RichEditor::make('content')->columnSpanFull(),
                    ])
                    ->defaultItems(2)
                    ->minItems(1)
                    ->columnSpanFull(),
            ]));
    }

    protected static function contactForm(): Block
    {
        return Block::make('contact_form')
            ->label('Contact form')
            ->icon('heroicon-o-envelope')
            ->preview('page-builder.blocks.contact_form')
            ->columns(2)
            ->schema(self::withDesign([
                TextInput::make('heading')->maxLength(160),
                TextInput::make('button_label')->label('Button label')->maxLength(40)->default('Send message'),
                Textarea::make('intro')->rows(2)->columnSpanFull(),
                Toggle::make('show_subject')->label('Include a subject field')->default(true),
                Toggle::make('show_phone')->label('Include a phone field')->default(false),
                Textarea::make('success_message')->rows(2)->columnSpanFull()
                    ->default('Thanks! Your message has been sent.'),
                Section::make('Notifications & delivery')
                    ->description('Submissions are always saved under "Form submissions". Optionally email them too.')
                    ->icon('heroicon-o-envelope')
                    ->collapsed()
                    ->columns(2)
                    ->schema([
                        Toggle::make('send_email')
                            ->label('Email me new submissions')
                            ->default(true)
                            ->columnSpanFull(),
                        TextInput::make('to_email')
                            ->label('Send notifications to')
                            ->helperText('Comma-separate several addresses. Leave blank to use your site email.')
                            ->maxLength(255)
                            ->columnSpanFull(),
                        TextInput::make('redirect_url')
                            ->label('Redirect after submit (optional)')
                            ->helperText('Send visitors to this URL instead of showing the success message.')
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ]),
            ]));
    }

    protected static function gallery(): Block
    {
        return Block::make('gallery')
            ->label('Gallery')
            ->icon('heroicon-o-photo')
            ->preview('page-builder.blocks.gallery')
            ->schema(self::withDesign([
                FileUpload::make('images')->image()->multiple()->reorderable()->disk('public')->directory('page-builder')->columnSpanFull(),
                Select::make('columns')->options(['2' => '2', '3' => '3', '4' => '4'])->default('3'),
            ]));
    }

    protected static function video(): Block
    {
        return Block::make('video')
            ->label('Video')
            ->icon('heroicon-o-play-circle')
            ->preview('page-builder.blocks.video')
            ->schema(self::withDesign([
                TextInput::make('url')->label('YouTube or Vimeo URL')->required()->maxLength(255)->columnSpanFull(),
                Select::make('width')->options(self::widthOptions())->default('content'),
            ]));
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
