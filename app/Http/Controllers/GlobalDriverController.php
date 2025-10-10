<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Driver;
use App\Models\Country;
use App\Models\GlobalState;
use App\Models\GlobalCity;
use App\Models\GlobalLanguage;
use App\Models\GlobalVehicleType;
use App\Models\Nationality;
use App\Services\GlobalKycService;
use App\Services\GlobalizationService;
use App\Http\Requests\GlobalDriverRegistrationRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class GlobalDriverController extends Controller
{
    protected $kycService;
    protected $globalizationService;

    public function __construct(GlobalKycService $kycService, GlobalizationService $globalizationService)
    {
        $this->kycService = $kycService;
        $this->globalizationService = $globalizationService;
    }

    /**
     * Show global driver registration form
     */
    public function showRegistration()
    {
        $data = [
            'countries' => $this->globalizationService->getCountriesByContinent(),
            'nationalities' => Nationality::orderBy('name')->get(['id', 'name', 'code']),
            'languages' => $this->globalizationService->getMajorLanguages(),
            'categories' => $this->globalizationService->getDriverCategoryNames(),
            'employment_types' => $this->globalizationService->getEmploymentTypeNames(),
        ];

        return view('global.driver.registration', $data);
    }

    /**
     * Process KYC Step 1: Basic Profile & Category Selection
     */
    public function processStep1(GlobalDriverRegistrationRequest $request)
    {
        try {
            DB::beginTransaction();

            // Create or find driver
            $driver = Driver::where('phone', $request->phone)->first();
            
            if (!$driver) {
                $driverId = $this->generateDriverId();
                
                $driver = Driver::create([
                    'driver_id' => $driverId,
                    'first_name' => $request->first_name,
                    'middle_name' => $request->middle_name,
                    'surname' => $request->surname,
                    'nickname' => $request->nickname,
                    'phone' => $request->phone,
                    'international_phone' => $request->international_phone,
                    'email' => $request->email,
                    'password' => $request->password ? Hash::make($request->password) : null,
                    'date_of_birth' => $request->date_of_birth,
                    'gender' => $request->gender,
                    'nationality_id' => $request->nationality_id,
                    'country_id' => $request->country_id,
                    'driver_category' => $request->driver_category,
                    'employment_preference' => $request->employment_preference,
                    'timezone' => $request->timezone ?? $this->getTimezoneForCountry($request->country_id),
                    'spoken_languages' => $request->spoken_languages ?? ['en'],
                    'preferred_communication_language' => $request->preferred_communication_language ?? 'en',
                    'currency_preference' => $this->getCurrencyForCountry($request->country_id),
                ]);
            }

            // Process Step 1 with KYC service
            $result = $this->kycService->processStep1($driver, $request->all());

            DB::commit();

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => __('driver.messages.kyc_step_completed'),
                    'driver_id' => $driver->driver_id,
                    'next_step' => 2,
                    'progress' => $result['progress_percentage'],
                    'next_requirements' => $result['next_requirements']
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $result['error']
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Global KYC Step 1 failed', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => __('global.common.error') . ': ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process KYC Step 2: Category-Specific Requirements
     */
    public function processStep2(GlobalDriverRegistrationRequest $request)
    {
        try {
            $driver = Driver::where('driver_id', $request->driver_id)->firstOrFail();
            
            $result = $this->kycService->processStep2($driver, $request->all());

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                    'next_step' => 3,
                    'progress' => $result['progress_percentage'],
                    'next_requirements' => $result['next_requirements']
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $result['error']
            ], 422);

        } catch (\Exception $e) {
            Log::error('Global KYC Step 2 failed', ['driver_id' => $request->driver_id, 'error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => __('global.common.error') . ': ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process KYC Step 3: Verification & Onboarding
     */
    public function processStep3(GlobalDriverRegistrationRequest $request)
    {
        try {
            $driver = Driver::where('driver_id', $request->driver_id)->firstOrFail();
            
            $result = $this->kycService->processStep3($driver, $request->all());

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                    'completed' => true,
                    'progress' => 100,
                    'next_action' => $result['next_action']
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $result['error']
            ], 422);

        } catch (\Exception $e) {
            Log::error('Global KYC Step 3 failed', ['driver_id' => $request->driver_id, 'error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => __('global.common.error') . ': ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get KYC step data for driver
     */
    public function getKycStepData(Request $request)
    {
        $driverId = $request->input('driver_id');
        $driver = Driver::where('driver_id', $driverId)->firstOrFail();
        
        $stepData = $this->kycService->getCurrentStepData($driver);
        
        return response()->json([
            'success' => true,
            'data' => $stepData
        ]);
    }

    /**
     * Get vehicle types by category
     */
    public function getVehicleTypes(Request $request)
    {
        $category = $request->input('category');
        
        $vehicleTypes = GlobalVehicleType::byCategory($category)
            ->active()
            ->ordered()
            ->get(['id', 'name', 'sub_category', 'specifications', 'requires_special_training']);

        return response()->json([
            'success' => true,
            'vehicle_types' => $vehicleTypes
        ]);
    }

    /**
     * Get states by country
     */
    public function getStates(Request $request)
    {
        $countryId = $request->input('country_id');
        
        $states = GlobalState::where('country_id', $countryId)
            ->active()
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return response()->json([
            'success' => true,
            'states' => $states
        ]);
    }

    /**
     * Get cities by state
     */
    public function getCities(Request $request)
    {
        $stateId = $request->input('state_id');
        
        $cities = GlobalCity::where('state_id', $stateId)
            ->active()
            ->orderBy('is_major_city', 'desc')
            ->orderBy('name')
            ->get(['id', 'name', 'type', 'is_major_city']);

        return response()->json([
            'success' => true,
            'cities' => $cities
        ]);
    }

    /**
     * Get requirements for driver category and country
     */
    public function getCategoryRequirements(Request $request)
    {
        $category = $request->input('category');
        $countryId = $request->input('country_id');
        
        $requirements = \App\Models\DriverCategoryRequirement::getRequirementsForCategory($category, $countryId);
        
        return response()->json([
            'success' => true,
            'requirements' => $requirements ? [
                'licenses' => $requirements->required_licenses,
                'certifications' => $requirements->required_certifications,
                'documents' => $requirements->required_documents,
                'background_checks' => $requirements->background_check_requirements,
                'minimum_experience' => $requirements->minimum_experience_years,
                'vehicle_requirements' => $requirements->vehicle_requirements
            ] : null
        ]);
    }

    /**
     * Global driver dashboard
     */
    public function dashboard()
    {
        $driver = auth()->user();
        
        $data = [
            'driver' => $driver,
            'kyc_progress' => $driver->getCategorySpecificKycProgress(),
            'current_step' => $this->kycService->getCurrentStepData($driver),
            'category_display' => $driver->getCategoryDisplayName(),
            'employment_display' => $driver->getEmploymentDisplayName(),
            'earnings' => [
                'total' => $driver->getTotalEarningsAttribute(),
                'this_month' => 0, // Implement based on your earnings tracking
                'currency' => $driver->currency_preference ?? 'NGN'
            ],
            'stats' => [
                'total_jobs' => $driver->getTotalJobsAttribute(),
                'rating' => $driver->getTotalRatingAttribute(),
                'completed_jobs' => $driver->performance?->total_jobs_completed ?? 0,
            ]
        ];

        return view('global.driver.dashboard', $data);
    }

    /**
     * Global driver profile
     */
    public function profile()
    {
        $driver = auth()->user();
        
        $data = [
            'driver' => $driver,
            'countries' => $this->globalizationService->getCountriesByContinent(),
            'nationalities' => Nationality::orderBy('name')->get(['id', 'name', 'code']),
            'languages' => $this->globalizationService->getSupportedLanguages(),
            'categories' => $this->globalizationService->getDriverCategoryNames(),
            'employment_types' => $this->globalizationService->getEmploymentTypeNames(),
            'vehicle_types' => GlobalVehicleType::byCategory($driver->driver_category)
                ->active()
                ->ordered()
                ->get(['id', 'name', 'sub_category']),
        ];

        return view('global.driver.profile', $data);
    }

    /**
     * Update driver profile
     */
    public function updateProfile(Request $request)
    {
        $driver = auth()->user();
        
        $validatedData = $request->validate([
            'nickname' => 'nullable|string|max:50',
            'email' => 'nullable|email|unique:drivers,email,' . $driver->id,
            'international_phone' => 'nullable|string|max:20',
            'preferred_work_regions' => 'nullable|array',
            'spoken_languages' => 'nullable|array',
            'preferred_communication_language' => 'nullable|string|exists:global_languages,code',
            'rate_per_hour' => 'nullable|numeric|min:0',
            'rate_per_km' => 'nullable|numeric|min:0',
            'service_radius_km' => 'nullable|integer|min:1',
            'availability_schedule' => 'nullable|array',
            'preferred_job_types' => 'nullable|array',
        ]);

        try {
            $driver->update($validatedData);
            
            return response()->json([
                'success' => true,
                'message' => __('driver.messages.profile_updated')
            ]);
            
        } catch (\Exception $e) {
            Log::error('Profile update failed', ['driver_id' => $driver->id, 'error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => __('global.common.error')
            ], 500);
        }
    }

    /**
     * Helper methods
     */
    protected function generateDriverId(): string
    {
        do {
            $driverId = 'DR' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
        } while (Driver::where('driver_id', $driverId)->exists());
        
        return $driverId;
    }

    protected function getTimezoneForCountry(?int $countryId): string
    {
        if (!$countryId) return 'Africa/Lagos';
        
        $country = Country::find($countryId);
        return $country?->timezone ?? 'Africa/Lagos';
    }

    protected function getCurrencyForCountry(?int $countryId): string
    {
        if (!$countryId) return 'NGN';
        
        $country = Country::find($countryId);
        return $country?->currency_code ?? 'NGN';
    }
}