<?php

namespace App\Services;

use App\Models\Company;
use App\Models\CompanyProfile;
use App\Models\CompanyMember;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CompanyService
{
    public function register(array $data): Company
    {
        $company = Company::create([
            'name' => $data['name'],
            'company_id' => $this->generateCompanyId(),
            'registration_number' => $data['registration_number'] ?? null,
            'tax_id' => $data['tax_id'] ?? null,
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'state' => $data['state'] ?? null,
            'lga' => $data['lga'] ?? null,
            'industry' => $data['industry'] ?? null,
            'company_size' => $data['company_size'] ?? null,
            'description' => $data['description'] ?? null,
            'contact_person_name' => $data['contact_person_name'] ?? null,
            'contact_person_title' => $data['contact_person_title'] ?? null,
            'contact_person_phone' => $data['contact_person_phone'] ?? null,
            'contact_person_email' => $data['contact_person_email'] ?? null,
            'default_commission_rate' => $data['default_commission_rate'] ?? 0,
            'payment_terms' => $data['payment_terms'] ?? null,
            'preferred_regions' => $data['preferred_regions'] ?? null,
            'vehicle_types_needed' => $data['vehicle_types_needed'] ?? null,
            'password' => Hash::make($data['password']),
        ]);

        // Create profile
        if (isset($data['profile'])) {
            $company->profile()->create($data['profile']);
        }

        // Send verification email
        $this->sendVerificationEmail($company);

        return $company;
    }

    public function verify(Company $company): bool
    {
        $company->update([
            'verification_status' => 'Verified',
            'verified_at' => now(),
        ]);

        return true;
    }

    public function reject(Company $company, string $reason): bool
    {
        $company->update([
            'verification_status' => 'Rejected',
        ]);

        // Send rejection email
        $this->sendRejectionEmail($company, $reason);

        return true;
    }

    public function addMember(Company $company, array $data): CompanyMember
    {
        return $company->members()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'role' => $data['role'],
            'permissions' => $data['permissions'] ?? [],
            'is_active' => true,
        ]);
    }

    public function updateProfile(Company $company, array $data): CompanyProfile
    {
        return $company->profile()->updateOrCreate([], $data);
    }

    private function generateCompanyId(): string
    {
        do {
            $id = 'COMP' . strtoupper(Str::random(8));
        } while (Company::where('company_id', $id)->exists());

        return $id;
    }

    private function sendVerificationEmail(Company $company): void
    {
        // TODO: Implement verification email
        // Mail::to($company->email)->send(new CompanyVerificationEmail($company));
    }

    private function sendRejectionEmail(Company $company, string $reason): void
    {
        // TODO: Implement rejection email
        // Mail::to($company->email)->send(new CompanyRejectionEmail($company, $reason));
    }

    public function getDashboardData(Company $company): array
    {
        $totalRequests = $company->requests()->count();
        $activeRequests = $company->requests()->where('status', 'active')->count();
        $completedRequests = $company->requests()->where('status', 'completed')->count();
        $totalMatches = $company->requests()->with('matches')->get()->pluck('matches')->flatten()->count();
        $totalFleets = $company->fleets()->count();
        $totalVehicles = $company->fleets()->with('vehicles')->get()->pluck('vehicles')->flatten()->count();
        $pendingInvoices = $company->invoices()->where('status', 'pending')->count();
        $totalSpent = $company->invoices()->where('status', 'paid')->sum('amount');

        return [
            'total_requests' => $totalRequests,
            'active_requests' => $activeRequests,
            'completed_requests' => $completedRequests,
            'total_matches' => $totalMatches,
            'total_fleets' => $totalFleets,
            'total_vehicles' => $totalVehicles,
            'pending_invoices' => $pendingInvoices,
            'total_spent' => $totalSpent,
        ];
    }
}
