<?php

namespace App\Services;

use App\Models\Company;
use App\Models\CompanyRequest;
use App\Models\AdminUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

class CompanyService
{
    /**
     * Create a new company.
     *
     * @param array $data
     * @return Company
     */
    public function createCompany(array $data): Company
    {
        return DB::transaction(function () use ($data) {
            // Generate unique company ID
            $companyId = $this->generateCompanyId();

            $company = Company::create([
                'company_id' => $companyId,
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'website' => $data['website'] ?? null,
                'description' => $data['description'] ?? null,
                'address' => $data['address'],
                'city' => $data['city'],
                'state' => $data['state'],
                'country' => $data['country'] ?? 'Nigeria',
                'industry' => $data['industry'],
                'company_size' => $data['company_size'],
                'registration_number' => $data['registration_number'],
                'tax_identification_number' => $data['tax_identification_number'] ?? null,
                'contact_person_name' => $data['contact_person_name'],
                'contact_person_phone' => $data['contact_person_phone'],
                'contact_person_email' => $data['contact_person_email'],
                'status' => 'Pending',
                'verification_status' => 'Pending',
            ]);

            // Handle file uploads
            $this->storeCompanyDocuments($company, $data);

            return $company;
        });
    }

    /**
     * Update company information.
     *
     * @param Company $company
     * @param array $data
     * @return Company
     */
    public function updateCompany(Company $company, array $data): Company
    {
        return DB::transaction(function () use ($company, $data) {
            $company->update(array_filter([
                'name' => $data['name'] ?? $company->name,
                'email' => $data['email'] ?? $company->email,
                'phone' => $data['phone'] ?? $company->phone,
                'website' => $data['website'] ?? $company->website,
                'description' => $data['description'] ?? $company->description,
                'address' => $data['address'] ?? $company->address,
                'city' => $data['city'] ?? $company->city,
                'state' => $data['state'] ?? $company->state,
                'country' => $data['country'] ?? $company->country,
                'industry' => $data['industry'] ?? $company->industry,
                'company_size' => $data['company_size'] ?? $company->company_size,
                'registration_number' => $data['registration_number'] ?? $company->registration_number,
                'tax_identification_number' => $data['tax_identification_number'] ?? $company->tax_identification_number,
                'contact_person_name' => $data['contact_person_name'] ?? $company->contact_person_name,
                'contact_person_phone' => $data['contact_person_phone'] ?? $company->contact_person_phone,
                'contact_person_email' => $data['contact_person_email'] ?? $company->contact_person_email,
            ]));

            // Update documents if provided
            $this->updateCompanyDocuments($company, $data);

            return $company->fresh();
        });
    }

    /**
     * Verify a company.
     *
     * @param Company $company
     * @param AdminUser $admin
     * @param string $notes
     * @return Company
     */
    public function verifyCompany(Company $company, AdminUser $admin, string $notes = null): Company
    {
        $company->update([
            'verification_status' => 'Verified',
            'verified_at' => now(),
            'verified_by' => $admin->id,
            'verification_notes' => $notes,
            'status' => 'Active',
        ]);

        return $company;
    }

    /**
     * Reject a company verification.
     *
     * @param Company $company
     * @param AdminUser $admin
     * @param string $reason
     * @return Company
     */
    public function rejectCompany(Company $company, AdminUser $admin, string $reason): Company
    {
        $company->update([
            'verification_status' => 'Rejected',
            'verified_at' => null,
            'verified_by' => $admin->id,
            'rejection_reason' => $reason,
            'status' => 'Inactive',
        ]);

        return $company;
    }

    /**
     * Suspend a company.
     *
     * @param Company $company
     * @param AdminUser $admin
     * @param string $reason
     * @return Company
     */
    public function suspendCompany(Company $company, AdminUser $admin, string $reason): Company
    {
        $company->update([
            'status' => 'Suspended',
            'suspension_reason' => $reason,
            'suspended_by' => $admin->id,
            'suspended_at' => now(),
        ]);

        return $company;
    }

    /**
     * Activate a suspended company.
     *
     * @param Company $company
     * @param AdminUser $admin
     * @return Company
     */
    public function activateCompany(Company $company, AdminUser $admin): Company
    {
        $company->update([
            'status' => 'Active',
            'suspension_reason' => null,
            'suspended_by' => null,
            'suspended_at' => null,
            'reactivated_by' => $admin->id,
            'reactivated_at' => now(),
        ]);

        return $company;
    }

    /**
     * Get companies with filters and pagination.
     *
     * @param array $filters
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getCompanies(array $filters = [], int $perPage = 15)
    {
        if (!Schema::hasTable('companies')) {
            // Return an empty paginator-like structure when table is missing during tests/bootstrap
            return collect([])->paginate($perPage);
        }

        $query = Company::query();

        // Apply filters
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['verification_status'])) {
            $query->where('verification_status', $filters['verification_status']);
        }

        if (isset($filters['industry'])) {
            $query->where('industry', $filters['industry']);
        }

        if (isset($filters['state'])) {
            $query->where('state', $filters['state']);
        }

        if (isset($filters['company_size'])) {
            $query->where('company_size', $filters['company_size']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('company_id', 'like', "%{$search}%")
                  ->orWhere('registration_number', 'like', "%{$search}%");
            });
        }

        // Apply sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($perPage);
    }

    /**
     * Get company statistics.
     *
     * @return array
     */
    public function getCompanyStatistics(): array
    {
        if (!Schema::hasTable('companies')) {
            return [
                'total' => 0,
                'verified' => 0,
                'pending' => 0,
                'rejected' => 0,
                'active' => 0,
                'suspended' => 0,
                'by_industry' => [],
                'by_state' => [],
                'by_size' => [],
                'recent_registrations' => 0,
                'recent_verifications' => 0,
            ];
        }

        return [
            'total' => Company::count(),
            'verified' => Company::where('verification_status', 'Verified')->count(),
            'pending' => Company::where('verification_status', 'Pending')->count(),
            'rejected' => Company::where('verification_status', 'Rejected')->count(),
            'active' => Company::where('status', 'Active')->count(),
            'suspended' => Company::where('status', 'Suspended')->count(),
            'by_industry' => Company::select('industry', DB::raw('count(*) as count'))
                ->groupBy('industry')
                ->pluck('count', 'industry')
                ->toArray(),
            'by_state' => Company::select('state', DB::raw('count(*) as count'))
                ->groupBy('state')
                ->orderByDesc('count')
                ->limit(10)
                ->pluck('count', 'state')
                ->toArray(),
            'by_size' => Company::select('company_size', DB::raw('count(*) as count'))
                ->groupBy('company_size')
                ->pluck('count', 'company_size')
                ->toArray(),
            'recent_registrations' => Company::where('created_at', '>=', now()->subDays(30))->count(),
            'recent_verifications' => Company::where('verified_at', '>=', now()->subDays(30))->count(),
        ];
    }

    /**
     * Create a company request for driver.
     *
     * @param Company $company
     * @param array $data
     * @return CompanyRequest
     */
    public function createDriverRequest(Company $company, array $data): CompanyRequest
    {
        return CompanyRequest::create([
            'company_id' => $company->id,
            'driver_id' => $data['driver_id'] ?? null,
            'status' => 'Active',
            'description' => $data['description'],
            'requirements' => json_encode($data['requirements'] ?? []),
            'location_preferences' => json_encode($data['location_preferences'] ?? []),
            'salary_range' => $data['salary_range'] ?? null,
            'experience_required' => $data['experience_required'] ?? null,
            'vehicle_type' => $data['vehicle_type'] ?? null,
            'urgency_level' => $data['urgency_level'] ?? 'Normal',
            'expires_at' => $data['expires_at'] ?? now()->addDays(30),
        ]);
    }

    /**
     * Update company request.
     *
     * @param CompanyRequest $request
     * @param array $data
     * @return CompanyRequest
     */
    public function updateDriverRequest(CompanyRequest $request, array $data): CompanyRequest
    {
        $request->update(array_filter([
            'description' => $data['description'] ?? $request->description,
            'requirements' => isset($data['requirements']) ? json_encode($data['requirements']) : $request->requirements,
            'location_preferences' => isset($data['location_preferences']) ? json_encode($data['location_preferences']) : $request->location_preferences,
            'salary_range' => $data['salary_range'] ?? $request->salary_range,
            'experience_required' => $data['experience_required'] ?? $request->experience_required,
            'vehicle_type' => $data['vehicle_type'] ?? $request->vehicle_type,
            'urgency_level' => $data['urgency_level'] ?? $request->urgency_level,
            'expires_at' => $data['expires_at'] ?? $request->expires_at,
            'status' => $data['status'] ?? $request->status,
        ]));

        return $request;
    }

    /**
     * Get company's driver requests.
     *
     * @param Company $company
     * @param array $filters
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getCompanyRequests(Company $company, array $filters = [], int $perPage = 15)
    {
        $query = $company->requests();

        // Apply filters
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }


        if (isset($filters['urgency_level'])) {
            $query->where('urgency_level', $filters['urgency_level']);
        }

        // Apply sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($perPage);
    }

    /**
     * Generate a unique company ID.
     *
     * @return string
     */
    private function generateCompanyId(): string
    {
        do {
            $id = 'COMP' . str_pad(random_int(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (Schema::hasTable('companies') && Company::where('company_id', $id)->exists());

        return $id;
    }

    /**
     * Store company documents.
     *
     * @param Company $company
     * @param array $data
     * @return void
     */
    private function storeCompanyDocuments(Company $company, array $data): void
    {
        // Store logo
        if (isset($data['logo'])) {
            $logoPath = $this->storeCompanyFile($data['logo'], 'logos');
            $company->update(['logo' => $logoPath]);
        }

        // Store other documents (for future enhancement)
        $documents = [
            'cac_document' => 'cac',
            'tax_certificate' => 'tax',
        ];

        foreach ($documents as $field => $type) {
            if (isset($data[$field])) {
                $path = $this->storeCompanyFile($data[$field], 'documents');
                // Store document information in a separate table if needed
            }
        }
    }

    /**
     * Update company documents.
     *
     * @param Company $company
     * @param array $data
     * @return void
     */
    private function updateCompanyDocuments(Company $company, array $data): void
    {
        // Update logo
        if (isset($data['logo'])) {
            // Delete old logo
            if ($company->logo) {
                Storage::disk('public')->delete($company->logo);
            }

            // Store new logo
            $logoPath = $this->storeCompanyFile($data['logo'], 'logos');
            $company->update(['logo' => $logoPath]);
        }

        // Update other documents (for future enhancement)
        $documents = [
            'cac_document' => 'cac',
            'tax_certificate' => 'tax',
        ];

        foreach ($documents as $field => $type) {
            if (isset($data[$field])) {
                $path = $this->storeCompanyFile($data[$field], 'documents');
                // Update document information in a separate table if needed
            }
        }
    }

    /**
     * Store a company file.
     *
     * @param UploadedFile $file
     * @param string $folder
     * @return string
     */
    private function storeCompanyFile(UploadedFile $file, string $folder): string
    {
        $extension = $file->getClientOriginalExtension();
        $filename = Str::uuid() . '.' . $extension;
        
        return $file->storeAs("companies/{$folder}", $filename, 'public');
    }
}