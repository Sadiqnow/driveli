# Database Structure Upgrade Plan

## Missing Tables to Create

1. role_user - Pivot table for users and roles
2. permission_role - Pivot table for permissions and roles
3. driver_profiles - Table for driver profile information
4. driver_company_relations - Pivot table for drivers and companies
5. admin_actions - Table for logging admin actions
6. verifications - Table for verification processes
7. activity_logs - Table for general activity logging
8. deactivation_requests - Table for deactivation requests
9. otp_notifications - Table for OTP notifications

## Steps

- [x] Create migration for role_user table
- [x] Create migration for permission_role table
- [x] Create migration for driver_profiles table
- [x] Create migration for driver_company_relations table
- [x] Create migration for admin_actions table
- [x] Create migration for verifications table
- [x] Create migration for activity_logs table
- [x] Create migration for deactivation_requests table
- [x] Create migration for otp_notifications table
- [x] Run all migrations
- [x] Verify tables exist
