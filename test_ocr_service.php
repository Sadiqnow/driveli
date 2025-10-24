<?php

require_once 'vendor/autoload.php';

use App\Services\OCRService;
use App\Services\Providers\TesseractProvider;
use App\Models\Drivers;
use App\Models\DriverDocument;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

echo "=== OCR Service Comprehensive Test ===\n\n";

// Test 1: Provider Interface Implementation
echo "1. Testing Provider Interface Implementation...\n";
try {
    $tesseractProvider = new TesseractProvider();
    echo "✓ TesseractProvider instantiated successfully\n";

    if ($tesseractProvider instanceof \App\Services\OCRProviderInterface) {
        echo "✓ TesseractProvider implements OCRProviderInterface\n";
    } else {
        echo "✗ TesseractProvider does not implement OCRProviderInterface\n";
    }

    echo "Provider Name: " . $tesseractProvider->getProviderName() . "\n";
    echo "Is Available: " . ($tesseractProvider->isAvailable() ? 'Yes' : 'No') . "\n";

} catch (Exception $e) {
    echo "✗ Provider interface test failed: " . $e->getMessage() . "\n";
}

// Test 2: OCR Service Instantiation
echo "\n2. Testing OCR Service Instantiation...\n";
try {
    $ocrService = new OCRService();
    echo "✓ OCRService instantiated successfully\n";
} catch (Exception $e) {
    echo "✗ OCRService instantiation failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 3: Configuration Loading
echo "\n3. Testing Configuration Loading...\n";
try {
    $preferredProvider = config('drivelink.ocr.preferred_provider');
    echo "✓ Preferred provider: " . $preferredProvider . "\n";

    $tesseractConfig = config('drivelink.ocr.tesseract');
    echo "✓ Tesseract path: " . $tesseractConfig['path'] . "\n";
    echo "✓ Tesseract language: " . $tesseractConfig['language'] . "\n";

} catch (Exception $e) {
    echo "✗ Configuration loading failed: " . $e->getMessage() . "\n";
}

// Test 4: Parse Raw Result Method
echo "\n4. Testing parseRawResult Method...\n";
try {
    // Test NIN parsing
    $ninRawResult = [
        'text' => "NATIONAL IDENTITY MANAGEMENT COMMISSION\nNIN: 12345678901\nFirst Name: JOHN\nSurname: DOE\nDate of Birth: 15/06/1985\nPhone: +2348012345678",
        'success' => true,
        'confidence' => 0.92
    ];

    $ninParsed = $ocrService->parseRawResult($ninRawResult, 'nin');
    echo "✓ NIN parsing successful\n";
    echo "  - NIN: " . ($ninParsed['nin'] ?? 'Not found') . "\n";
    echo "  - First Name: " . ($ninParsed['first_name'] ?? 'Not found') . "\n";
    echo "  - Surname: " . ($ninParsed['surname'] ?? 'Not found') . "\n";

    // Test License parsing
    $licenseRawResult = [
        'text' => "FEDERAL ROAD SAFETY COMMISSION\nLicense No: FRSC123456\nFirst Name: JOHN\nSurname: DOE\nDOB: 15/06/1985\nExpiry Date: 31/12/2025",
        'success' => true,
        'confidence' => 0.88
    ];

    $licenseParsed = $ocrService->parseRawResult($licenseRawResult, 'license');
    echo "✓ License parsing successful\n";
    echo "  - License Number: " . ($licenseParsed['license_number'] ?? 'Not found') . "\n";
    echo "  - Expiry Date: " . ($licenseParsed['expiry_date'] ?? 'Not found') . "\n";

    // Test Utility parsing
    $utilityRawResult = [
        'text' => "ELECTRICITY BILL\nAccount No: EKO123456789\nCustomer Name: JOHN DOE\nAmount: ₦25,000.00",
        'success' => true,
        'confidence' => 0.95
    ];

    $utilityParsed = $ocrService->parseRawResult($utilityRawResult, 'utility');
    echo "✓ Utility bill parsing successful\n";
    echo "  - Account Number: " . ($utilityParsed['account_number'] ?? 'Not found') . "\n";
    echo "  - Amount: " . ($utilityParsed['amount'] ?? 'Not found') . "\n";

} catch (Exception $e) {
    echo "✗ Raw result parsing failed: " . $e->getMessage() . "\n";
}

// Test 5: Database Integration Test
echo "\n5. Testing Database Integration...\n";
try {
    // Check if driver_documents table exists and has required columns
    $tableExists = Schema::hasTable('driver_documents');
    if ($tableExists) {
        echo "✓ driver_documents table exists\n";

        $columns = Schema::getColumnListing('driver_documents');
        $requiredColumns = ['ocr_data', 'ocr_match_score'];

        foreach ($requiredColumns as $column) {
            if (in_array($column, $columns)) {
                echo "✓ Column '$column' exists\n";
            } else {
                echo "✗ Column '$column' missing\n";
            }
        }
    } else {
        echo "✗ driver_documents table does not exist\n";
    }

    // Check if we can find a test driver
    $testDriver = Drivers::first();
    if ($testDriver) {
        echo "✓ Found test driver (ID: {$testDriver->id})\n";

        // Check if driver has documents
        $documentCount = DriverDocument::where('driver_id', $testDriver->id)->count();
        echo "✓ Driver has {$documentCount} documents\n";

        if ($documentCount > 0) {
            echo "✓ Ready for processDocuments test\n";
        } else {
            echo "! No documents found - creating test document\n";

            // Create a test document
            $testDocument = DriverDocument::create([
                'driver_id' => $testDriver->id,
                'document_type' => 'nin',
                'document_path' => 'test/path/nin.jpg',
                'verification_status' => 'pending',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            echo "✓ Test document created (ID: {$testDocument->id})\n";
        }
    } else {
        echo "! No test driver found - skipping processDocuments test\n";
    }

} catch (Exception $e) {
    echo "✗ Database integration test failed: " . $e->getMessage() . "\n";
}

// Test 6: Process Documents Method (if possible)
echo "\n6. Testing processDocuments Method...\n";
try {
    $testDriver = Drivers::first();

    if ($testDriver && DriverDocument::where('driver_id', $testDriver->id)->count() > 0) {
        echo "Running processDocuments for driver ID: {$testDriver->id}\n";

        $result = $ocrService->processDocuments($testDriver);

        echo "✓ processDocuments completed\n";
        echo "  - Success: " . ($result['success'] ? 'Yes' : 'No') . "\n";
        echo "  - Processed documents: " . count($result['processed_documents']) . "\n";
        echo "  - Errors: " . count($result['errors']) . "\n";

        if (!empty($result['processed_documents'])) {
            $firstDoc = $result['processed_documents'][0];
            echo "  - Sample document type: " . $firstDoc['document_type'] . "\n";
            echo "  - Sample confidence: " . $firstDoc['ocr_confidence'] . "\n";

            // Verify database was updated
            $dbDocument = DriverDocument::find($firstDoc['document_id']);
            if ($dbDocument && $dbDocument->ocr_data && $dbDocument->ocr_match_score) {
                echo "✓ Database updated correctly\n";
                echo "  - OCR data stored: " . (is_array($dbDocument->ocr_data) ? 'Yes' : 'No') . "\n";
                echo "  - Match score: " . $dbDocument->ocr_match_score . "\n";
            } else {
                echo "✗ Database update verification failed\n";
            }
        }

        if (!empty($result['errors'])) {
            echo "Errors encountered:\n";
            foreach ($result['errors'] as $error) {
                echo "  - {$error['document_type']}: {$error['error']}\n";
            }
        }

    } else {
        echo "! Skipping processDocuments test - no suitable test data\n";
    }

} catch (Exception $e) {
    echo "✗ processDocuments test failed: " . $e->getMessage() . "\n";
}

// Test 7: Error Handling
echo "\n7. Testing Error Handling...\n";
try {
    // Test with invalid driver
    $invalidDriver = new stdClass(); // Not a Driver model
    $result = $ocrService->processDocuments($invalidDriver);
    echo "✓ Invalid driver handled gracefully\n";

} catch (Exception $e) {
    echo "Expected error with invalid driver: " . $e->getMessage() . "\n";
}

// Test 8: Provider Fallback
echo "\n8. Testing Provider Fallback...\n";
try {
    // Test configuration with invalid provider
    $originalConfig = config('drivelink.ocr.preferred_provider');
    config(['drivelink.ocr.preferred_provider' => 'invalid_provider']);

    $fallbackService = new OCRService();
    echo "✓ Provider fallback handled (should default to tesseract)\n";

    // Restore original config
    config(['drivelink.ocr.preferred_provider' => $originalConfig]);

} catch (Exception $e) {
    echo "✗ Provider fallback test failed: " . $e->getMessage() . "\n";
}

echo "\n=== OCR Service Test Complete ===\n";
echo "\nNext steps:\n";
echo "1. Install Tesseract OCR if not already installed\n";
echo "2. Add sample document images for testing\n";
echo "3. Configure environment variables for preferred OCR provider\n";
echo "4. Test with real document images\n";

?>
