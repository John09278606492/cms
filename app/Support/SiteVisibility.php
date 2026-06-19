<?php

namespace App\Support;

use App\Models\Site;
use App\Models\User;

class SiteVisibility
{
    public static function canView(Site $site, ?User $user = null): bool
    {
        return $site->is_active || ($user?->canAccessTenant($site) ?? false);
    }
}
