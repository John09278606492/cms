<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Enums\ContentStatus;
use App\Filament\Resources\Posts\PostResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditPost extends EditRecord
{
    protected static string $resource = PostResource::class;

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

    protected function getHeaderActions(): array
    {
        return [
            Action::make('preview')
                ->label('Preview')
                ->icon('heroicon-o-eye')
                ->url(fn (): string => $this->getRecord()->previewUrl(), shouldOpenInNewTab: true)
                ->visible(fn (): bool => ! $this->getRecord()->trashed()),
            DeleteAction::make(),
            RestoreAction::make(),
            ForceDeleteAction::make(),
        ];
    }
}
