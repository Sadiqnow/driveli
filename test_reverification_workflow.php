<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Console\Command;
use App\Models\Verification;
use App\Models\Drivers;
use App\Jobs\ReverificationSchedulerJob;
use App\Jobs\NINVerificationJob;
use App\Jobs\LicenseVerificationJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

echo "=== COMPREHENSIVE REVERIFICATION WORKFLOW TEST ===\n\n";

try {
    // Initialize Laravel
    $app = require_once 'bootstrap/app.php';
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

    echo "1. INITIAL SYSTEM STATE\n";
    echo "------------------------\n";

    // Check current verifications
    $totalVerifications = Verification::count();
    $expiredVerifications = Verification::whereNotNull('expires_at')
        ->where('expires_at', '<=', now())
        ->whereIn('status', ['completed', 'approved'])
        ->count();

    $requiringReverification = Verification::where('requires_reverification', true)->count();

    echo "Total verifications: {$totalVerifications}\n";
    echo "Expired verifications: {$expiredVerifications}\n";
    echo "Requiring reverification: {$requiringReverification}\n\n";

    // Show detailed verification status
    $verifications = Verification::with('verifiable')->get();
    foreach ($verifications as $verification) {
        $driver = $verification->verifiable;
        echo "ID {$verification->id}: {$verification->type} ({$verification->status})";
        if ($verification->expires_at) {
            echo " - Expires: {$verification->expires_at}";
        }
        if ($verification->requires_reverification) {
            echo " - REQUIRES REV";
        }
        if ($verification->last_reverification_check) {
            echo " - Last check: {$verification->last_reverification_check}";
        }
        echo "\n";
        if ($driver) {
            echo "  Driver: {$driver->full_name} (ID: {$driver->id})\n";
        }
    }
    echo "\n";

    echo "2. RUNNING REVERIFICATION SCHEDULER\n";
    echo "-------------------------------------\n";

    // Dispatch the reverification scheduler job
    ReverificationSchedulerJob::dispatchSync();

    echo "Reverification scheduler completed.\n\n";

    echo "3. POST-SCHEDULER STATE\n";
    echo "-----------------------\n";

    // Check state after scheduler
    $requiringReverificationAfter = Verification::where('requires_reverification', true)->count();
    $driversRequiringReverification = Drivers::where('verification_status', 'requires_reverification')->count();

    echo "Verifications requiring reverification: {$requiringReverificationAfter}\n";
    echo "Drivers requiring reverification: {$driversRequiringReverification}\n\n";

    // Show updated verification details
    $updatedVerifications = Verification::where('requires_reverification', true)->get();
    foreach ($updatedVerifications as $verification) {
        echo "Marked for reverification - ID {$verification->id}: {$verification->type}\n";
        echo "  Last check: {$verification->last_reverification_check}\n";
    }
    echo "\n";

    echo "4. CHECKING QUEUED JOBS\n";
    echo "------------------------\n";

    // Check if any verification jobs were queued
    $pendingJobs = DB::table('jobs')->count();
    echo "Jobs in queue: {$pendingJobs}\n";

    if ($pendingJobs > 0) {
        $jobs = DB::table('jobs')->get();
        foreach ($jobs as $job) {
            $payload = json_decode($job->payload, true);
            $jobName = $payload['displayName'] ?? 'Unknown';
            echo "  - {$jobName}\n";
        }
    }
    echo "\n";

    echo "5. PROCESSING QUEUED JOBS\n";
    echo "--------------------------\n";

    // Process the queue
    $processedJobs = 0;
    while (DB::table('jobs')->count() > 0 && $processedJobs < 10) { // Limit to prevent infinite loop
        $exitCode = null;
        exec('php artisan queue:work --once --no-interaction', $output, $exitCode);

        if ($exitCode === 0) {
            $processedJobs++;
            echo "Processed job #{$processedJobs}\n";
        } else {
            echo "Job processing failed with exit code: {$exitCode}\n";
            break;
        }
    }

    echo "Total jobs processed: {$processedJobs}\n\n";

    echo "6. FINAL VERIFICATION STATE\n";
    echo "-----------------------------\n";

    // Check final state
    $finalVerifications = Verification::all();
    foreach ($finalVerifications as $verification) {
        $status = $verification->status;
        $requiresRev = $verification->requires_reverification ? 'REQUIRES_REV' : 'OK';
        echo "ID {$verification->id}: {$verification->type} ({$status}) - {$requiresRev}\n";
    }
    echo "\n";

    echo "7. NOTIFICATION CHECK\n";
    echo "---------------------\n";

    // Check if notifications were created
    $recentNotifications = DB::table('notifications')
        ->where('created_at', '>=', now()->subMinutes(10))
        ->count();

    echo "Recent notifications created: {$recentNotifications}\n";

    if ($recentNotifications > 0) {
        $notifications = DB::table('notifications')
            ->where('created_at', '>=', now()->subMinutes(10))
            ->get();

        foreach ($notifications as $notification) {
            echo "  - {$notification->title}: {$notification->message}\n";
        }
    }
    echo "\n";

    echo "8. EDGE CASE TESTING\n";
    echo "--------------------\n";

    // Test with multiple expired verifications
    echo "Testing multiple expired scenarios...\n";

    // Create a test verification that's about to expire
    $testVerification = Verification::first();
    if ($testVerification) {
        // Temporarily modify expiration date
        $originalExpiresAt = $testVerification->expires_at;
        $testVerification->update(['expires_at' => now()->subDays(1)]);

        echo "Modified verification ID {$testVerification->id} to be expired\n";

        // Run scheduler again
        ReverificationSchedulerJob::dispatchSync();

        // Check if it was marked again (should not be due to last_reverification_check)
        $testVerification->refresh();
        $markedAgain = $testVerification->requires_reverification ? 'YES' : 'NO';
        echo "Verification marked again: {$markedAgain}\n";

        // Restore original date
        $testVerification->update(['expires_at' => $originalExpiresAt]);
        echo "Restored original expiration date\n";
    }

    echo "\n";

    echo "9. DATABASE CONSISTENCY CHECK\n";
    echo "------------------------------\n";

    // Check for orphaned records or inconsistencies
    $driversWithReverification = Drivers::where('verification_status', 'requires_reverification')->count();
    $verificationsRequiringRev = Verification::where('requires_reverification', true)->count();

    echo "Drivers requiring reverification: {$driversWithReverification}\n";
    echo "Verifications requiring reverification: {$verificationsRequiringRev}\n";

    if ($driversWithReverification !== $verificationsRequiringRev) {
        echo "⚠️  WARNING: Mismatch between driver and verification counts!\n";
    } else {
        echo "✅ Database consistency maintained\n";
    }

    echo "\n";

    echo "=== TEST COMPLETED SUCCESSFULLY ===\n";

} catch (\Exception $e) {
    echo "❌ TEST FAILED: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
