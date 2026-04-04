<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identity')
                    ->schema([
                        FileUpload::make('avatar_url')
                            ->avatar()
                            ->disk('public')
                            ->directory('avatars')
                            ->image()
                            ->imageEditor()
                            ->columnSpanFull(),
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                    ])
                    ->columns(2),
                Section::make('Access')
                    ->schema([
                        Select::make('roles')
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->label('Roles'),
                    ]),
                Section::make('Security')
                    ->schema([
                        TextInput::make('password')
                            ->password()
                            ->revealable()
                            ->autocomplete('new-password')
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn ($state): bool => filled($state))
                            ->same('passwordConfirmation'),
                        TextInput::make('passwordConfirmation')
                            ->label('Password confirmation')
                            ->password()
                            ->revealable()
                            ->dehydrated(false)
                            ->required(fn (Get $get): bool => filled($get('password')))
                            ->visible(fn (Get $get): bool => filled($get('password'))),
                    ])
                    ->columns(2),
            ]);
    }
}
