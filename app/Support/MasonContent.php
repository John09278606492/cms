<?php

namespace App\Support;

use App\Mason\Bricks\CallToAction;
use App\Mason\Bricks\FeatureGrid;
use App\Mason\Bricks\Hero;
use App\Mason\Bricks\RichText;

class MasonContent
{
    /**
     * @return array<int, array<string, mixed>>|null
     */
    public static function normalize(mixed $content): ?array
    {
        if (blank($content)) {
            return null;
        }

        if (is_array($content)) {
            if (array_key_exists('content', $content) && is_array($content['content'])) {
                $content = $content['content'];
            }

            return static::looksLikeBlocks($content) ? array_values($content) : null;
        }

        if (! is_string($content)) {
            return null;
        }

        $decoded = json_decode($content, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return static::normalize($decoded);
        }

        return [
            static::richText(static::normalizeLegacyHtml($content)),
        ];
    }

    public static function encode(mixed $content): ?string
    {
        $blocks = static::normalize($content);

        if ($blocks === null) {
            return null;
        }

        return json_encode($blocks, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }

    /**
     * @param  array<int, array<string, mixed>>  ...$blocks
     * @return array<int, array<string, mixed>>
     */
    public static function blocks(array ...$blocks): array
    {
        return array_values($blocks);
    }

    /**
     * @return array<string, mixed>
     */
    public static function hero(
        string $heading,
        ?string $copy = null,
        ?string $eyebrow = null,
        ?string $primaryLabel = null,
        ?string $primaryUrl = null,
        ?string $secondaryLabel = null,
        ?string $secondaryUrl = null,
        string $alignment = 'start',
        string $surface = 'soft',
    ): array {
        return static::brick(Hero::getId(), [
            'eyebrow' => $eyebrow,
            'heading' => $heading,
            'copy' => $copy,
            'primary_label' => $primaryLabel,
            'primary_url' => $primaryUrl,
            'secondary_label' => $secondaryLabel,
            'secondary_url' => $secondaryUrl,
            'alignment' => $alignment,
            'surface' => $surface,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public static function richText(
        string $content,
        ?string $heading = null,
        string $width = 'content',
    ): array {
        return static::brick(RichText::getId(), [
            'heading' => $heading,
            'content' => static::normalizeLegacyHtml($content),
            'width' => $width,
        ]);
    }

    /**
     * @param  array<int, array{title:string,description:string}>  $items
     * @return array<string, mixed>
     */
    public static function featureGrid(
        string $heading,
        array $items,
        ?string $intro = null,
        ?string $eyebrow = null,
        string $columns = '3',
    ): array {
        return static::brick(FeatureGrid::getId(), [
            'eyebrow' => $eyebrow,
            'heading' => $heading,
            'intro' => $intro,
            'columns' => $columns,
            'items' => $items,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public static function callToAction(
        string $heading,
        ?string $copy = null,
        ?string $buttonLabel = null,
        ?string $buttonUrl = null,
        ?string $eyebrow = null,
        string $theme = 'amber',
    ): array {
        return static::brick(CallToAction::getId(), [
            'eyebrow' => $eyebrow,
            'heading' => $heading,
            'copy' => $copy,
            'button_label' => $buttonLabel,
            'button_url' => $buttonUrl,
            'theme' => $theme,
        ]);
    }

    public static function paragraphs(string ...$paragraphs): string
    {
        return collect($paragraphs)
            ->map(fn (string $paragraph): string => '<p>' . e($paragraph) . '</p>')
            ->implode('');
    }

    /**
     * @return array<string, mixed>
     */
    protected static function brick(string $id, array $config): array
    {
        return [
            'type' => 'masonBrick',
            'attrs' => [
                'id' => $id,
                'config' => $config,
            ],
        ];
    }

    protected static function looksLikeBlocks(array $content): bool
    {
        return array_is_list($content)
            && collect($content)->every(
                fn (mixed $block): bool => is_array($block)
                    && ($block['type'] ?? null) === 'masonBrick'
                    && is_array($block['attrs'] ?? null),
            );
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
}
