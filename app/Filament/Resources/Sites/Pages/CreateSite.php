<?php

namespace App\Filament\Resources\Sites\Pages;

use App\Filament\Resources\Sites\SiteResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateSite extends CreateRecord
{
    protected static string $resource = SiteResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['slug'] = Str::slug((string) ($data['slug'] ?? $data['name']));

        return $data;
    }
}
