<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\InteractsWithAlertDismissal;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use CodeWithDennis\SimpleAlert\Components\SimpleAlert;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Widgets\Widget;

class CmsForgeAlertWidget extends Widget implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithAlertDismissal;

    protected static bool $isDiscovered = false;

    protected int | string | array $columnSpan = 'full';

    protected string $view = 'filament.widgets.cms-forge-alert-widget';

    public string $panelId = 'admin';

    public string $context = 'dashboard';

    public function content(Schema $schema): Schema
    {
        return $schema->components(
            $this->shouldRenderAlert()
                ? [$this->getAlertComponent()]
                : [],
        );
    }

    public function shouldRenderAlert(): bool
    {
        return ! $this->isCmsForgeAlertDismissed();
    }

    public function dismissCmsForgeAlert(): void
    {
        $this->dismissAlert($this->getAlertKey(), $this->getAlertFingerprint());
    }

    protected function getAlertComponent(): SimpleAlert
    {
        $alert = SimpleAlert::make('cmsForgeAlert')
            ->columnSpanFull()
            ->info()
            ->border()
            ->actionsVerticalAlignment('center');

        $definition = $this->getAlertDefinition();

        return $alert
            ->title($definition['title'])
            ->description($definition['description'])
            ->actions($this->getAlertActions($definition));
    }

    /**
     * @param  array{title: string, description: string, action_label: string, action_url: string, fingerprint: string}  $definition
     * @return array<int, Action>
     */
    protected function getAlertActions(array $definition): array
    {
        return [
            Action::make('openCmsForgeNotice')
                ->label($definition['action_label'])
                ->url($definition['action_url'])
                ->button()
                ->color('primary'),
            Action::make('dismissCmsForgeAlert')
                ->label('Dismiss this notice')
                ->iconButton()
                ->icon('heroicon-m-x-mark')
                ->tooltip('Dismiss this notice for now')
                ->color('gray')
                ->action(function (): void {
                    $this->dismissCmsForgeAlert();
                }),
        ];
    }

    protected function isCmsForgeAlertDismissed(): bool
    {
        return $this->isAlertDismissed($this->getAlertKey(), $this->getAlertFingerprint());
    }

    protected function getAlertKey(): string
    {
        return sprintf('cms-forge:%s:%s', strtolower($this->panelId), strtolower($this->context));
    }

    protected function getAlertFingerprint(): string
    {
        return $this->getAlertDefinition()['fingerprint'];
    }

    /**
     * @return array{title: string, description: string, action_label: string, action_url: string, fingerprint: string}
     */
    protected function getAlertDefinition(): array
    {
        $panelId = strtolower($this->panelId);
        $context = strtolower($this->context);

        if ($context === 'login') {
            if ($panelId === 'platform') {
                return [
                    'title' => 'Platform sign in',
                    'description' => 'You are signing into the platform console. Switch to the site owner workspace if you manage a tenant site.',
                    'action_label' => 'Switch to site owner login',
                    'action_url' => Filament::getPanel('admin', false)?->getLoginUrl() ?? route('platform.home'),
                    'fingerprint' => 'platform:login:v1',
                ];
            }

            return [
                'title' => 'Site owner sign in',
                'description' => 'You are signing into the site owner workspace. Switch to the platform console if you need site-wide tools.',
                'action_label' => 'Switch to platform login',
                'action_url' => Filament::getPanel('platform', false)?->getLoginUrl() ?? route('platform.home'),
                'fingerprint' => 'admin:login:v1',
            ];
        }

        if ($panelId === 'platform') {
            return [
                'title' => 'Platform console',
                'description' => 'You are in the platform console. Manage sites, users, and audit logs here.',
                'action_label' => 'Open CMS Forge home',
                'action_url' => route('platform.home'),
                'fingerprint' => 'platform:dashboard:v1',
            ];
        }

        return [
            'title' => 'Site workspace',
            'description' => 'You are in a site workspace. Manage pages, posts, menus, media, and settings here.',
            'action_label' => 'Open CMS Forge home',
            'action_url' => route('platform.home'),
            'fingerprint' => 'admin:dashboard:v1',
        ];
    }
}
