<?php

declare(strict_types=1);

namespace App\Layup\Widgets;

use Crumbls\Layup\View\BaseWidget;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Illuminate\Contracts\View\View;

class CallToActionWidget extends BaseWidget
{
    public static function getType(): string
    {
        return 'call-to-action';
    }

    public static function getLabel(): string
    {
        return 'Call to Action';
    }

    public static function getIcon(): string
    {
        return 'heroicon-o-megaphone';
    }

    public static function getCategory(): string
    {
        return 'interactive';
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
            TextInput::make('button_label')
                ->label('Button label')
                ->maxLength(40),
            TextInput::make('button_url')
                ->label('Button URL')
                ->placeholder('/contact or https://...')
                ->maxLength(255),
            Grid::make(2)
                ->schema([
                    Select::make('theme')
                        ->label('Theme')
                        ->options([
                            'amber' => 'Amber',
                            'stone' => 'Stone',
                        ])
                        ->default('amber'),
                ]),
        ];
    }

    public static function getDefaultData(): array
    {
        return [
            'eyebrow' => '',
            'heading' => '',
            'copy' => '',
            'button_label' => 'Learn more',
            'button_url' => '#',
            'theme' => 'amber',
        ];
    }

    public static function getPreview(array $data): string
    {
        $heading = trim((string) ($data['heading'] ?? $data['title'] ?? ''));

        return $heading !== '' ? 'CTA: ' . $heading : 'Call to action';
    }

    public function render(): View
    {
        $data = $this->data;

        return view('mason.bricks.call-to-action', [
            'buttonLabel' => $data['button_label'] ?? $data['button_text'] ?? null,
            'buttonUrl' => $data['button_url'] ?? null,
            'copy' => $data['copy'] ?? $data['content'] ?? null,
            'eyebrow' => $data['eyebrow'] ?? null,
            'heading' => $data['heading'] ?? $data['title'] ?? null,
            'theme' => $data['theme'] ?? 'amber',
            'styles' => is_array($data['styles'] ?? null) ? $data['styles'] : [],
        ]);
    }
}
