<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Resources\Pages\Pages\CreatePage;
use App\Filament\Resources\Pages\Pages\EditPage;
use App\Filament\Resources\Pages\Pages\ListPages;
use App\Filament\Resources\Pages\Schemas\PageForm;
use App\Filament\Resources\Pages\Tables\PagesTable;
use App\Models\Page;
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

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-duplicate';

    protected static ?string $recordTitleAttribute = 'title';

    protected static string|\UnitEnum|null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 20;

    public static function form(Schema $schema): Schema
    {
        return PageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PagesTable::configure($table);
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
            'index' => ListPages::route('/'),
            'create' => CreatePage::route('/create'),
            'edit' => EditPage::route('/{record}/edit'),
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
     * Scope record route binding to the active tenant. Pages use a slug route
     * key, and slugs are only unique per-site, so without this an URL slug that
     * exists in several sites would resolve to another tenant's page.
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
