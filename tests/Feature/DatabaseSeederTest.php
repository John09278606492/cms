<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeding_produces_a_super_admin_who_can_manage_content(): void
    {
        $this->seed(DatabaseSeeder::class);

        // Shield permissions must actually be generated, otherwise the role syncs
        // assign nothing and every policy denies the super admin.
        $this->assertGreaterThan(0, Permission::query()->count());

        $admin = User::query()->where('email', 'admin@cms.test')->first();

        $this->assertNotNull($admin);
        $this->assertTrue($admin->hasRole('super_admin'));
        $this->assertTrue($admin->can('Update:Post'));
        $this->assertTrue($admin->can('Delete:Site'));
    }

    public function test_seeding_scopes_the_site_owner_role_to_tenant_content(): void
    {
        $this->seed(DatabaseSeeder::class);

        $siteOwner = User::query()->create([
            'name' => 'Tenant Owner',
            'email' => 'seeded-owner@example.test',
            'password' => 'password',
        ]);
        $siteOwner->assignRole('site_owner');

        // Site owners manage their own tenant content...
        $this->assertTrue($siteOwner->can('Update:Post'));
        $this->assertTrue($siteOwner->can('Update:Page'));

        // ...but not platform-level resources reserved for super admins.
        $this->assertFalse($siteOwner->can('Delete:Site'));
        $this->assertFalse($siteOwner->can('ViewAny:User'));
    }
}
