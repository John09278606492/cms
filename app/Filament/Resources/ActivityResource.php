<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityResource\Pages\ListActivities;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;
use MrAdder\FilamentLogger\Resources\ActivityResource as BaseActivityResource;

class ActivityResource extends BaseActivityResource
{
    /**
     * Keep activity log URLs explicit so dashboard widgets always resolve to
     * the platform panel, even when the caller is inside the tenant workspace.
     *
     * @param  array<mixed>  $parameters
     */
    public static function getUrl(?string $name = null, array $parameters = [], bool $isAbsolute = true, ?string $panel = null, ?Model $tenant = null, bool $shouldGuessMissingParameters = false, ?string $configuration = null): string
    {
        $platformPanel = Filament::getPanel('platform', false);
        $panel ??= $platformPanel?->getId() ?? Filament::getCurrentOrDefaultPanel()?->getId();

        if ($platformPanel && $panel !== $platformPanel->getId()) {
            $panel = $platformPanel->getId();
        }

        return parent::getUrl(
            $name,
            $parameters,
            $isAbsolute,
            $panel,
            $tenant,
            $shouldGuessMissingParameters,
            $configuration,
        );
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Filament::getCurrentOrDefaultPanel()?->getId() === 'platform';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Platform';
    }

    protected static function getListActivitiesPage(): string
    {
        return ListActivities::class;
    }
}
