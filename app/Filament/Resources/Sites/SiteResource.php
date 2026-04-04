<?php

namespace App\Filament\Resources\Sites;

use App\Filament\Resources\Sites\Pages\CreateSite;
use App\Filament\Resources\Sites\Pages\EditSite;
use App\Filament\Resources\Sites\Pages\ListSites;
use App\Models\Site;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SiteResource extends Resource
{
    protected static ?string $model = Site::class;

    protected static bool $isScopedToTenant = false;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-globe-alt';

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|\UnitEnum|null $navigationGroup = 'Platform';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Site')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Select::make('owner_id')
                            ->relationship('owner', 'name')
                            ->label('Owner')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Toggle::make('is_active')
                            ->inline(false)
                            ->default(true),
                        Textarea::make('description')
                            ->rows(4)
                            ->columnSpanFull(),
                        TextInput::make('subdomain')
                            ->maxLength(255)
                            ->alphaDash()
                            ->unique(ignoreRecord: true),
                        TextInput::make('domain')
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->withCount('users'))
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Site $record): ?string => $record->slug),
                TextColumn::make('owner.name')
                    ->label('Owner')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Unassigned'),
                TextColumn::make('users_count')
                    ->label('Members')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('domain')
                    ->placeholder('Path-based site')
                    ->toggleable(),
                TextColumn::make('subdomain')
                    ->placeholder('Not set')
                    ->toggleable(),
                IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
                TextColumn::make('updated_at')
                    ->since()
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('visitSite')
                    ->label('Visit')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (Site $record): string => route('sites.home', $record), shouldOpenInNewTab: true),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSites::route('/'),
            'create' => CreateSite::route('/create'),
            'edit' => EditSite::route('/{record}/edit'),
        ];
    }
}
