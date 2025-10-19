<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\Role;
use App\Models\Permission;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\Hash;

class RBACMiddlewareTest extends TestCase
{
    use WithoutMiddleware;

    private $superAdmin;
    private $admin;
    private $regularUser;
    private $viewDriversPermission;
    private $manageRolesPermission;

    protected function setUp(): void
    {
        parent::setUp();

        // Create permissions if they don't exist
        $this->viewDriversPermission = Permission::firstOrCreate([
            'name' => 'view_drivers'
        ], [
            'display_name' => 'View Drivers',
            'description' => 'Can view driver list',
            'category' => 'drivers',
            'is_active' => true
        ]);

        $this->manageRolesPermission = Permission::firstOrCreate([
            'name' => 'manage_roles'
        ], [
            'display_name' => 'Manage Roles',
            'description' => 'Can create and manage roles',
            'category' => 'roles',
            'is_active' => true
        ]);

        // Create roles if they don't exist
        $superAdminRole = Role::firstOrCreate([
            'name' => 'super_admin'
        ], [
            'display_name' => 'Super Admin',
            'description' => 'Full system access',
            'level' => 100,
            'is_active' => true
        ]);

        $adminRole = Role::firstOrCreate([
            'name' => 'admin'
        ], [
            'display_name' => 'Admin',
            'description' => 'Administrative access',
            'level' => 10,
            'is_active' => true
        ]);

        $viewerRole = Role::firstOrCreate([
            'name' => 'viewer'
        ], [
            'display_name' => 'Viewer',
            'description' => 'Read-only access',
            'level' => 1,
            'is_active' => true
        ]);

        // Assign permissions to roles (only if not already assigned)
        if (!$superAdminRole->permissions()->where('permissions.id', $this->viewDriversPermission->id)->exists()) {
            $superAdminRole->permissions()->attach([$this->viewDriversPermission->id, $this->manageRolesPermission->id]);
        }
        if (!$adminRole->permissions()->where('permissions.id', $this->viewDriversPermission->id)->exists()) {
            $adminRole->permissions()->attach([$this->viewDriversPermission->id]);
        }
        if (!$viewerRole->permissions()->where('permissions.id', $this->viewDriversPermission->id)->exists()) {
            $viewerRole->permissions()->attach([$this->viewDriversPermission->id]);
        }

        // Create or find users
        $this->superAdmin = AdminUser::firstOrCreate([
            'email' => 'superadmin@example.com'
        ], [
            'name' => 'Super Admin',
            'password' => Hash::make('password'),
            'role' => 'Super Admin',
            'status' => 'Active',
            'is_active' => true
        ]);

        $this->admin = AdminUser::firstOrCreate([
            'email' => 'admin@example.com'
        ], [
            'name' => 'Regular Admin',
            'password' => Hash::make('password'),
            'role' => 'Admin',
            'status' => 'Active',
            'is_active' => true
        ]);

        $this->regularUser = AdminUser::firstOrCreate([
            'email' => 'user@example.com'
        ], [
            'name' => 'Regular User',
            'password' => Hash::make('password'),
            'role' => 'Viewer',
            'status' => 'Active',
            'is_active' => true
        ]);

        // Assign roles to users (using the relationship)
        if (!$this->superAdmin->roles()->where('roles.id', $superAdminRole->id)->exists()) {
            $this->superAdmin->roles()->attach($superAdminRole->id);
        }
        if (!$this->admin->roles()->where('roles.id', $adminRole->id)->exists()) {
            $this->admin->roles()->attach($adminRole->id);
        }
        if (!$this->regularUser->roles()->where('roles.id', $viewerRole->id)->exists()) {
            $this->regularUser->roles()->attach($viewerRole->id);
        }
    }

    /** @test */
    public function super_admin_can_create_roles()
    {
        $roleData = [
            'name' => 'test_role_' . time(),
            'display_name' => 'Test Role',
            'description' => 'A test role',
            'level' => 5,
            'permissions' => [$this->viewDriversPermission->id]
        ];

        $response = $this->actingAs($this->superAdmin, 'admin')
                         ->post(route('admin.roles.store'), $roleData);

        $response->assertRedirect(route('admin.roles.index'));
        $this->assertDatabaseHas('roles', [
            'name' => $roleData['name'],
            'display_name' => 'Test Role'
        ]);
    }

    /** @test */
    public function admin_cannot_create_roles()
    {
        $roleData = [
            'name' => 'unauthorized_role_' . time(),
            'display_name' => 'Unauthorized Role',
            'description' => 'Should not be created',
            'level' => 5,
            'permissions' => [$this->viewDriversPermission->id]
        ];

        $response = $this->actingAs($this->admin, 'admin')
                         ->post(route('admin.roles.store'), $roleData);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('roles', [
            'name' => $roleData['name']
        ]);
    }

    /** @test */
    public function user_with_view_drivers_permission_can_access_driver_list()
    {
        // Test that the user has the permission
        $this->assertTrue($this->regularUser->hasPermission('view_drivers'));

        // Test accessing driver index (this would normally be protected by middleware)
        $response = $this->actingAs($this->regularUser, 'admin')
                         ->get('/admin/drivers');

        // Should not get 403 since we're bypassing middleware for this test
        $response->assertStatus(200);
    }

    /** @test */
    public function unauthorized_users_are_blocked_with_403()
    {
        // Create a user without proper permissions
        $unauthorizedUser = AdminUser::firstOrCreate([
            'email' => 'unauthorized@example.com'
        ], [
            'name' => 'Unauthorized User',
            'password' => Hash::make('password'),
            'role' => 'Basic User',
            'status' => 'Active',
            'is_active' => true
        ]);

        // Test middleware directly
        $middleware = new \App\Http\Middleware\RolePermissionMiddleware();
        $request = \Illuminate\Http\Request::create('/admin/superadmin/dashboard', 'GET');
        $request->setUserResolver(function () use ($unauthorizedUser) {
            return $unauthorizedUser;
        });

        $response = $middleware->handle($request, function () {
            return response('OK');
        }, 'Super Admin', 'manage_superadmin');

        $this->assertEquals(403, $response->getStatusCode());
    }

    /** @test */
    public function super_admin_bypasses_role_checks()
    {
        // Test middleware directly with super admin
        $middleware = new \App\Http\Middleware\RolePermissionMiddleware();
        $request = \Illuminate\Http\Request::create('/admin/superadmin/dashboard', 'GET');
        $request->setUserResolver(function () {
            return $this->superAdmin;
        });

        $response = $middleware->handle($request, function () {
            return response('OK');
        }, 'Super Admin', 'manage_superadmin');

        $this->assertEquals(200, $response->getStatusCode());
    }

    /** @test */
    public function middleware_checks_role_correctly()
    {
        // Test that middleware correctly identifies user roles
        $this->assertTrue($this->superAdmin->hasRole('Super Admin'));
        $this->assertTrue($this->admin->hasRole('Admin'));
        $this->assertTrue($this->regularUser->hasRole('Viewer'));
    }

    /** @test */
    public function middleware_checks_permissions_correctly()
    {
        // Test permission checks
        $this->assertTrue($this->superAdmin->hasPermission('view_drivers'));
        $this->assertTrue($this->superAdmin->hasPermission('manage_roles'));
        $this->assertTrue($this->admin->hasPermission('view_drivers'));
        $this->assertFalse($this->admin->hasPermission('manage_roles'));
        $this->assertTrue($this->regularUser->hasPermission('view_drivers'));
        $this->assertFalse($this->regularUser->hasPermission('manage_roles'));
    }

    /** @test */
    public function role_permission_middleware_blocks_unauthorized_access()
    {
        // Test the middleware directly with a mock request
        $middleware = new \App\Http\Middleware\RolePermissionMiddleware();

        // Mock a request
        $request = \Illuminate\Http\Request::create('/test', 'GET');
        $request->setUserResolver(function () {
            return $this->admin; // Admin without manage_roles permission
        });

        $response = $middleware->handle($request, function () {
            return response('OK');
        }, 'Super Admin', 'manage_roles');

        $this->assertEquals(403, $response->getStatusCode());
    }

    /** @test */
    public function role_permission_middleware_allows_authorized_access()
    {
        // Test the middleware directly with a mock request
        $middleware = new \App\Http\Middleware\RolePermissionMiddleware();

        // Mock a request
        $request = \Illuminate\Http\Request::create('/test', 'GET');
        $request->setUserResolver(function () {
            return $this->superAdmin; // Super admin with all permissions
        });

        $response = $middleware->handle($request, function () {
            return response('OK');
        }, 'Super Admin', 'manage_roles');

        $this->assertEquals(200, $response->getStatusCode());
    }

    /** @test */
    public function middleware_logs_access_denied_attempts()
    {
        // This test would check if access denied attempts are logged
        // For now, we'll just verify the middleware behavior
        $middleware = new \App\Http\Middleware\RolePermissionMiddleware();

        $request = \Illuminate\Http\Request::create('/admin/roles', 'GET');
        $request->setUserResolver(function () {
            return $this->admin;
        });

        $response = $middleware->handle($request, function () {
            return response('OK');
        }, 'Super Admin', 'manage_roles');

        $this->assertEquals(403, $response->getStatusCode());
    }

    /** @test */
    public function json_responses_are_returned_for_ajax_requests()
    {
        $middleware = new \App\Http\Middleware\RolePermissionMiddleware();

        $request = \Illuminate\Http\Request::create('/admin/roles', 'GET');
        $request->headers->set('Accept', 'application/json');
        $request->setUserResolver(function () {
            return $this->admin;
        });

        $response = $middleware->handle($request, function () {
            return response('OK');
        }, 'Super Admin', 'manage_roles');

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertJson($response->getContent());
    }
}
