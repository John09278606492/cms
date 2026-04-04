<?php

namespace App\Providers\Filament;

use App\Filament\Auth\Register;
use App\Filament\Plugins\LayupPageBuilderPlugin;
use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Resources\Pages\PageResource;
use App\Filament\Resources\Posts\PostResource;
use App\Filament\Resources\Tags\TagResource;
use App\Filament\Resources\Users\UserResource;
use App\Layup\Widgets\CallToActionWidget;
use App\Layup\Widgets\FeatureGridWidget;
use App\Layup\Widgets\HeroWidget;
use App\Layup\Widgets\ImageWidget;
use App\Layup\Widgets\RichTextWidget;
use App\Filament\Widgets\ContentStatsOverview;
use App\Filament\Widgets\RecentContent;
use App\Filament\Pages\Tenancy\EditSiteProfile;
use App\Filament\Pages\Tenancy\RegisterSite;
use App\Models\Menu;
use App\Models\Page;
use App\Models\Post;
use App\Models\Site;
use Awcodes\Overlook\OverlookPlugin;
use Awcodes\Overlook\Widgets\OverlookWidget;
use Awcodes\QuickCreate\QuickCreatePlugin;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Datlechin\FilamentMenuBuilder\FilamentMenuBuilderPlugin;
use Datlechin\FilamentMenuBuilder\MenuPanel\ModelMenuPanel;
use Datlechin\FilamentMenuBuilder\MenuPanel\StaticMenuPanel;
use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Jeffgreco13\FilamentBreezy\BreezyCore;
use MrAdder\FilamentLogger\Widgets\ActivityOverviewWidget;
use Slimani\MediaManager\MediaManagerPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->brandName('CMS Forge')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->login()
            ->registration(Register::class)
            ->tenant(Site::class, slugAttribute: 'slug', ownershipRelationship: 'site')
            ->tenantRoutePrefix('site')
            ->tenantRegistration(RegisterSite::class)
            ->tenantProfile(EditSiteProfile::class)
            ->sidebarCollapsibleOnDesktop()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->resources([
                config('filament-logger.activity_resource'),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                OverlookWidget::class,
                ContentStatsOverview::class,
                ActivityOverviewWidget::class,
                RecentContent::class,
            ])
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
                    // Roles and permissions are platform-level in this panel.
                    ->scopeToTenant(false),
                OverlookPlugin::make()
                    ->sort(0)
                    ->withoutTrashed()
                    ->includes([
                        PostResource::class,
                        PageResource::class,
                        CategoryResource::class,
                        TagResource::class,
                        UserResource::class,
                    ]),
                QuickCreatePlugin::make()
                    ->label('New')
                    ->tooltip('Create content')
                    ->sortBy('navigation')
                    ->includes([
                        PostResource::class,
                        PageResource::class,
                        CategoryResource::class,
                        TagResource::class,
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
                FilamentMenuBuilderPlugin::make()
                    ->usingMenuModel(Menu::class)
                    ->navigationGroup('Content')
                    ->navigationSort(45)
                    ->addLocations([
                        'header' => 'Header Navigation',
                        'footer' => 'Footer Navigation',
                    ])
                    ->addMenuPanels([
                        StaticMenuPanel::make('Core links')
                            ->description('Built-in site destinations that are available for every tenant.')
                            ->sort(0)
                            ->add('Home', fn (): string => Filament::getTenant()
                                ? route('sites.home', Filament::getTenant())
                                : '#')
                            ->add('Blog', fn (): string => Filament::getTenant()
                                ? route('sites.blog.index', Filament::getTenant())
                                : '#'),
                        ModelMenuPanel::make()
                            ->model(Page::class)
                            ->sort(10),
                        ModelMenuPanel::make()
                            ->model(Post::class)
                            ->sort(20),
                    ]),
                MediaManagerPlugin::make()
                    ->disk('public')
                    ->navigationGroup('Content')
                    ->navigationLabel('Assets')
                    ->navigationIcon('heroicon-o-photo')
                    ->navigationSort(35)
                    ->shouldRegisterNavigation(fn (): bool => Filament::getTenant() !== null),
                LayupPageBuilderPlugin::make()
                    ->withoutConfigWidgets()
                    ->widgets([
                        HeroWidget::class,
                        RichTextWidget::class,
                        ImageWidget::class,
                        FeatureGridWidget::class,
                        CallToActionWidget::class,
                    ]),
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
