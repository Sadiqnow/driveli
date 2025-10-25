# Company Portal Module Implementation TODO

## Phase 1: Database Layer
- [x] Create migration for company_profiles table
- [x] Create migration for company_members table
- [x] Create migration for fleets table
- [x] Create migration for vehicles table
- [x] Create migration for company_matches table
- [x] Create migration for company_invoices table
- [x] Create seeders for demo data
- [x] Run php artisan migrate

## Phase 2: Backend Logic
- [x] Create Company model with relationships
- [x] Create CompanyProfile model
- [x] Create CompanyMember model
- [x] Create Fleet model
- [x] Create Vehicle model
- [x] Create CompanyRequest model (exists, update if needed)
- [x] Create CompanyMatch model
- [x] Create CompanyInvoice model
- [x] Implement CompanyService
- [x] Implement MatchingService
- [x] Implement FleetService
- [x] Implement BillingService

## Phase 3: API Layer
- [x] Create CompanyController
- [x] Create CompanyRequestController
- [x] Create CompanyMatchController
- [x] Create FleetController
- [x] Create VehicleController
- [x] Create InvoiceController
- [x] Add routes to routes/api.php
- [x] Create policies for access control
- [x] Integrate Laravel Sanctum for company auth

## Phase 4: UI/UX Frontend
- [x] Create company signup/login Blade pages
- [x] Create company dashboard with KPIs and charts
- [x] Create post request wizard (multi-step)
- [x] Create matches list & actions page
- [x] Create fleet & vehicle management pages
- [x] Create billing/invoices page
- [x] Create settings/profile page
- [x] Apply Bootstrap 5 and responsive design
- [x] Use Laravel components (x-card, x-table, etc.)

## Phase 5: Notifications & Integrations
- [x] Integrate email/SMS notifications for requests, matches, invoices
- [x] Connect to payment gateway (Paystack/Flutterwave)
- [x] Implement webhook listener at /webhooks/payment

## Phase 6: Testing
- [x] Write CompanyRequestTest
- [x] Write FleetTest
- [x] Write MatchingServiceTest
- [x] Mock queue jobs and verify dispatch
- [ ] Achieve minimum 80% coverage

## Phase 7: Deployment
- [x] Add CI/CD pipeline (Composer, PHPStan, PHPUnit, npm build)
- [ ] Deploy to staging branch
- [x] Run php artisan migrate --seed
- [ ] Ensure queue workers active for MatchDriversJob

## Phase 8: Queued Jobs
- [x] Implement MatchDriversJob
- [x] Implement SendNotificationJob
- [x] Implement GenerateReportJob
