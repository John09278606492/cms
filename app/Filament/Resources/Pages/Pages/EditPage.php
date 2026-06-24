<?php

namespace App\Filament\Resources\Pages\Pages;

use App\Enums\ContentStatus;
use App\Filament\Resources\Pages\Concerns\InteractsWithPageTemplates;
use App\Filament\Resources\Pages\PageResource;
use App\Models\Page as PageModel;
use App\Support\PageNavigationManager;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditPage extends EditRecord
{
    use InteractsWithPageTemplates;

    protected static string $resource = PageResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (($data['status'] ?? null) === ContentStatus::Published->value && blank($data['published_at'] ?? null)) {
            $data['published_at'] = now();
        }

        return $data;
    }

    protected function afterSave(): void
    {
        if (! $this->record->is_homepage) {
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

    protected function getHeaderActions(): array
    {
        return [
            Action::make('preview')
                ->label('Preview')
                ->icon('heroicon-o-eye')
                ->url(fn (): string => $this->getRecord()->previewUrl(), shouldOpenInNewTab: true)
                ->visible(fn (): bool => ! $this->getRecord()->trashed()),
            ...$this->templateActions(),
            DeleteAction::make(),
            RestoreAction::make(),
            ForceDeleteAction::make(),
        ];
    }
}
