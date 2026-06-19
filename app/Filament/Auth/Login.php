<?php

namespace App\Filament\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Facades\Filament;
use Filament\Schemas\Components\RenderHook;
use Filament\Schemas\Schema;
use Filament\View\PanelsRenderHook;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class Login extends BaseLogin
{
    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                RenderHook::make(PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE),
                $this->getFormContentComponent(),
                $this->getMultiFactorChallengeFormContentComponent(),
                RenderHook::make(PanelsRenderHook::AUTH_LOGIN_FORM_AFTER),
            ]);
    }

    public function getTitle(): string | Htmlable
    {
        return $this->getWorkspaceSignInLabel();
    }

    public function getHeading(): string | Htmlable | null
    {
        if (filled($this->userUndertakingMultiFactorAuthentication)) {
            return parent::getHeading();
        }

        return $this->getWorkspaceSignInLabel();
    }

    public function getSubheading(): string | Htmlable | null
    {
        if (filled($this->userUndertakingMultiFactorAuthentication)) {
            return parent::getSubheading();
        }

        $copy = $this->isPlatformPanel()
            ? 'Use the platform console to manage sites, users, and activity'
            : 'Use the site owner workspace to manage pages, posts, menus, media, and settings';

        if (! filament()->hasRegistration()) {
            return $copy . '.';
        }

        return new HtmlString($copy . '. Need an account? ' . $this->registerAction->toHtml());
    }

    protected function getWorkspaceSignInLabel(): string
    {
        return $this->isPlatformPanel()
            ? 'Platform sign in'
            : 'Site owner sign in';
    }

    protected function isPlatformPanel(): bool
    {
        return Filament::getCurrentOrDefaultPanel()?->getId() === 'platform';
    }
}
