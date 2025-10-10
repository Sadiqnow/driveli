<?php

namespace Tests\Unit\Models;

use App\Models\AdminUser;
use App\Models\Drivers;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_user_can_be_created_with_valid_data()
    {
        $admin = AdminUser::factory()->create([
            'name' => 'John Admin',
            'email' => 'admin@example.com',
            'role' => 'Admin',
        ]);

        $this->assertDatabaseHas('admin_users', [
            'name' => 'John Admin',
            'email' => 'admin@example.com',
            'role' => 'Admin',
        ]);
    }

    public function test_initials_accessor_returns_correct_initials()
    {
        $admin = AdminUser::factory()->create([
            'name' => 'John Michael Doe',
        ]);

        $this->assertEquals('JM', $admin->initials);
    }

    public function test_initials_accessor_handles_single_name()
    {
        $admin = AdminUser::factory()->create([
            'name' => 'John',
        ]);

        $this->assertEquals('J', $admin->initials);
    }

    public function test_has_permission_returns_true_for_super_admin()
    {
        $superAdmin = AdminUser::factory()->superAdmin()->create();

        $this->assertTrue($superAdmin->hasPermission('any_permission'));
        $this->assertTrue($superAdmin->hasPermission('manage_drivers'));
        $this->assertTrue($superAdmin->hasPermission('system_settings'));
    }

    public function test_has_permission_checks_permissions_array_for_regular_admin()
    {
        $admin = AdminUser::factory()->create([
            'role' => 'Admin',
            'permissions' => ['view_drivers', 'manage_companies'],
        ]);

        $this->assertTrue($admin->hasPermission('view_drivers'));
        $this->assertTrue($admin->hasPermission('manage_companies'));
        $this->assertFalse($admin->hasPermission('system_settings'));
        $this->assertFalse($admin->hasPermission('delete_all_data'));
    }

    public function test_has_permission_returns_false_when_no_permissions()
    {
        $admin = AdminUser::factory()->create([
            'role' => 'Operator',
            'permissions' => null,
        ]);

        $this->assertFalse($admin->hasPermission('view_drivers'));
        $this->assertFalse($admin->hasPermission('manage_companies'));
    }

    public function test_has_permission_returns_false_when_empty_permissions()
    {
        $admin = AdminUser::factory()->create([
            'role' => 'Operator',
            'permissions' => [],
        ]);

        $this->assertFalse($admin->hasPermission('view_drivers'));
        $this->assertFalse($admin->hasPermission('manage_companies'));
    }

    public function test_update_last_login_updates_timestamp_and_ip()
    {
        $admin = AdminUser::factory()->create([
            'last_login_at' => null,
            'last_login_ip' => null,
        ]);

        $testIp = '192.168.1.100';
        $admin->updateLastLogin($testIp);

        $admin->refresh();

        $this->assertNotNull($admin->last_login_at);
        $this->assertEquals($testIp, $admin->last_login_ip);
    }

    public function test_update_last_login_uses_request_ip_when_not_provided()
    {
        $admin = AdminUser::factory()->create();
        
        // Mock the request IP
        $this->app['request']->server->set('REMOTE_ADDR', '127.0.0.1');
        
        $admin->updateLastLogin();
        $admin->refresh();

        $this->assertNotNull($admin->last_login_at);
        $this->assertNotNull($admin->last_login_ip);
    }

    public function test_is_active_returns_true_for_active_status()
    {
        $activeAdmin = AdminUser::factory()->active()->create();
        $inactiveAdmin = AdminUser::factory()->inactive()->create();

        $this->assertTrue($activeAdmin->isActive());
        $this->assertFalse($inactiveAdmin->isActive());
    }

    public function test_is_super_admin_returns_true_for_super_admin_role()
    {
        $superAdmin = AdminUser::factory()->superAdmin()->create();
        $regularAdmin = AdminUser::factory()->create(['role' => 'Admin']);

        $this->assertTrue($superAdmin->isSuperAdmin());
        $this->assertFalse($regularAdmin->isSuperAdmin());
    }

    public function test_active_scope_filters_active_admins()
    {
        AdminUser::factory()->create(['status' => 'Active']);
        AdminUser::factory()->create(['status' => 'Inactive']);
        AdminUser::factory()->create(['status' => 'Suspended']);

        $activeAdmins = AdminUser::active()->get();
        
        $this->assertCount(1, $activeAdmins);
        $this->assertEquals('Active', $activeAdmins->first()->status);
    }

    public function test_by_role_scope_filters_by_role()
    {
        AdminUser::factory()->create(['role' => 'Super Admin']);
        AdminUser::factory()->create(['role' => 'Admin']);
        AdminUser::factory()->create(['role' => 'Admin']);
        AdminUser::factory()->create(['role' => 'Operator']);

        $admins = AdminUser::byRole('Admin')->get();
        
        $this->assertCount(2, $admins);
        $this->assertTrue($admins->every(fn($admin) => $admin->role === 'Admin'));
    }

    public function test_verified_drivers_relationship_works()
    {
        $admin = AdminUser::factory()->create();
        
        Drivers::factory()->count(3)->create(['verified_by' => $admin->id]);
        Drivers::factory()->count(2)->create(['verified_by' => null]);

        $verifiedDrivers = $admin->verifiedDrivers;
        
        $this->assertCount(3, $verifiedDrivers);
        $this->assertTrue($verifiedDrivers->every(fn($driver) => $driver->verified_by === $admin->id));
    }

    public function test_permissions_are_cast_to_array()
    {
        $admin = AdminUser::factory()->create([
            'permissions' => ['view_drivers', 'manage_companies'],
        ]);

        $this->assertIsArray($admin->permissions);
        $this->assertContains('view_drivers', $admin->permissions);
        $this->assertContains('manage_companies', $admin->permissions);
    }

    public function test_password_is_hidden_in_array_output()
    {
        $admin = AdminUser::factory()->create([
            'password' => 'secret_password',
        ]);

        $adminArray = $admin->toArray();
        
        $this->assertArrayNotHasKey('password', $adminArray);
        $this->assertArrayNotHasKey('remember_token', $adminArray);
    }

    public function test_soft_deletes_work_correctly()
    {
        $admin = AdminUser::factory()->create();
        $adminId = $admin->id;

        $admin->delete();

        $this->assertSoftDeleted('admin_users', ['id' => $adminId]);
        $this->assertCount(0, AdminUser::all());
        $this->assertCount(1, AdminUser::withTrashed()->get());
    }

    public function test_permissions_can_be_null()
    {
        $admin = AdminUser::factory()->create([
            'permissions' => null,
        ]);

        $this->assertNull($admin->permissions);
        $this->assertFalse($admin->hasPermission('any_permission'));
    }
}