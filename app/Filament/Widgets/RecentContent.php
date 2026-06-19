<?php

namespace App\Filament\Widgets;

use App\Enums\ContentStatus;
use App\Filament\Resources\Posts\PostResource;
use App\Models\Post;
use App\Models\Site;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
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
            ->query(fn (): Builder => $this->scopeToTenant(
                Post::query()->with(['author', 'categories'])->latest('updated_at'),
            ))
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
                    ->url(fn (): ?string => $this->resourceUrl('create')),
            ])
            ->recordActions([
                EditAction::make()
                    ->url(fn (Post $record): ?string => $this->resourceUrl('edit', ['record' => $record])),
            ]);
    }

    protected function resourceUrl(string $page = 'index', array $parameters = []): ?string
    {
        $tenant = Filament::getTenant();

        if (! $tenant instanceof Site) {
            return null;
        }

        return PostResource::getUrl(
            $page,
            $parameters,
            panel: Filament::getCurrentOrDefaultPanel()->getId(),
            tenant: $tenant,
        );
    }

    protected function scopeToTenant(Builder $query): Builder
    {
        $tenant = Filament::getTenant();

        if (! $tenant instanceof Site) {
            return $query;
        }

        return $query->whereBelongsTo($tenant, 'site');
    }
}
