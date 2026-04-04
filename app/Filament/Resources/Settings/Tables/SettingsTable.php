<?php

namespace App\Filament\Resources\Settings\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Slimani\MediaManager\Tables\Columns\MediaColumn;

class SettingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                MediaColumn::make('siteLogo')
                    ->conversion('')
                    ->label('Logo'),
                TextColumn::make('site_name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('site_email')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('posts_per_page')
                    ->label('Posts / page')
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([]);
    }
}
