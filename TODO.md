# PHASE 4 — Frontend UI/UX (Blade System) Implementation Plan

## Information Gathered
- **Existing Structure**: Company portal uses `company.layouts.app` with Bootstrap 5, sidebar navigation, and responsive design.
- **Existing Files**: `dashboard.blade.php` exists with KPI cards, charts, and recent activity. Subdirs exist for `requests/`, `matches/`, `fleets/`, `vehicles/`, `invoices/`, `profile/`.
- **Components**: UI components in `resources/views/components/ui/` include `card.blade.php`, `stats-widget.blade.php`, etc.
- **AJAX Integration**: Existing AJAX in request creation (location loading) and match actions (accept/reject).
- **Routes**: Sidebar links to routes like `company.requests.index`, assuming controllers exist.
- **Accessibility**: Need to ensure WCAG compliance with proper ARIA labels, semantic HTML, keyboard navigation.

## Plan
### 1. Create Partial Components
- Create `table.blade.php` for reusable data tables.
- Create `modal.blade.php` for confirmation and form modals.
- Enhance existing components for accessibility.

### 2. Develop Blade Templates
- Create `resources/views/company/requests.blade.php` (index view for requests list).
- Create `resources/views/company/matches.blade.php` (index view for matches, enhance existing if needed).
- Create `resources/views/company/fleet.blade.php` (index view for fleets).
- Create `resources/views/company/vehicles.blade.php` (index view for vehicles).
- Create `resources/views/company/invoices.blade.php` (index view for invoices).
- Create `resources/views/company/settings.blade.php` (settings/profile page).

### 3. Integrate AJAX and JS
- Add AJAX for request posting (enhance existing create form).
- Add AJAX for match acceptance/rejection (enhance existing).
- Add AJAX for vehicle CRUD operations.
- Implement toast notifications using Bootstrap toasts.
- Add real-time updates using polling or WebSockets if possible.

### 4. Ensure Responsive Design and Accessibility
- Use Bootstrap 5 grid system throughout.
- Add WCAG compliance: ARIA labels, roles, keyboard support, color contrast, screen reader support.

### 5. Testing and Validation
- Verify all views load correctly.
- Test AJAX interactions.
- Ensure mobile responsiveness.
- Validate accessibility with tools.

## Dependent Files to Edit/Create
- New: `resources/views/components/ui/table.blade.php`
- New: `resources/views/components/ui/modal.blade.php`
- New: `resources/views/company/requests.blade.php`
- New: `resources/views/company/matches.blade.php` (or enhance existing index)
- New: `resources/views/company/fleet.blade.php`
- New: `resources/views/company/vehicles.blade.php`
- New: `resources/views/company/invoices.blade.php`
- New: `resources/views/company/settings.blade.php`
- Update: `resources/views/company/dashboard.blade.php` (if needed for enhancements)
- Update: `resources/views/company/layouts/app.blade.php` (add toast container, scripts)

## Followup Steps
- Install any missing dependencies (e.g., if using additional JS libraries).
- Test views by running Laravel server and navigating to company routes.
- Commit changes with message "feature/company-portal-ui".
- Validate responsiveness on different screen sizes.
- Run accessibility audit.
