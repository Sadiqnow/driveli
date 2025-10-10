# TODO: Seed Banks Table and Remove Deletion Triggers

## Steps to Complete
- [x] Seed the banks table by running `php artisan db:seed --class=BanksSeeder` to populate with Nigerian banks data.
- [x] Edit `tests/Feature/DriverKycAndApprovalTest.php` to remove `RefreshDatabase` trait and enhance `seedLookupTables()` to explicitly seed banks per test, preventing full DB truncation.
- [x] Edit `test_api_simple.php` to remove or comment out any truncate statements that could affect lookup tables like banks.
- [x] Run verification script `verify_seeding.php` to confirm banks data (should show 21 banks).
- [x] Run affected tests (e.g., `php artisan test --filter=DriverKycAndApprovalTest`) to ensure no deletion occurs during testing.
- [x] Update TODO.md to mark completed steps.
