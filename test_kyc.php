<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$driver = App\Models\Drivers::first();
echo 'Driver ID: ' . $driver->id . PHP_EOL;
echo 'canPerformKyc: ' . ($driver->canPerformKyc() ? 'true' : 'false') . PHP_EOL;
echo 'kyc_status: ' . $driver->kyc_status . PHP_EOL;
echo 'getCurrentKycStep: ' . $driver->getCurrentKycStep() . PHP_EOL;
echo 'getKycProgressPercentage: ' . $driver->getKycProgressPercentage() . PHP_EOL;
echo 'isKycStepCompleted(1): ' . ($driver->isKycStepCompleted(1) ? 'true' : 'false') . PHP_EOL;
echo 'hasCompletedKyc: ' . ($driver->hasCompletedKyc() ? 'true' : 'false') . PHP_EOL;
echo 'getKycStatusBadge: ' . json_encode($driver->getKycStatusBadge()) . PHP_EOL;
echo 'getRequiredKycDocuments: ' . json_encode($driver->getRequiredKycDocuments()) . PHP_EOL;
echo 'getKycDocumentStatus: ' . json_encode($driver->getKycDocumentStatus()) . PHP_EOL;
echo 'getKycSummaryForAdmin: ' . json_encode($driver->getKycSummaryForAdmin()) . PHP_EOL;
echo 'getNextKycStep: ' . $driver->getNextKycStep() . PHP_EOL;
echo 'getVerificationScore: ' . $driver->getVerificationScore() . PHP_EOL;

try {
    echo 'getDocumentCompletionPercentage: ' . $driver->getDocumentCompletionPercentage() . PHP_EOL;
} catch (Exception $e) {
    echo 'getDocumentCompletionPercentage error: ' . $e->getMessage() . PHP_EOL;
}

try {
    echo 'getVerificationCompletionPercentage: ' . $driver->getVerificationCompletionPercentage() . PHP_EOL;
} catch (Exception $e) {
    echo 'getVerificationCompletionPercentage error: ' . $e->getMessage() . PHP_EOL;
}
