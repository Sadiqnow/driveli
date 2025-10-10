<?php

namespace Tests\Feature\Admin;

use App\Models\AdminUser;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_login_page()
    {
        $response = $this->get('/admin/login');

        $response->assertStatus(200);
        $response->assertViewIs('admin.login');
    }

    public function test_admin_can_login_with_valid_credentials()
    {
        $admin = AdminUser::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'status' => 'Active',
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/admin/dashboard');
        $this->assertAuthenticatedAs($admin, 'admin');
    }

    public function test_admin_cannot_login_with_invalid_credentials()
    {
        AdminUser::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => 'wrong_password',
        ]);

        $response->assertRedirect('/admin/login');
        $response->assertSessionHasErrors(['email']);
        $this->assertGuest('admin');
    }

    public function test_admin_cannot_login_when_inactive()
    {
        AdminUser::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'status' => 'Inactive',
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/admin/login');
        $this->assertGuest('admin');
    }

    public function test_admin_can_logout()
    {
        $admin = AdminUser::factory()->active()->create();

        $response = $this->actingAs($admin, 'admin')
                         ->post('/admin/logout');

        $response->assertRedirect('/admin/login');
        $this->assertGuest('admin');
    }

    public function test_admin_cannot_access_dashboard_when_not_authenticated()
    {
        $response = $this->get('/admin/dashboard');

        $response->assertRedirect('/admin/login');
    }

    public function test_admin_can_access_dashboard_when_authenticated()
    {
        $admin = AdminUser::factory()->active()->create();

        $response = $this->actingAs($admin, 'admin')
                         ->get('/admin/dashboard');

        $response->assertStatus(200);
    }

    public function test_authenticated_admin_cannot_access_login_page()
    {
        $admin = AdminUser::factory()->active()->create();

        $response = $this->actingAs($admin, 'admin')
                         ->get('/admin/login');

        $response->assertRedirect('/admin/dashboard');
    }

    public function test_admin_registration_creates_new_admin()
    {
        $response = $this->post('/admin/register', [
            'name' => 'Test Admin',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '+2348012345678',
            'role' => 'Admin',
        ]);

        $this->assertDatabaseHas('admin_users', [
            'name' => 'Test Admin',
            'email' => 'test@example.com',
            'role' => 'Admin',
        ]);

        $response->assertRedirect('/admin/dashboard');
    }

    public function test_admin_registration_requires_valid_data()
    {
        $response = $this->post('/admin/register', [
            'name' => '',
            'email' => 'invalid-email',
            'password' => '123',
            'password_confirmation' => '456',
        ]);

        $response->assertSessionHasErrors(['name', 'email', 'password']);
        $this->assertDatabaseCount('admin_users', 0);
    }

    public function test_admin_registration_prevents_duplicate_email()
    {
        AdminUser::factory()->create(['email' => 'existing@example.com']);

        $response = $this->post('/admin/register', [
            'name' => 'Test Admin',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertDatabaseCount('admin_users', 1);
    }

    public function test_login_validation_requires_email_and_password()
    {
        $response = $this->post('/admin/login', []);

        $response->assertSessionHasErrors(['email', 'password']);
    }

    public function test_login_validation_requires_valid_email_format()
    {
        $response = $this->post('/admin/login', [
            'email' => 'invalid-email',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_admin_can_view_register_page()
    {
        $response = $this->get('/admin/register');

        $response->assertStatus(200);
        $response->assertViewIs('admin.register');
    }

    public function test_last_login_is_updated_on_successful_login()
    {
        $admin = AdminUser::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'status' => 'Active',
            'last_login_at' => null,
        ]);

        $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => 'password123',
        ]);

        $admin->refresh();
        $this->assertNotNull($admin->last_login_at);
    }
}