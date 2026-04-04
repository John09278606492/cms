<?php

namespace App\Filament\Pages\Tenancy;

use App\Models\Site;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Pages\Tenancy\EditTenantProfile;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class EditSiteProfile extends EditTenantProfile
{
    public static function getLabel(): string
    {
        return 'Site profile';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Site details')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, callable $set): mixed => $set('slug', Str::slug((string) $state))),
                        TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(Site::class, 'slug', ignoreRecord: true),
                        Textarea::make('description')
                            ->rows(4)
                            ->columnSpanFull(),
                        TextInput::make('subdomain')
                            ->maxLength(255)
                            ->alphaDash()
                            ->unique(Site::class, 'subdomain', ignoreRecord: true),
                        TextInput::make('domain')
                            ->maxLength(255)
                            ->unique(Site::class, 'domain', ignoreRecord: true)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['slug'] = Str::slug((string) ($data['slug'] ?? $data['name']));

        return $data;
    }
}
