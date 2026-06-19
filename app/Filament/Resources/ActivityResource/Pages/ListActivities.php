<?php

namespace App\Filament\Resources\ActivityResource\Pages;

use App\Filament\Resources\ActivityResource\Pages\Concerns\HasActivityTabBadges;
use MrAdder\FilamentLogger\Resources\ActivityResource\Pages\ListActivities as BaseListActivities;

class ListActivities extends BaseListActivities
{
    use HasActivityTabBadges;

    public function getTabs(): array
    {
        return $this->buildActivityTabs();
    }
}
