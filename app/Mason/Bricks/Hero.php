<?php

namespace App\Mason\Bricks;

use Awcodes\Mason\Brick;
use Filament\Actions\Action;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\ToggleButtons;

class Hero extends Brick
{
    public static function getId(): string
    {
        return 'hero';
    }

    public static function getLabel(): string
    {
        return 'Hero Banner';
    }

    public static function getIcon(): string|null
    {
        return 'heroicon-o-sparkles';
    }

    public static function toHtml(array $config, ?array $data = null): ?string
    {
        return view('mason.bricks.hero', [
            'alignment' => $config['alignment'] ?? 'start',
            'copy' => $config['copy'] ?? null,
            'eyebrow' => $config['eyebrow'] ?? null,
            'heading' => $config['heading'] ?? null,
            'primaryLabel' => $config['primary_label'] ?? null,
            'primaryUrl' => $config['primary_url'] ?? null,
            'secondaryLabel' => $config['secondary_label'] ?? null,
            'secondaryUrl' => $config['secondary_url'] ?? null,
            'surface' => $config['surface'] ?? 'soft',
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
                            ->options([
                                'start' => 'Left',
                                'center' => 'Center',
                            ])
                            ->default('start')
                            ->grouped(),
                        ToggleButtons::make('surface')
                            ->options([
                                'soft' => 'Soft',
                                'contrast' => 'Contrast',
                                'minimal' => 'Minimal',
                            ])
                            ->default('soft')
                            ->grouped(),
                    ]),
            ]);
    }
}
