<?php

namespace App\Filament\Resources\ContactMessages\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ContactMessageInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()
                ->columns(2)
                ->schema([
                    TextEntry::make('name'),
                    TextEntry::make('email')->copyable(),
                    TextEntry::make('phone')->placeholder('—')->copyable(),
                    TextEntry::make('subject')->placeholder('—'),
                    TextEntry::make('created_at')->label('Received')->dateTime(),
                    TextEntry::make('message')
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
