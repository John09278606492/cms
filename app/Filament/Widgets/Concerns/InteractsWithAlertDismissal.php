<?php

namespace App\Filament\Widgets\Concerns;

use Illuminate\Support\Facades\Cache;

trait InteractsWithAlertDismissal
{
    protected function alertDismissalTtl(): \DateTimeInterface
    {
        return now()->addMinute();
    }

    protected function alertDismissalScope(): string
    {
        $user = auth()->user();

        if (filled($user?->getAuthIdentifier())) {
            return 'user:' . $user->getAuthIdentifier();
        }

        return 'session:' . (session()->getId() ?: 'guest');
    }

    protected function alertDismissalKey(string $alertKey, string $fingerprint = ''): string
    {
        return implode(':', [
            'cms-forge',
            'alert-dismissal',
            $this->alertDismissalScope(),
            $alertKey,
            filled($fingerprint) ? $fingerprint : 'default',
        ]);
    }

    protected function isAlertDismissed(string $alertKey, string $fingerprint = ''): bool
    {
        return Cache::has($this->alertDismissalKey($alertKey, $fingerprint));
    }

    protected function dismissAlert(string $alertKey, string $fingerprint = ''): void
    {
        Cache::put($this->alertDismissalKey($alertKey, $fingerprint), true, $this->alertDismissalTtl());
    }

    protected function restoreAlert(string $alertKey, string $fingerprint = ''): void
    {
        Cache::forget($this->alertDismissalKey($alertKey, $fingerprint));
    }
}
