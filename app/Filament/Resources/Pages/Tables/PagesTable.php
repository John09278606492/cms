<?php

namespace App\Filament\Resources\Pages\Tables;

use App\Enums\ContentStatus;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Slimani\MediaManager\Tables\Columns\MediaColumn;

class PagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                MediaColumn::make('featuredImage')
                    ->conversion('thumb')
                    ->label('Image'),
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record): ?string => $record->slug),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (ContentStatus|string $state): string => $state instanceof ContentStatus ? $state->label() : ContentStatus::from($state)->label())
                    ->color(fn (ContentStatus|string $state): string => $state instanceof ContentStatus ? $state->color() : ContentStatus::from($state)->color()),
                IconColumn::make('is_homepage')
                    ->boolean()
                    ->label('Home'),
                IconColumn::make('show_in_menu')
                    ->boolean()
                    ->label('Auto nav'),
                TextColumn::make('parent.title')
                    ->label('Parent')
                    ->placeholder('Top level'),
                TextColumn::make('updated_at')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(ContentStatus::options()),
                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('preview')
                    ->label('Preview')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record): string => $record->previewUrl(), shouldOpenInNewTab: true)
                    ->visible(fn ($record): bool => ! $record->trashed()),
                EditAction::make(),
                RestoreAction::make(),
                ForceDeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order');
    }
}
