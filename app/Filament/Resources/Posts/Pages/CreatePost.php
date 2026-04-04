<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Enums\ContentStatus;
use App\Filament\Resources\Posts\PostResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePost extends CreateRecord
{
    protected static string $resource = PostResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] ??= auth()->id();

        if (($data['status'] ?? null) === ContentStatus::Published->value && blank($data['published_at'] ?? null)) {
            $data['published_at'] = now();
        }

        return $data;
    }
}
