<?php

namespace App\Filament\Forms\Components;

use Crumbls\Layup\Forms\Components\LayupBuilder;
use Crumbls\Layup\Support\WidgetRegistry;

/**
 * Layup's page builder with a live design preview: instead of text-label
 * placeholders, each widget in the canvas is rendered to its real frontend HTML
 * (the same Blade views the public site uses), so the editor shows the actual
 * design. Milestone 1 of the inline WYSIWYG editor.
 */
class VisualPageBuilder extends LayupBuilder
{
    protected string $view = 'filament.forms.components.visual-page-builder';

    public function getViewData(): array
    {
        return array_merge(parent::getViewData(), [
            'widgetPreviews' => $this->buildWidgetPreviews(),
        ]);
    }

    /**
     * Map every widget id in the current content to its rendered HTML.
     *
     * @return array<string, string>
     */
    protected function buildWidgetPreviews(): array
    {
        $state = $this->getState();

        if (is_string($state)) {
            $state = json_decode($state, true);
        }

        $rows = is_array($state) && is_array($state['rows'] ?? null) ? $state['rows'] : [];

        $previews = [];

        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }

            foreach ($row['columns'] ?? [] as $column) {
                if (! is_array($column)) {
                    continue;
                }

                foreach ($column['widgets'] ?? [] as $widget) {
                    if (! is_array($widget)) {
                        continue;
                    }

                    $id = $widget['id'] ?? null;

                    if (! is_string($id) || $id === '') {
                        continue;
                    }

                    $html = $this->renderWidget(
                        (string) ($widget['type'] ?? ''),
                        is_array($widget['data'] ?? null) ? $widget['data'] : [],
                    );

                    if ($html !== '') {
                        $previews[$id] = $html;
                    }
                }
            }
        }

        return $previews;
    }

    /**
     * Render a single widget to the same HTML the public site would produce.
     */
    protected function renderWidget(string $type, array $data): string
    {
        $class = app(WidgetRegistry::class)->get($type);

        if ($class === null) {
            return '';
        }

        try {
            $widget = new $class($class::prepareForRender($data));
            $html = trim($widget->render()->render());

            // A freshly added widget often has empty defaults and renders with no
            // visible content. Returning '' makes the canvas fall back to a labelled
            // "click to edit" placeholder so the block stays visible and selectable.
            $hasText = trim(strip_tags($html)) !== '';
            $hasMedia = (bool) preg_match('/<(img|svg|hr|video|iframe|audio|canvas)\b/i', $html);

            return ($hasText || $hasMedia) ? $html : '';
        } catch (\Throwable $e) {
            report($e);

            return '';
        }
    }
}
