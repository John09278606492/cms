<?php

namespace App\Filament\Resources\Pages\Schemas;

use App\Enums\ContentStatus;
use App\Filament\Forms\Components\VisualPageBuilder;
use Filament\Facades\Filament;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Slimani\MediaManager\Form\MediaPicker;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Page content')
                    ->schema([
                        TextInput::make('title')
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
                        Textarea::make('excerpt')
                            ->rows(4)
                            ->columnSpanFull(),
                        VisualPageBuilder::make('content')
                            ->label('Page builder')
                            ->helperText('Build the page with rows, columns, and reusable widgets.'),
                    ])
                    ->columns(2),
                Section::make('Structure')
                    ->schema([
                        Select::make('parent_id')
                            ->relationship('parent', 'title')
                            ->searchable()
                            ->preload()
                            ->label('Parent page'),
                        TextInput::make('sort_order')
                            ->numeric()
                            ->default(0)
                            ->required(),
                        Toggle::make('is_homepage')
                            ->label('Use as homepage')
                            ->helperText('This page becomes the main homepage for the site. On a brand-new site, the first page usually should be your homepage.')
                            ->default(fn (): bool => ! (Filament::getTenant()?->pages()->where('is_homepage', true)->exists() ?? false))
                            ->inline(false),
                        Toggle::make('show_in_menu')
                            ->label('Show in site navigation')
                            ->helperText('Automatically keep this page in the site navigation after it is published.')
                            ->inline(false)
                            ->default(true),
                    ])
                    ->columns(2),
                Section::make('Publishing')
                    ->schema([
                        Select::make('status')
                            ->options(ContentStatus::options())
                            ->required()
                            ->default(ContentStatus::Draft->value),
                        DateTimePicker::make('published_at')
                            ->seconds(false)
                            ->required(fn (Get $get): bool => $get('status') === ContentStatus::Scheduled->value),
                        Select::make('user_id')
                            ->relationship('author', 'name')
                            ->label('Author')
                            ->searchable()
                            ->preload(),
                        MediaPicker::make('featured_image_id')
                            ->relationship('featuredImage')
                            ->label('Featured image')
                            ->helperText('Upload a new image or reuse one from this site\'s asset library.')
                            ->conversion('')
                            ->directory(fn (): string => 'Pages/Featured Images')
                            ->image()
                            ->imageEditor()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('SEO')
                    ->schema([
                        TextInput::make('meta_title')
                            ->maxLength(255),
                        Textarea::make('meta_description')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
