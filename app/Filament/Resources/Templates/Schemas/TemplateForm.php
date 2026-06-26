<?php

namespace App\Filament\Resources\Templates\Schemas;

use App\PageBuilder\PageBuilder;
use Filament\Forms\Components\Builder as ContentBuilder;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()
                ->columns(2)
                ->schema([
                    TextInput::make('name')->required()->maxLength(120),
                    TextInput::make('description')->maxLength(255),
                ]),
            ContentBuilder::make('content')
                ->label('Template blocks')
                ->blocks(PageBuilder::blocks())
                ->blockPreviews()
                ->addActionLabel('Add a block')
                ->blockPickerColumns(2)
                ->blockPickerWidth('xl')
                ->collapsible()
                ->blockNumbers(false)
                ->columnSpanFull(),
        ]);
    }
}
