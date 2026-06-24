<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Pure helpers for the reusable page-template feature. Kept free of Livewire /
 * Filament so the block-merging behaviour can be unit tested in isolation.
 */
class PageTemplates
{
    /**
     * Combine the editor's current blocks with a template's blocks, then re-key
     * the result with fresh UUIDs (the shape Filament's Builder uses for its
     * live state, so the merged list hydrates cleanly into the field).
     *
     * @param  array<int|string, mixed>  $existing
     * @param  array<int|string, mixed>  $templateBlocks
     * @return array<string, mixed>
     */
    public static function merge(array $existing, array $templateBlocks, bool $replace): array
    {
        $base = $replace ? [] : array_values($existing);
        $blocks = [...$base, ...array_values($templateBlocks)];

        return self::keyByUuid($blocks);
    }

    /**
     * @param  array<int|string, mixed>  $blocks
     * @return array<string, mixed>
     */
    public static function keyByUuid(array $blocks): array
    {
        $keyed = [];

        foreach (array_values($blocks) as $block) {
            $keyed[(string) Str::uuid()] = $block;
        }

        return $keyed;
    }
}
