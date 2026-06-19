<?php

namespace App\Filament\Resources\Posts;

use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Filament\Resources\Posts\Pages\EditPost;
use App\Filament\Resources\Posts\Pages\ListPosts;
use App\Filament\Resources\Posts\Schemas\PostForm;
use App\Filament\Resources\Posts\Tables\PostsTable;
use App\Models\Post;
use BackedEnum;
use Closure;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-newspaper';

    protected static ?string $recordTitleAttribute = 'title';

    protected static string|\UnitEnum|null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return PostForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PostsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPosts::route('/'),
            'create' => CreatePost::route('/create'),
            'edit' => EditPost::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    /**
     * Scope record route binding to the active tenant. Posts use a slug route
     * key, and slugs are only unique per-site, so without this an URL slug that
     * exists in several sites would resolve to another tenant's post.
     */
    public static function resolveRecordRouteBinding(int|string $key, ?Closure $modifyQuery = null): ?Model
    {
        return parent::resolveRecordRouteBinding($key, function (Builder $query) use ($modifyQuery): Builder {
            if ($tenant = Filament::getTenant()) {
                $query->whereBelongsTo($tenant, 'site');
            }

            return $modifyQuery ? ($modifyQuery($query) ?? $query) : $query;
        });
    }
}
