# 🚀 **DRIVELINK MIGRATION ROLLOUT GUIDE**

## 🔒 **SAFE ROLLOUT PROCESS**

### **Phase 1: Local Testing (Week 1)**

#### **Environment Setup**
```bash
# 1. Create testing branch
git checkout -b migration/schema-consolidation

# 2. Backup local database
mysqldump -u username -p drivelink > backups/drivelink_pre_migration_$(date +%Y%m%d).sql

# 3. Set up migration testing environment
cp .env .env.migration
# Update .env.migration with test database credentials
```

#### **Local Migration Testing**
```bash
# Test migrations step by step
php artisan migrate:status
php artisan migrate --step --env=migration

# Verify each step
php artisan tinker
>>> \App\Models\Driver::count()
>>> \App\Models\Company::count()
>>> \Schema::getColumnListing('drivers')
```

#### **Rollback Testing**
```bash
# Test rollback capability
php artisan migrate:rollback --step=1
php artisan migrate:rollback --step=3

# Re-run migrations
php artisan migrate --env=migration
```

### **Phase 2: Staging Deployment (Week 2)**

#### **Pre-Deployment**
```bash
# 1. Full database backup
pg_dump drivelink_staging > staging_backup_$(date +%Y%m%d_%H%M).sql

# 2. Run in maintenance mode
php artisan down --render="errors::503"

# 3. Verify backup integrity
mysql -u username -p drivelink_staging < staging_backup_test.sql
```

#### **Staged Migration**
```bash
# Run migrations in batches
php artisan migrate:install
php artisan migrate --step=5  # First 5 migrations
php artisan migrate --step=5  # Next 5 migrations
php artisan migrate            # Remaining migrations

# Verify at each step
php artisan migration:status
```

#### **Application Testing**
```bash
# 1. Bring app back online
php artisan up

# 2. Run comprehensive tests
php artisan test --testsuite=Feature
php artisan test --testsuite=Integration

# 3. Performance testing
ab -n 1000 -c 10 http://staging.drivelink.com/api/drivers
```

### **Phase 3: Production Deployment (Week 3)**

#### **Pre-Production Checklist**
- [ ] All staging tests passed
- [ ] Performance benchmarks met
- [ ] Rollback procedures tested
- [ ] Team trained on new schema
- [ ] Monitoring alerts configured

#### **Production Migration**
```bash
# 1. Schedule maintenance window (2-4 AM recommended)
php artisan down --message="Database maintenance in progress"

# 2. Final backup
mysqldump --single-transaction drivelink > prod_backup_$(date +%Y%m%d_%H%M).sql

# 3. Run migrations with monitoring
nohup php artisan migrate --force > migration.log 2>&1 &
tail -f migration.log

# 4. Verify migration success
php artisan migrate:status
php artisan tinker
>>> \App\Models\Driver::count()
>>> \DB::table('migration_backups')->latest()->first()

# 5. Bring application online
php artisan up
```

---

## 🔄 **BACKWARD COMPATIBILITY DURING MIGRATION**

### **Model Aliasing Strategy**
```php
// File: app/Models/DriverLegacy.php
class DriverLegacy extends Model
{
    protected $table = 'drivers_backup_final';
    
    // Legacy methods for compatibility
    public function getOldAttributes()
    {
        return $this->attributes;
    }
}

// File: app/Models/Driver.php  
class Driver extends DriverNormalized
{
    // New consolidated model
    protected $table = 'drivers';
    
    // Compatibility methods
    public function getAvailableAttribute()
    {
        return $this->status === 'active';
    }
    
    public function getLastNameAttribute()
    {
        return $this->surname;
    }
}
```

### **API Compatibility Layer**
```php
// File: app/Http/Controllers/API/CompatibilityController.php
class CompatibilityController extends Controller
{
    // Legacy API endpoints that map to new structure
    public function getLegacyDriver($id)
    {
        $driver = Driver::findOrFail($id);
        
        // Transform new structure to legacy format
        return response()->json([
            'driver_id' => $driver->driver_id,
            'first_name' => $driver->first_name,
            'last_name' => $driver->surname, // Map surname to last_name
            'status' => $driver->status === 'active' ? 'Available' : 'Not Available',
            'verification_status' => ucfirst($driver->verification_status),
            // ... other legacy field mappings
        ]);
    }
}
```

### **Database View for Legacy Queries**
```sql
-- Create view for legacy applications
CREATE VIEW drivers_legacy AS
SELECT 
    id,
    driver_id,
    first_name,
    surname as last_name,
    CASE 
        WHEN status = 'active' THEN 'Available'
        WHEN status = 'inactive' THEN 'Not Available'  
        WHEN status = 'suspended' THEN 'Suspended'
        ELSE 'Not Available'
    END as status,
    CASE
        WHEN verification_status = 'verified' THEN 'Verified'
        WHEN verification_status = 'rejected' THEN 'Rejected'
        ELSE 'Pending'
    END as verification_status,
    phone,
    email,
    created_at,
    updated_at
FROM drivers
WHERE deleted_at IS NULL;
```

---

## 🧪 **TESTING MIGRATIONS LOCALLY**

### **Unit Testing for Migrations**
```php
// File: tests/Feature/MigrationTest.php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

class MigrationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function drivers_table_has_correct_structure()
    {
        $columns = Schema::getColumnListing('drivers');
        
        $expectedColumns = [
            'id', 'driver_id', 'first_name', 'middle_name', 'surname',
            'phone', 'email', 'status', 'verification_status', 'created_at'
        ];
        
        foreach ($expectedColumns as $column) {
            $this->assertContains($column, $columns, "Column {$column} missing from drivers table");
        }
    }

    /** @test */
    public function foreign_keys_are_properly_set()
    {
        $this->assertTrue(Schema::hasTable('countries'));
        $this->assertTrue(Schema::hasTable('states'));
        $this->assertTrue(Schema::hasTable('drivers'));
        
        // Test foreign key constraint
        $driver = \App\Models\Driver::factory()->create([
            'nationality_id' => 1
        ]);
        
        $this->assertDatabaseHas('drivers', [
            'id' => $driver->id,
            'nationality_id' => 1
        ]);
    }

    /** @test */
    public function data_migration_preserves_relationships()
    {
        // Create test data before migration
        $originalCount = \App\Models\Driver::count();
        
        // Run specific migration
        $this->artisan('migrate', ['--path' => 'database/migrations/2025_01_15_200007_consolidate_drivers_table_final.php']);
        
        // Verify data integrity
        $newCount = \App\Models\Driver::count();
        $this->assertEquals($originalCount, $newCount, "Driver count mismatch after migration");
    }
}
```

### **Data Integrity Testing**
```php
// File: tests/Feature/DataIntegrityTest.php
<?php

namespace Tests\Feature;

class DataIntegrityTest extends TestCase
{
    /** @test */
    public function all_drivers_have_valid_foreign_keys()
    {
        $driversWithInvalidCountry = \App\Models\Driver::whereNotExists(function($query) {
            $query->select(DB::raw(1))
                  ->from('countries')
                  ->whereRaw('countries.id = drivers.nationality_id');
        })->count();
        
        $this->assertEquals(0, $driversWithInvalidCountry, "Found drivers with invalid nationality_id");
    }

    /** @test */
    public function enum_values_are_properly_migrated()
    {
        $invalidStatuses = \App\Models\Driver::whereNotIn('status', ['active', 'inactive', 'suspended', 'blocked'])->count();
        $this->assertEquals(0, $invalidStatuses, "Found drivers with invalid status values");
        
        $invalidVerificationStatuses = \App\Models\Driver::whereNotIn('verification_status', ['pending', 'verified', 'rejected', 'reviewing'])->count();
        $this->assertEquals(0, $invalidVerificationStatuses, "Found drivers with invalid verification_status values");
    }

    /** @test */
    public function profile_completion_calculated_correctly()
    {
        $driver = \App\Models\Driver::factory()->create([
            'first_name' => 'John',
            'surname' => 'Doe', 
            'phone' => '+234123456789',
            'email' => 'john@example.com',
            'date_of_birth' => '1990-01-01',
            'nin_number' => '12345678901',
            'license_number' => 'ABC123456789'
        ]);
        
        // Profile completion should be high with all these fields
        $this->assertGreaterThan(80, $driver->profile_completion_percentage);
    }
}
```

---

## 📋 **DATA MIGRATION NOTES (OLD → NEW)**

### **Critical Field Mappings**
```php
// drivers + drivers → drivers
'last_name' → 'surname'
'status: Available' → 'status: active'  
'status: Not Available' → 'status: inactive'
'verification_status: Verified' → 'verification_status: verified'
'verification_status: Pending' → 'verification_status: pending'
'nin' → 'nin_number'
'state' (string) → 'state_of_origin_id' (FK)
'lga' (string) → 'lga_of_origin_id' (FK)

// Performance data separation
'rating' → driver_performance.average_rating
'total_jobs' → driver_performance.total_jobs_completed  
'completed_jobs' → driver_performance.total_jobs_completed
'total_earnings' → driver_performance.total_earnings

// Address normalization  
'address' → driver_locations.address (type: 'residence')
'state' → driver_locations.state_id
'lga' → driver_locations.lga_id
```

### **Data Cleaning Rules**
```php
// Clean phone numbers
'phone' => preg_replace('/[^\d\+]/', '', $phone)

// Standardize names  
'first_name' => trim(ucfirst(strtolower($firstName)))
'surname' => trim(ucfirst(strtolower($surname)))

// Default values for missing data
'nationality_id' => 1 (Nigeria)
'kyc_status' => 'not_started'
'profile_completion_percentage' => calculated based on filled fields
'registration_source' => 'legacy_migration'

// Handle null/empty values
'middle_name' => $middleName ?: null
'nickname' => $nickname ?: null
'phone_2' => $phone2 ?: null
```

---

## ✅ **POST-MIGRATION CHECKLIST**

<function_calls>
<invoke name="TodoWrite">
<parameter name="todos">[{"content": "Create step-by-step migration execution plan", "status": "completed", "activeForm": "Creating step-by-step migration execution plan"}, {"content": "Design data migration strategy for existing data", "status": "completed", "activeForm": "Designing data migration strategy for existing data"}, {"content": "Create detailed Laravel migration files", "status": "completed", "activeForm": "Creating detailed Laravel migration files"}, {"content": "Design rollback safety and testing procedures", "status": "completed", "activeForm": "Designing rollback safety and testing procedures"}, {"content": "Create post-migration validation checklist", "status": "completed", "activeForm": "Creating post-migration validation checklist"}]