<?php

namespace App\Filament\Resources\Pages\Pages;

use App\Enums\ContentStatus;
use App\Filament\Resources\Pages\PageResource;
use App\Models\Page as PageModel;
use App\Support\PageNavigationManager;
use Filament\Resources\Pages\CreateRecord;

class CreatePage extends CreateRecord
{
    protected static string $resource = PageResource::class;

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

    protected function afterCreate(): void
    {
        if (! $this->record?->is_homepage) {
            return;
        }

        PageModel::query()
            ->whereBelongsTo($this->record->site, 'site')
            ->whereKeyNot($this->record->getKey())
            ->where('is_homepage', true)
            ->get()
            ->each(function (PageModel $page): void {
                $page->forceFill(['is_homepage' => false])->saveQuietly();

                app(PageNavigationManager::class)->sync($page);
            });
    }
}
