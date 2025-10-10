<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureKycStepOrder
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $requiredStep): Response
    {
        $driver = auth()->guard('driver')->user() ?? $request->route('driver');
        
        if (!$driver) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Driver not authenticated.',
                    'redirect' => route('driver.login')
                ], 401);
            }
            return redirect()->route('driver.login')->with('error', 'Please log in to continue KYC verification.');
        }

        // Check if KYC is already completed
        if ($driver->kyc_status === 'completed') {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'KYC verification is already completed.',
                    'redirect' => route('driver.dashboard')
                ]);
            }
            return redirect()->route('driver.dashboard')->with('info', 'Your KYC verification is already completed.');
        }

        // Check if KYC is rejected and not allowing retry
        if ($driver->kyc_status === 'rejected' && !$this->allowKycRetry($driver)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'KYC verification was rejected. Please contact support.',
                    'redirect' => route('driver.dashboard')
                ]);
            }
            return redirect()->route('driver.dashboard')->with('error', 'Your KYC verification was rejected. Please contact support for assistance.');
        }

        // Validate step access
        $currentStep = $driver->kyc_step ?? 'not_started';
        $allowedAccess = $this->validateStepAccess($currentStep, $requiredStep);

        if (!$allowedAccess) {
            $redirectStep = $this->getCorrectStep($currentStep);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please complete the previous KYC steps first.',
                    'redirect' => route("driver.kyc.step{$redirectStep}"),
                    'current_step' => $currentStep,
                    'required_step' => $requiredStep
                ]);
            }
            
            return redirect()->route("driver.kyc.step{$redirectStep}")
                ->with('warning', 'Please complete the KYC steps in order.');
        }

        return $next($request);
    }

    /**
     * Validate if user can access the requested step.
     */
    private function validateStepAccess(string $currentStep, string $requiredStep): bool
    {
        $stepOrder = [
            'not_started' => 0,
            'step_1' => 1,
            'step_2' => 2,
            'step_3' => 3,
            'completed' => 4,
        ];

        $currentStepNumber = $stepOrder[$currentStep] ?? 0;
        $requiredStepNumber = (int) str_replace('step_', '', $requiredStep);

        // Allow access to current step or next step
        return $requiredStepNumber <= ($currentStepNumber + 1);
    }

    /**
     * Get the correct step the user should be on.
     */
    private function getCorrectStep(string $currentStep): int
    {
        return match ($currentStep) {
            'not_started' => 1,
            'step_1' => 2,
            'step_2' => 3,
            'step_3' => 3, // Stay on step 3 if not completed
            'completed' => 1, // Should redirect to dashboard, but fallback
            default => 1,
        };
    }

    /**
     * Check if driver is allowed to retry KYC after rejection.
     */
    private function allowKycRetry($driver): bool
    {
        // Allow retry if rejection was recent (within 30 days) and no more than 3 attempts
        if (!$driver->kyc_reviewed_at) {
            return true;
        }

        $daysSinceRejection = $driver->kyc_reviewed_at->diffInDays(now());
        $retryCount = $this->getKycRetryCount($driver);

        return $daysSinceRejection <= 30 && $retryCount < 3;
    }

    /**
     * Get the number of KYC retry attempts.
     */
    private function getKycRetryCount($driver): int
    {
        // This would typically be stored in a separate retry tracking table
        // For now, we'll use a simple approach with session or driver field
        return (int) ($driver->kyc_retry_count ?? 0);
    }
}