<?php

declare(strict_types=1);

namespace App\Layup\Widgets;

use Crumbls\Layup\View\BaseWidget;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\ToggleButtons;
use Illuminate\Contracts\View\View;

class FeatureGridWidget extends BaseWidget
{
    public static function getType(): string
    {
        return 'feature-grid';
    }

    public static function getLabel(): string
    {
        return 'Feature Grid';
    }

    public static function getIcon(): string
    {
        return 'heroicon-o-squares-plus';
    }

    public static function getCategory(): string
    {
        return 'content';
    }

    public static function getFormSchema(): array
    {
        return [
            TextInput::make('eyebrow')
                ->label('Eyebrow')
                ->maxLength(80),
            TextInput::make('heading')
                ->label('Heading')
                ->required()
                ->maxLength(120),
            Textarea::make('intro')
                ->label('Intro')
                ->rows(3)
                ->columnSpanFull(),
            ToggleButtons::make('columns')
                ->label('Columns')
                ->options([
                    '2' => '2 columns',
                    '3' => '3 columns',
                    '4' => '4 columns',
                ])
                ->default('3')
                ->grouped(),
            Repeater::make('items')
                ->label('Features')
                ->defaultItems(3)
                ->schema([
                    TextInput::make('emoji')
                        ->label('Emoji')
                        ->maxLength(8),
                    TextInput::make('title')
                        ->label('Title')
                        ->required()
                        ->maxLength(80),
                    TextInput::make('description')
                        ->label('Description')
                        ->maxLength(255),
                ])
                ->columnSpanFull(),
        ];
    }

    public static function getDefaultData(): array
    {
        return [
            'eyebrow' => '',
            'heading' => '',
            'intro' => '',
            'columns' => '3',
            'items' => [],
        ];
    }

    public static function getPreview(array $data): string
    {
        $count = count($data['items'] ?? $data['features'] ?? []);

        return 'Feature grid: ' . $count . ' item' . ($count === 1 ? '' : 's');
    }

    public function render(): View
    {
        $data = $this->data;

        return view('mason.bricks.feature-grid', [
            'columns' => $data['columns'] ?? '3',
            'eyebrow' => $data['eyebrow'] ?? null,
            'heading' => $data['heading'] ?? null,
            'intro' => $data['intro'] ?? null,
            'items' => $data['items'] ?? $data['features'] ?? [],
        ]);
    }
}
