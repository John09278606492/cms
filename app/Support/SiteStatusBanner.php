<?php

namespace App\Support;

use App\Models\Site;

class SiteStatusBanner
{
    /**
     * @return array<string, string>|null
     */
    public static function forSite(
        Site $site,
        ?string $primaryUrl = null,
        ?string $secondaryUrl = null,
    ): ?array {
        if ($site->is_active) {
            return null;
        }

        if (blank($primaryUrl)) {
            return null;
        }

        return [
            'wrapper_classes' => 'mb-6',
            'site_id' => (string) $site->getKey(),
            'fingerprint' => sprintf(
                'site:%s:updated:%s',
                $site->getKey(),
                $site->updated_at?->timestamp ?? 0,
            ),
            'eyebrow' => 'Workspace alert',
            'badge' => 'Inactive',
            'title' => $site->name,
            'description' => 'This site is currently inactive. Visitors see the custom error page, but you can still preview it until you reactivate it.',
            'primary_label' => 'Open site profile',
            'primary_url' => $primaryUrl,
            'secondary_label' => 'Preview your site',
            'secondary_url' => $secondaryUrl ?? '',
        ];
    }
}
