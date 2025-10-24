<?php

namespace App\Services;

use App\Models\DriverDocument;
use App\Models\Drivers;
use Illuminate\Support\Facades\Log;
use Exception;

class OCRService
{
    private $provider;

    public function __construct()
    {
        $this->initializeProvider();
    }

    /**
     * Initialize OCR provider based on configuration
     */
    private function initializeProvider()
    {
        $preferredProvider = config('drivelink.ocr.preferred_provider', 'tesseract');

        switch ($preferredProvider) {
            case 'google_vision':
                $this->provider = new \App\Services\Providers\GoogleVisionProvider();
                break;
            case 'aws_textract':
                $this->provider = new \App\Services\Providers\AwsTextractProvider();
                break;
            case 'tesseract':
            default:
                $this->provider = new \App\Services\Providers\TesseractProvider();
                break;
        }

        if (!$this->provider->isAvailable()) {
            Log::warning("Preferred OCR provider '{$preferredProvider}' is not available, falling back to Tesseract");
            $this->provider = new \App\Services\Providers\TesseractProvider();
        }
    }

    /**
     * Process documents for a driver and store OCR data and confidence
     *
     * @param Drivers $driver
     * @return array Processing results
     */
    public function processDocuments(Drivers $driver): array
    {
        $results = [
            'success' => true,
            'processed_documents' => [],
            'errors' => []
        ];

        try {
            // Get all documents for the driver
            $documents = DriverDocument::where('driver_id', $driver->id)->get();

            foreach ($documents as $document) {
                try {
                    $ocrResult = $this->processSingleDocument($document);

                    if ($ocrResult['success']) {
                        $results['processed_documents'][] = [
                            'document_id' => $document->id,
                            'document_type' => $document->document_type,
                            'ocr_data' => $ocrResult['normalized_data'],
                            'ocr_confidence' => $ocrResult['confidence']
                        ];
                    } else {
                        $results['errors'][] = [
                            'document_id' => $document->id,
                            'document_type' => $document->document_type,
                            'error' => $ocrResult['error']
                        ];
                    }

                } catch (Exception $e) {
                    Log::error('Error processing document', [
                        'document_id' => $document->id,
                        'driver_id' => $driver->id,
                        'error' => $e->getMessage()
                    ]);

                    $results['errors'][] = [
                        'document_id' => $document->id,
                        'document_type' => $document->document_type,
                        'error' => $e->getMessage()
                    ];
                }
            }

            // Update overall success status
            $results['success'] = empty($results['errors']);

        } catch (Exception $e) {
            Log::error('OCR processing failed for driver', [
                'driver_id' => $driver->id,
                'error' => $e->getMessage()
            ]);

            $results['success'] = false;
            $results['error'] = $e->getMessage();
        }

        return $results;
    }

    /**
     * Process a single document
     */
    private function processSingleDocument(DriverDocument $document): array
    {
        // Get the document image path
        $imagePath = $this->getDocumentImagePath($document);

        if (!$imagePath || !file_exists($imagePath)) {
            return [
                'success' => false,
                'error' => 'Document image not found',
                'normalized_data' => [],
                'confidence' => 0.0
            ];
        }

        // Extract text using OCR provider
        $rawResult = $this->provider->extractText($imagePath, $document->document_type);

        if (!$rawResult['success']) {
            return [
                'success' => false,
                'error' => $rawResult['error'] ?? 'OCR extraction failed',
                'normalized_data' => [],
                'confidence' => 0.0
            ];
        }

        // Parse raw result into normalized format
        $normalizedData = $this->parseRawResult($rawResult, $document->document_type);
        $confidence = $this->provider->getConfidenceScore($rawResult);

        // Update document with OCR data
        $document->update([
            'ocr_data' => $normalizedData,
            'ocr_match_score' => $confidence
        ]);

        return [
            'success' => true,
            'normalized_data' => $normalizedData,
            'confidence' => $confidence,
            'raw_text' => $rawResult['text']
        ];
    }

    /**
     * Parse raw OCR result into normalized field map
     *
     * @param array $rawResult
     * @param string $documentType
     * @return array Normalized data
     */
    public function parseRawResult(array $rawResult, string $documentType): array
    {
        $text = $rawResult['text'];

        switch ($documentType) {
            case 'nin':
                return $this->parseNINData($text);
            case 'license':
                return $this->parseLicenseData($text);
            case 'utility':
                return $this->parseUtilityData($text);
            case 'photo':
                return $this->parsePhotoData($text);
            default:
                return [
                    'raw_text' => $text,
                    'parsed_at' => now(),
                    'document_type' => $documentType
                ];
        }
    }

    /**
     * Parse NIN document data
     */
    private function parseNINData(string $text): array
    {
        // Example parsing logic for NIN document
        $data = [
            'nin' => $this->extractPattern($text, '/NIN[:\s]*([A-Z0-9]+)/i'),
            'first_name' => $this->extractPattern($text, '/First\s*Name[:\s]*([A-Za-z\s]+)/i'),
            'surname' => $this->extractPattern($text, '/Surname[:\s]*([A-Za-z\s]+)/i'),
            'date_of_birth' => $this->extractPattern($text, '/Date\s*of\s*Birth[:\s]*([\d\/\-\.]+)/i'),
            'phone' => $this->extractPattern($text, '/Phone[:\s]*([\d\s\-\+\(\)])/i'),
            'address' => $this->extractPattern($text, '/Address[:\s]*([^\n]+)/i'),
            'raw_text' => $text,
            'parsed_at' => now()
        ];

        return array_filter($data, fn($value) => !empty($value));
    }

    /**
     * Parse driver's license data
     */
    private function parseLicenseData(string $text): array
    {
        $data = [
            'license_number' => $this->extractPattern($text, '/License\s*No[:\s]*([A-Z0-9]+)/i'),
            'first_name' => $this->extractPattern($text, '/First\s*Name[:\s]*([A-Za-z\s]+)/i'),
            'surname' => $this->extractPattern($text, '/Surname[:\s]*([A-Za-z\s]+)/i'),
            'date_of_birth' => $this->extractPattern($text, '/DOB[:\s]*([\d\/\-\.]+)/i'),
            'issue_date' => $this->extractPattern($text, '/Issue\s*Date[:\s]*([\d\/\-\.]+)/i'),
            'expiry_date' => $this->extractPattern($text, '/Expiry\s*Date[:\s]*([\d\/\-\.]+)/i'),
            'class' => $this->extractPattern($text, '/Class[:\s]*([A-Z0-9]+)/i'),
            'raw_text' => $text,
            'parsed_at' => now()
        ];

        return array_filter($data, fn($value) => !empty($value));
    }

    /**
     * Parse utility bill data
     */
    private function parseUtilityData(string $text): array
    {
        $data = [
            'account_number' => $this->extractPattern($text, '/Account\s*No[:\s]*([A-Z0-9]+)/i'),
            'customer_name' => $this->extractPattern($text, '/Customer\s*Name[:\s]*([A-Za-z\s]+)/i'),
            'address' => $this->extractPattern($text, '/Address[:\s]*([^\n]+)/i'),
            'bill_date' => $this->extractPattern($text, '/Bill\s*Date[:\s]*([\d\/\-\.]+)/i'),
            'due_date' => $this->extractPattern($text, '/Due\s*Date[:\s]*([\d\/\-\.]+)/i'),
            'amount' => $this->extractPattern($text, '/Amount[:\s]*([\d\.,]+)/i'),
            'utility_type' => $this->extractPattern($text, '/(Electricity|Water|Gas|Internet)/i'),
            'raw_text' => $text,
            'parsed_at' => now()
        ];

        return array_filter($data, fn($value) => !empty($value));
    }

    /**
     * Parse photo/ID data (for face recognition or additional verification)
     */
    private function parsePhotoData(string $text): array
    {
        // Photos typically don't have much text, but OCR might detect watermarks or metadata
        $data = [
            'detected_text' => $text,
            'has_watermark' => preg_match('/watermark|copyright/i', $text),
            'has_metadata' => preg_match('/\d{4}-\d{2}-\d{2}|\d{2}\/\d{2}\/\d{4}/', $text),
            'raw_text' => $text,
            'parsed_at' => now()
        ];

        return array_filter($data, fn($value) => !empty($value));
    }

    /**
     * Extract pattern from text using regex
     */
    private function extractPattern(string $text, string $pattern): ?string
    {
        if (preg_match($pattern, $text, $matches)) {
            return trim($matches[1]);
        }
        return null;
    }

    /**
     * Get document image path
     */
    private function getDocumentImagePath(DriverDocument $document): ?string
    {
        if ($document->document_path) {
            return storage_path('app/public/' . $document->document_path);
        }

        if ($document->file_content) {
            // Handle binary content if needed
            return null;
        }

        return null;
    }
}
