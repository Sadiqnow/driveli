# Driver Data Safety and Transactional Table Architecture

## Algorithmic Explanation of Driver Onboarding, Profile Updates, and Management Workflows

### **Core Architecture Overview**

The driver data architecture implements a **Blackbox Core + Transactional Tables** pattern:

```
drivers (Blackbox Core - 26 fields)
├── driver_id (unique identifier)
├── personal details (name, contact, auth)
├── status & verification fields
└── audit timestamps

Transactional Tables (linked via driver_id):
├── driver_next_of_kin (personal info)
├── driver_performance (professional metrics)
├── driver_banking_details (financial info)
├── driver_documents (KYC & uploads)
├── driver_matches (job assignments)
└── driver_category_requirements (compliance)
```

---

## **Algorithm 1: Driver Onboarding Workflow**

### **Step-by-Step Process:**

```php
/**
 * Driver Onboarding Algorithm
 * Time Complexity: O(1) for core creation, O(n) for document processing
 * Space Complexity: O(1) amortized (fixed transactional records)
 */
public function onboardDriver(array $input): Driver
{
    // Step 1: Validate Core Data (Blackbox Safety)
    $coreData = $this->validateCoreFields($input);

    // Step 2: Create Blackbox Core Record
    $driver = Driver::create([
        'driver_id' => 'DRV-' . strtoupper(uniqid()),
        'first_name' => $coreData['first_name'],
        'surname' => $coreData['surname'],
        'email' => $coreData['email'],
        'phone' => $coreData['phone'],
        'password' => bcrypt($coreData['password']),
        'status' => 'inactive',
        'verification_status' => 'pending',
        'kyc_status' => 'pending',
        'is_active' => false,
        'is_available' => false,
    ]);

    // Step 3: Initialize Transactional Records
    $this->initializeTransactionalData($driver, $input);

    // Step 4: Calculate Profile Completion
    $completion = $this->calculateProfileCompletion($driver);
    $driver->update(['profile_completion_percentage' => $completion]);

    // Step 5: Trigger KYC Workflow
    $this->initiateKycProcess($driver);

    return $driver;
}

/**
 * Initialize Transactional Data
 * Creates empty records for all required transactional tables
 */
private function initializeTransactionalData(Driver $driver, array $input): void
{
    // Personal Info (Next of Kin)
    if (isset($input['emergency_contact'])) {
        $driver->personalInfo()->create($input['emergency_contact']);
    }

    // Banking Details
    if (isset($input['banking'])) {
        $driver->bankingDetails()->create($input['banking']);
    }

    // Performance Metrics (always initialize)
    $driver->performance()->create([
        'total_jobs_completed' => 0,
        'average_rating' => 0.00,
        'total_earnings' => 0.00,
    ]);

    // Documents
    if (isset($input['documents'])) {
        foreach ($input['documents'] as $doc) {
            $driver->documents()->create($doc);
        }
    }
}
```

### **Data Flow During Onboarding:**

```
Input Data → Validation → Blackbox Core Creation → Transactional Initialization → Completion Calculation
     ↓              ↓              ↓                        ↓                        ↓
  Raw Form      Sanitize      drivers table          Related tables          Update %
  Data          & Filter      (26 fields)             (variable)              drivers table
```

---

## **Algorithm 2: Profile Update Workflow**

### **Step-by-Step Process:**

```php
/**
 * Profile Update Algorithm
 * Ensures Blackbox integrity while allowing transactional flexibility
 * Time Complexity: O(1) for core updates, O(m) for transactional updates
 */
public function updateDriverProfile(Driver $driver, array $updates): bool
{
    DB::beginTransaction();

    try {
        // Step 1: Separate Core vs Transactional Updates
        $coreUpdates = $this->extractCoreUpdates($updates);
        $transactionalUpdates = $this->extractTransactionalUpdates($updates);

        // Step 2: Update Blackbox Core (restricted fields only)
        if (!empty($coreUpdates)) {
            $driver->update($coreUpdates);
        }

        // Step 3: Update Transactional Tables (flexible)
        $this->updateTransactionalTables($driver, $transactionalUpdates);

        // Step 4: Recalculate Profile Completion
        $newCompletion = $this->calculateProfileCompletion($driver);
        $driver->update(['profile_completion_percentage' => $newCompletion]);

        // Step 5: Trigger Status Updates if Needed
        $this->checkAndUpdateDriverStatus($driver);

        DB::commit();
        return true;

    } catch (\Exception $e) {
        DB::rollBack();
        return false;
    }
}

/**
 * Extract Core Updates (Blackbox Safety)
 * Only allows updates to predefined safe fields
 */
private function extractCoreUpdates(array $updates): array
{
    $allowedCoreFields = [
        'first_name', 'middle_name', 'surname', 'email', 'phone', 'phone_2',
        'status', 'verification_status', 'is_active', 'is_available',
        'kyc_status', 'kyc_retry_count'
    ];

    return array_intersect_key($updates, array_flip($allowedCoreFields));
}

/**
 * Update Transactional Tables
 * Allows flexible updates to all related tables
 */
private function updateTransactionalTables(Driver $driver, array $updates): void
{
    // Personal Information
    if (isset($updates['personal_info'])) {
        $driver->personalInfo()->updateOrCreate(
            ['driver_id' => $driver->id],
            $updates['personal_info']
        );
    }

    // Banking Details
    if (isset($updates['banking'])) {
        $driver->bankingDetails()->updateOrCreate(
            ['driver_id' => $driver->id, 'is_primary' => true],
            $updates['banking']
        );
    }

    // Performance Data
    if (isset($updates['performance'])) {
        $driver->performance()->updateOrCreate(
            ['driver_id' => $driver->id],
            $updates['performance']
        );
    }

    // Documents
    if (isset($updates['documents'])) {
        foreach ($updates['documents'] as $docType => $docData) {
            $driver->documents()->updateOrCreate(
                ['driver_id' => $driver->id, 'document_type' => $docType],
                $docData
            );
        }
    }
}
```

### **Update Flow:**

```
Update Request → Field Classification → Core Update (drivers) → Transactional Updates → Status Check
      ↓                ↓                      ↓                        ↓                    ↓
  Mixed Data      Core vs            Restricted fields       Flexible fields       Auto-update
  (all fields)    Transactional       (26 fields)             (variable)            status/KYC
```

---

## **Algorithm 3: Driver Management Workflow**

### **Step-by-Step Process:**

```php
/**
 * Driver Management Algorithm
 * Handles verification, matching, and performance tracking
 * Time Complexity: O(n) for batch operations, O(1) for single operations
 */
public function manageDriverOperations(Driver $driver, string $operation, array $params = []): array
{
    switch ($operation) {
        case 'verify':
            return $this->verifyDriver($driver, $params);

        case 'match':
            return $this->matchDriverToJob($driver, $params);

        case 'update_performance':
            return $this->updateDriverPerformance($driver, $params);

        case 'suspend':
            return $this->suspendDriver($driver, $params);

        default:
            throw new \InvalidArgumentException("Unknown operation: {$operation}");
    }
}

/**
 * Driver Verification Algorithm
 */
private function verifyDriver(Driver $driver, array $params): array
{
    // Update core verification status
    $driver->update([
        'verification_status' => $params['status'],
        'verified_at' => now(),
        'verified_by' => $params['admin_id'],
        'verification_notes' => $params['notes'] ?? null,
    ]);

    // Update KYC status in core table
    if (isset($params['kyc_status'])) {
        $driver->update(['kyc_status' => $params['kyc_status']]);
    }

    // Update document verification status in transactional table
    if (isset($params['document_updates'])) {
        foreach ($params['document_updates'] as $docType => $status) {
            $driver->documents()
                ->where('document_type', $docType)
                ->update(['verification_status' => $status]);
        }
    }

    return ['status' => 'verified', 'driver' => $driver];
}

/**
 * Driver Matching Algorithm
 */
private function matchDriverToJob(Driver $driver, array $params): array
{
    // Create match record in transactional table
    $match = DriverMatch::create([
        'match_id' => 'MATCH-' . strtoupper(uniqid()),
        'company_request_id' => $params['company_request_id'],
        'driver_id' => $driver->id,
        'status' => 'pending',
        'commission_rate' => $params['commission_rate'] ?? 15.0,
        'matched_at' => now(),
        'matched_by_admin' => $params['admin_id'],
    ]);

    // Update driver availability in core table
    $driver->update(['is_available' => false]);

    return ['status' => 'matched', 'match' => $match];
}

/**
 * Performance Update Algorithm
 */
private function updateDriverPerformance(Driver $driver, array $params): array
{
    // Update performance metrics in transactional table
    $performance = $driver->performance()->first();

    if ($performance) {
        $performance->update([
            'total_jobs_completed' => $params['jobs_completed'] ?? $performance->total_jobs_completed,
            'average_rating' => $params['rating'] ?? $performance->average_rating,
            'total_earnings' => $params['earnings'] ?? $performance->total_earnings,
            'last_job_completed_at' => $params['last_job_at'] ?? $performance->last_job_completed_at,
        ]);
    }

    // Update driver status based on performance
    $this->updateDriverStatusBasedOnPerformance($driver, $performance);

    return ['status' => 'updated', 'performance' => $performance];
}
```

### **Management Flow:**

```
Management Request → Operation Type → Core Table Update → Transactional Updates → Status Sync
       ↓                   ↓                ↓                      ↓                    ↓
   Operation          Switch Case      drivers table         Related tables         Auto-sync
   (verify/match/     (verify,         (status, KYC)         (documents,           availability
    performance)      match, perf)     verification)         matches, perf)         status
```

---

## **Algorithm 4: Data Integrity and Cascade Operations**

### **Cascade Update Algorithm:**

```php
/**
 * Cascade Update Algorithm
 * Ensures data consistency across all tables
 * Only cascades FROM core TO transactional, never reverse
 */
public function cascadeDriverUpdates(Driver $driver, array $changes): void
{
    // Core field changes that affect transactional tables
    $cascadeRules = [
        'status' => ['action' => 'update_status_dependents'],
        'verification_status' => ['action' => 'update_verification_dependents'],
        'is_active' => ['action' => 'update_active_dependents'],
        'kyc_status' => ['action' => 'update_kyc_dependents'],
    ];

    foreach ($changes as $field => $newValue) {
        if (isset($cascadeRules[$field])) {
            $action = $cascadeRules[$field]['action'];
            $this->$action($driver, $newValue);
        }
    }
}

/**
 * Update dependents when driver status changes
 */
private function update_status_dependents(Driver $driver, string $newStatus): void
{
    switch ($newStatus) {
        case 'suspended':
            // Mark all active matches as cancelled
            $driver->matches()
                ->where('status', 'accepted')
                ->update(['status' => 'cancelled', 'cancelled_at' => now()]);
            break;

        case 'inactive':
            // Set availability to false
            $driver->update(['is_available' => false]);
            break;
    }
}

/**
 * Update dependents when verification status changes
 */
private function update_verification_dependents(Driver $driver, string $newStatus): void
{
    if ($newStatus === 'verified') {
        // Mark banking details as verified if not already
        $driver->bankingDetails()
            ->where('is_verified', false)
            ->update(['is_verified' => true, 'verified_at' => now()]);
    }
}
```

---

## **Migration Examples**

### **1. Create Driver with Transactional Data:**
```php
// Migration: Create driver and initialize transactional tables
$driver = Driver::create([
    'driver_id' => 'DRV001',
    'first_name' => 'John',
    'surname' => 'Doe',
    'email' => 'john@example.com',
    'phone' => '+2348012345678',
    'status' => 'active',
    'verification_status' => 'verified',
]);

// Create transactional records
$driver->personalInfo()->create(['name' => 'Jane Doe', 'relationship' => 'Sister']);
$driver->performance()->create(['total_jobs_completed' => 0]);
$driver->bankingDetails()->create(['account_number' => '1234567890']);
```

### **2. Update Profile Information:**
```php
// Update core fields (restricted)
$driver->update(['first_name' => 'Johnny']);

// Update transactional fields (flexible)
$driver->personalInfo()->update(['phone' => '+2348098765432']);
$driver->performance()->update(['total_earnings' => 50000.00]);
```

### **3. Relationship Queries:**
```php
// Get driver with all transactional data
$driver = Driver::with(['personalInfo', 'performance', 'bankingDetails', 'documents'])->find(1);

// Access transactional data
$earnings = $driver->performance->total_earnings;
$nextOfKin = $driver->personalInfo->name;
$account = $driver->primaryBankingDetail->account_number;
```

---

## **Performance Characteristics**

- **Read Operations:** O(1) for core data, O(n) for transactional relationships
- **Write Operations:** O(1) for core updates, O(m) for transactional updates
- **Search Operations:** O(log n) with proper indexing on driver_id
- **Cascade Operations:** O(k) where k is number of dependent records
- **Memory Usage:** Minimal - lazy loading prevents N+1 query issues

---

## **Security and Data Integrity**

1. **Blackbox Protection:** Core driver data immutable except through controlled APIs
2. **Transactional Flexibility:** Related data can be updated without affecting core integrity
3. **Audit Trail:** All changes logged with timestamps and admin attribution
4. **Foreign Key Constraints:** Maintain referential integrity across all tables
5. **Soft Deletes:** Preserve data integrity even after logical deletion

This architecture ensures maximum data safety while providing the flexibility needed for complex driver management workflows.
