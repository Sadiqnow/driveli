<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Services\DriverOnboardingProgressService;
use App\Services\NotificationService;
use App\Services\SuperadminActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class DriverOnboardingController extends Controller
{
    protected $progressService;
    protected $notificationService;

    public function __construct(
        DriverOnboardingProgressService $progressService,
        NotificationService $notificationService
    ) {
        $this->middleware('auth:admin');
        $this->middleware('SuperAdminDriverAccess');
        $this->progressService = $progressService;
        $this->notificationService = $notificationService;
    }

    /**
     * Start the onboarding process
     */
    public function start(Request $request)
    {
        // Create initial driver record with minimal data
        $driver = Driver::create([
            'driver_id' => 'DRV-' . strtoupper(uniqid()),
            'first_name' => $request->get('first_name', ''),
            'surname' => $request->get('surname', ''),
            'email' => $request->get('email', ''),
            'phone' => $request->get('phone', ''),
            'status' => 'onboarding',
            'verification_status' => 'pending',
            'kyc_status' => 'pending',
            'is_active' => false,
            'is_available' => false,
        ]);

        // Initialize session for onboarding
        Session::put('onboarding_driver_id', $driver->id);
        Session::put('onboarding_step', 'personal_info');

        // Log activity
        SuperadminActivityLogger::logDriverCreation($driver, $request);

        return redirect()->route('admin.superadmin.drivers.onboarding.step', [
            'driver' => $driver->id,
            'step' => 'personal_info'
        ]);
    }

    /**
     * Show specific onboarding step
     */
    public function showStep(Driver $driver, string $step)
    {
        // Validate step exists
        $steps = $this->progressService->getOnboardingSteps();
        $stepKeys = collect($steps)->pluck('key')->toArray();

        if (!in_array($step, $stepKeys)) {
            abort(404, 'Step not found');
        }

        // Get progress data
        $progress = $this->progressService->getCompletionSummary($driver);
        $stepStatus = $this->progressService->getStepStatus($driver, $step);

        // Store current step in session
        Session::put('onboarding_driver_id', $driver->id);
        Session::put('onboarding_step', $step);

        return view('admin.superadmin.drivers.onboarding.step', compact(
            'driver', 'step', 'progress', 'stepStatus', 'steps'
        ));
    }

    /**
     * Process step submission
     */
    public function processStep(Request $request, Driver $driver, string $step)
    {
        try {
            DB::beginTransaction();

            // Validate step data
            $validator = $this->getStepValidator($request, $step);
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }

            // Process step data
            $this->processStepData($driver, $step, $request->all());

            // Update progress
            $driver->update(['profile_completion_percentage' => $this->progressService->calculateProgress($driver)]);

            // Send notification
            $this->notificationService->sendStepCompletionNotification($driver, $step, $request->all());

            // Log activity
            SuperadminActivityLogger::log(
                'update',
                "Completed onboarding step: {$step} for driver {$driver->full_name}",
                $driver,
                null,
                ['step' => $step, 'progress' => $driver->profile_completion_percentage],
                null,
                $request
            );

            DB::commit();

            // Determine next step
            $nextStep = $this->progressService->getNextStep($driver);

            if ($nextStep) {
                return redirect()->route('admin.superadmin.drivers.onboarding.step', [
                    'driver' => $driver->id,
                    'step' => $nextStep
                ])->with('success', ucfirst(str_replace('_', ' ', $step)) . ' completed successfully!');
            } else {
                // Onboarding completed
                return $this->completeOnboarding($driver, $request);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to process step: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Complete the onboarding process
     */
    private function completeOnboarding(Driver $driver, Request $request)
    {
        // Update driver status
        $driver->update([
            'status' => 'pending_review',
            'kyc_status' => 'submitted',
            'profile_completion_percentage' => 100
        ]);

        // Send submission notification
        $this->notificationService->sendOnboardingSubmissionNotification($driver);

        // Log completion
        SuperadminActivityLogger::log(
            'submit',
            "Driver onboarding completed and submitted for review: {$driver->full_name}",
            $driver,
            null,
            ['status' => 'pending_review', 'progress' => 100],
            null,
            $request
        );

        // Clear session
        Session::forget(['onboarding_driver_id', 'onboarding_step']);

        return redirect()->route('admin.superadmin.drivers.onboarding.review', $driver)
                        ->with('success', 'Onboarding completed! Application submitted for review.');
    }

    /**
     * Show review page for superadmin
     */
    public function showReview(Driver $driver)
    {
        $progress = $this->progressService->getCompletionSummary($driver);

        return view('admin.superadmin.drivers.onboarding.review', compact('driver', 'progress'));
    }

    /**
     * Process admin review decision
     */
    public function processReview(Request $request, Driver $driver)
    {
        $request->validate([
            'decision' => 'required|in:approve,reject',
            'notes' => 'nullable|string|max:1000'
        ]);

        try {
            DB::beginTransaction();

            if ($request->decision === 'approve') {
                $driver->update([
                    'verification_status' => 'verified',
                    'status' => 'active',
                    'verified_at' => now(),
                    'verified_by' => auth('admin')->id(),
                    'verification_notes' => $request->notes,
                    'is_active' => true,
                    'is_available' => true
                ]);

                SuperadminActivityLogger::logDriverApproval($driver, $request->notes, $request);
            } else {
                $driver->update([
                    'verification_status' => 'rejected',
                    'status' => 'rejected',
                    'rejection_reason' => $request->notes
                ]);

                SuperadminActivityLogger::logDriverRejection($driver, $request->notes, $request);
            }

            // Send notification to driver
            $this->notificationService->sendAdminReviewNotification($driver, $request->decision, $request->notes);

            DB::commit();

            return redirect()->route('admin.superadmin.drivers.show', $driver)
                            ->with('success', 'Driver ' . $request->decision . 'd successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to process review: ' . $e->getMessage()]);
        }
    }

    /**
     * Get validator for specific step
     */
    private function getStepValidator(Request $request, string $step)
    {
        $rules = [];

        switch ($step) {
            case 'personal_info':
                $rules = [
                    'first_name' => 'required|string|max:255',
                    'middle_name' => 'nullable|string|max:255',
                    'surname' => 'required|string|max:255',
                    'date_of_birth' => 'required|date|before:today',
                    'gender' => 'required|in:male,female,other',
                    'marital_status' => 'nullable|in:single,married,divorced,widowed'
                ];
                break;

            case 'contact_info':
                $rules = [
                    'phone' => 'required|string|max:20',
                    'phone_2' => 'nullable|string|max:20',
                    'emergency_contact_name' => 'required|string|max:255',
                    'emergency_contact_phone' => 'required|string|max:20',
                    'emergency_contact_relationship' => 'required|string|max:100',
                    'address' => 'nullable|string|max:500',
                    'city' => 'nullable|string|max:100',
                    'state' => 'nullable|string|max:100'
                ];
                break;

            case 'documents':
                $rules = [
                    'profile_picture' => 'required|image|mimes:jpeg,png,jpg|max:2048',
                    'id_document' => 'required|file|mimes:jpeg,png,jpg,pdf|max:5120',
                    'drivers_license' => 'required|file|mimes:jpeg,png,jpg,pdf|max:5120'
                ];
                break;

            case 'banking':
                $rules = [
                    'account_number' => 'required|string|max:20',
                    'account_name' => 'required|string|max:255',
                    'bank_name' => 'required|string|max:255',
                    'bank_code' => 'nullable|string|max:10',
                    'is_primary' => 'boolean'
                ];
                break;

            case 'professional':
                $rules = [
                    'license_number' => 'required|string|max:50|unique:driver_performance,license_number',
                    'license_expiry' => 'required|date|after:today',
                    'years_of_experience' => 'required|integer|min:0|max:50',
                    'vehicle_type' => 'required|string|max:100',
                    'has_guarantor' => 'boolean',
                    'guarantor_name' => 'required_if:has_guarantor,true|string|max:255',
                    'guarantor_phone' => 'required_if:has_guarantor,true|string|max:20'
                ];
                break;

            case 'verification':
                $rules = [
                    'email_verification_code' => 'required|string|size:6',
                    'phone_verification_code' => 'nullable|string|size:6'
                ];
                break;
        }

        return Validator::make($request->all(), $rules);
    }

    /**
     * Process data for specific step
     */
    private function processStepData(Driver $driver, string $step, array $data)
    {
        switch ($step) {
            case 'personal_info':
                $driver->update([
                    'first_name' => $data['first_name'],
                    'middle_name' => $data['middle_name'] ?? null,
                    'surname' => $data['surname']
                ]);

                $driver->personalInfo()->updateOrCreate(
                    ['driver_id' => $driver->id],
                    [
                        'date_of_birth' => $data['date_of_birth'],
                        'gender' => $data['gender'],
                        'marital_status' => $data['marital_status'] ?? null
                    ]
                );
                break;

            case 'contact_info':
                $driver->update([
                    'phone' => $data['phone'],
                    'phone_2' => $data['phone_2'] ?? null
                ]);

                $driver->personalInfo()->updateOrCreate(
                    ['driver_id' => $driver->id],
                    [
                        'name' => $data['emergency_contact_name'],
                        'phone' => $data['emergency_contact_phone'],
                        'relationship' => $data['emergency_contact_relationship'],
                        'address' => $data['address'] ?? null,
                        'city' => $data['city'] ?? null,
                        'state' => $data['state'] ?? null,
                        'is_primary' => true
                    ]
                );
                break;

            case 'documents':
                // Handle file uploads
                if (isset($data['profile_picture'])) {
                    $path = $data['profile_picture']->store('drivers/' . $driver->driver_id . '/profile');
                    $driver->documents()->updateOrCreate(
                        ['driver_id' => $driver->id, 'document_type' => 'profile_picture'],
                        [
                            'document_path' => $path,
                            'file_name' => $data['profile_picture']->getClientOriginalName(),
                            'verification_status' => 'pending'
                        ]
                    );
                }

                if (isset($data['id_document'])) {
                    $path = $data['id_document']->store('drivers/' . $driver->driver_id . '/documents');
                    $driver->documents()->updateOrCreate(
                        ['driver_id' => $driver->id, 'document_type' => 'id_card'],
                        [
                            'document_path' => $path,
                            'file_name' => $data['id_document']->getClientOriginalName(),
                            'verification_status' => 'pending'
                        ]
                    );
                }

                if (isset($data['drivers_license'])) {
                    $path = $data['drivers_license']->store('drivers/' . $driver->driver_id . '/documents');
                    $driver->documents()->updateOrCreate(
                        ['driver_id' => $driver->id, 'document_type' => 'drivers_license'],
                        [
                            'document_path' => $path,
                            'file_name' => $data['drivers_license']->getClientOriginalName(),
                            'verification_status' => 'pending'
                        ]
                    );
                }
                break;

            case 'banking':
                $driver->bankingDetails()->updateOrCreate(
                    ['driver_id' => $driver->id, 'is_primary' => true],
                    [
                        'account_number' => $data['account_number'],
                        'account_name' => $data['account_name'],
                        'bank_name' => $data['bank_name'],
                        'bank_code' => $data['bank_code'] ?? null,
                        'is_primary' => true,
                        'is_verified' => false
                    ]
                );
                break;

            case 'professional':
                $driver->performance()->updateOrCreate(
                    ['driver_id' => $driver->id],
                    [
                        'license_number' => $data['license_number'],
                        'license_expiry_date' => $data['license_expiry'],
                        'years_of_experience' => $data['years_of_experience'],
                        'vehicle_type' => $data['vehicle_type'],
                        'total_jobs_completed' => 0,
                        'average_rating' => 0.00,
                        'total_earnings' => 0.00
                    ]
                );

                // Handle guarantor if provided
                if (isset($data['has_guarantor']) && $data['has_guarantor']) {
                    // Could create a separate guarantor record or store in next_of_kin
                    // For now, we'll store in a note or separate field
                }
                break;

            case 'verification':
                // Handle verification codes
                // This would typically integrate with SMS/email verification services
                $driver->update([
                    'email_verified_at' => now(),
                    'phone_verified_at' => isset($data['phone_verification_code']) ? now() : null
                ]);
                break;
        }
    }

    /**
     * Navigate to previous step
     */
    public function previousStep(Driver $driver, string $currentStep)
    {
        $steps = $this->progressService->getOnboardingSteps();
        $stepKeys = collect($steps)->pluck('key')->toArray();
        $currentIndex = array_search($currentStep, $stepKeys);

        if ($currentIndex > 0) {
            $previousStep = $stepKeys[$currentIndex - 1];
            return redirect()->route('admin.superadmin.drivers.onboarding.step', [
                'driver' => $driver->id,
                'step' => $previousStep
            ]);
        }

        return back()->withErrors(['error' => 'No previous step available']);
    }

    /**
     * Save draft and exit
     */
    public function saveDraft(Driver $driver)
    {
        Session::forget(['onboarding_driver_id', 'onboarding_step']);

        return redirect()->route('admin.superadmin.drivers.show', $driver)
                        ->with('success', 'Onboarding draft saved. You can continue later.');
    }
}
