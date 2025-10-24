<?php

namespace App\Listeners;

use App\Events\DriverVerified;
use App\Notifications\DriverVerificationNotification;
use App\Models\Company;
use Illuminate\Support\Facades\Log;

class NotifyCompanyOnVerification
{
    public function handle(DriverVerified $event)
    {
        try {
            // Get the driver from the event
            $driver = $event->driver;

            // Find the company associated with the driver
            $company = Company::find($driver->company_id);

            if (!$company) {
                Log::warning('No company found for driver verification notification', [
                    'driver_id' => $driver->id,
                    'company_id' => $driver->company_id
                ]);
                return;
            }

            // Send notification to the company
            $company->notify(new DriverVerificationNotification($event->verificationData));

            Log::info('Driver verification notification sent to company', [
                'driver_id' => $driver->id,
                'company_id' => $company->id,
                'notification_type' => 'DriverVerificationNotification'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send driver verification notification', [
                'driver_id' => $event->driver->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
