<?php

namespace App\PageBuilder;

/**
 * Compact, render-agnostic field schemas for the visual designer's inspector.
 * Mirrors the editable content of each Filament block, but as plain arrays the
 * designer can bind to with wire:model. Field types: text, textarea, richtext,
 * select, toggle, color, number, datetime, image (read-only note), repeater.
 */
class BlockFields
{
    /**
     * Content fields for a block type.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function for(string $type): array
    {
        return self::all()[$type] ?? [];
    }

    /**
     * Shared "Design" fields appended to every block in the inspector.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function design(): array
    {
        $space = ['none' => 'None', 'sm' => 'Small', 'md' => 'Medium', 'lg' => 'Large', 'xl' => 'Extra large'];

        return [
            ['key' => '_w', 'label' => 'Width (e.g. 480px or 60%)', 'type' => 'text'],
            ['key' => '_w_tablet', 'label' => 'Width on tablet', 'type' => 'text'],
            ['key' => '_w_mobile', 'label' => 'Width on mobile', 'type' => 'text'],
            ['key' => '_align_mobile', 'label' => 'Text alignment on mobile', 'type' => 'select', 'options' => ['' => 'Same as desktop', 'left' => 'Left', 'center' => 'Center', 'right' => 'Right']],
            ['key' => '_minh', 'label' => 'Min height (e.g. 320px)', 'type' => 'text'],
            ['key' => '_self', 'label' => 'Box alignment', 'type' => 'select', 'options' => ['' => 'Default', 'left' => 'Left', 'center' => 'Center', 'right' => 'Right']],
            ['key' => '_offset_x', 'label' => 'Nudge right / left (e.g. 20px, -30px)', 'type' => 'text'],
            ['key' => '_offset_y', 'label' => 'Nudge down / up (e.g. -40px)', 'type' => 'text'],
            ['key' => '_z', 'label' => 'Layer (front/back)', 'type' => 'number'],
            ['key' => '_bg', 'label' => 'Background colour', 'type' => 'color'],
            ['key' => '_grad_to', 'label' => 'Gradient to', 'type' => 'color'],
            ['key' => '_text_color', 'label' => 'Text colour', 'type' => 'color'],
            ['key' => '_pad', 'label' => 'Vertical padding', 'type' => 'select', 'options' => $space],
            ['key' => '_padx', 'label' => 'Horizontal padding', 'type' => 'select', 'options' => $space],
            ['key' => '_mt', 'label' => 'Margin top', 'type' => 'select', 'options' => $space],
            ['key' => '_mb', 'label' => 'Margin bottom', 'type' => 'select', 'options' => $space],
            ['key' => '_width', 'label' => 'Width', 'type' => 'select', 'options' => ['default' => 'Default', 'narrow' => 'Narrow', 'wide' => 'Wide', 'full' => 'Full width']],
            ['key' => '_align', 'label' => 'Alignment', 'type' => 'select', 'options' => ['' => 'Inherit', 'left' => 'Left', 'center' => 'Center', 'right' => 'Right']],
            ['key' => '_radius', 'label' => 'Rounded corners', 'type' => 'select', 'options' => ['none' => 'None', 'sm' => 'Small', 'md' => 'Medium', 'lg' => 'Large', 'xl' => 'Extra large', 'full' => 'Pill / circle']],
            ['key' => '_shadow', 'label' => 'Shadow', 'type' => 'select', 'options' => $space],
            ['key' => '_border_width', 'label' => 'Border width', 'type' => 'select', 'options' => ['none' => 'None', '1' => '1px', '2' => '2px', '4' => '4px']],
            ['key' => '_border_color', 'label' => 'Border colour', 'type' => 'color'],
            ['key' => '_anim', 'label' => 'Entrance animation', 'type' => 'select', 'options' => ['none' => 'None', 'fade' => 'Fade in', 'fade-up' => 'Fade up', 'fade-down' => 'Fade down', 'zoom' => 'Zoom in', 'slide-left' => 'Slide from right', 'slide-right' => 'Slide from left']],
            ['key' => '_hide_mobile', 'label' => 'Hide on mobile', 'type' => 'toggle'],
            ['key' => '_hide_tablet', 'label' => 'Hide on tablet', 'type' => 'toggle'],
            ['key' => '_hide_desktop', 'label' => 'Hide on desktop', 'type' => 'toggle'],
        ];
    }

    protected static function align(): array
    {
        return ['left' => 'Left', 'center' => 'Center', 'right' => 'Right'];
    }

    protected static function cols(): array
    {
        return ['2' => '2', '3' => '3', '4' => '4'];
    }

    /**
     * @return array<string, array<int, array<string, mixed>>>
     */
    protected static function all(): array
    {
        return [
            'hero' => [
                ['key' => 'eyebrow', 'label' => 'Eyebrow', 'type' => 'text'],
                ['key' => 'heading', 'label' => 'Heading', 'type' => 'text'],
                ['key' => 'copy', 'label' => 'Body', 'type' => 'textarea'],
                ['key' => 'primary_label', 'label' => 'Primary button label', 'type' => 'text'],
                ['key' => 'primary_url', 'label' => 'Primary button URL', 'type' => 'text'],
                ['key' => 'secondary_label', 'label' => 'Secondary button label', 'type' => 'text'],
                ['key' => 'secondary_url', 'label' => 'Secondary button URL', 'type' => 'text'],
                ['key' => 'surface', 'label' => 'Surface', 'type' => 'select', 'options' => ['soft' => 'Soft', 'contrast' => 'Contrast (dark)', 'minimal' => 'Minimal']],
                ['key' => 'align', 'label' => 'Alignment', 'type' => 'select', 'options' => self::align()],
            ],
            'heading' => [
                ['key' => 'text', 'label' => 'Text', 'type' => 'text'],
                ['key' => 'level', 'label' => 'Level', 'type' => 'select', 'options' => ['h1' => 'H1', 'h2' => 'H2', 'h3' => 'H3', 'h4' => 'H4']],
                ['key' => 'align', 'label' => 'Alignment', 'type' => 'select', 'options' => self::align()],
                ['key' => 'color', 'label' => 'Colour', 'type' => 'color'],
            ],
            'paragraph' => [
                ['key' => 'content', 'label' => 'Text (HTML allowed)', 'type' => 'richtext'],
                ['key' => 'width', 'label' => 'Width', 'type' => 'select', 'options' => ['content' => 'Content', 'wide' => 'Wide', 'full' => 'Full width']],
            ],
            'image' => [
                ['key' => 'image', 'label' => 'Image', 'type' => 'image'],
                ['key' => 'alt', 'label' => 'Alt text', 'type' => 'text'],
                ['key' => 'caption', 'label' => 'Caption', 'type' => 'text'],
                ['key' => 'width', 'label' => 'Width', 'type' => 'select', 'options' => ['content' => 'Content', 'wide' => 'Wide', 'full' => 'Full width']],
                ['key' => 'rounded', 'label' => 'Rounded corners', 'type' => 'toggle'],
            ],
            'media_text' => [
                ['key' => 'image', 'label' => 'Image', 'type' => 'image'],
                ['key' => 'image_side', 'label' => 'Image side', 'type' => 'select', 'options' => ['left' => 'Image left', 'right' => 'Image right']],
                ['key' => 'heading', 'label' => 'Heading', 'type' => 'text'],
                ['key' => 'body', 'label' => 'Body (HTML allowed)', 'type' => 'richtext'],
                ['key' => 'button_label', 'label' => 'Button label', 'type' => 'text'],
                ['key' => 'button_url', 'label' => 'Button URL', 'type' => 'text'],
            ],
            'button' => [
                ['key' => 'label', 'label' => 'Label', 'type' => 'text'],
                ['key' => 'url', 'label' => 'URL', 'type' => 'text'],
                ['key' => 'style', 'label' => 'Style', 'type' => 'select', 'options' => ['primary' => 'Primary', 'secondary' => 'Secondary', 'outline' => 'Outline']],
                ['key' => 'align', 'label' => 'Alignment', 'type' => 'select', 'options' => self::align()],
                ['key' => 'new_tab', 'label' => 'Open in new tab', 'type' => 'toggle'],
            ],
            'feature_grid' => [
                ['key' => 'eyebrow', 'label' => 'Eyebrow', 'type' => 'text'],
                ['key' => 'heading', 'label' => 'Heading', 'type' => 'text'],
                ['key' => 'intro', 'label' => 'Intro', 'type' => 'textarea'],
                ['key' => 'columns', 'label' => 'Columns', 'type' => 'select', 'options' => self::cols()],
                ['key' => 'items', 'label' => 'Items', 'type' => 'repeater', 'fields' => [
                    ['key' => 'emoji', 'label' => 'Icon / emoji', 'type' => 'text'],
                    ['key' => 'title', 'label' => 'Title', 'type' => 'text'],
                    ['key' => 'description', 'label' => 'Description', 'type' => 'textarea'],
                ]],
            ],
            'stats' => [
                ['key' => 'heading', 'label' => 'Heading', 'type' => 'text'],
                ['key' => 'columns', 'label' => 'Columns', 'type' => 'select', 'options' => self::cols()],
                ['key' => 'items', 'label' => 'Stats', 'type' => 'repeater', 'fields' => [
                    ['key' => 'value', 'label' => 'Value', 'type' => 'text'],
                    ['key' => 'label', 'label' => 'Label', 'type' => 'text'],
                ]],
            ],
            'accordion' => [
                ['key' => 'heading', 'label' => 'Heading', 'type' => 'text'],
                ['key' => 'items', 'label' => 'Questions', 'type' => 'repeater', 'fields' => [
                    ['key' => 'question', 'label' => 'Question', 'type' => 'text'],
                    ['key' => 'answer', 'label' => 'Answer (HTML allowed)', 'type' => 'richtext'],
                ]],
            ],
            'testimonial' => [
                ['key' => 'quote', 'label' => 'Quote', 'type' => 'textarea'],
                ['key' => 'author', 'label' => 'Author', 'type' => 'text'],
                ['key' => 'role', 'label' => 'Role', 'type' => 'text'],
                ['key' => 'avatar', 'label' => 'Avatar', 'type' => 'image'],
            ],
            'call_to_action' => [
                ['key' => 'eyebrow', 'label' => 'Eyebrow', 'type' => 'text'],
                ['key' => 'heading', 'label' => 'Heading', 'type' => 'text'],
                ['key' => 'copy', 'label' => 'Body', 'type' => 'textarea'],
                ['key' => 'button_label', 'label' => 'Button label', 'type' => 'text'],
                ['key' => 'button_url', 'label' => 'Button URL', 'type' => 'text'],
                ['key' => 'theme', 'label' => 'Theme', 'type' => 'select', 'options' => ['amber' => 'Amber', 'stone' => 'Dark']],
            ],
            'pricing_table' => [
                ['key' => 'eyebrow', 'label' => 'Eyebrow', 'type' => 'text'],
                ['key' => 'heading', 'label' => 'Heading', 'type' => 'text'],
                ['key' => 'intro', 'label' => 'Intro', 'type' => 'textarea'],
                ['key' => 'columns', 'label' => 'Columns', 'type' => 'select', 'options' => self::cols()],
                ['key' => 'plans', 'label' => 'Plans', 'type' => 'repeater', 'fields' => [
                    ['key' => 'name', 'label' => 'Name', 'type' => 'text'],
                    ['key' => 'price', 'label' => 'Price', 'type' => 'text'],
                    ['key' => 'period', 'label' => 'Period', 'type' => 'text'],
                    ['key' => 'description', 'label' => 'Description', 'type' => 'textarea'],
                    ['key' => 'features', 'label' => 'Features (one per line)', 'type' => 'textarea'],
                    ['key' => 'button_label', 'label' => 'Button label', 'type' => 'text'],
                    ['key' => 'button_url', 'label' => 'Button URL', 'type' => 'text'],
                    ['key' => 'featured', 'label' => 'Highlight', 'type' => 'toggle'],
                ]],
            ],
            'logo_cloud' => [
                ['key' => 'heading', 'label' => 'Heading', 'type' => 'text'],
                ['key' => 'logos', 'label' => 'Logos', 'type' => 'image', 'multiple' => true],
                ['key' => 'grayscale', 'label' => 'Greyscale', 'type' => 'toggle'],
            ],
            'tabs' => [
                ['key' => 'items', 'label' => 'Tabs', 'type' => 'repeater', 'fields' => [
                    ['key' => 'label', 'label' => 'Label', 'type' => 'text'],
                    ['key' => 'content', 'label' => 'Content (HTML allowed)', 'type' => 'richtext'],
                ]],
            ],
            'contact_form' => [
                ['key' => 'heading', 'label' => 'Heading', 'type' => 'text'],
                ['key' => 'intro', 'label' => 'Intro', 'type' => 'textarea'],
                ['key' => 'button_label', 'label' => 'Button label', 'type' => 'text'],
                ['key' => 'show_subject', 'label' => 'Subject field', 'type' => 'toggle'],
                ['key' => 'show_phone', 'label' => 'Phone field', 'type' => 'toggle'],
                ['key' => 'success_message', 'label' => 'Success message', 'type' => 'textarea'],
                ['key' => 'send_email', 'label' => 'Email me submissions', 'type' => 'toggle'],
                ['key' => 'to_email', 'label' => 'Send to', 'type' => 'text'],
                ['key' => 'redirect_url', 'label' => 'Redirect after submit', 'type' => 'text'],
            ],
            'icon_box' => [
                ['key' => 'icon', 'label' => 'Icon / emoji', 'type' => 'text'],
                ['key' => 'align', 'label' => 'Alignment', 'type' => 'select', 'options' => self::align()],
                ['key' => 'title', 'label' => 'Title', 'type' => 'text'],
                ['key' => 'text', 'label' => 'Text', 'type' => 'textarea'],
                ['key' => 'link_label', 'label' => 'Link label', 'type' => 'text'],
                ['key' => 'link_url', 'label' => 'Link URL', 'type' => 'text'],
            ],
            'counter' => [
                ['key' => 'heading', 'label' => 'Heading', 'type' => 'text'],
                ['key' => 'columns', 'label' => 'Columns', 'type' => 'select', 'options' => self::cols()],
                ['key' => 'items', 'label' => 'Counters', 'type' => 'repeater', 'fields' => [
                    ['key' => 'value', 'label' => 'Number', 'type' => 'number'],
                    ['key' => 'prefix', 'label' => 'Prefix', 'type' => 'text'],
                    ['key' => 'suffix', 'label' => 'Suffix', 'type' => 'text'],
                    ['key' => 'label', 'label' => 'Label', 'type' => 'text'],
                ]],
            ],
            'progress_bars' => [
                ['key' => 'heading', 'label' => 'Heading', 'type' => 'text'],
                ['key' => 'items', 'label' => 'Bars', 'type' => 'repeater', 'fields' => [
                    ['key' => 'label', 'label' => 'Label', 'type' => 'text'],
                    ['key' => 'percent', 'label' => 'Percent', 'type' => 'number'],
                    ['key' => 'color', 'label' => 'Colour', 'type' => 'color'],
                ]],
            ],
            'star_rating' => [
                ['key' => 'rating', 'label' => 'Rating', 'type' => 'select', 'options' => ['1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5']],
                ['key' => 'align', 'label' => 'Alignment', 'type' => 'select', 'options' => self::align()],
                ['key' => 'label', 'label' => 'Label', 'type' => 'text'],
            ],
            'social_icons' => [
                ['key' => 'align', 'label' => 'Alignment', 'type' => 'select', 'options' => self::align()],
                ['key' => 'style', 'label' => 'Style', 'type' => 'select', 'options' => ['solid' => 'Solid', 'outline' => 'Outline']],
                ['key' => 'links', 'label' => 'Links', 'type' => 'repeater', 'fields' => [
                    ['key' => 'network', 'label' => 'Network', 'type' => 'select', 'options' => ['facebook' => 'Facebook', 'x' => 'X / Twitter', 'instagram' => 'Instagram', 'linkedin' => 'LinkedIn', 'youtube' => 'YouTube', 'github' => 'GitHub', 'tiktok' => 'TikTok', 'email' => 'Email', 'website' => 'Website']],
                    ['key' => 'url', 'label' => 'URL', 'type' => 'text'],
                ]],
            ],
            'posts_grid' => [
                ['key' => 'heading', 'label' => 'Heading', 'type' => 'text'],
                ['key' => 'intro', 'label' => 'Intro', 'type' => 'textarea'],
                ['key' => 'count', 'label' => 'How many', 'type' => 'select', 'options' => ['3' => '3', '6' => '6', '9' => '9']],
                ['key' => 'columns', 'label' => 'Columns', 'type' => 'select', 'options' => self::cols()],
            ],
            'carousel' => [
                ['key' => 'images', 'label' => 'Images', 'type' => 'image', 'multiple' => true],
                ['key' => 'autoplay', 'label' => 'Autoplay', 'type' => 'toggle'],
                ['key' => 'interval', 'label' => 'Autoplay speed', 'type' => 'select', 'options' => ['3000' => '3 seconds', '5000' => '5 seconds', '8000' => '8 seconds']],
                ['key' => 'ratio', 'label' => 'Aspect ratio', 'type' => 'select', 'options' => ['video' => '16 : 9', 'wide' => '21 : 9', 'square' => '1 : 1']],
            ],
            'countdown' => [
                ['key' => 'heading', 'label' => 'Heading', 'type' => 'text'],
                ['key' => 'until', 'label' => 'Counts down to', 'type' => 'datetime'],
                ['key' => 'expired_text', 'label' => 'Message when finished', 'type' => 'text'],
            ],
            'map' => [
                ['key' => 'query', 'label' => 'Address or place', 'type' => 'text'],
                ['key' => 'height', 'label' => 'Height', 'type' => 'select', 'options' => ['sm' => 'Short', 'md' => 'Medium', 'lg' => 'Tall']],
                ['key' => 'zoom', 'label' => 'Zoom', 'type' => 'select', 'options' => ['10' => 'City', '13' => 'District', '16' => 'Street']],
            ],
            'gallery' => [
                ['key' => 'images', 'label' => 'Images', 'type' => 'image', 'multiple' => true],
                ['key' => 'columns', 'label' => 'Columns', 'type' => 'select', 'options' => self::cols()],
            ],
            'video' => [
                ['key' => 'url', 'label' => 'YouTube or Vimeo URL', 'type' => 'text'],
                ['key' => 'width', 'label' => 'Width', 'type' => 'select', 'options' => ['content' => 'Content', 'wide' => 'Wide', 'full' => 'Full width']],
            ],
            'spacer' => [
                ['key' => 'height', 'label' => 'Height', 'type' => 'select', 'options' => ['sm' => 'Small', 'md' => 'Medium', 'lg' => 'Large', 'xl' => 'Extra large']],
            ],
            'columns' => [
                ['key' => 'gap', 'label' => 'Gap', 'type' => 'select', 'options' => ['sm' => 'Small', 'md' => 'Medium', 'lg' => 'Large']],
            ],
            'container' => [
                ['key' => 'direction', 'label' => 'Layout', 'type' => 'select', 'options' => ['column' => 'Stacked', 'row' => 'Side by side']],
                ['key' => 'gap', 'label' => 'Gap', 'type' => 'select', 'options' => ['sm' => 'Small', 'md' => 'Medium', 'lg' => 'Large']],
                ['key' => 'align', 'label' => 'Align items', 'type' => 'select', 'options' => ['start' => 'Start', 'center' => 'Center', 'end' => 'End', 'stretch' => 'Stretch']],
            ],
            'divider' => [],
        ];
    }
}
