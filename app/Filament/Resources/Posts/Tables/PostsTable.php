<?php

namespace App\Filament\Resources\Posts\Tables;

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
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Slimani\MediaManager\Tables\Columns\MediaColumn;

class PostsTable
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
                    ->description(fn ($record): ?string => $record->excerpt),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (ContentStatus|string $state): string => $state instanceof ContentStatus ? $state->label() : ContentStatus::from($state)->label())
                    ->color(fn (ContentStatus|string $state): string => $state instanceof ContentStatus ? $state->color() : ContentStatus::from($state)->color()),
                IconColumn::make('is_featured')
                    ->boolean()
                    ->label('Featured'),
                TextColumn::make('author.name')
                    ->label('Author')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('categories.name')
                    ->label('Categories')
                    ->badge()
                    ->separator(', '),
                TextColumn::make('published_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Not scheduled'),
                TextColumn::make('updated_at')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(ContentStatus::options()),
                TernaryFilter::make('is_featured')
                    ->label('Featured'),
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
            ->defaultSort('updated_at', 'desc');
    }
}
