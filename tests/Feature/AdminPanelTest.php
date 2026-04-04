<?php

namespace Tests\Feature;

use BezhanSalleh\FilamentShield\Resources\Roles\RoleResource;
use Filament\Facades\Filament;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    public function test_shield_role_resource_is_not_tenant_scoped(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $this->assertFalse(RoleResource::isScopedToTenant());
    }
}
