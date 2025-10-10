# ✅ **POST-MIGRATION VALIDATION CHECKLIST**

## 🗂️ **DATA INTEGRITY CHECKS**

### **1. Record Count Validation**
```sql
-- Verify no data loss during migration
SELECT 
    'drivers' as table_name,
    COUNT(*) as current_count,
    (SELECT record_count FROM migration_backups WHERE source_table = 'drivers' ORDER BY created_at DESC LIMIT 1) as backup_count
FROM drivers
UNION ALL
SELECT 
    'companies' as table_name,
    COUNT(*) as current_count,
    (SELECT record_count FROM migration_backups WHERE source_table = 'companies' ORDER BY created_at DESC LIMIT 1) as backup_count  
FROM companies;

-- Expected: current_count >= backup_count
```

### **2. Foreign Key Integrity**
```sql
-- Check all foreign keys are valid
SELECT 'Invalid nationality_id' as issue, COUNT(*) as count
FROM drivers d 
LEFT JOIN countries c ON d.nationality_id = c.id 
WHERE c.id IS NULL AND d.nationality_id IS NOT NULL

UNION ALL

SELECT 'Invalid state_of_origin_id' as issue, COUNT(*) as count
FROM drivers d 
LEFT JOIN states s ON d.state_of_origin_id = s.id 
WHERE s.id IS NULL AND d.state_of_origin_id IS NOT NULL

UNION ALL  

SELECT 'Invalid verified_by' as issue, COUNT(*) as count
FROM drivers d 
LEFT JOIN admin_users a ON d.verified_by = a.id 
WHERE a.id IS NULL AND d.verified_by IS NOT NULL;

-- Expected: All counts should be 0
```

### **3. Enum Value Validation**  
```sql
-- Check all enum values are valid
SELECT 'Invalid driver status' as issue, status, COUNT(*) as count
FROM drivers 
WHERE status NOT IN ('active', 'inactive', 'suspended', 'blocked')
GROUP BY status

UNION ALL

SELECT 'Invalid verification status' as issue, verification_status, COUNT(*) as count  
FROM drivers
WHERE verification_status NOT IN ('pending', 'verified', 'rejected', 'reviewing')
GROUP BY verification_status

UNION ALL

SELECT 'Invalid gender' as issue, gender, COUNT(*) as count
FROM drivers 
WHERE gender NOT IN ('Male', 'Female', 'Other')
GROUP BY gender;

-- Expected: No results (empty result set)
```

## 🏗️ **SCHEMA VALIDATION**

### **4. Table Structure Checks**
```sql
-- Verify all expected tables exist
SELECT 
    TABLE_NAME,
    TABLE_ROWS,
    DATA_LENGTH,
    INDEX_LENGTH
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME IN (
    'users', 'admin_users', 'companies', 'drivers',
    'company_requests', 'driver_matches', 'driver_documents',
    'driver_locations', 'driver_performance', 'countries', 'states'
)
ORDER BY TABLE_NAME;

-- Expected: All tables present with reasonable row counts
```

### **5. Index Verification**
```sql
-- Check critical indexes exist
SELECT 
    TABLE_NAME,
    INDEX_NAME,
    COLUMN_NAME,
    SEQ_IN_INDEX
FROM information_schema.STATISTICS 
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'drivers'
AND INDEX_NAME IN (
    'idx_drivers_status_active',
    'idx_drivers_verification', 
    'idx_drivers_location',
    'idx_drivers_phone',
    'idx_drivers_email'
)
ORDER BY INDEX_NAME, SEQ_IN_INDEX;

-- Expected: All critical indexes present
```

### **6. Foreign Key Constraints**
```sql
-- Verify foreign key constraints exist
SELECT 
    CONSTRAINT_NAME,
    TABLE_NAME,
    COLUMN_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM information_schema.KEY_COLUMN_USAGE 
WHERE TABLE_SCHEMA = DATABASE()
AND REFERENCED_TABLE_NAME IS NOT NULL
AND TABLE_NAME IN ('drivers', 'companies', 'driver_matches', 'driver_locations')
ORDER BY TABLE_NAME, CONSTRAINT_NAME;

-- Expected: All expected FK constraints present
```

## 🔧 **APPLICATION FUNCTIONALITY CHECKS**

### **7. Model Relationship Tests**
```bash
# Test Laravel model relationships work correctly
php artisan tinker
```

```php
// Test driver relationships
$driver = \App\Models\Driver::with(['nationality', 'locations', 'documents', 'performance'])->first();
echo "Driver: " . $driver->full_name . "\n";
echo "Country: " . $driver->nationality->name . "\n";  
echo "Locations: " . $driver->locations->count() . "\n";
echo "Documents: " . $driver->documents->count() . "\n";

// Test company relationships  
$company = \App\Models\Company::with(['requests', 'state'])->first();
echo "Company: " . $company->name . "\n";
echo "Requests: " . $company->requests->count() . "\n";

// Test matching relationships
$match = \App\Models\DriverMatch::with(['driver', 'companyRequest'])->first();
if ($match) {
    echo "Match: " . $match->driver->full_name . " -> " . $match->companyRequest->title . "\n";
}
```

### **8. API Endpoint Validation**  
```bash
# Test critical API endpoints still work
curl -X GET "http://localhost/api/drivers?limit=10" -H "Accept: application/json"
curl -X GET "http://localhost/api/companies?limit=10" -H "Accept: application/json"  
curl -X GET "http://localhost/api/drivers/1" -H "Accept: application/json"

# Expected: Valid JSON responses with correct data structure
```

### **9. Admin Dashboard Tests**
```bash
# Test admin functionality
curl -X GET "http://localhost/admin/dashboard" -H "Accept: text/html"
curl -X GET "http://localhost/admin/drivers" -H "Accept: text/html"
curl -X GET "http://localhost/admin/companies" -H "Accept: text/html"

# Expected: Pages load without errors, data displays correctly
```

## 📊 **PERFORMANCE VALIDATION**

### **10. Query Performance Tests**
```sql
-- Test critical query performance (should be under 100ms)
EXPLAIN SELECT * FROM drivers 
WHERE status = 'active' 
AND verification_status = 'verified' 
LIMIT 20;

EXPLAIN SELECT d.*, c.name as country_name 
FROM drivers d 
JOIN countries c ON d.nationality_id = c.id 
WHERE d.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY);

EXPLAIN SELECT cr.*, c.name as company_name 
FROM company_requests cr 
JOIN companies c ON cr.company_id = c.id 
WHERE cr.status = 'active' 
ORDER BY cr.created_at DESC 
LIMIT 50;

-- Expected: All queries use indexes (key column shows index name, not NULL)
```

### **11. Index Usage Analysis**
```sql
-- Check index efficiency
SELECT 
    OBJECT_SCHEMA,
    OBJECT_NAME,
    INDEX_NAME,
    COUNT_FETCH,
    COUNT_INSERT, 
    COUNT_UPDATE,
    COUNT_DELETE
FROM performance_schema.table_io_waits_summary_by_index_usage 
WHERE OBJECT_SCHEMA = DATABASE()
AND OBJECT_NAME IN ('drivers', 'companies', 'company_requests')
ORDER BY COUNT_FETCH DESC;

-- Expected: High COUNT_FETCH on critical indexes
```

## 🔍 **DATA QUALITY VALIDATION**

### **12. Profile Completion Accuracy**
```sql  
-- Verify profile completion percentages are reasonable
SELECT 
    profile_completion_percentage,
    COUNT(*) as driver_count,
    ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM drivers), 2) as percentage
FROM drivers 
GROUP BY profile_completion_percentage
ORDER BY profile_completion_percentage;

-- Expected: Reasonable distribution, no negative values or > 100%
```

### **13. Phone Number Validation**
```sql
-- Check phone number formats
SELECT 
    'Invalid phone format' as issue,
    phone,
    COUNT(*) as count
FROM drivers 
WHERE phone IS NOT NULL 
AND phone NOT REGEXP '^\\+?[0-9]{10,15}$'
GROUP BY phone
LIMIT 10;

-- Expected: Few or no invalid phone numbers
```

### **14. Email Validation**  
```sql
-- Check email formats
SELECT 
    'Invalid email format' as issue,
    email, 
    COUNT(*) as count
FROM drivers 
WHERE email IS NOT NULL
AND email NOT REGEXP '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\\.[A-Za-z]{2,}$'
GROUP BY email
LIMIT 10;

-- Expected: Few or no invalid emails
```

## 🎯 **MVC STRUCTURE VALIDATION**

### **15. Model-Table Alignment**
```bash
# Verify models correctly map to tables
php artisan tinker
```

```php
// Test each model maps to correct table
echo "Driver table: " . (new \App\Models\Driver)->getTable() . "\n";
echo "Company table: " . (new \App\Models\Company)->getTable() . "\n";  
echo "CompanyRequest table: " . (new \App\Models\CompanyRequest)->getTable() . "\n";
echo "DriverMatch table: " . (new \App\Models\DriverMatch)->getTable() . "\n";

// Test fillable/guarded attributes
$driver = new \App\Models\Driver;
print_r($driver->getFillable());
print_r($driver->getGuarded());

// Expected: Correct table names, reasonable fillable/guarded arrays
```

### **16. Controller-Model Integration**  
```bash
# Test controllers work with new models
php artisan route:list --name=admin.drivers
php artisan route:list --name=admin.companies

# Test sample controller methods
curl -X GET "http://localhost/admin/drivers/1/edit" -H "Accept: text/html"

# Expected: Routes exist, controllers load without errors
```

### **17. View-Model Integration**
```bash  
# Test views render with new model structure
php artisan view:clear
php artisan view:cache

# Check critical views load
curl -X GET "http://localhost/admin/drivers" -H "Accept: text/html" | grep -i "error"
curl -X GET "http://localhost/admin/companies" -H "Accept: text/html" | grep -i "error"

# Expected: No errors in HTML output
```

## 🚨 **ROLLBACK READINESS**

### **18. Backup Verification**
```sql
-- Verify backups are accessible and complete
SELECT 
    backup_name,
    source_table,
    backup_table,
    record_count,
    created_at
FROM migration_backups 
ORDER BY created_at DESC;

-- Test backup table accessibility  
SELECT COUNT(*) FROM drivers_backup_final_20250115;

-- Expected: All backup tables exist and are queryable
```

### **19. Rollback Test (Staging Only)**
```bash
# Test rollback procedures in staging
php artisan migrate:status
php artisan migrate:rollback --step=1

# Verify application still works
curl -X GET "http://staging.drivelink.com/api/drivers?limit=5"

# Re-migrate  
php artisan migrate

# Expected: Clean rollback and re-migration without errors
```

## 📈 **SUCCESS CRITERIA**

### **Migration is Successful If:**
- [ ] ✅ All data integrity checks pass (0 invalid records)
- [ ] ✅ All expected tables and indexes exist  
- [ ] ✅ Foreign key constraints are properly set
- [ ] ✅ Laravel models work correctly with new schema
- [ ] ✅ API endpoints return correct data structure
- [ ] ✅ Admin dashboard loads and functions properly
- [ ] ✅ Query performance meets benchmarks (<100ms)
- [ ] ✅ Profile completion percentages are reasonable
- [ ] ✅ Phone/email formats are valid
- [ ] ✅ Backup tables are complete and accessible
- [ ] ✅ No application errors in logs

### **If Any Check Fails:**
1. **Stop deployment immediately**
2. **Review migration logs**  
3. **Execute rollback procedure**
4. **Fix issues in staging environment**
5. **Re-test all validations**
6. **Re-attempt migration**

---

## 🎉 **COMPLETION VERIFICATION**

```bash
# Final verification command
php artisan drivelink:verify-migration

# This should be a custom command that runs all checks
# and reports overall migration success/failure
```

**Expected Output:**
```
✅ DriveLink Schema Migration Verification Complete

📊 Summary:
- Tables: 23/23 ✅
- Data Integrity: PASSED ✅  
- Foreign Keys: 15/15 ✅
- Indexes: 45/45 ✅
- Model Tests: PASSED ✅
- API Tests: PASSED ✅
- Performance: PASSED ✅

🎯 Result: MIGRATION SUCCESSFUL ✅

Next Steps:
1. Monitor application performance
2. Clean up backup tables after 30 days  
3. Update documentation
4. Train team on new schema structure
```