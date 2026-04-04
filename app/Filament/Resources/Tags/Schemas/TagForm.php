<?php

namespace App\Filament\Resources\Tags\Schemas;

use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class TagForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Tag details')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, callable $set): mixed => $set('slug', Str::slug((string) $state))),
                        TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->scopedUnique(ignoreRecord: true, modifyQueryUsing: function (Builder $query): Builder {
                                $tenant = Filament::getTenant();

                                return $tenant ? $query->whereBelongsTo($tenant, 'site') : $query;
                            }),
                        Textarea::make('description')
                            ->rows(5)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
