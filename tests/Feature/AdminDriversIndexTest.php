<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\AdminUser;
use App\Models\Drivers as Driver;
use App\Constants\DrivelinkConstants;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class AdminDriversIndexTest extends TestCase
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

    public function test_admin_drivers_index_loads_with_statistics()
    {
        // Create some test drivers with different statuses
        Driver::factory()->count(5)->create(['status' => DrivelinkConstants::DRIVER_STATUS_ACTIVE]);
        Driver::factory()->count(3)->create(['status' => DrivelinkConstants::DRIVER_STATUS_PENDING]);
        Driver::factory()->count(2)->create(['status' => DrivelinkConstants::DRIVER_STATUS_SUSPENDED]);
        Driver::factory()->count(4)->create(['verification_status' => DrivelinkConstants::VERIFICATION_STATUS_VERIFIED]);
        Driver::factory()->count(3)->create(['kyc_status' => DrivelinkConstants::KYC_STATUS_COMPLETED]);

        $response = $this->actingAs($this->admin, 'admin')->get('/admin/drivers');

        $response->assertStatus(200);
        $response->assertSee('Driver Management');
        $response->assertSee('Total Drivers');
        $response->assertSee('Active Drivers');
        $response->assertSee('Verified Drivers');
        $response->assertSee('KYC Completed');
    }

    public function test_drivers_index_displays_driver_cards()
    {
        $driver = Driver::factory()->create([
            'first_name' => 'John',
            'surname' => 'Doe',
            'email' => 'john@example.com',
            'status' => DrivelinkConstants::DRIVER_STATUS_ACTIVE,
            'verification_status' => DrivelinkConstants::VERIFICATION_STATUS_VERIFIED
        ]);

        $response = $this->actingAs($this->admin, 'admin')->get('/admin/drivers');

        $response->assertStatus(200);
        $response->assertSee('John Doe');
        $response->assertSee('john@example.com');
        $response->assertSee('Active');
        $response->assertSee('Verified');
    }

    public function test_drivers_index_search_functionality()
    {
        Driver::factory()->create(['first_name' => 'John', 'surname' => 'Doe']);
        Driver::factory()->create(['first_name' => 'Jane', 'surname' => 'Smith']);
        Driver::factory()->create(['first_name' => 'Bob', 'surname' => 'Johnson']);

        // Search for John
        $response = $this->actingAs($this->admin, 'admin')->get('/admin/drivers?search=John');

        $response->assertStatus(200);
        $response->assertSee('John Doe');
        $response->assertDontSee('Jane Smith');
        $response->assertDontSee('Bob Johnson');
    }

    public function test_drivers_index_filter_by_status()
    {
        Driver::factory()->create(['status' => DrivelinkConstants::DRIVER_STATUS_ACTIVE]);
        Driver::factory()->create(['status' => DrivelinkConstants::DRIVER_STATUS_PENDING]);
        Driver::factory()->create(['status' => DrivelinkConstants::DRIVER_STATUS_SUSPENDED]);

        // Filter by active status
        $response = $this->actingAs($this->admin, 'admin')->get('/admin/drivers?status=' . DrivelinkConstants::DRIVER_STATUS_ACTIVE);

        $response->assertStatus(200);
        $response->assertSee('Active');
        $response->assertDontSee('Pending');
        $response->assertDontSee('Suspended');
    }

    public function test_drivers_index_filter_by_verification_status()
    {
        Driver::factory()->create(['verification_status' => DrivelinkConstants::VERIFICATION_STATUS_VERIFIED]);
        Driver::factory()->create(['verification_status' => DrivelinkConstants::VERIFICATION_STATUS_PENDING]);
        Driver::factory()->create(['verification_status' => DrivelinkConstants::VERIFICATION_STATUS_REJECTED]);

        // Filter by verified status
        $response = $this->actingAs($this->admin, 'admin')->get('/admin/drivers?verification_status=' . DrivelinkConstants::VERIFICATION_STATUS_VERIFIED);

        $response->assertStatus(200);
        $response->assertSee('Verified');
        $response->assertDontSee('Pending');
        $response->assertDontSee('Rejected');
    }

    public function test_drivers_index_pagination()
    {
        // Create more drivers than the default page size (assuming 10 per page)
        Driver::factory()->count(25)->create();

        $response = $this->actingAs($this->admin, 'admin')->get('/admin/drivers');

        $response->assertStatus(200);
        // Should show pagination links
        $response->assertSee('pagination');
    }

    public function test_drivers_index_per_page_parameter()
    {
        Driver::factory()->count(50)->create();

        $response = $this->actingAs($this->admin, 'admin')->get('/admin/drivers?per_page=20');

        $response->assertStatus(200);
        // The response should contain pagination info, but we can't easily test the exact count without parsing HTML
        // This test ensures the parameter is accepted without errors
    }

    public function test_drivers_index_bulk_actions_modal()
    {
        Driver::factory()->count(5)->create();

        $response = $this->actingAs($this->admin, 'admin')->get('/admin/drivers');

        $response->assertStatus(200);
        $response->assertSee('Bulk Actions');
        $response->assertSee('Select All');
    }

    public function test_drivers_index_expandable_details()
    {
        $driver = Driver::factory()->create([
            'first_name' => 'Test',
            'surname' => 'Driver',
            'phone' => '08012345678',
            'date_of_birth' => '1990-01-01'
        ]);

        $response = $this->actingAs($this->admin, 'admin')->get('/admin/drivers');

        $response->assertStatus(200);
        $response->assertSee('Test Driver');
        // The expandable details should be present in the HTML structure
        $response->assertSee('collapse'); // Bootstrap collapse class
    }

    public function test_drivers_index_verification_buttons()
    {
        $pendingDriver = Driver::factory()->create([
            'verification_status' => DrivelinkConstants::VERIFICATION_STATUS_PENDING
        ]);

        $response = $this->actingAs($this->admin, 'admin')->get('/admin/drivers');

        $response->assertStatus(200);
        $response->assertSee('Verify');
        $response->assertSee('Reject');
    }

    public function test_drivers_index_empty_state()
    {
        // No drivers in database
        $response = $this->actingAs($this->admin, 'admin')->get('/admin/drivers');

        $response->assertStatus(200);
        $response->assertSee('No drivers found');
    }

    public function test_drivers_index_reset_filters()
    {
        Driver::factory()->count(5)->create();

        $response = $this->actingAs($this->admin, 'admin')->get('/admin/drivers?search=test&status=active');

        $response->assertStatus(200);
        $response->assertSee('Reset');
    }

    public function test_drivers_index_statistics_links()
    {
        Driver::factory()->count(5)->create(['status' => DrivelinkConstants::DRIVER_STATUS_ACTIVE]);

        $response = $this->actingAs($this->admin, 'admin')->get('/admin/drivers');

        $response->assertStatus(200);
        // Check that statistics boxes have links
        $response->assertSee('href="/admin/drivers?status=' . DrivelinkConstants::DRIVER_STATUS_ACTIVE . '"');
        $response->assertSee('href="/admin/drivers?verification_status=' . DrivelinkConstants::VERIFICATION_STATUS_VERIFIED . '"');
    }
}
