<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\CompanyRequest;
use App\Http\Requests\CompanyRequest as CompanyFormRequest;
use Illuminate\Support\Facades\Schema;

class CompanyController extends Controller
{
    public function index(Request $request)
    {
        if (!Schema::hasTable('companies')) {
            // Return an empty view-friendly dataset when the companies table isn't available yet
            $companies = collect();
            $requests = collect();
            $activeCompanies = $pendingVerification = $verifiedCompanies = $inactiveCompanies = $suspendedCompanies = 0;
            $newCompaniesThisWeek = $activeRequestsToday = $monthlyRequests = 0;
            $avgRequestsPerCompany = '0.0';

            return view('admin.companies.index', compact(
                'companies',
                'requests',
                'allRequests',
                'pendingRequests',
                'approvedRequests',
                'rejectedRequests',
                'activeCompanies',
                'pendingVerification',
                'verifiedCompanies',
                'inactiveCompanies',
                'suspendedCompanies',
                'newCompaniesThisWeek',
                'activeRequestsToday',
                'monthlyRequests',
                'avgRequestsPerCompany'
            ));
        }

        $query = Company::withCount('requests');
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%")
                  ->orWhere('company_id', 'LIKE', "%{$search}%")
                  ->orWhere('contact_person_name', 'LIKE', "%{$search}%");
            });
        }
        
        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Verification status filter
        if ($request->filled('verification_status')) {
            $query->where('verification_status', $request->verification_status);
        }
        
        // Date range filter
        if ($request->filled('date_range')) {
            switch ($request->date_range) {
                case 'today':
                    $query->whereDate('created_at', today());
                    break;
                case 'week':
                    $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                    break;
                case 'month':
                    $query->whereMonth('created_at', now()->month)
                          ->whereYear('created_at', now()->year);
                    break;
            }
        }
        
        $companies = $query->orderBy('created_at', 'desc')->paginate(20);
        
        // Calculate widget statistics
        $activeCompanies = Company::where('status', 'active')->count();
        $pendingVerification = Company::where('verification_status', 'pending')->count();
        $verifiedCompanies = Company::where('verification_status', 'verified')->count();
        $inactiveCompanies = Company::where('status', 'inactive')->count();
        $suspendedCompanies = Company::where('status', 'suspended')->count();
        
        // Recent activity metrics
        $newCompaniesThisWeek = Company::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
        $activeRequestsToday = CompanyRequest::whereDate('created_at', today())->count();
        
        // Monthly requests
        $monthlyRequests = CompanyRequest::whereMonth('created_at', now()->month)
                                        ->whereYear('created_at', now()->year)
                                        ->count();
        
        // Average requests per company
        $totalCompanies = Company::count();
        $totalRequests = CompanyRequest::count();
        $avgRequestsPerCompany = $totalCompanies > 0 ? number_format($totalRequests / $totalCompanies, 1) : '0.0';
        
        // Get company requests data
        $requests = CompanyRequest::with(['company', 'driver'])
                                  ->orderBy('created_at', 'desc')
                                  ->get();
                                  
        // Calculate request counts
        $allRequests = $requests->count();
        $pendingRequests = $requests->where('status', 'pending')->count();
        $approvedRequests = $requests->where('status', 'approved')->count();
        $rejectedRequests = $requests->where('status', 'rejected')->count();
        
        return view('admin.companies.index', compact(
            'companies', 
            'requests', 
            'allRequests', 
            'pendingRequests', 
            'approvedRequests', 
            'rejectedRequests',
            'activeCompanies',
            'pendingVerification',
            'verifiedCompanies',
            'inactiveCompanies',
            'suspendedCompanies',
            'newCompaniesThisWeek',
            'activeRequestsToday',
            'monthlyRequests',
            'avgRequestsPerCompany'
        ));
    }

    public function create()
    {
        return view('admin.companies.create');
    }

    public function store(CompanyFormRequest $request)
    {

        Company::create([
            'company_id' => $this->generateCompanyId(),
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'contact_person_name' => $request->contact_person_name,
            'contact_person_title' => $request->contact_person_title,
            'contact_person_email' => $request->contact_person_email,
            'contact_person_phone' => $request->contact_person_phone,
            'website' => $request->website,
            'industry' => $request->industry,
            'description' => $request->description,
            'state' => $request->state,
            'lga' => $request->lga,
            'postal_code' => $request->postal_code,
            'vehicle_types_needed' => $request->vehicle_types_needed,
            'preferred_regions' => $request->preferred_regions,
            'default_commission_rate' => $request->default_commission_rate,
            'payment_terms' => $request->payment_terms,
            'status' => $request->status ?: 'active',
            'verification_status' => $request->verification_status ?: 'pending',
        ]);

        return redirect()->route('admin.companies.index')
                        ->with('success', 'Company created successfully!');
    }

    public function show(Company $company)
    {
        return view('admin.companies.show', compact('company'));
    }

    public function edit(Company $company)
    {
        return view('admin.companies.edit', compact('company'));
    }

    public function update(CompanyFormRequest $request, Company $company)
    {

        $company->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'contact_person_name' => $request->contact_person_name,
            'contact_person_title' => $request->contact_person_title,
            'contact_person_email' => $request->contact_person_email,
            'contact_person_phone' => $request->contact_person_phone,
            'website' => $request->website,
            'industry' => $request->industry,
            'description' => $request->description,
            'state' => $request->state,
            'lga' => $request->lga,
            'postal_code' => $request->postal_code,
            'vehicle_types_needed' => $request->vehicle_types_needed,
            'preferred_regions' => $request->preferred_regions,
            'default_commission_rate' => $request->default_commission_rate,
            'payment_terms' => $request->payment_terms,
            'status' => $request->status,
            'verification_status' => $request->verification_status,
        ]);

        return redirect()->route('admin.companies.index')
                        ->with('success', 'Company updated successfully!');
    }

    public function destroy(Company $company)
    {
        $company->delete();
        
        return redirect()->route('admin.companies.index')
                        ->with('success', 'Company deleted successfully!');
    }

    public function toggleStatus(Company $company)
    {
        $newStatus = $company->status === 'active' ? 'inactive' : 'active';
        $company->update(['status' => $newStatus]);

        return back()->with('success', "Company status changed to {$newStatus}!");
    }

    public function verification(Request $request)
    {
        $query = Company::query();
        
        // Filter by verification status if specified
        if ($request->filled('verification_status')) {
            $query->where('verification_status', $request->verification_status);
        }
        
        $companies = $query->orderBy('created_at', 'desc')->paginate(20);
        
        // Calculate verification statistics
        $stats = [
            'total' => Company::count(),
            'verified' => Company::where('verification_status', 'Verified')->count(),
            'pending' => Company::where('verification_status', 'Pending')->count(),
            'rejected' => Company::where('verification_status', 'Rejected')->count(),
        ];
        
        return view('admin.companies.verification', compact('companies', 'stats'));
    }

    public function pending(Request $request)
    {
        $companies = Company::where('verification_status', 'Pending')
                           ->orderBy('created_at', 'asc')
                           ->paginate(12);
        
        $pendingCount = $companies->total();
        
        return view('admin.companies.pending', compact('companies', 'pendingCount'));
    }

    public function updateVerification(Request $request)
    {
        $request->validate([
            'company_id' => 'required|exists:companies,id',
            'action' => 'required|in:verify,reject',
            'notes' => 'nullable|string',
            'rejection_reason' => 'nullable|string'
        ]);

        $company = Company::findOrFail($request->company_id);
        
        $verificationStatus = $request->action === 'verify' ? 'Verified' : 'Rejected';
        
        $updateData = [
            'verification_status' => $verificationStatus,
            'verified_by' => auth('admin')->id(),
            'verification_notes' => $request->notes,
        ];
        
        if ($request->action === 'verify') {
            $updateData['verified_at'] = now();
        } else {
            $updateData['rejection_reason'] = $request->rejection_reason;
            $updateData['rejected_at'] = now();
        }
        
        $company->update($updateData);
        
        return response()->json([
            'success' => true,
            'message' => "Company has been {$verificationStatus} successfully."
        ]);
    }

    public function bulkVerification(Request $request)
    {
        $request->validate([
            'company_ids' => 'required|string',
            'action' => 'required|in:verify,reject',
            'notes' => 'nullable|string',
            'rejection_reason' => 'nullable|string'
        ]);

        $companyIds = explode(',', $request->company_ids);
        $companies = Company::whereIn('id', $companyIds)->get();
        
        $verificationStatus = $request->action === 'verify' ? 'Verified' : 'Rejected';
        $processedCount = 0;
        
        foreach ($companies as $company) {
            $updateData = [
                'verification_status' => $verificationStatus,
                'verified_by' => auth('admin')->id(),
                'verification_notes' => $request->notes,
            ];
            
            if ($request->action === 'verify') {
                $updateData['verified_at'] = now();
            } else {
                $updateData['rejection_reason'] = $request->rejection_reason;
                $updateData['rejected_at'] = now();
            }
            
            $company->update($updateData);
            $processedCount++;
        }
        
        return response()->json([
            'success' => true,
            'message' => "{$processedCount} companies have been {$verificationStatus} successfully."
        ]);
    }

    public function register(Request $request)
    {
        // Log the registration attempt
        \Log::info('Company registration attempt', [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'request_method' => $request->method(),
            'request_data' => $request->except(['_token', 'password'])
        ]);

        // Validate the registration data
        $validatedData = $request->validate([
            'company_name' => 'required|string|max:255',
            'business_type' => 'required|string|in:logistics,delivery,ride_hailing,freight,other',
            'registration_number' => 'nullable|string|max:100',
            'tax_id' => 'nullable|string|max:100',
            'company_address' => 'required|string',
            'city' => 'nullable|string|max:100', // Make city optional since we'll append it to address
            'state' => 'required|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'contact_name' => 'required|string|max:255',
            'contact_title' => 'nullable|string|max:100',
            'email' => 'required|email|max:255|unique:companies,email',
            'phone' => 'required|string|max:20',
            'website' => 'nullable|url|max:255',
            'drivers_needed' => 'nullable|integer|min:1|max:1000',
            'urgency' => 'nullable|string|in:immediate,urgent,normal,planning',
            'driver_requirements' => 'nullable|string|max:1000',
            'terms' => 'required|accepted',
            'marketing_emails' => 'nullable|boolean'
        ]);

        try {
            // Generate unique company ID
            $companyId = $this->generateCompanyId();

            // Map business type to industry (required field in database)
            $industryMapping = [
                'logistics' => 'Logistics',
                'delivery' => 'Logistics', 
                'ride_hailing' => 'Logistics',
                'freight' => 'Logistics',
                'other' => 'Other'
            ];

            // Combine address with city if provided
            $fullAddress = $validatedData['company_address'];
            if (!empty($validatedData['city'])) {
                $fullAddress .= ', ' . $validatedData['city'];
            }

            // Create the company record with correct field mapping
            $company = Company::create([
                'company_id' => $companyId,
                'name' => $validatedData['company_name'],
                'registration_number' => $validatedData['registration_number'],
                'tax_id' => $validatedData['tax_id'],
                'address' => $fullAddress,
                'state' => $validatedData['state'],
                'postal_code' => $validatedData['postal_code'],
                'contact_person_name' => $validatedData['contact_name'],
                'contact_person_title' => $validatedData['contact_title'],
                'contact_person_phone' => $validatedData['phone'], // Map to contact person phone
                'contact_person_email' => $validatedData['email'], // Map to contact person email
                'email' => $validatedData['email'], // Also set main company email
                'phone' => $validatedData['phone'], // Also set main company phone
                'website' => $validatedData['website'],
                'industry' => $industryMapping[$validatedData['business_type']] ?? 'Other',
                'description' => $validatedData['driver_requirements'], // Use driver requirements as description
                'status' => 'Active', // Set as Active by default
                'verification_status' => 'Pending', // Pending verification
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Log the successful registration
            \Log::info('Company registration submitted', [
                'company_id' => $company->company_id,
                'name' => $company->name,
                'email' => $company->email,
                'ip' => $request->ip()
            ]);

            // Redirect with success message
            return redirect()->route('company.register')->with('success', 
                'Thank you for your registration! Your company application has been submitted successfully. ' .
                'Our team will review your application and contact you within 24-48 hours. ' .
                'Your Company ID is: ' . $company->company_id
            );

        } catch (\Illuminate\Database\QueryException $e) {
            \Log::error('Company registration database error', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'request_data' => $request->except(['_token']),
                'ip' => $request->ip()
            ]);

            $errorMessage = 'There was a database error processing your registration.';
            
            // Check for specific constraint violations
            if (strpos($e->getMessage(), 'companies_email_unique') !== false) {
                $errorMessage = 'This email address is already registered with another company.';
            } elseif (strpos($e->getMessage(), 'companies_company_id_unique') !== false) {
                $errorMessage = 'System error generating unique company ID. Please try again.';
            }

            return back()
                ->withInput($request->except(['_token', 'terms']))
                ->withErrors(['general' => $errorMessage]);

        } catch (\Exception $e) {
            \Log::error('Company registration failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['_token']),
                'ip' => $request->ip()
            ]);

            return back()
                ->withInput($request->except(['_token', 'terms']))
                ->withErrors(['general' => 'There was an unexpected error processing your registration. Please try again later.']);
        }
    }

    private function generateCompanyId()
    {
        do {
            $id = 'CP' . str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        } while (Company::where('company_id', $id)->exists());
        
        return $id;
    }
}