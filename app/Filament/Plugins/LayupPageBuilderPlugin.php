<?php

namespace App\Filament\Plugins;

use Filament\Panel;

class LayupPageBuilderPlugin extends \Crumbls\Layup\LayupPlugin
{
    public function register(Panel $panel): void
    {
        // Our app uses Layup's builder field and widget registry, but keeps its
        // own content resources. We intentionally skip the package Pages resource.
    }
}
