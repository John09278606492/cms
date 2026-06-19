<?php

namespace App\Support;

use Filament\Facades\Filament;

class CmsForgeBanner
{
    /**
     * @return array<string, string>|null
     */
    public static function forLanding(?string $platformLoginUrl = null, ?string $siteOwnerLoginUrl = null): ?array
    {
        $platformLoginUrl ??= Filament::getPanel('platform', false)?->getLoginUrl();
        $siteOwnerLoginUrl ??= Filament::getPanel('admin', false)?->getLoginUrl();

        if (blank($platformLoginUrl)) {
            return null;
        }

        return [
            'wrapper_classes' => 'mx-auto max-w-6xl px-6 pt-6',
            'eyebrow' => 'CMS Forge alert',
            'badge' => 'Global notice',
            'title' => 'Two panels, one CMS Forge',
            'description' => 'Super admins manage the platform console, while site owners work inside their own tenant workspace.',
            'primary_label' => 'Platform login',
            'primary_url' => $platformLoginUrl,
            'secondary_label' => 'Site owner login',
            'secondary_url' => $siteOwnerLoginUrl ?? '',
            'fingerprint' => 'landing:v1',
        ];
    }

    /**
     * @return array<string, string>|null
     */
    public static function forPanel(string $panelId): ?array
    {
        $panelId = strtolower($panelId);

        if (! in_array($panelId, ['admin', 'platform'], true)) {
            return null;
        }

        return [
            'wrapper_classes' => 'mb-6',
            'eyebrow' => 'CMS Forge notice',
            'badge' => 'Global',
            'title' => $panelId === 'platform'
                ? 'Platform console'
                : 'Site workspace',
            'description' => $panelId === 'platform'
                ? 'You are in the platform console. Manage sites, users, and audit logs here.'
                : 'You are in a site workspace. Manage pages, posts, menus, media, and settings here.',
            'primary_label' => 'Open CMS Forge home',
            'primary_url' => route('platform.home'),
            'secondary_label' => '',
            'secondary_url' => '',
            'fingerprint' => "panel:{$panelId}:v1",
        ];
    }
}
