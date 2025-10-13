<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Drivers;
use App\Models\State;
use App\Models\LocalGovernment;
use App\Models\Nationality;
use App\Models\Bank;
use App\Services\NotificationService;
use App\Services\VerificationLoggerService;
use App\Jobs\NINVerificationJob;
use App\Jobs\LicenseVerificationJob;
use App\Jobs\SmileIDVerificationJob;
use App\Jobs\FaceIDVerificationJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class DriverKycController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->middleware('auth:driver');
        $this->notificationService = $notificationService;
    }

    /**
     * KYC Overview Dashboard
     */
    public function index()
    {
        $driver = Auth::guard('driver')->user();
        
        $kycData = [
            'current_step' => $driver->getCurrentKycStep(),
            'progress_percentage' => $driver->getKycProgressPercentage(),
            'kyc_status' => $driver->kyc_status,
            'kyc_status_badge' => $driver->getKycStatusBadge(),
            'can_perform_kyc' => $driver->canPerformKyc(),
            'step_completion' => [
                'step_1' => $driver->isKycStepCompleted(1),
                'step_2' => $driver->isKycStepCompleted(2),
                'step_3' => $driver->isKycStepCompleted(3),
            ],
            'next_step' => $driver->getNextKycStep(),
            'rejection_reason' => $driver->kyc_rejection_reason,
            'retry_count' => $driver->kyc_retry_count,
            'completed_at' => $driver->kyc_completed_at,
            'status' => $driver->kyc_status,
        ];

        // Calculate profile completeness for KYC eligibility
        $completionPercentage = $this->calculateKycReadiness($driver);

        return view('driver.kyc.index', compact('driver', 'completionPercentage'))->with('kycStatus', $kycData);
    }

    // Facial Capture Step (Optional)

        public function showFacialPage()
    {
        return view('driver.kyc.facial-capture');
    }

    public function submitFacialCapture(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $driver = auth('driver')->user();

        // Store the uploaded image
        $path = $request->file('photo')->store('drivers/faces', 'public');

        // Update driver record
        $driver->update([
            'passport_photograph' => $path,
            'ocr_verification_status' => 'pending',
            'ocr_verification_notes' => 'Facial image captured, pending OCR match',
        ]);

        // OPTIONAL: Integrate OCR API or local AI match logic here
        // FaceRecognitionService::match($driver->passport_photograph, $driver->nin_document);

        return redirect()->route('driver.kyc.summary')
            ->with('success', 'Facial image captured successfully. Verification in progress.');
    }

        /**
         * KYC Summary for completed applications
         */
        public function summary()
        {
            $driver = Auth::guard('driver')->user();
            
            if (!$driver->hasCompletedKyc()) {
                return redirect()->route('driver.kyc.index')
                    ->with('warning', 'Please complete your KYC process first.');
            }

            $summary = $driver->getKycSummaryForAdmin();
            
            return view('driver.kyc.summary', compact('driver', 'summary'));
        }

        /**
         * Step 1: Personal Information
         */
        public function showStep1()
        {
            $driver = Auth::guard('driver')->user();
            
            if (!$driver->canPerformKyc()) {
                return redirect()->route('driver.kyc.index')
                    ->with('error', 'KYC process is not available at this time.');
            }

            // Load related data
            $nationalities = Nationality::orderBy('name')->get();
            $states = State::orderBy('name')->get();
            
            return view('driver.kyc.step1', compact('driver', 'nationalities', 'states'));
        }

    /**
     * Process Step 1: Personal Information
     */
    public function postStep1(Request $request)
    {
        $driver = Auth::guard('driver')->user();
        
        if (!$driver->canPerformKyc()) {
            return redirect()->route('driver.kyc.index')
                ->with('error', 'KYC process is not available at this time.');
        }

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:50|regex:/^[a-zA-Z\s]+$/',
            'middle_name' => 'nullable|string|max:50|regex:/^[a-zA-Z\s]+$/',
            'surname' => 'required|string|max:50|regex:/^[a-zA-Z\s]+$/',
            'date_of_birth' => 'required|date|before:-18 years',
            'gender' => 'required|in:male,female,other',
            'marital_status' => 'required|in:single,married,divorced,widowed',
            'nationality_id' => 'required|exists:nationalities,id',
            'state_of_origin' => 'required|exists:states,id',
            'lga_of_origin' => 'required|exists:local_governments,id',
            'religion' => 'nullable|string|max:50',
            'blood_group' => 'nullable|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'height_meters' => 'nullable|numeric|between:1.0,2.5',
            'nin_number' => 'required|string|size:11|unique:drivers,nin_number,' . $driver->id,
            'license_number' => 'required|string|max:50|unique:drivers,license_number,' . $driver->id,
            'phone_2' => 'nullable|string|max:15',
            'emergency_contact_name' => 'required|string|max:100',
            'emergency_contact_phone' => 'required|string|max:15',
            'emergency_contact_relationship' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Please correct the errors below.');
        }

        try {
            DB::beginTransaction();

            // Update driver information
            $driver->update([
                'first_name' => $request->first_name,
                'middle_name' => $request->middle_name,
                'surname' => $request->surname,
                'date_of_birth' => $request->date_of_birth,
                'gender' => $request->gender,
                'marital_status' => $request->marital_status,
                'nationality_id' => $request->nationality_id,
                'state_of_origin' => $request->state_of_origin,
                'lga_of_origin' => $request->lga_of_origin,
                'religion' => $request->religion,
                'blood_group' => $request->blood_group,
                'height_meters' => $request->height_meters,
                'nin_number' => $request->nin_number,
                'license_number' => $request->license_number,
                'phone_2' => $request->phone_2,
                'emergency_contact_name' => $request->emergency_contact_name,
                'emergency_contact_phone' => $request->emergency_contact_phone,
                'emergency_contact_relationship' => $request->emergency_contact_relationship,
                
                // Update KYC tracking
                'kyc_step' => 1,
                'kyc_status' => 'in_progress',
                'kyc_step_1_completed_at' => now(),
                'kyc_last_activity_at' => now(),
            ]);

            DB::commit();

            Log::info('KYC Step 1 completed', ['driver_id' => $driver->id]);

            return redirect()->route('driver.kyc.step2')
                ->with('success', 'Personal information saved successfully! Proceed to Step 2.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('KYC Step 1 failed', ['driver_id' => $driver->id, 'error' => $e->getMessage()]);
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'An error occurred while saving your information. Please try again.');
        }
    }

    /**
     * Step 2: Contact & Address Information
     */
    public function showStep2()
    {
        $driver = Auth::guard('driver')->user();
        
        if (!$driver->isKycStepCompleted(1)) {
            return redirect()->route('driver.kyc.step1')
                ->with('error', 'Please complete Step 1 first.');
        }

        $states = State::orderBy('name')->get();
        $banks = Bank::orderBy('name')->get();
        
        return view('driver.kyc.step2', compact('driver', 'states', 'banks'));
    }

    /**
     * Process Step 2: Contact & Address Information
     */
    public function postStep2(Request $request)
    {
        $driver = Auth::guard('driver')->user();
        
        if (!$driver->isKycStepCompleted(1)) {
            return redirect()->route('driver.kyc.step1')
                ->with('error', 'Please complete Step 1 first.');
        }

        // Enhanced validation with better error messages
        $validator = Validator::make($request->all(), [
            'residential_address' => 'required|string|max:500',
            'residence_state_id' => 'required|exists:states,id',
            'residence_lga_id' => 'required|exists:local_governments,id',
            'city' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:10',
            'license_class' => 'required|in:A,B,C,D,E,F',
            'license_issue_date' => 'required|date|before:today',
            'license_expiry_date' => 'required|date|after:today|after:license_issue_date',
            'years_of_experience' => 'required|integer|min:0|max:50',
            'previous_company' => 'nullable|string|max:100',
            'bank_id' => 'required|exists:banks,id',
            'account_number' => 'required|string|size:10|regex:/^[0-9]{10}$/',
            'account_name' => 'required|string|max:100',
            'bvn' => 'required|string|size:11|regex:/^[0-9]{11}$/',
        ], [
            'residential_address.required' => 'Please enter your current residential address.',
            'residence_state_id.required' => 'Please select your current state.',
            'residence_lga_id.required' => 'Please select your current Local Government Area.',
            'license_class.required' => 'Please select your driver\'s license class.',
            'license_issue_date.required' => 'Please enter your license issue date.',
            'license_issue_date.before' => 'License issue date cannot be in the future.',
            'license_expiry_date.required' => 'Please enter your license expiry date.',
            'license_expiry_date.after' => 'License expiry date must be in the future and after issue date.',
            'years_of_experience.required' => 'Please enter your years of driving experience.',
            'bank_id.required' => 'Please select your bank.',
            'account_number.required' => 'Please enter your bank account number.',
            'account_number.size' => 'Account number must be exactly 10 digits.',
            'account_number.regex' => 'Account number must contain only numbers.',
            'account_name.required' => 'Please enter the account holder name.',
            'bvn.required' => 'Please enter your Bank Verification Number (BVN).',
            'bvn.size' => 'BVN must be exactly 11 digits.',
            'bvn.regex' => 'BVN must contain only numbers.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Please correct the errors below.');
        }

        try {
            DB::beginTransaction();

            // Prepare data for update
            $updateData = [
                'residential_address' => trim($request->residential_address),
                'residence_state_id' => $request->residence_state_id,
                'residence_lga_id' => $request->residence_lga_id,
                'city' => $request->city ? trim($request->city) : null,
                'postal_code' => $request->postal_code ? trim($request->postal_code) : null,
                'license_class' => $request->license_class,
                'license_issue_date' => $request->license_issue_date,
                'license_expiry_date' => $request->license_expiry_date,
                'years_of_experience' => (int)$request->years_of_experience,
                'previous_company' => $request->previous_company ? trim($request->previous_company) : null,
                'bank_id' => $request->bank_id,
                'account_number' => $request->account_number,
                'account_name' => trim($request->account_name),
                'bvn' => $request->bvn,
                
                // Update KYC tracking
                'kyc_step' => 2,
                'kyc_step_2_completed_at' => now(),
                'kyc_last_activity_at' => now(),
                'kyc_status' => 'in_progress',
            ];

            // Log the data being saved for debugging
            Log::info('KYC Step 2 - Attempting to save data', [
                'driver_id' => $driver->id,
                'data_keys' => array_keys($updateData),
                'fillable_check' => array_intersect(array_keys($updateData), $driver->getFillable())
            ]);

            // Update driver information
            $result = $driver->update($updateData);

            if (!$result) {
                throw new \Exception('Failed to update driver record');
            }

            // Verify the data was saved
            $driver->refresh();
            Log::info('KYC Step 2 - Data verification', [
                'driver_id' => $driver->id,
                'kyc_step' => $driver->kyc_step,
                'kyc_step_2_completed_at' => $driver->kyc_step_2_completed_at,
                'residential_address_saved' => !empty($driver->residential_address),
                'bank_id_saved' => !empty($driver->bank_id),
            ]);

            DB::commit();

            Log::info('KYC Step 2 completed successfully', ['driver_id' => $driver->id]);

            return redirect()->route('driver.kyc.step3')
                ->with('success', 'Contact and address information saved successfully! Proceed to Step 3.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('KYC Step 2 failed', [
                'driver_id' => $driver->id, 
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'An error occurred while saving your information. Please try again. Error: ' . $e->getMessage());
        }
    }

    /**
     * Step 3: Employment & Document Upload
     */
    public function showStep3()
    {
        $driver = Auth::guard('driver')->user();
        
        if (!$driver->isKycStepCompleted(2)) {
            return redirect()->route('driver.kyc.step2')
                ->with('error', 'Please complete Step 2 first.');
        }

        $requiredDocuments = $driver->getRequiredKycDocuments();
        $documentStatus = $driver->getKycDocumentStatus();
        
        return view('driver.kyc.step3', compact('driver', 'requiredDocuments', 'documentStatus'));
    }

    /**
     * Process Step 3: Employment & Document Upload
     */
    public function postStep3(Request $request)
    {
        $driver = Auth::guard('driver')->user();
        
        if (!$driver->isKycStepCompleted(2)) {
            return redirect()->route('driver.kyc.step2')
                ->with('error', 'Please complete Step 2 first.');
        }

        $validator = Validator::make($request->all(), [
            'current_employer' => 'nullable|string|max:100',
            'employment_start_date' => 'nullable|date|before_or_equal:today',
            'is_working' => 'required|boolean',
            'previous_workplace' => 'nullable|string|max:255',
            'previous_work_id_record' => 'nullable|string|max:255',
            'reason_stopped_working' => 'nullable|string|max:500',
            'has_vehicle' => 'required|boolean',
            'vehicle_type' => 'nullable|string|max:100',
            'vehicle_year' => 'nullable|integer|min:1980|max:' . (date('Y') + 1),
            'preferred_work_location' => 'nullable|string|max:255',
            'available_for_night_shifts' => 'required|boolean',
            'available_for_weekend_work' => 'required|boolean',
            'special_skills' => 'nullable|string|max:500',

            // Document uploads
            'driver_license_scan' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'national_id' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'passport_photo' => 'required|file|mimes:jpg,jpeg,png|max:2048',
            'utility_bill' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Please correct the errors below.');
        }

        try {
            DB::beginTransaction();

            // Handle file uploads
            $uploadedFiles = [];
            $uploadDir = 'driver_documents/' . $driver->driver_id;

            foreach (['driver_license_scan', 'national_id', 'passport_photo', 'utility_bill'] as $fileField) {
                if ($request->hasFile($fileField)) {
                    $file = $request->file($fileField);
                    $filename = $fileField . '_' . time() . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs($uploadDir, $filename, 'public');
                    $uploadedFiles[$fileField] = $path;
                }
            }

            // Update driver information
            $updateData = [
                'current_employer' => $request->current_employer,
                'employment_start_date' => $request->employment_start_date,
                'is_working' => $request->is_working,
                'previous_workplace' => $request->previous_workplace,
                'previous_work_id_record' => $request->previous_work_id_record,
                'reason_stopped_working' => $request->reason_stopped_working,
                'has_vehicle' => $request->has_vehicle,
                'vehicle_type' => $request->vehicle_type,
                'vehicle_year' => $request->vehicle_year,
                'preferred_work_location' => $request->preferred_work_location,
                'available_for_night_shifts' => $request->available_for_night_shifts,
                'available_for_weekend_work' => $request->available_for_weekend_work,
                'special_skills' => $request->special_skills,

                // Update KYC tracking
                'kyc_step' => 3,
                'kyc_step_3_completed_at' => now(),
                'kyc_completed_at' => now(),
                'kyc_submitted_at' => now(),
                'kyc_status' => 'completed',
                'verification_status' => 'reviewing',
                'kyc_last_activity_at' => now(),
            ];

            // Add document paths
            foreach ($uploadedFiles as $field => $path) {
                if ($field === 'driver_license_scan') {
                    $updateData['driver_license_scan'] = $path;
                } elseif ($field === 'national_id') {
                    $updateData['national_id'] = $path;
                } elseif ($field === 'passport_photo') {
                    $updateData['passport_photo'] = $path;
                }
            }

            $driver->update($updateData);

            // Create document records
            foreach ($uploadedFiles as $docType => $path) {
                $driver->documents()->create([
                    'document_type' => $docType,
                    'document_path' => $path,
                    'original_filename' => $request->file($docType)->getClientOriginalName(),
                    'mime_type' => $request->file($docType)->getMimeType(),
                    'file_size' => $request->file($docType)->getSize(),
                    'verification_status' => 'pending',
                    'uploaded_at' => now(),
                ]);
            }

            DB::commit();

            // Dispatch verification jobs
            $this->dispatchVerificationJobs($driver, $uploadedFiles);

            // Send notification to admin
            $this->notificationService->notifyAdminsOfKycSubmission($driver);

            Log::info('KYC Step 3 completed', ['driver_id' => $driver->id]);

            return redirect()->route('driver.kyc.index')
                ->with('success', 'Congratulations! Your KYC application has been submitted successfully and is now under review.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('KYC Step 3 failed', ['driver_id' => $driver->id, 'error' => $e->getMessage()]);
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'An error occurred while submitting your KYC application. Please try again.');
        }
    }

    /**
     * Retry KYC process (for rejected applications)
     */
    public function retryKyc(Request $request)
    {
        $driver = Auth::guard('driver')->user();
        
        if ($driver->kyc_status !== 'rejected') {
            return redirect()->route('driver.kyc.index')
                ->with('error', 'KYC retry is not available.');
        }

        if ($driver->kyc_retry_count >= 3) {
            return redirect()->route('driver.kyc.index')
                ->with('error', 'Maximum retry attempts reached. Please contact support.');
        }

        try {
            DB::beginTransaction();

            // Reset KYC process
            $driver->update([
                'kyc_step' => 1,
                'kyc_status' => 'in_progress',
                'kyc_step_1_completed_at' => null,
                'kyc_step_2_completed_at' => null,
                'kyc_step_3_completed_at' => null,
                'kyc_completed_at' => null,
                'kyc_submitted_at' => null,
                'kyc_reviewed_at' => null,
                'kyc_reviewed_by' => null,
                'kyc_retry_count' => $driver->kyc_retry_count + 1,
                'kyc_last_activity_at' => now(),
                'verification_status' => 'pending',
            ]);

            DB::commit();

            Log::info('KYC retry initiated', ['driver_id' => $driver->id, 'retry_count' => $driver->kyc_retry_count]);

            return redirect()->route('driver.kyc.step1')
                ->with('success', 'KYC process has been reset. Please complete all steps again.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('KYC retry failed', ['driver_id' => $driver->id, 'error' => $e->getMessage()]);
            
            return redirect()->route('driver.kyc.index')
                ->with('error', 'An error occurred while resetting your KYC process. Please try again.');
        }
    }

    /**
     * Get Local Governments for a state (AJAX)
     */
    public function getLocalGovernments($stateId)
    {
        $lgas = LocalGovernment::where('state_id', $stateId)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($lgas);
    }

    /**
     * Calculate KYC readiness percentage
     */
    private function calculateKycReadiness($driver)
    {
        $completeness = 0;
        $totalFields = 10;
        
        // Basic profile fields required for KYC
        if ($driver->first_name) $completeness++;
        if ($driver->surname) $completeness++;
        if ($driver->email) $completeness++;
        if ($driver->phone) $completeness++;
        if ($driver->date_of_birth) $completeness++;
        if ($driver->gender) $completeness++;
        if ($driver->residential_address) $completeness++;
        if ($driver->emergency_contact_name) $completeness++;
        if ($driver->emergency_contact_phone) $completeness++;
        if ($driver->license_number) $completeness++;
        
        return round(($completeness / $totalFields) * 100);
    }

    /**
     * Dispatch all verification jobs for the driver
     */
    protected function dispatchVerificationJobs($driver, $uploadedFiles = [])
    {
        $logger = app(VerificationLoggerService::class);

        // Dispatch NIN verification if NIN is provided
        if ($driver->nin_number) {
            NINVerificationJob::dispatch($driver->id, $driver->nin_number)
                ->onQueue('verifications');
        }

        // Dispatch License verification if license details are available
        if ($driver->license_number && $driver->license_expiry_date) {
            LicenseVerificationJob::dispatch($driver->id, $driver->license_number, $driver->license_expiry_date)
                ->onQueue('verifications');
        }

        // Dispatch SmileID verification if biometric data is available
        if (isset($uploadedFiles['passport_photo']) || $driver->passport_photograph) {
            SmileIDVerificationJob::dispatch($driver->id, $driver->nin_number ?? null)
                ->onQueue('verifications');
        }

        // Dispatch FaceID verification if facial image is available
        if (isset($uploadedFiles['passport_photo']) || $driver->passport_photograph) {
            FaceIDVerificationJob::dispatch($driver->id, $driver->passport_photograph ?? $uploadedFiles['passport_photo'] ?? null)
                ->onQueue('verifications');
        }

        Log::info('Verification jobs dispatched', [
            'driver_id' => $driver->id,
            'nin_number' => $driver->nin_number ? 'present' : 'missing',
            'license_number' => $driver->license_number ? 'present' : 'missing',
            'has_photo' => !empty($driver->passport_photograph) || !empty($uploadedFiles)
        ]);
    }
}
