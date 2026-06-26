<?php

namespace App\PageBuilder;

use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DateTimePicker;
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
            self::container(),
            self::columns(),
        ];
    }

    /**
     * Widget palette metadata for the visual designer — name, label, icon and a
     * group. Kept as a lightweight static map (not derived from the Filament
     * Block objects) so rendering the designer never has to build the heavy
     * nested form schemas. A test guards it against drifting from blocks().
     *
     * @return array<int, array{name: string, label: string, icon: string, group: string}>
     */
    public static function palette(): array
    {
        $groups = self::paletteGroups();

        return collect(self::paletteMeta())
            ->map(fn (array $meta, string $name): array => [
                'name' => $name,
                'label' => $meta[0],
                'icon' => $meta[1],
                'group' => $groups[$name] ?? 'Content',
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<string, array{0: string, 1: string}> name => [label, icon]
     */
    public static function paletteMeta(): array
    {
        return [
            'hero' => ['Hero', 'heroicon-o-rectangle-group'],
            'heading' => ['Heading', 'heroicon-o-bars-3-bottom-left'],
            'paragraph' => ['Text', 'heroicon-o-document-text'],
            'image' => ['Image', 'heroicon-o-photo'],
            'media_text' => ['Image + text', 'heroicon-o-view-columns'],
            'button' => ['Button', 'heroicon-o-cursor-arrow-rays'],
            'feature_grid' => ['Feature grid', 'heroicon-o-squares-2x2'],
            'stats' => ['Stats', 'heroicon-o-chart-bar'],
            'accordion' => ['Accordion / FAQ', 'heroicon-o-queue-list'],
            'testimonial' => ['Testimonial', 'heroicon-o-chat-bubble-bottom-center-text'],
            'call_to_action' => ['Call to action', 'heroicon-o-megaphone'],
            'pricing_table' => ['Pricing table', 'heroicon-o-currency-dollar'],
            'logo_cloud' => ['Logo cloud', 'heroicon-o-building-office-2'],
            'tabs' => ['Tabs', 'heroicon-o-folder'],
            'contact_form' => ['Contact form', 'heroicon-o-envelope'],
            'icon_box' => ['Icon box', 'heroicon-o-sparkles'],
            'counter' => ['Animated counters', 'heroicon-o-calculator'],
            'progress_bars' => ['Progress bars', 'heroicon-o-chart-bar-square'],
            'star_rating' => ['Star rating', 'heroicon-o-star'],
            'social_icons' => ['Social icons', 'heroicon-o-share'],
            'posts_grid' => ['Blog posts', 'heroicon-o-newspaper'],
            'carousel' => ['Image carousel', 'heroicon-o-rectangle-stack'],
            'countdown' => ['Countdown timer', 'heroicon-o-clock'],
            'map' => ['Map', 'heroicon-o-map-pin'],
            'gallery' => ['Gallery', 'heroicon-o-photo'],
            'video' => ['Video', 'heroicon-o-play-circle'],
            'divider' => ['Divider', 'heroicon-o-minus'],
            'spacer' => ['Spacer', 'heroicon-o-arrows-up-down'],
            'container' => ['Container', 'heroicon-o-square-3-stack-3d'],
            'columns' => ['Columns', 'heroicon-o-view-columns'],
        ];
    }

    /**
     * Sensible starter data for a freshly-dropped widget so it is visible and
     * editable immediately.
     *
     * @return array<string, mixed>
     */
    public static function defaultData(string $name): array
    {
        return self::blockDefaults()[$name] ?? [];
    }

    /**
     * @return array<string, string>
     */
    protected static function paletteGroups(): array
    {
        return [
            'container' => 'Layout', 'columns' => 'Layout', 'spacer' => 'Layout', 'divider' => 'Layout',
            'hero' => 'Sections', 'call_to_action' => 'Sections', 'feature_grid' => 'Sections',
            'pricing_table' => 'Sections', 'stats' => 'Sections', 'posts_grid' => 'Sections',
            'heading' => 'Content', 'paragraph' => 'Content', 'button' => 'Content',
            'icon_box' => 'Content', 'accordion' => 'Content', 'tabs' => 'Content',
            'testimonial' => 'Content', 'star_rating' => 'Content', 'counter' => 'Content',
            'progress_bars' => 'Content', 'countdown' => 'Content', 'contact_form' => 'Content',
            'social_icons' => 'Content',
            'image' => 'Media', 'media_text' => 'Media', 'gallery' => 'Media',
            'carousel' => 'Media', 'video' => 'Media', 'logo_cloud' => 'Media', 'map' => 'Media',
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    protected static function blockDefaults(): array
    {
        return [
            'hero' => ['eyebrow' => 'Welcome', 'heading' => 'Your headline goes here', 'copy' => 'A short supporting sentence that explains the value.', 'primary_label' => 'Get started', 'primary_url' => '#', 'surface' => 'contrast', 'align' => 'center'],
            'heading' => ['text' => 'New heading', 'level' => 'h2', 'align' => 'left'],
            'paragraph' => ['content' => '<p>New text block. Select it to edit the content.</p>', 'width' => 'content'],
            'image' => ['width' => 'content', 'rounded' => true],
            'media_text' => ['image_side' => 'left', 'heading' => 'A section heading', 'body' => '<p>Describe this section here.</p>'],
            'button' => ['label' => 'Click me', 'url' => '#', 'style' => 'primary', 'align' => 'left'],
            'feature_grid' => ['heading' => 'Features', 'columns' => '3', 'items' => [['emoji' => '⚡', 'title' => 'Feature one', 'description' => 'Describe it.'], ['emoji' => '🔒', 'title' => 'Feature two', 'description' => 'Describe it.'], ['emoji' => '💜', 'title' => 'Feature three', 'description' => 'Describe it.']]],
            'stats' => ['columns' => '3', 'items' => [['value' => '10+', 'label' => 'Years'], ['value' => '5k', 'label' => 'Customers'], ['value' => '99%', 'label' => 'Uptime']]],
            'accordion' => ['heading' => 'FAQ', 'items' => [['question' => 'A question?', 'answer' => '<p>The answer.</p>']]],
            'testimonial' => ['quote' => 'A short, glowing quote goes here.', 'author' => 'Happy Customer', 'role' => 'Role, Company'],
            'call_to_action' => ['heading' => 'Ready to begin?', 'copy' => 'Add a final nudge here.', 'button_label' => 'Get started', 'button_url' => '#', 'theme' => 'amber'],
            'pricing_table' => ['heading' => 'Pricing', 'columns' => '3', 'plans' => [['name' => 'Starter', 'price' => '$0', 'period' => '/mo', 'features' => "Feature\nFeature", 'button_label' => 'Choose', 'button_url' => '#'], ['name' => 'Pro', 'price' => '$29', 'period' => '/mo', 'featured' => true, 'features' => "Everything\nPlus more", 'button_label' => 'Choose', 'button_url' => '#']]],
            'logo_cloud' => ['heading' => 'Trusted by', 'grayscale' => true],
            'tabs' => ['items' => [['label' => 'Tab one', 'content' => '<p>First tab.</p>'], ['label' => 'Tab two', 'content' => '<p>Second tab.</p>']]],
            'contact_form' => ['heading' => 'Get in touch', 'button_label' => 'Send message', 'show_subject' => true, 'send_email' => true],
            'icon_box' => ['icon' => '★', 'title' => 'Icon box', 'text' => 'A short description.', 'align' => 'center'],
            'counter' => ['columns' => '3', 'items' => [['value' => 100, 'suffix' => '+', 'label' => 'Projects'], ['value' => 50, 'label' => 'Clients'], ['value' => 5, 'suffix' => '★', 'label' => 'Rating']]],
            'progress_bars' => ['heading' => 'Skills', 'items' => [['label' => 'Design', 'percent' => 90], ['label' => 'Development', 'percent' => 75]]],
            'star_rating' => ['rating' => '5', 'label' => 'Loved by customers', 'align' => 'center'],
            'social_icons' => ['align' => 'center', 'style' => 'solid', 'links' => [['network' => 'facebook', 'url' => '#'], ['network' => 'instagram', 'url' => '#']]],
            'posts_grid' => ['heading' => 'Latest posts', 'count' => '3', 'columns' => '3'],
            'carousel' => ['autoplay' => true, 'interval' => '5000', 'ratio' => 'video'],
            'countdown' => ['heading' => 'Countdown', 'until' => now()->addDays(7)->setTime(12, 0)->toDateTimeString(), 'expired_text' => "We're live!"],
            'map' => ['query' => 'Eiffel Tower, Paris', 'height' => 'md', 'zoom' => '13'],
            'gallery' => ['columns' => '3'],
            'video' => ['width' => 'content'],
            'divider' => [],
            'spacer' => ['height' => 'md'],
            'columns' => ['gap' => 'md', 'columns' => [['blocks' => []], ['blocks' => []]]],
            'container' => ['direction' => 'column', 'gap' => 'md', 'align' => 'stretch', 'blocks' => []],
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
            self::iconBox(),
            self::counter(),
            self::progressBars(),
            self::starRating(),
            self::socialIcons(),
            self::postsGrid(),
            self::carousel(),
            self::countdown(),
            self::map(),
            self::gallery(),
            self::video(),
            self::divider(),
            self::spacer(),
        ];
    }

    protected static function container(): Block
    {
        return Block::make('container')
            ->label('Container')
            ->icon('heroicon-o-square-3-stack-3d')
            ->preview('page-builder.blocks.container')
            ->schema(self::withDesign([
                Select::make('direction')->label('Layout')
                    ->options(['column' => 'Stacked', 'row' => 'Side by side'])->default('column'),
                Select::make('gap')->options(['sm' => 'Small', 'md' => 'Medium', 'lg' => 'Large'])->default('md'),
                Select::make('align')->label('Align items')
                    ->options(['start' => 'Start', 'center' => 'Center', 'end' => 'End', 'stretch' => 'Stretch'])->default('stretch'),
                Builder::make('blocks')
                    ->label('Content')
                    ->blocks(self::contentBlocks())
                    ->blockPreviews()
                    ->addActionLabel('Add a block')
                    ->columnSpanFull(),
            ]));
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
                        TextInput::make('_w')->label('Width')->placeholder('e.g. 480px or 60%')
                            ->helperText('Caps to 100% on small screens.'),
                        TextInput::make('_w_tablet')->label('Width on tablet')->placeholder('e.g. 70%'),
                        TextInput::make('_w_mobile')->label('Width on mobile')->placeholder('e.g. 100%'),
                        Select::make('_align_mobile')->label('Text alignment on mobile')
                            ->options(self::alignOptions())->placeholder('Same as desktop'),
                        TextInput::make('_minh')->label('Min height')->placeholder('e.g. 320px'),
                        Select::make('_self')->label('Box alignment')
                            ->options(['left' => 'Left', 'center' => 'Center', 'right' => 'Right'])
                            ->placeholder('Default'),
                        TextInput::make('_offset_x')->label('Nudge right / left')->placeholder('e.g. 20px or -30px'),
                        TextInput::make('_offset_y')->label('Nudge down / up')->placeholder('e.g. -40px'),
                        TextInput::make('_z')->label('Layer (front/back)')->numeric()
                            ->helperText('Higher numbers sit in front when elements overlap.'),
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
                Fieldset::make('Responsive & motion')
                    ->columns(2)
                    ->schema([
                        Select::make('_anim')->label('Entrance animation')
                            ->options([
                                'none' => 'None',
                                'fade' => 'Fade in',
                                'fade-up' => 'Fade up',
                                'fade-down' => 'Fade down',
                                'zoom' => 'Zoom in',
                                'slide-left' => 'Slide from right',
                                'slide-right' => 'Slide from left',
                            ])
                            ->default('none')
                            ->helperText('Plays once as the block scrolls into view.'),
                        Toggle::make('_hide_mobile')->label('Hide on mobile'),
                        Toggle::make('_hide_tablet')->label('Hide on tablet'),
                        Toggle::make('_hide_desktop')->label('Hide on desktop'),
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

    protected static function iconBox(): Block
    {
        return Block::make('icon_box')
            ->label('Icon box')
            ->icon('heroicon-o-sparkles')
            ->preview('page-builder.blocks.icon_box')
            ->columns(2)
            ->schema(self::withDesign([
                TextInput::make('icon')->label('Icon / emoji')->maxLength(8)->default('★'),
                Select::make('align')->options(self::alignOptions())->default('center'),
                TextInput::make('title')->required()->maxLength(120)->columnSpanFull(),
                Textarea::make('text')->rows(3)->columnSpanFull(),
                TextInput::make('link_label')->label('Link label')->maxLength(40),
                TextInput::make('link_url')->label('Link URL')->maxLength(255),
            ]));
    }

    protected static function counter(): Block
    {
        return Block::make('counter')
            ->label('Animated counters')
            ->icon('heroicon-o-calculator')
            ->preview('page-builder.blocks.counter')
            ->columns(2)
            ->schema(self::withDesign([
                TextInput::make('heading')->maxLength(160),
                Select::make('columns')->options(['2' => '2', '3' => '3', '4' => '4'])->default('3'),
                Repeater::make('items')
                    ->schema([
                        TextInput::make('value')->label('Number')->numeric()->required(),
                        TextInput::make('prefix')->maxLength(8),
                        TextInput::make('suffix')->maxLength(8),
                        TextInput::make('label')->required()->maxLength(60),
                    ])
                    ->defaultItems(3)
                    ->columns(2)
                    ->columnSpanFull(),
            ]));
    }

    protected static function progressBars(): Block
    {
        return Block::make('progress_bars')
            ->label('Progress bars')
            ->icon('heroicon-o-chart-bar-square')
            ->preview('page-builder.blocks.progress_bars')
            ->schema(self::withDesign([
                TextInput::make('heading')->maxLength(160)->columnSpanFull(),
                Repeater::make('items')
                    ->schema([
                        TextInput::make('label')->required()->maxLength(60),
                        TextInput::make('percent')->numeric()->minValue(0)->maxValue(100)->required()->default(80),
                        ColorPicker::make('color'),
                    ])
                    ->defaultItems(3)
                    ->columns(3)
                    ->columnSpanFull(),
            ]));
    }

    protected static function starRating(): Block
    {
        return Block::make('star_rating')
            ->label('Star rating')
            ->icon('heroicon-o-star')
            ->preview('page-builder.blocks.star_rating')
            ->columns(2)
            ->schema(self::withDesign([
                Select::make('rating')->options(['1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5'])->default('5'),
                Select::make('align')->options(self::alignOptions())->default('center'),
                TextInput::make('label')->maxLength(160)->columnSpanFull(),
            ]));
    }

    protected static function socialIcons(): Block
    {
        return Block::make('social_icons')
            ->label('Social icons')
            ->icon('heroicon-o-share')
            ->preview('page-builder.blocks.social_icons')
            ->columns(2)
            ->schema(self::withDesign([
                Select::make('align')->options(self::alignOptions())->default('center'),
                Select::make('style')->options(['solid' => 'Solid', 'outline' => 'Outline'])->default('solid'),
                Repeater::make('links')
                    ->schema([
                        Select::make('network')
                            ->options([
                                'facebook' => 'Facebook', 'x' => 'X / Twitter', 'instagram' => 'Instagram',
                                'linkedin' => 'LinkedIn', 'youtube' => 'YouTube', 'github' => 'GitHub',
                                'tiktok' => 'TikTok', 'email' => 'Email', 'website' => 'Website',
                            ])
                            ->required(),
                        TextInput::make('url')->required()->maxLength(255),
                    ])
                    ->defaultItems(3)
                    ->columns(2)
                    ->columnSpanFull(),
            ]));
    }

    protected static function postsGrid(): Block
    {
        return Block::make('posts_grid')
            ->label('Blog posts')
            ->icon('heroicon-o-newspaper')
            ->preview('page-builder.blocks.posts_grid')
            ->columns(2)
            ->schema(self::withDesign([
                TextInput::make('heading')->maxLength(160),
                Textarea::make('intro')->rows(2)->columnSpanFull(),
                Select::make('count')->label('How many')->options(['3' => '3', '6' => '6', '9' => '9'])->default('3'),
                Select::make('columns')->options(['2' => '2', '3' => '3', '4' => '4'])->default('3'),
            ]));
    }

    protected static function carousel(): Block
    {
        return Block::make('carousel')
            ->label('Image carousel')
            ->icon('heroicon-o-rectangle-stack')
            ->preview('page-builder.blocks.carousel')
            ->columns(2)
            ->schema(self::withDesign([
                FileUpload::make('images')->image()->multiple()->reorderable()->disk('public')->directory('page-builder')->columnSpanFull(),
                Toggle::make('autoplay')->label('Autoplay')->default(true),
                Select::make('interval')->label('Autoplay speed')
                    ->options(['3000' => '3 seconds', '5000' => '5 seconds', '8000' => '8 seconds'])
                    ->default('5000'),
                Select::make('ratio')->label('Aspect ratio')
                    ->options(['video' => '16 : 9', 'wide' => '21 : 9', 'square' => '1 : 1'])
                    ->default('video'),
            ]));
    }

    protected static function countdown(): Block
    {
        return Block::make('countdown')
            ->label('Countdown timer')
            ->icon('heroicon-o-clock')
            ->preview('page-builder.blocks.countdown')
            ->columns(2)
            ->schema(self::withDesign([
                TextInput::make('heading')->maxLength(160),
                DateTimePicker::make('until')->label('Counts down to')->required()->seconds(false),
                TextInput::make('expired_text')->label('Message when finished')->maxLength(160)->default("We're live!")->columnSpanFull(),
            ]));
    }

    protected static function map(): Block
    {
        return Block::make('map')
            ->label('Map')
            ->icon('heroicon-o-map-pin')
            ->preview('page-builder.blocks.map')
            ->columns(2)
            ->schema(self::withDesign([
                TextInput::make('query')->label('Address or place')->required()->maxLength(255)->columnSpanFull()
                    ->helperText('e.g. "Eiffel Tower, Paris" or a full street address.'),
                Select::make('height')->options(['sm' => 'Short', 'md' => 'Medium', 'lg' => 'Tall'])->default('md'),
                Select::make('zoom')->options(['10' => 'City', '13' => 'District', '16' => 'Street'])->default('13'),
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
