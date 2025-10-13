<?php

namespace App\Services;

use App\Models\DeactivationRequest;
use App\Models\OtpNotification;
use App\Models\DriverNormalized;
use App\Models\Company;
use App\Models\AdminUser;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DeactivationService
{
    /**
     * Create a deactivation request for a driver
     */
    public function createDriverDeactivationRequest($driverId, $reason, AdminUser $requestedBy = null)
    {
        $driver = DriverNormalized::findOrFail($driverId);

        // Check if driver is currently active
        if (!$driver->is_current) {
            throw new \Exception('Driver is not currently active');
        }

        DB::beginTransaction();

        try {
            $request = DeactivationRequest::create([
                'user_type' => 'driver',
                'user_id' => $driverId,
                'reason' => $reason,
                'status' => 'pending',
                'requested_by' => $requestedBy ? $requestedBy->id : null,
            ]);

            // Log activity
            ActivityLog::create([
                'user_type' => $requestedBy ? AdminUser::class : 'driver',
                'user_id' => $requestedBy ? $requestedBy->id : $driverId,
                'action' => 'deactivation_requested',
                'description' => "Deactivation request created for driver: {$driver->full_name}",
                'metadata' => [
                    'request_id' => $request->id,
                    'driver_id' => $driverId,
                    'reason' => $reason,
                ],
            ]);

            DB::commit();
            return $request;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Create a deactivation request for a company
     */
    public function createCompanyDeactivationRequest($companyId, $reason, AdminUser $requestedBy = null)
    {
        $company = Company::findOrFail($companyId);

        DB::beginTransaction();

        try {
            $request = DeactivationRequest::create([
                'user_type' => 'company',
                'user_id' => $companyId,
                'reason' => $reason,
                'status' => 'pending',
                'requested_by' => $requestedBy ? $requestedBy->id : null,
            ]);

            // Log activity
            ActivityLog::create([
                'user_type' => $requestedBy ? AdminUser::class : 'company',
                'user_id' => $requestedBy ? $requestedBy->id : $companyId,
                'action' => 'deactivation_requested',
                'description' => "Deactivation request created for company: {$company->name}",
                'metadata' => [
                    'request_id' => $request->id,
                    'company_id' => $companyId,
                    'reason' => $reason,
                ],
            ]);

            DB::commit();
            return $request;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Admin-II reviews and sends to Admin-I for approval
     */
    public function adminIIReview($requestId, AdminUser $adminII)
    {
        $request = DeactivationRequest::findOrFail($requestId);

        if ($request->status !== 'pending') {
            throw new \Exception('Request has already been processed');
        }

        // Update request status to under review
        $request->update(['notes' => 'Under Admin-I review']);

        // Log activity
        ActivityLog::create([
            'user_type' => AdminUser::class,
            'user_id' => $adminII->id,
            'action' => 'deactivation_reviewed',
            'description' => "Deactivation request reviewed by Admin-II for {$request->user_type} ID: {$request->user_id}",
            'metadata' => [
                'request_id' => $request->id,
                'admin_level' => 'admin_ii',
            ],
        ]);

        return $request;
    }

    /**
     * Admin-I approves the deactivation request
     */
    public function adminIApprove($requestId, AdminUser $adminI, $notes = null)
    {
        $request = DeactivationRequest::findOrFail($requestId);

        if ($request->status !== 'pending') {
            throw new \Exception('Request has already been processed');
        }

        DB::beginTransaction();

        try {
            // Generate OTP for final confirmation
            $otp = $this->generateOTP($request->user_type, $request->user_id, 'deactivation_confirmation');

            // Approve the request
            $request->approve($adminI, $notes);

            DB::commit();
            return ['request' => $request, 'otp' => $otp];

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Generate OTP for deactivation confirmation
     */
    public function generateOTP($userType, $userId, $type = 'deactivation_confirmation')
    {
        $otpCode = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

        $otp = OtpNotification::create([
            'user_type' => $userType,
            'user_id' => $userId,
            'otp_code' => $otpCode,
            'type' => $type,
            'status' => 'pending',
            'expires_at' => now()->addMinutes(10),
        ]);

        // Here you would integrate with SMS/Email service
        // For now, we'll just mark as sent
        $otp->markAsSent();

        return $otp;
    }

    /**
     * Verify OTP and complete deactivation
     */
    public function verifyOTPAndDeactivate($otpId, $otpCode, $ipAddress = null, $userAgent = null)
    {
        $otp = OtpNotification::findOrFail($otpId);

        if (!$otp->verify($otpCode, $ipAddress, $userAgent)) {
            throw new \Exception('Invalid or expired OTP');
        }

        // Find the deactivation request
        $request = DeactivationRequest::where('user_type', $otp->user_type)
            ->where('user_id', $otp->user_id)
            ->where('status', 'approved')
            ->first();

        if (!$request) {
            throw new \Exception('No approved deactivation request found');
        }

        DB::beginTransaction();

        try {
            // Deactivate the user
            if ($otp->user_type === 'driver') {
                $this->deactivateDriver($otp->user_id);
            } elseif ($otp->user_type === 'company') {
                $this->deactivateCompany($otp->user_id);
            }

            // Update request status
            $request->update(['notes' => 'Deactivated via OTP confirmation']);

            DB::commit();
            return $request;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Deactivate a driver
     */
    private function deactivateDriver($driverId)
    {
        $driver = DriverNormalized::findOrFail($driverId);

        $driver->update([
            'is_current' => false,
            'status' => 'inactive',
        ]);

        // Update driver-company relations
        $driver->driverCompanyRelations()->update(['status' => 'inactive']);

        // Log activity
        ActivityLog::create([
            'user_type' => 'driver',
            'user_id' => $driverId,
            'action' => 'driver_deactivated',
            'description' => "Driver deactivated: {$driver->full_name}",
            'metadata' => [
                'driver_id' => $driverId,
            ],
        ]);
    }

    /**
     * Deactivate a company
     */
    private function deactivateCompany($companyId)
    {
        $company = Company::findOrFail($companyId);

        $company->update(['status' => 'inactive']);

        // Update driver-company relations
        $company->driverCompanyRelations()->update(['status' => 'inactive']);

        // Log activity
        ActivityLog::create([
            'user_type' => 'company',
            'user_id' => $companyId,
            'action' => 'company_deactivated',
            'description' => "Company deactivated: {$company->name}",
            'metadata' => [
                'company_id' => $companyId,
            ],
        ]);
    }

    /**
     * Send OTP challenge for suspicious activity monitoring
     */
    public function sendOTPChallenge($userType, $userId, $reason = 'suspicious_activity')
    {
        $otp = $this->generateOTP($userType, $userId, 'security_challenge');

        // Log activity
        ActivityLog::create([
            'user_type' => $userType,
            'user_id' => $userId,
            'action' => 'otp_challenge_sent',
            'description' => "OTP challenge sent for {$reason}",
            'metadata' => [
                'otp_id' => $otp->id,
                'reason' => $reason,
            ],
        ]);

        return $otp;
    }

    /**
     * Get pending deactivation requests
     */
    public function getPendingRequests()
    {
        return DeactivationRequest::with(['requester', 'approver'])
            ->pending()
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get deactivation statistics
     */
    public function getDeactivationStats()
    {
        return [
            'pending_requests' => DeactivationRequest::pending()->count(),
            'approved_today' => DeactivationRequest::approved()
                ->whereDate('approved_at', today())
                ->count(),
            'total_deactivated_drivers' => DriverNormalized::where('is_current', false)->count(),
            'total_deactivated_companies' => Company::where('status', 'inactive')->count(),
        ];
    }
}
