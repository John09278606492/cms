<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\InteractsWithAlertDismissal;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use CodeWithDennis\SimpleAlert\Components\SimpleAlert;
use Filament\Actions\Action;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Widgets\Widget;

class SiteStatusAlertWidget extends Widget implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithAlertDismissal;

    protected static bool $isDiscovered = false;

    protected int | string | array $columnSpan = 'full';

    protected string $view = 'filament.widgets.site-status-alert-widget';

    /**
     * @var array<string, string>
     */
    public array $banner = [];

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
        return ! $this->isSiteStatusAlertDismissed();
    }

    public function dismissSiteStatusAlert(): void
    {
        $this->dismissAlert($this->getAlertKey(), $this->getAlertFingerprint());
    }

    protected function getAlertComponent(): SimpleAlert
    {
        return SimpleAlert::make('siteStatusAlert')
            ->columnSpanFull()
            ->warning()
            ->border()
            ->title($this->banner['title'] ?? 'Site unavailable')
            ->description($this->banner['description'] ?? 'This site is currently inactive.')
            ->actionsVerticalAlignment('center')
            ->actions($this->getAlertActions());
    }

    /**
     * @return array<int, Action>
     */
    protected function getAlertActions(): array
    {
        $actions = [];

        if (filled($this->banner['primary_url'] ?? null)) {
            $actions[] = Action::make('openSiteProfile')
                ->label($this->banner['primary_label'] ?? 'Open site profile')
                ->url($this->banner['primary_url'])
                ->button()
                ->color('primary');
        }

        if (filled($this->banner['secondary_url'] ?? null)) {
            $actions[] = Action::make('viewPublicErrorPage')
                ->label($this->banner['secondary_label'] ?? 'View public error page')
                ->url($this->banner['secondary_url'])
                ->button()
                ->color('gray');
        }

        $actions[] = Action::make('dismissSiteStatusAlert')
            ->label('Dismiss this alert')
            ->iconButton()
            ->icon('heroicon-m-x-mark')
            ->tooltip('Dismiss this alert for now')
            ->color('gray')
            ->action(function (): void {
                $this->dismissSiteStatusAlert();
            });

        return $actions;
    }

    protected function isSiteStatusAlertDismissed(): bool
    {
        return $this->isAlertDismissed($this->getAlertKey(), $this->getAlertFingerprint());
    }

    protected function getAlertKey(): string
    {
        return sprintf('site-status:%s', (string) ($this->banner['site_id'] ?? 'unknown'));
    }

    protected function getAlertFingerprint(): string
    {
        return (string) ($this->banner['fingerprint'] ?? 'default');
    }
}
