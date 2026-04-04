<?php

namespace App\Mason\Bricks;

use Awcodes\Mason\Brick;
use Filament\Actions\Action;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;

class RichText extends Brick
{
    public static function getId(): string
    {
        return 'rich-text';
    }

    public static function getLabel(): string
    {
        return 'Rich Text';
    }

    public static function getIcon(): string|null
    {
        return 'heroicon-o-pencil-square';
    }

    public static function toHtml(array $config, ?array $data = null): ?string
    {
        return view('mason.bricks.rich-text', [
            'content' => $config['content'] ?? null,
            'heading' => $config['heading'] ?? null,
            'width' => $config['width'] ?? 'content',
        ])->render();
    }

    public static function configureBrickAction(Action $action): Action
    {
        return $action
            ->slideOver()
            ->schema([
                TextInput::make('heading')
                    ->maxLength(120),
                ToggleButtons::make('width')
                    ->options([
                        'content' => 'Content',
                        'wide' => 'Wide',
                    ])
                    ->default('content')
                    ->grouped(),
                RichEditor::make('content')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }
}
