<?php

namespace App\Filament\Resources\Sites\Pages;

use App\Filament\Resources\Sites\SiteResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Str;

class EditSite extends EditRecord
{
    protected static string $resource = SiteResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['slug'] = Str::slug((string) ($data['slug'] ?? $data['name']));

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
