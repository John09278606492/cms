<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $this->upgradeTable('pages');
        $this->upgradeTable('posts');
    }

    public function down(): void
    {
        //
    }

    protected function upgradeTable(string $table): void
    {
        DB::table($table)
            ->select(['id', 'content'])
            ->whereNotNull('content')
            ->orderBy('id')
            ->lazyById()
            ->each(function (object $record) use ($table): void {
                $blocks = $this->normalizeContent($record->content);

                if ($blocks === null) {
                    return;
                }

                DB::table($table)
                    ->where('id', $record->id)
                    ->update([
                        'content' => json_encode($blocks, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                    ]);
            });
    }

    /**
     * @return array<int, array<string, mixed>>|null
     */
    protected function normalizeContent(?string $content): ?array
    {
        if (! filled($content)) {
            return null;
        }

        $decoded = json_decode($content, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            if (is_array($decoded) && array_key_exists('content', $decoded) && is_array($decoded['content']) && $this->looksLikeBlocks($decoded['content'])) {
                return $decoded['content'];
            }

            if (is_array($decoded) && $this->looksLikeBlocks($decoded)) {
                return $decoded;
            }

            return null;
        }

        return [[
            'type' => 'masonBrick',
            'attrs' => [
                'id' => 'rich-text',
                'config' => [
                    'content' => $this->normalizeLegacyHtml($content),
                    'width' => 'content',
                ],
            ],
        ]];
    }

    /**
     * @param  array<int, mixed>  $blocks
     */
    protected function looksLikeBlocks(array $blocks): bool
    {
        return array_is_list($blocks)
            && collect($blocks)->every(
                fn (mixed $block): bool => is_array($block)
                    && ($block['type'] ?? null) === 'masonBrick'
                    && is_array($block['attrs'] ?? null),
            );
    }

    protected function normalizeLegacyHtml(string $content): string
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
};
