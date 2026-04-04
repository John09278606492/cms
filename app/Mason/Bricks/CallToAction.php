<?php

namespace App\Mason\Bricks;

use Awcodes\Mason\Brick;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\ToggleButtons;

class CallToAction extends Brick
{
    public static function getId(): string
    {
        return 'call-to-action';
    }

    public static function getLabel(): string
    {
        return 'Call To Action';
    }

    public static function getIcon(): string|null
    {
        return 'heroicon-o-megaphone';
    }

    public static function toHtml(array $config, ?array $data = null): ?string
    {
        return view('mason.bricks.call-to-action', [
            'buttonLabel' => $config['button_label'] ?? null,
            'buttonUrl' => $config['button_url'] ?? null,
            'copy' => $config['copy'] ?? null,
            'eyebrow' => $config['eyebrow'] ?? null,
            'heading' => $config['heading'] ?? null,
            'theme' => $config['theme'] ?? 'amber',
        ])->render();
    }

    public static function configureBrickAction(Action $action): Action
    {
        return $action
            ->slideOver()
            ->schema([
                TextInput::make('eyebrow')
                    ->maxLength(80),
                TextInput::make('heading')
                    ->required()
                    ->maxLength(120),
                Textarea::make('copy')
                    ->rows(4)
                    ->columnSpanFull(),
                TextInput::make('button_label')
                    ->label('Button label')
                    ->maxLength(40),
                TextInput::make('button_url')
                    ->label('Button URL')
                    ->placeholder('/contact or https://...')
                    ->maxLength(255),
                ToggleButtons::make('theme')
                    ->options([
                        'amber' => 'Amber',
                        'stone' => 'Stone',
                    ])
                    ->default('amber')
                    ->grouped(),
            ]);
    }
}
