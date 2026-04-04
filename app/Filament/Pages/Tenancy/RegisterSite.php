<?php

namespace App\Filament\Pages\Tenancy;

use App\Models\Site;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Pages\Tenancy\RegisterTenant;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class RegisterSite extends RegisterTenant
{
    public static function getLabel(): string
    {
        return 'Create a site';
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
                            ->unique(Site::class, 'slug'),
                        Textarea::make('description')
                            ->rows(4)
                            ->columnSpanFull(),
                        TextInput::make('subdomain')
                            ->maxLength(255)
                            ->alphaDash()
                            ->unique(Site::class, 'subdomain')
                            ->helperText('Optional for future tenant subdomain routing.'),
                        TextInput::make('domain')
                            ->maxLength(255)
                            ->unique(Site::class, 'domain')
                            ->helperText('Optional custom domain to connect later.')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeRegister(array $data): array
    {
        $data['slug'] = Str::slug((string) ($data['slug'] ?? $data['name']));

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRegistration(array $data): Model
    {
        /** @var User $user */
        $user = Filament::auth()->user();

        $site = Site::query()->create([
            ...$data,
            'owner_id' => $user->getKey(),
            'is_active' => true,
        ]);

        $site->users()->syncWithoutDetaching([
            $user->getKey() => ['role' => 'owner'],
        ]);

        $user->assignRole('site_owner');

        return $site;
    }
}
