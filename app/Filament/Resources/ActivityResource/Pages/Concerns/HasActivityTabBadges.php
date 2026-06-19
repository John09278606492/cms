<?php

namespace App\Filament\Resources\ActivityResource\Pages\Concerns;

use Illuminate\Database\Eloquent\Builder;
use MrAdder\FilamentLogger\Support\ActivityFilterPresetManager;

trait HasActivityTabBadges
{
    /**
     * Build the activity tabs with badge counts for each saved preset.
     *
     * @return array<string, object>
     */
    protected function buildActivityTabs(): array
    {
        $tabs = [];

        foreach (ActivityFilterPresetManager::saved() as $key => $preset) {
            $tabs[$key] = $this->makeTab(data_get($preset, 'label', $this->generateTabLabel((string) $key)))
                ->icon(data_get($preset, 'icon'))
                ->badge($this->countActivityTabRecords($preset))
                ->badgeColor('gray')
                ->modifyQueryUsing(fn ($query) => ActivityFilterPresetManager::apply($query, $preset));
        }

        return $tabs;
    }

    /**
     * @param  array<string, mixed>  $preset
     */
    protected function countActivityTabRecords(array $preset): int
    {
        $query = $this->getTableQuery();

        if (! $query instanceof Builder) {
            return 0;
        }

        return ActivityFilterPresetManager::apply(clone $query, $preset)->count();
    }
}
