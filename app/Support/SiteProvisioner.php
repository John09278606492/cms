<?php

namespace App\Support;

use App\Models\Setting;
use App\Models\Site;

class SiteProvisioner
{
    public function provision(Site $site): void
    {
        $this->provisionSettings($site);
    }

    protected function provisionSettings(Site $site): void
    {
        Setting::query()->firstOrCreate(
            ['site_id' => $site->getKey()],
            [
                'site_name' => $site->name,
                'site_description' => $site->description,
                'site_email' => $site->owner?->email,
                'posts_per_page' => 10,
                'meta_title' => $site->name,
                'meta_description' => $site->description,
            ],
        );
    }
}
