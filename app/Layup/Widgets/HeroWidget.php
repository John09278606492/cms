<?php

declare(strict_types=1);

namespace App\Layup\Widgets;

use Crumbls\Layup\View\BaseWidget;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\ToggleButtons;
use Illuminate\Contracts\View\View;

class HeroWidget extends BaseWidget
{
    public static function getType(): string
    {
        return 'hero';
    }

    public static function getLabel(): string
    {
        return 'Hero';
    }

    public static function getIcon(): string
    {
        return 'heroicon-o-rectangle-group';
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
            Textarea::make('copy')
                ->label('Body copy')
                ->rows(4)
                ->columnSpanFull(),
            Grid::make(2)
                ->schema([
                    TextInput::make('primary_label')
                        ->label('Primary button label')
                        ->maxLength(40),
                    TextInput::make('primary_url')
                        ->label('Primary button URL')
                        ->placeholder('/contact or https://...')
                        ->maxLength(255),
                    TextInput::make('secondary_label')
                        ->label('Secondary button label')
                        ->maxLength(40),
                    TextInput::make('secondary_url')
                        ->label('Secondary button URL')
                        ->placeholder('/about or https://...')
                        ->maxLength(255),
                ]),
            Grid::make(2)
                ->schema([
                    ToggleButtons::make('alignment')
                        ->label('Content alignment')
                        ->options([
                            'start' => 'Left',
                            'center' => 'Center',
                        ])
                        ->default('start')
                        ->grouped(),
                    ToggleButtons::make('surface')
                        ->label('Surface')
                        ->options([
                            'soft' => 'Soft',
                            'contrast' => 'Contrast',
                            'minimal' => 'Minimal',
                        ])
                        ->default('soft')
                        ->grouped(),
                ]),
        ];
    }

    public static function getDefaultData(): array
    {
        return [
            'eyebrow' => '',
            'heading' => '',
            'copy' => '',
            'primary_label' => '',
            'primary_url' => '',
            'secondary_label' => '',
            'secondary_url' => '',
            'alignment' => 'start',
            'surface' => 'soft',
        ];
    }

    public static function getPreview(array $data): string
    {
        $heading = trim((string) ($data['heading'] ?? $data['title'] ?? ''));

        return $heading !== '' ? 'Hero: ' . $heading : 'Hero block';
    }

    public function render(): View
    {
        $data = $this->data;

        return view('mason.bricks.hero', [
            'alignment' => $data['alignment'] ?? 'start',
            'copy' => $data['copy'] ?? $data['description'] ?? null,
            'eyebrow' => $data['eyebrow'] ?? $data['subheading'] ?? null,
            'heading' => $data['heading'] ?? $data['title'] ?? null,
            'primaryLabel' => $data['primary_label'] ?? $data['primary_button_text'] ?? null,
            'primaryUrl' => $data['primary_url'] ?? $data['primary_button_url'] ?? null,
            'secondaryLabel' => $data['secondary_label'] ?? $data['secondary_button_text'] ?? null,
            'secondaryUrl' => $data['secondary_url'] ?? $data['secondary_button_url'] ?? null,
            'surface' => $data['surface'] ?? 'soft',
            'styles' => is_array($data['styles'] ?? null) ? $data['styles'] : [],
        ]);
    }
}
