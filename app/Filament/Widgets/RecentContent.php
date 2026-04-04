<?php

namespace App\Filament\Widgets;

use App\Enums\ContentStatus;
use App\Filament\Resources\Posts\PostResource;
use App\Models\Post;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentContent extends TableWidget
{
    protected static ?string $heading = 'Recent posts';

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => Post::query()->with(['author', 'categories'])->latest('updated_at'))
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Post $record): ?string => $record->excerpt),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (ContentStatus|string $state): string => $state instanceof ContentStatus ? $state->label() : ContentStatus::from($state)->label())
                    ->color(fn (ContentStatus|string $state): string => $state instanceof ContentStatus ? $state->color() : ContentStatus::from($state)->color()),
                TextColumn::make('author.name')
                    ->label('Author')
                    ->placeholder('Unknown'),
                TextColumn::make('updated_at')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Action::make('createPost')
                    ->label('New post')
                    ->url(PostResource::getUrl('create')),
            ])
            ->recordActions([
                EditAction::make()
                    ->url(fn (Post $record): string => PostResource::getUrl('edit', ['record' => $record])),
            ]);
    }
}
