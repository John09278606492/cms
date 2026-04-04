<?php

namespace App\Support;

use Illuminate\Support\Str;

class PageBuilderContent
{
    /**
     * Normalize a legacy Mason payload or Layup payload into Layup's
     * canonical page-builder structure.
     *
     * @return array<string, mixed>|null
     */
    public static function normalize(mixed $content): ?array
    {
        if (blank($content)) {
            return null;
        }

        if (is_string($content)) {
            $decoded = json_decode($content, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                return static::normalize($decoded);
            }

            return static::fromLegacyHtml($content);
        }

        if (! is_array($content)) {
            return null;
        }

        if (array_key_exists('content', $content) && is_array($content['content'])) {
            $content = $content['content'];
        }

        if (static::looksLikeMasonBlocks($content)) {
            $normalized = static::fromMasonBlocks($content);

            return static::hasRenderableNodes($normalized) ? $normalized : null;
        }

        if (static::looksLikeLayupContent($content)) {
            $normalized = static::normalizeLayupContent($content);

            return static::hasRenderableNodes($normalized) ? $normalized : null;
        }

        if (static::looksLikeLayupRows($content)) {
            $normalized = [
                'rows' => static::normalizeRows($content),
            ];

            return static::hasRenderableNodes($normalized) ? $normalized : null;
        }

        return null;
    }

    /**
     * Normalize and encode content for storage.
     */
    public static function encode(mixed $content): ?string
    {
        $normalized = static::normalize($content);

        if ($normalized === null) {
            return null;
        }

        return json_encode($normalized, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * @return array<string, mixed>
     */
    protected static function fromMasonBlocks(array $blocks): array
    {
        $rows = [];

        foreach ($blocks as $block) {
            if (! is_array($block)) {
                continue;
            }

            $row = static::masonBlockToRow($block);

            if ($row !== null) {
                $rows[] = $row;
            }
        }

        return [
            'rows' => $rows,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    protected static function masonBlockToRow(array $block): ?array
    {
        if (($block['type'] ?? null) !== 'masonBrick') {
            return null;
        }

        $attrs = $block['attrs'] ?? [];
        $type = $attrs['id'] ?? null;
        $config = is_array($attrs['config'] ?? null) ? $attrs['config'] : [];

        if (! is_string($type) || $type === '') {
            return null;
        }

        return [
            'id' => static::newId('row'),
            'settings' => [],
            'columns' => [
                [
                    'id' => static::newId('col'),
                    'span' => 12,
                    'settings' => [],
                    'widgets' => [
                        [
                            'id' => static::newId('widget'),
                            'type' => $type,
                            'data' => static::normalizeWidgetData($type, $config),
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected static function fromLegacyHtml(string $content): array
    {
        return [
            'rows' => [
                [
                    'id' => static::newId('row'),
                    'settings' => [],
                    'columns' => [
                        [
                            'id' => static::newId('col'),
                            'span' => 12,
                            'settings' => [],
                            'widgets' => [
                                [
                                    'id' => static::newId('widget'),
                                    'type' => 'rich-text',
                                    'data' => [
                                        'heading' => null,
                                        'content' => static::normalizeLegacyHtml($content),
                                        'width' => 'content',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected static function normalizeLayupContent(array $content): array
    {
        if (array_key_exists('rows', $content) && is_array($content['rows'])) {
            $content['rows'] = static::normalizeRows($content['rows']);
        }

        if (array_key_exists('sections', $content) && is_array($content['sections'])) {
            $content['sections'] = array_values(array_map(
                fn (mixed $section): array => static::normalizeSection(is_array($section) ? $section : []),
                $content['sections'],
            ));
        }

        return $content;
    }

    /**
     * @return array<string, mixed>
     */
    protected static function normalizeSection(array $section): array
    {
        if (array_key_exists('rows', $section) && is_array($section['rows'])) {
            $section['rows'] = static::normalizeRows($section['rows']);
        }

        $section['settings'] = is_array($section['settings'] ?? null) ? $section['settings'] : [];

        return $section;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected static function normalizeRows(array $rows): array
    {
        return array_values(array_map(
            fn (mixed $row): array => static::normalizeRow(is_array($row) ? $row : []),
            $rows,
        ));
    }

    /**
     * @return array<string, mixed>
     */
    protected static function normalizeRow(array $row): array
    {
        $row['id'] = is_string($row['id'] ?? null) && $row['id'] !== ''
            ? $row['id']
            : static::newId('row');
        $row['settings'] = is_array($row['settings'] ?? null) ? $row['settings'] : [];
        $row['columns'] = array_values(array_map(
            fn (mixed $column): array => static::normalizeColumn(is_array($column) ? $column : []),
            is_array($row['columns'] ?? null) ? $row['columns'] : [],
        ));

        return $row;
    }

    /**
     * @return array<string, mixed>
     */
    protected static function normalizeColumn(array $column): array
    {
        $column['id'] = is_string($column['id'] ?? null) && $column['id'] !== ''
            ? $column['id']
            : static::newId('col');
        $column['span'] = $column['span'] ?? 12;
        $column['settings'] = is_array($column['settings'] ?? null) ? $column['settings'] : [];
        $column['widgets'] = array_values(array_map(
            fn (mixed $widget): array => static::normalizeWidget(is_array($widget) ? $widget : []),
            is_array($column['widgets'] ?? null) ? $column['widgets'] : [],
        ));

        return $column;
    }

    /**
     * @return array<string, mixed>
     */
    protected static function normalizeWidget(array $widget): array
    {
        $widget['id'] = is_string($widget['id'] ?? null) && $widget['id'] !== ''
            ? $widget['id']
            : static::newId('widget');
        $widget['type'] = static::normalizeWidgetType(is_string($widget['type'] ?? null) && $widget['type'] !== ''
            ? $widget['type']
            : 'text');
        $widget['data'] = static::normalizeWidgetData(
            $widget['type'],
            is_array($widget['data'] ?? null) ? $widget['data'] : [],
        );

        return $widget;
    }

    protected static function normalizeWidgetType(string $type): string
    {
        return match ($type) {
            'cta' => 'call-to-action',
            'text' => 'rich-text',
            default => $type,
        };
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected static function normalizeWidgetData(string $type, array $data): array
    {
        $normalized = match ($type) {
            'hero' => static::normalizeHeroData($data),
            'rich-text' => static::normalizeRichTextData($data),
            'image' => static::normalizeImageData($data),
            'feature-grid' => static::normalizeFeatureGridData($data),
            'call-to-action' => static::normalizeCallToActionData($data),
            default => $data,
        };

        return array_merge($data, $normalized);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected static function normalizeHeroData(array $data): array
    {
        return [
            'eyebrow' => $data['eyebrow'] ?? $data['subheading'] ?? null,
            'heading' => $data['heading'] ?? null,
            'copy' => $data['copy'] ?? $data['description'] ?? null,
            'primary_label' => $data['primary_label'] ?? $data['primary_button_text'] ?? null,
            'primary_url' => $data['primary_url'] ?? $data['primary_button_url'] ?? null,
            'secondary_label' => $data['secondary_label'] ?? $data['secondary_button_text'] ?? null,
            'secondary_url' => $data['secondary_url'] ?? $data['secondary_button_url'] ?? null,
            'alignment' => $data['alignment'] ?? 'start',
            'surface' => $data['surface'] ?? 'soft',
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected static function normalizeRichTextData(array $data): array
    {
        return [
            'heading' => $data['heading'] ?? null,
            'content' => $data['content'] ?? $data['text'] ?? '',
            'width' => $data['width'] ?? 'content',
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected static function normalizeImageData(array $data): array
    {
        return [
            'image' => $data['image'] ?? $data['src'] ?? null,
            'alt' => $data['alt'] ?? null,
            'caption' => $data['caption'] ?? null,
            'link_url' => $data['link_url'] ?? null,
            'link_new_tab' => $data['link_new_tab'] ?? false,
            'hover_effect' => $data['hover_effect'] ?? null,
            'width' => $data['width'] ?? 'content',
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected static function normalizeFeatureGridData(array $data): array
    {
        $items = $data['items'] ?? $data['features'] ?? [];

        return [
            'eyebrow' => $data['eyebrow'] ?? null,
            'heading' => $data['heading'] ?? null,
            'intro' => $data['intro'] ?? null,
            'columns' => $data['columns'] ?? '3',
            'items' => is_array($items) ? static::normalizeFeatureGridItems($items) : [],
        ];
    }

    /**
     * @param  array<int, mixed>  $items
     * @return array<int, array<string, mixed>>
     */
    protected static function normalizeFeatureGridItems(array $items): array
    {
        return array_values(array_map(function (mixed $item): array {
            $item = is_array($item) ? $item : [];

            return array_merge($item, [
                'emoji' => $item['emoji'] ?? null,
                'title' => $item['title'] ?? '',
                'description' => $item['description'] ?? '',
            ]);
        }, $items));
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected static function normalizeCallToActionData(array $data): array
    {
        return [
            'eyebrow' => $data['eyebrow'] ?? null,
            'heading' => $data['heading'] ?? $data['title'] ?? null,
            'copy' => $data['copy'] ?? $data['content'] ?? null,
            'button_label' => $data['button_label'] ?? $data['button_text'] ?? null,
            'button_url' => $data['button_url'] ?? null,
            'theme' => $data['theme'] ?? static::normalizeCtaTheme($data['button_style'] ?? null),
        ];
    }

    protected static function normalizeCtaTheme(mixed $buttonStyle): string
    {
        return match ($buttonStyle) {
            'secondary', 'outline' => 'stone',
            default => 'amber',
        };
    }

    protected static function looksLikeMasonBlocks(array $content): bool
    {
        return array_is_list($content)
            && collect($content)->every(
                fn (mixed $block): bool => is_array($block)
                    && ($block['type'] ?? null) === 'masonBrick'
                    && is_array($block['attrs'] ?? null),
            );
    }

    protected static function looksLikeLayupContent(array $content): bool
    {
        return array_key_exists('rows', $content) || array_key_exists('sections', $content);
    }

    protected static function looksLikeLayupRows(array $content): bool
    {
        return array_is_list($content)
            && collect($content)->every(
                fn (mixed $row): bool => is_array($row) && array_key_exists('columns', $row),
            );
    }

    protected static function hasRenderableNodes(array $content): bool
    {
        if (array_key_exists('rows', $content) && is_array($content['rows']) && $content['rows'] !== []) {
            return true;
        }

        if (array_key_exists('sections', $content) && is_array($content['sections'])) {
            return collect($content['sections'])->contains(
                fn (mixed $section): bool => is_array($section)
                    && is_array($section['rows'] ?? null)
                    && $section['rows'] !== [],
            );
        }

        return false;
    }

    protected static function normalizeLegacyHtml(string $content): string
    {
        $trimmed = trim($content);

        if ($trimmed === '') {
            return '';
        }

        if ($trimmed === strip_tags($trimmed)) {
            return '<p>' . e($trimmed) . '</p>';
        }

        return $trimmed;
    }

    protected static function newId(string $prefix): string
    {
        return $prefix . '_' . Str::uuid()->toString();
    }
}
