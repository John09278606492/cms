<?php

declare(strict_types=1);

namespace App\Layup\Widgets;

use Crumbls\Layup\View\BaseWidget;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;

class RichTextWidget extends BaseWidget
{
    public static function getType(): string
    {
        return 'rich-text';
    }

    public static function getLabel(): string
    {
        return 'Rich Text';
    }

    public static function getIcon(): string
    {
        return 'heroicon-o-document-text';
    }

    public static function getCategory(): string
    {
        return 'content';
    }

    public static function getFormSchema(): array
    {
        return [
            TextInput::make('heading')
                ->label('Heading')
                ->maxLength(120),
            ToggleButtons::make('width')
                ->label('Width')
                ->options([
                    'content' => 'Content',
                    'wide' => 'Wide',
                ])
                ->default('content')
                ->grouped(),
            RichEditor::make('content')
                ->label('Content')
                ->required()
                ->columnSpanFull(),
        ];
    }

    public static function getDefaultData(): array
    {
        return [
            'heading' => '',
            'content' => '',
            'width' => 'content',
        ];
    }

    public static function getPreview(array $data): string
    {
        $content = trim(strip_tags((string) ($data['content'] ?? $data['text'] ?? '')));

        if ($content === '') {
            return 'Rich text';
        }

        return Str::limit($content, 60, '...');
    }

    public function render(): View
    {
        $data = $this->data;

        return view('mason.bricks.rich-text', [
            'content' => $data['content'] ?? $data['text'] ?? null,
            'heading' => $data['heading'] ?? null,
            'width' => $data['width'] ?? 'content',
            'styles' => is_array($data['styles'] ?? null) ? $data['styles'] : [],
        ]);
    }
}
