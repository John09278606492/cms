<?php

namespace App\Filament\Resources\Posts\Schemas;

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

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Editorial')
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
                            ->label('Post builder')
                            ->helperText('Compose the article with rows, columns, and reusable widgets.'),
                        Select::make('categories')
                            ->relationship('categories', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->columnSpanFull(),
                        Select::make('tags')
                            ->relationship('tags', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->columnSpanFull(),
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
                        Toggle::make('is_featured')
                            ->inline(false),
                        MediaPicker::make('featured_image_id')
                            ->relationship('featuredImage')
                            ->label('Featured image')
                            ->helperText('Upload a new image or reuse one from this site\'s asset library.')
                            ->conversion('')
                            ->directory(fn (): string => 'Posts/Featured Images')
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
