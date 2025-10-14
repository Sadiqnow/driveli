<?php

require_once 'vendor/autoload.php';

use App\Models\Verification;
use Illuminate\Foundation\Application;
use Illuminate\Contracts\Console\Kernel;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

echo "=== VERIFICATION STATUS CHECK ===\n\n";

try {
    $total = Verification::count();
    $requiring = Verification::where('requires_reverification', true)->count();
    $expired = Verification::expired()->count();
    $failed = Verification::failed()->count();

    echo "Total verifications: $total\n";
    echo "Requiring reverification: $requiring\n";
    echo "Expired: $expired\n";
    echo "Failed: $failed\n\n";

    if ($requiring > 0) {
        echo "Verifications marked for reverification:\n";
        $reverifications = Verification::where('requires_reverification', true)->get();
        foreach ($reverifications as $verification) {
            echo "- ID {$verification->id}: {$verification->type} ({$verification->status}) - Expires: " . ($verification->expires_at ? $verification->expires_at->format('Y-m-d') : 'Never') . "\n";
        }
    }

    echo "\n=== RECENT VERIFICATIONS ===\n";
    $recent = Verification::orderBy('created_at', 'desc')->limit(5)->get();
    foreach ($recent as $verification) {
        echo "- ID {$verification->id}: {$verification->type} ({$verification->status}) - Created: {$verification->created_at->format('Y-m-d H:i:s')}\n";
    }

    echo "\n=== EXPIRED VERIFICATIONS QUERY DEBUG ===\n";
    $expiredQuery = Verification::whereNotNull('expires_at')
        ->where('expires_at', '<=', now())
        ->whereIn('status', ['completed', 'approved'])
        ->where('requires_reverification', false)
        ->where('last_reverification_check', '<', now()->subDays(1));

    echo "Query would find: " . $expiredQuery->count() . " verifications\n";

    $allExpired = Verification::whereNotNull('expires_at')->where('expires_at', '<=', now())->get();
    echo "Total expired (any status): " . count($allExpired) . "\n";

    foreach ($allExpired as $verification) {
        echo "- ID {$verification->id}: {$verification->type} ({$verification->status}) - Expires: " . ($verification->expires_at ? $verification->expires_at->format('Y-m-d') : 'Never') . " - Requires rev: " . ($verification->requires_reverification ? 'Yes' : 'No') . " - Last check: " . ($verification->last_reverification_check ? $verification->last_reverification_check->format('Y-m-d') : 'Never') . "\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
