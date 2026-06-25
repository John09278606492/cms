<?php

namespace App\Filament\Resources\Settings\Schemas;

use App\Models\Setting;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Slimani\MediaManager\Form\MediaPicker;

class SettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Site identity')
                    ->schema([
                        MediaPicker::make('site_logo_id')
                            ->relationship('siteLogo')
                            ->label('Site logo')
                            ->helperText('Use a shared brand asset so the same logo can be reused across the site.')
                            ->conversion('')
                            ->directory(fn (): string => 'Brand/Logo')
                            ->image()
                            ->imageEditor()
                            ->columnSpanFull(),
                        TextInput::make('site_name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('site_tagline')
                            ->maxLength(255),
                        Textarea::make('site_description')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('Contact')
                    ->schema([
                        TextInput::make('site_email')
                            ->email()
                            ->maxLength(255),
                        TextInput::make('site_phone')
                            ->maxLength(255),
                        Textarea::make('site_address')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('SEO defaults')
                    ->schema([
                        TextInput::make('meta_title')
                            ->maxLength(255),
                        TextInput::make('posts_per_page')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->default(10),
                        Textarea::make('meta_description')
                            ->rows(4)
                            ->columnSpanFull(),
                        MediaPicker::make('default_og_image_id')
                            ->relationship('defaultOgImage')
                            ->label('Default social image')
                            ->helperText('Used for link previews when a page or post does not have its own image.')
                            ->conversion('')
                            ->directory(fn (): string => 'Brand/Sharing')
                            ->image()
                            ->imageEditor()
                            ->columnSpanFull(),
                        MediaPicker::make('site_favicon_id')
                            ->relationship('siteFavicon')
                            ->label('Favicon')
                            ->helperText('Shown in browser tabs and bookmarks for this site.')
                            ->conversion('')
                            ->directory(fn (): string => 'Brand/Favicon')
                            ->image()
                            ->imageEditor()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('Brand & theme')
                    ->description('Applied across every page on this site.')
                    ->schema([
                        ColorPicker::make('brand_primary')
                            ->label('Primary brand colour')
                            ->helperText('Used for links in your content.'),
                        ColorPicker::make('brand_accent')
                            ->label('Accent colour'),
                        Select::make('heading_font')
                            ->label('Heading font')
                            ->options(Setting::fontOptions())
                            ->searchable()
                            ->placeholder('Theme default'),
                        Select::make('body_font')
                            ->label('Body font')
                            ->options(Setting::fontOptions())
                            ->searchable()
                            ->placeholder('Theme default'),
                    ])
                    ->columns(2),
                Section::make('Social links')
                    ->schema([
                        KeyValue::make('social_links')
                            ->keyLabel('Network')
                            ->valueLabel('URL')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
