<?php

namespace Tests\Feature;

use BezhanSalleh\FilamentShield\Resources\Roles\RoleResource;
use Filament\Facades\Filament;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    public function test_shield_role_resource_is_not_tenant_scoped(): void
    {
        // Shield's role resource lives on the platform panel, which has no tenancy
        // and configures the plugin with scopeToTenant(false).
        Filament::setCurrentPanel(Filament::getPanel('platform'));

        $this->assertFalse(RoleResource::isScopedToTenant());
    }
}
