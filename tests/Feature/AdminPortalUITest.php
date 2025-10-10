<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\AdminUser;
use App\Models\DriverNormalized;
use App\Models\CompanyRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class AdminPortalUITest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = AdminUser::factory()->create([
            'role' => 'Admin',
            'status' => 'Active'
        ]);
    }

    public function test_admin_login_page_loads()
    {
        $response = $this->get('/admin/login');
        $response->assertStatus(200);
        $response->assertSee('Admin Login');
    }

    public function test_admin_dashboard_loads_with_stats()
    {
        $response = $this->actingAs($this->admin, 'admin')->get('/admin/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Dashboard');
        $response->assertSee('Total Drivers');
        $response->assertSee('Active Drivers');
    }

    public function test_admin_drivers_page_loads()
    {
        $response = $this->actingAs($this->admin, 'admin')->get('/admin/drivers');
        $response->assertStatus(200);
        $response->assertSee('Driver Management');
    }

    public function test_admin_requests_page_loads()
    {
        $response = $this->actingAs($this->admin, 'admin')->get('/admin/requests');
        $response->assertStatus(200);
        $response->assertSee('Company Requests');
    }

    public function test_admin_matching_page_loads()
    {
        $response = $this->actingAs($this->admin, 'admin')->get('/admin/matching');
        $response->assertStatus(200);
        $response->assertSee('Matching System');
    }

    public function test_admin_notifications_page_loads()
    {
        $response = $this->actingAs($this->admin, 'admin')->get('/admin/notifications');
        $response->assertStatus(200);
        $response->assertSee('Notification Center');
    }

    public function test_admin_reports_page_loads()
    {
        $response = $this->actingAs($this->admin, 'admin')->get('/admin/reports');
        $response->assertStatus(200);
        $response->assertSee('Reports & Analytics');
    }

    public function test_admin_commissions_page_loads()
    {
        $response = $this->actingAs($this->admin, 'admin')->get('/admin/commissions');
        $response->assertStatus(200);
        $response->assertSee('Commission Management');
    }

    public function test_admin_verification_page_loads()
    {
        $response = $this->actingAs($this->admin, 'admin')->get('/admin/verification/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Verification Dashboard');
    }

    public function test_admin_companies_page_loads()
    {
        $response = $this->actingAs($this->admin, 'admin')->get('/admin/companies');
        $response->assertStatus(200);
        $response->assertSee('Company Management');
    }

    public function test_admin_unauthenticated_redirects_to_login()
    {
        $response = $this->get('/admin/dashboard');
        $response->assertRedirect('/admin/login');
    }

    public function test_admin_create_driver_form_loads()
    {
        $response = $this->actingAs($this->admin, 'admin')->get('/admin/drivers/create');
        $response->assertStatus(200);
        $response->assertSee('Create Driver');
    }

    public function test_admin_create_request_form_loads()
    {
        $response = $this->actingAs($this->admin, 'admin')->get('/admin/requests/create');
        $response->assertStatus(200);
        $response->assertSee('Create Company Request');
    }

    public function test_admin_ocr_dashboard_loads()
    {
        $response = $this->actingAs($this->admin, 'admin')->get('/admin/drivers/ocr-dashboard');
        $response->assertStatus(200);
        $response->assertSee('OCR Verification Dashboard');
    }

    public function test_admin_kyc_review_page_loads()
    {
        $response = $this->actingAs($this->admin, 'admin')->get('/admin/drivers/kyc-review');
        $response->assertStatus(200);
        $response->assertSee('KYC Review');
    }

    public function test_admin_notification_compose_page_loads()
    {
        $response = $this->actingAs($this->admin, 'admin')->get('/admin/notifications/compose');
        $response->assertStatus(200);
        $response->assertSee('Compose Notification');
    }

    public function test_admin_commission_report_loads()
    {
        $response = $this->actingAs($this->admin, 'admin')->get('/admin/reports/commission');
        $response->assertStatus(200);
        $response->assertSee('Commission Report');
    }

    public function test_admin_driver_performance_report_loads()
    {
        $response = $this->actingAs($this->admin, 'admin')->get('/admin/reports/driver-performance');
        $response->assertStatus(200);
        $response->assertSee('Driver Performance Report');
    }

    public function test_admin_financial_report_loads()
    {
        $response = $this->actingAs($this->admin, 'admin')->get('/admin/reports/financial');
        $response->assertStatus(200);
        $response->assertSee('Financial Report');
    }

    public function test_admin_company_activity_report_loads()
    {
        $response = $this->actingAs($this->admin, 'admin')->get('/admin/reports/company-activity');
        $response->assertStatus(200);
        $response->assertSee('Company Activity Report');
    }

    public function test_admin_verification_report_loads()
    {
        $response = $this->actingAs($this->admin, 'admin')->get('/admin/verification/report');
        $response->assertStatus(200);
        $response->assertSee('Verification Report');
    }

    public function test_admin_company_verification_page_loads()
    {
        $response = $this->actingAs($this->admin, 'admin')->get('/admin/companies/verification');
        $response->assertStatus(200);
        $response->assertSee('Company Verification');
    }

    public function test_admin_company_pending_page_loads()
    {
        $response = $this->actingAs($this->admin, 'admin')->get('/admin/companies/pending');
        $response->assertStatus(200);
        $response->assertSee('Pending Companies');
    }

    public function test_admin_create_company_form_loads()
    {
        $response = $this->actingAs($this->admin, 'admin')->get('/admin/companies/create');
        $response->assertStatus(200);
        $response->assertSee('Create Company');
    }

    public function test_admin_superadmin_dashboard_loads()
    {
        $superAdmin = AdminUser::factory()->create([
            'role' => 'Super Admin',
            'status' => 'Active'
        ]);

        $response = $this->actingAs($superAdmin, 'admin')->get('/admin/superadmin');
        $response->assertStatus(200);
        $response->assertSee('Super Admin Dashboard');
    }

    public function test_admin_superadmin_users_page_loads()
    {
        $superAdmin = AdminUser::factory()->create([
            'role' => 'Super Admin',
            'status' => 'Active'
        ]);

        $response = $this->actingAs($superAdmin, 'admin')->get('/admin/superadmin/users');
        $response->assertStatus(200);
        $response->assertSee('User Management');
    }

    public function test_admin_superadmin_audit_logs_page_loads()
    {
        $superAdmin = AdminUser::factory()->create([
            'role' => 'Super Admin',
            'status' => 'Active'
        ]);

        $response = $this->actingAs($superAdmin, 'admin')->get('/admin/superadmin/audit-logs');
        $response->assertStatus(200);
        $response->assertSee('Audit Logs');
    }

    public function test_admin_superadmin_settings_page_loads()
    {
        $superAdmin = AdminUser::factory()->create([
            'role' => 'Super Admin',
            'status' => 'Active'
        ]);

        $response = $this->actingAs($superAdmin, 'admin')->get('/admin/superadmin/settings');
        $response->assertStatus(200);
        $response->assertSee('System Settings');
    }
}
