<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureKycStepComplete
{
    public function handle(Request $request, Closure $next, $requiredStep)
    {
        $driver = $request->user()->driver;
        
        if (!$driver) {
            return redirect()->route('drivers.register');
        }

        // Map steps to their requirements
        $stepRequirements = [
            2 => ['kyc_step' => 'not_started', 'fields' => ['license_number', 'first_name', 'surname']],
            3 => ['kyc_step' => 'step_2_in_progress', 'fields' => ['date_of_birth', 'nin_number']]
        ];

        $requirement = $stepRequirements[$requiredStep] ?? null;
        if (!$requirement) {
            return $next($request);
        }

        // Check if previous step is complete
        if ($driver->kyc_step === $requirement['kyc_step']) {
            foreach ($requirement['fields'] as $field) {
                if (empty($driver->$field)) {
                    return redirect()->route('drivers.step'.($requiredStep-1))
                        ->with('error', 'Please complete the previous step first.');
                }
            }
        }

        return $next($request);
    }
}