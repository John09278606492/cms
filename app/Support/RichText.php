<?php

namespace App\Support;

use Filament\Forms\Components\RichEditor\RichContentRenderer;

/**
 * Safe rendering of RichEditor content. Filament's renderer reads `->type` on
 * the decoded document and throws on empty/blank input — which happens whenever
 * an owner leaves a text block empty. This guards that and any malformed value.
 */
class RichText
{
    public static function render(mixed $value): string
    {
        if (blank($value)) {
            return '';
        }

        try {
            return RichContentRenderer::make($value)->toHtml();
        } catch (\Throwable) {
            // Fall back to escaped plain text rather than crashing the page.
            return is_string($value) ? e($value) : '';
        }
    }
}
