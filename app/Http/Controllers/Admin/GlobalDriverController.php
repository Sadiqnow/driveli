<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Drivers as Driver;
use App\Models\Country;
use App\Models\GlobalState;
use App\Models\GlobalVehicleType;
use App\Models\DriverCategoryRequirement;
use App\Services\GlobalKycService;
use App\Services\GlobalizationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\SecureSearch;

class GlobalDriverController extends Controller
{
    use SecureSearch;

    protected $kycService;
    protected $globalizationService;

    public function __construct(GlobalKycService $kycService, GlobalizationService $globalizationService)
    {
        $this->kycService = $kycService;
        $this->globalizationService = $globalizationService;
        $this->middleware('auth:admin');
    }

    /**
     * Enhanced driver listing with global filters
     */
    public function index(Request $request)
    {
        $query = Driver::withCompleteProfile(false, true);

        // Apply existing search
        if ($request->filled('search')) {
            $searchFields = ['first_name', 'surname', 'phone', 'email', 'driver_id'];
            $query = $this->applySecureSearch($query, $request->search, $searchFields);
        }

        // Enhanced global filters
        if ($request->filled('category')) {
            $query->byCategory($request->category);
        }

        if ($request->filled('employment_type')) {
            $query->byEmploymentType($request->employment_type);
        }

        if ($request->filled('country_id')) {
            $query->byCountry($request->country_id);
        }

        if ($request->filled('currency')) {
            $query->byCurrency($request->currency);
        }

        if ($request->filled('verification_status')) {
            $query->where('verification_status', $request->verification_status);
        }

        if ($request->filled('kyc_status')) {
            $query->where('kyc_status', $request->kyc_status);
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to . ' 23:59:59');
        }

        $drivers = $query->paginate(20);

        $data = [
            'drivers' => $drivers,
            'countries' => Country::active()->orderBy('name')->get(['id', 'name']),
            'categories' => $this->globalizationService->getDriverCategoryNames(),
            'employment_types' => $this->globalizationService->getEmploymentTypeNames(),
            'verification_statuses' => $this->globalizationService->getVerificationStatusNames(),
            'kyc_statuses' => $this->globalizationService->getKycStatusNames(),
            'currencies' => $this->getUniqueCurrencies(),
            'filters' => $request->only(['search', 'category', 'employment_type', 'country_id', 'currency', 'verification_status', 'kyc_status', 'date_from', 'date_to']),
        ];

        return view('admin.drivers.global.index', $data);
    }

    /**
     * Enhanced driver details view with global context
     */
    public function show($id)
    {
        $driver = Driver::withCompleteProfile(true, true)
            ->with([
                'country:id,name,currency_code,currency_symbol',
                'globalState:id,name,code',
                'globalCity:id,name,type',
            ])
            ->findOrFail($id);

        $kycData = $this->kycService->getCurrentStepData($driver);
        $categoryRequirements = DriverCategoryRequirement::getRequirementsForCategory(
            $driver->driver_category,
            $driver->country_id
        );

        $data = [
            'driver' => $driver,
            'kyc_data' => $kycData,
            'category_requirements' => $categoryRequirements,
            'category_display' => $driver->getCategoryDisplayName(),
            'employment_display' => $driver->getEmploymentDisplayName(),
            'category_specific_data' => $driver->getCategorySpecificData(),
            'spoken_languages' => $driver->getSpokenLanguagesNames(),
            'communication_language' => $driver->getPreferredCommunicationLanguageName(),
        ];

        return view('admin.drivers.global.show', $data);
    }

    /**
     * Global driver analytics dashboard
     */
    public function analytics()
    {
        $stats = [
            'total_drivers' => Driver::count(),
            'by_category' => Driver::select('driver_category', DB::raw('count(*) as count'))
                ->groupBy('driver_category')
                ->pluck('count', 'driver_category')
                ->toArray(),
            'by_country' => Driver::select('countries.name', DB::raw('count(*) as count'))
                ->join('countries', 'drivers.country_id', '=', 'countries.id')
                ->groupBy('countries.name')
                ->pluck('count', 'name')
                ->toArray(),
            'by_employment_type' => Driver::select('employment_preference', DB::raw('count(*) as count'))
                ->groupBy('employment_preference')
                ->pluck('count', 'employment_preference')
                ->toArray(),
            'by_verification_status' => Driver::select('verification_status', DB::raw('count(*) as count'))
                ->groupBy('verification_status')
                ->pluck('count', 'verification_status')
                ->toArray(),
            'by_kyc_status' => Driver::select('kyc_status', DB::raw('count(*) as count'))
                ->groupBy('kyc_status')
                ->pluck('count', 'kyc_status')
                ->toArray(),
            'recent_registrations' => Driver::where('created_at', '>=', now()->subDays(30))->count(),
            'completed_kyc_this_month' => Driver::where('kyc_completed_at', '>=', now()->startOfMonth())->count(),
        ];

        // Average completion rates
        $stats['avg_kyc_progress'] = Driver::whereNotNull('profile_completion_percentage')
            ->avg('profile_completion_percentage');

        // Top countries by driver count
        $stats['top_countries'] = Driver::select('countries.name', 'countries.iso_code_2', DB::raw('count(*) as count'))
            ->join('countries', 'drivers.country_id', '=', 'countries.id')
            ->groupBy('countries.id', 'countries.name', 'countries.iso_code_2')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        return view('admin.drivers.global.analytics', compact('stats'));
    }

    /**
     * Category management
     */
    public function categoryManagement()
    {
        $categories = DriverCategoryRequirement::with('country:id,name')
            ->get()
            ->groupBy('category');

        $countries = Country::active()->orderBy('name')->get(['id', 'name']);
        $vehicleTypes = GlobalVehicleType::active()->ordered()->get();

        return view('admin.drivers.global.categories', compact('categories', 'countries', 'vehicleTypes'));
    }

    /**
     * Update category requirements
     */
    public function updateCategoryRequirements(Request $request)
    {
        $request->validate([
            'category' => 'required|in:commercial_truck,professional,public,executive',
            'country_id' => 'required|exists:countries,id',
            'required_licenses' => 'nullable|array',
            'required_certifications' => 'nullable|array',
            'required_documents' => 'nullable|array',
            'background_check_requirements' => 'nullable|array',
            'minimum_experience_years' => 'nullable|integer|min:0',
            'vehicle_requirements' => 'nullable|array',
        ]);

        try {
            DriverCategoryRequirement::updateOrCreate(
                [
                    'category' => $request->category,
                    'country_id' => $request->country_id,
                ],
                $request->only([
                    'required_licenses',
                    'required_certifications',
                    'required_documents',
                    'background_check_requirements',
                    'minimum_experience_years',
                    'vehicle_requirements',
                ])
            );

            return response()->json([
                'success' => true,
                'message' => 'Category requirements updated successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update category requirements: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk operations for global drivers
     */
    public function bulkOperations(Request $request)
    {
        $request->validate([
            'operation' => 'required|in:verify,reject,suspend,activate,delete',
            'driver_ids' => 'required|array',
            'driver_ids.*' => 'exists:drivers,id',
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            $drivers = Driver::whereIn('id', $request->driver_ids)->get();
            $operation = $request->operation;
            $notes = $request->notes;

            foreach ($drivers as $driver) {
                switch ($operation) {
                    case 'verify':
                        $driver->adminUpdateVerification('verified', auth('admin')->user(), $notes);
                        break;
                    case 'reject':
                        $driver->adminUpdateVerification('rejected', auth('admin')->user(), $notes);
                        break;
                    case 'suspend':
                        $driver->adminUpdateStatus('suspended', auth('admin')->user());
                        break;
                    case 'activate':
                        $driver->adminUpdateStatus('active', auth('admin')->user());
                        break;
                    case 'delete':
                        $driver->delete();
                        break;
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => ucfirst($operation) . ' operation completed for ' . count($drivers) . ' drivers.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Bulk operation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export drivers data
     */
    public function export(Request $request)
    {
        $query = Driver::withCompleteProfile(false, true);

        // Apply same filters as index
        if ($request->filled('category')) {
            $query->byCategory($request->category);
        }
        if ($request->filled('employment_type')) {
            $query->byEmploymentType($request->employment_type);
        }
        if ($request->filled('country_id')) {
            $query->byCountry($request->country_id);
        }

        $drivers = $query->get();

        $filename = 'global_drivers_' . now()->format('Y_m_d_H_i_s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($drivers) {
            $file = fopen('php://output', 'w');
            
            // CSV Headers
            fputcsv($file, [
                'Driver ID',
                'Name',
                'Email',
                'Phone',
                'Category',
                'Employment Type',
                'Country',
                'Verification Status',
                'KYC Status',
                'Registration Date',
                'KYC Completion Date',
                'Rate (Hour)',
                'Currency',
            ]);

            // CSV Data
            foreach ($drivers as $driver) {
                fputcsv($file, [
                    $driver->driver_id,
                    $driver->full_name,
                    $driver->email,
                    $driver->phone,
                    $driver->getCategoryDisplayName(),
                    $driver->getEmploymentDisplayName(),
                    $driver->country?->name ?? 'N/A',
                    $driver->verification_status,
                    $driver->kyc_status,
                    $driver->created_at?->format('Y-m-d'),
                    $driver->kyc_completed_at?->format('Y-m-d') ?? 'N/A',
                    $driver->rate_per_hour ?? 'N/A',
                    $driver->currency_preference ?? 'N/A',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Global driver search API
     */
    public function apiSearch(Request $request)
    {
        $query = Driver::withBasicDetails();

        if ($request->filled('q')) {
            $searchFields = ['first_name', 'surname', 'phone', 'email', 'driver_id'];
            $query = $this->applySecureSearch($query, $request->q, $searchFields);
        }

        if ($request->filled('category')) {
            $query->byCategory($request->category);
        }

        if ($request->filled('country_id')) {
            $query->byCountry($request->country_id);
        }

        $drivers = $query->limit(20)->get();

        return response()->json([
            'success' => true,
            'drivers' => $drivers->map(function($driver) {
                return [
                    'id' => $driver->id,
                    'driver_id' => $driver->driver_id,
                    'name' => $driver->full_name,
                    'category' => $driver->getCategoryDisplayName(),
                    'country' => $driver->country?->name,
                    'status' => $driver->verification_status,
                    'phone' => $driver->phone,
                    'email' => $driver->email,
                ];
            })
        ]);
    }

    /**
     * Helper methods
     */
    protected function getUniqueCurrencies(): array
    {
        return Country::active()
            ->select('currency_code', 'currency_symbol')
            ->distinct()
            ->orderBy('currency_code')
            ->get()
            ->mapWithKeys(function($country) {
                return [$country->currency_code => $country->currency_code . ' (' . $country->currency_symbol . ')'];
            })
            ->toArray();
    }
}