<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Drivers;
use App\Models\Company;
use App\Repositories\DriverRepository;
use App\Repositories\CompanyRepository;
use App\Services\DriverService;
use App\Services\CompanyService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RepositoryPatternTest extends TestCase
{
    use RefreshDatabase;

    public function test_repositories_can_be_resolved_from_container()
    {
        $driverRepo = app(DriverRepository::class);
        $companyRepo = app(CompanyRepository::class);

        $this->assertInstanceOf(DriverRepository::class, $driverRepo);
        $this->assertInstanceOf(CompanyRepository::class, $companyRepo);
    }

    public function test_services_can_be_resolved_from_container()
    {
        $driverService = app(DriverService::class);
        $companyService = app(CompanyService::class);

        $this->assertInstanceOf(DriverService::class, $driverService);
        $this->assertInstanceOf(CompanyService::class, $companyService);
    }

    public function test_driver_repository_basic_crud_operations()
    {
        $repo = app(DriverRepository::class);

        // Create
        $driverData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'phone' => '08012345678',
            'status' => 'pending'
        ];

        $driver = $repo->create($driverData);
        $this->assertInstanceOf(Drivers::class, $driver);
        $this->assertEquals('John', $driver->first_name);
        $this->assertEquals('Doe', $driver->last_name);

        // Read
        $found = $repo->find($driver->id);
        $this->assertInstanceOf(Drivers::class, $found);
        $this->assertEquals($driver->id, $found->id);

        // Update
        $updated = $repo->update($driver->id, ['first_name' => 'Jane']);
        $this->assertEquals('Jane', $updated->first_name);

        // Delete
        $repo->delete($driver->id);
        $deleted = $repo->find($driver->id);
        $this->assertNull($deleted);
    }

    public function test_company_repository_basic_crud_operations()
    {
        $repo = app(CompanyRepository::class);

        // Create
        $companyData = [
            'name' => 'Test Company',
            'email' => 'company@example.com',
            'phone' => '08012345678',
            'status' => 'active',
            'company_id' => 'COMP001'
        ];

        $company = $repo->create($companyData);
        $this->assertInstanceOf(Company::class, $company);
        $this->assertEquals('Test Company', $company->name);

        // Read
        $found = $repo->find($company->id);
        $this->assertInstanceOf(Company::class, $found);
        $this->assertEquals($company->id, $found->id);

        // Update
        $updated = $repo->update($company->id, ['name' => 'Updated Company']);
        $this->assertEquals('Updated Company', $updated->name);

        // Delete
        $repo->delete($company->id);
        $deleted = $repo->find($company->id);
        $this->assertNull($deleted);
    }

    public function test_driver_repository_search_and_pagination()
    {
        $repo = app(DriverRepository::class);

        // Create test data
        for ($i = 1; $i <= 5; $i++) {
            $repo->create([
                'first_name' => "Driver{$i}",
                'last_name' => 'Test',
                'email' => "driver{$i}@example.com",
                'phone' => "0801234567{$i}",
                'status' => 'pending'
            ]);
        }

        // Test search
        $results = $repo->search(['first_name' => 'Driver1'], ['created_at' => 'desc'], 10);
        $this->assertCount(1, $results);
        $this->assertEquals('Driver1', $results->first()->first_name);

        // Test pagination
        $paginated = $repo->search([], ['created_at' => 'desc'], 2);
        $this->assertCount(2, $paginated);
        $this->assertTrue($paginated->hasPages());
    }

    public function test_company_repository_search_and_pagination()
    {
        $repo = app(CompanyRepository::class);

        // Create test data
        for ($i = 1; $i <= 5; $i++) {
            $repo->create([
                'name' => "Company{$i}",
                'email' => "company{$i}@example.com",
                'phone' => "0801234567{$i}",
                'status' => 'active',
                'company_id' => "COMP00{$i}"
            ]);
        }

        // Test search
        $results = $repo->search(['name' => 'Company1'], ['created_at' => 'desc'], 10);
        $this->assertCount(1, $results);
        $this->assertEquals('Company1', $results->first()->name);

        // Test pagination
        $paginated = $repo->search([], ['created_at' => 'desc'], 2);
        $this->assertCount(2, $paginated);
        $this->assertTrue($paginated->hasPages());
    }

    public function test_driver_service_integration_with_repository()
    {
        $service = app(DriverService::class);

        // Test that service can access repository methods
        $stats = $service->getDashboardStats();
        $this->assertIsArray($stats);
        $this->assertArrayHasKey('total_drivers', $stats);
        $this->assertArrayHasKey('pending_verification', $stats);
    }

    public function test_company_service_integration_with_repository()
    {
        $service = app(CompanyService::class);

        // Test that service can access repository methods
        $stats = $service->getCompanyStatistics();
        $this->assertIsArray($stats);
        $this->assertArrayHasKey('total_companies', $stats);
    }

    public function test_repository_statistics_methods()
    {
        $driverRepo = app(DriverRepository::class);
        $companyRepo = app(CompanyRepository::class);

        // Create test data
        $driverRepo->create([
            'first_name' => 'Test',
            'last_name' => 'Driver',
            'email' => 'test@example.com',
            'phone' => '08012345678',
            'status' => 'verified'
        ]);

        $companyRepo->create([
            'name' => 'Test Company',
            'email' => 'company@example.com',
            'phone' => '08012345678',
            'status' => 'active',
            'company_id' => 'COMP001'
        ]);

        // Test statistics
        $driverStats = $driverRepo->getStatistics();
        $this->assertIsArray($driverStats);
        $this->assertArrayHasKey('total', $driverStats);

        $companyStats = $companyRepo->getStatistics();
        $this->assertIsArray($companyStats);
        $this->assertArrayHasKey('total', $companyStats);
    }
}
