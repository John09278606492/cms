<?php

namespace App\Mason\Bricks;

use Awcodes\Mason\Brick;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\ToggleButtons;

class FeatureGrid extends Brick
{
    public static function getId(): string
    {
        return 'feature-grid';
    }

    public static function getLabel(): string
    {
        return 'Feature Grid';
    }

    public static function getIcon(): string|null
    {
        return 'heroicon-o-squares-2x2';
    }

    public static function toHtml(array $config, ?array $data = null): ?string
    {
        return view('mason.bricks.feature-grid', [
            'columns' => $config['columns'] ?? '3',
            'eyebrow' => $config['eyebrow'] ?? null,
            'heading' => $config['heading'] ?? null,
            'intro' => $config['intro'] ?? null,
            'items' => $config['items'] ?? [],
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
                Textarea::make('intro')
                    ->rows(3)
                    ->columnSpanFull(),
                ToggleButtons::make('columns')
                    ->options([
                        '2' => '2 columns',
                        '3' => '3 columns',
                    ])
                    ->default('3')
                    ->grouped(),
                Repeater::make('items')
                    ->label('Features')
                    ->defaultItems(3)
                    ->minItems(1)
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(80),
                        Textarea::make('description')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->addActionLabel('Add feature')
                    ->columnSpanFull(),
            ]);
    }
}
