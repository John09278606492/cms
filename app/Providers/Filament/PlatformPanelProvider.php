<?php

namespace App\Providers\Filament;

use App\Filament\Auth\Login as PanelLogin;
use App\Filament\Widgets\CmsForgeAlertWidget;
use App\Filament\Resources\ActivityResource;
use App\Filament\Resources\Sites\SiteResource;
use App\Filament\Resources\Users\UserResource;
use Awcodes\Overlook\OverlookPlugin;
use Awcodes\Overlook\Widgets\OverlookWidget;
use Awcodes\QuickCreate\QuickCreatePlugin;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Jeffgreco13\FilamentBreezy\BreezyCore;
use MrAdder\FilamentLogger\Widgets\ActivityOverviewWidget;

class PlatformPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('platform')
            ->path('platform')
            ->brandName('CMS Forge Platform')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->login(PanelLogin::class)
            ->sidebarCollapsibleOnDesktop()
            ->colors([
                'primary' => Color::Indigo,
            ])
            ->resources([
                ActivityResource::class,
                SiteResource::class,
                UserResource::class,
            ])
            ->pages([
                Dashboard::class,
            ])
            ->widgets([
                AccountWidget::class,
                OverlookWidget::class,
                ActivityOverviewWidget::class,
            ])
            ->renderHook(
                PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE,
                fn (): string => $this->renderCmsForgeAlert('platform', 'login'),
            )
            ->renderHook(
                PanelsRenderHook::CONTENT_BEFORE,
                fn (): string => $this->renderPlatformContentBanner(),
            )
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->plugins([
                FilamentShieldPlugin::make()
                    // Roles and permissions live at the platform level here.
                    ->scopeToTenant(false),
                OverlookPlugin::make()
                    ->sort(0)
                    ->withoutTrashed()
                    ->includes([
                        SiteResource::class,
                        UserResource::class,
                    ]),
                QuickCreatePlugin::make()
                    ->label('New')
                    ->tooltip('Create platform records')
                    ->sortBy('navigation')
                    ->includes([
                        SiteResource::class,
                        UserResource::class,
                    ]),
                BreezyCore::make()
                    ->myProfile(
                        shouldRegisterUserMenu: true,
                        shouldRegisterNavigation: true,
                        hasAvatars: true,
                        navigationGroup: 'Users',
                        userMenuLabel: 'My profile',
                    )
                    ->enableTwoFactorAuthentication()
                    ->enablePasskeys()
                    ->enableBrowserSessions(),
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }

    private function renderCmsForgeAlert(string $panelId, string $context): string
    {
        return app('livewire')->mount(CmsForgeAlertWidget::class, [
            'panelId' => $panelId,
            'context' => $context,
        ]);
    }

    private function renderPlatformContentBanner(): string
    {
        return $this->renderAlertStack([
            $this->renderCmsForgeAlert('platform', 'dashboard'),
        ]);
    }

    /**
     * @param  array<int, string>  $alerts
     */
    private function renderAlertStack(array $alerts): string
    {
        $alerts = array_values(array_filter($alerts, fn (string $alert): bool => filled(trim($alert))));

        if ($alerts === []) {
            return '';
        }

        return '<div class="cms-forge-panel-alert-stack">' . implode('', $alerts) . '</div>';
    }
}
