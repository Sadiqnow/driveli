<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class DocumentOCRService
{
    private $googleVisionApiKey;
    private $awsTextractConfig;
    private $preferredProvider;

    public function __construct()
    {
        $this->googleVisionApiKey = config('services.google_vision.api_key');
        $this->awsTextractConfig = [
            'region' => config('services.aws.region', 'us-east-1'),
            'key' => config('services.aws.access_key_id'),
            'secret' => config('services.aws.secret_access_key')
        ];
        $this->preferredProvider = config('drivelink.ocr.preferred_provider', 'google_vision');
    }

    /**
     * Extract text and data from document image
     * 
     * @param string $imagePath Path to the document image
     * @param string $documentType Type of document (nin, license, bvn)
     * @return array Extracted data
     */
    public function extractDocumentData($imagePath, $documentType)
    {
        try {
            Log::info('Starting OCR extraction', [
                'image_path' => $imagePath,
                'document_type' => $documentType,
                'provider' => $this->preferredProvider
            ]);

            $extractedText = $this->performOCR($imagePath);
            $structuredData = $this->parseDocumentData($extractedText, $documentType);

            Log::info('OCR extraction completed', [
                'document_type' => $documentType,
                'extracted_fields' => array_keys($structuredData)
            ]);

            return [
                'success' => true,
                'data' => $structuredData,
                'raw_text' => $extractedText,
                'provider' => $this->preferredProvider,
                'timestamp' => now()
            ];

        } catch (Exception $e) {
            Log::error('OCR extraction failed', [
                'image_path' => $imagePath,
                'document_type' => $documentType,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'data' => [],
                'raw_text' => '',
                'provider' => $this->preferredProvider,
                'timestamp' => now()
            ];
        }
    }

    /**
     * Perform OCR using the configured provider
     */
    private function performOCR($imagePath)
    {
        switch ($this->preferredProvider) {
            case 'google_vision':
                return $this->googleVisionOCR($imagePath);
            case 'aws_textract':
                return $this->awsTextractOCR($imagePath);
            case 'tesseract':
                return $this->tesseractOCR($imagePath);
            default:
                return $this->fallbackOCR($imagePath);
        }
    }

    /**
     * Google Vision API OCR
     */
    private function googleVisionOCR($imagePath)
    {
        if (!$this->googleVisionApiKey) {
            throw new Exception('Google Vision API key not configured');
        }

        $imageData = base64_encode(file_get_contents($imagePath));

        $response = Http::post("https://vision.googleapis.com/v1/images:annotate?key={$this->googleVisionApiKey}", [
            'requests' => [
                [
                    'image' => ['content' => $imageData],
                    'features' => [
                        ['type' => 'TEXT_DETECTION', 'maxResults' => 10],
                        ['type' => 'DOCUMENT_TEXT_DETECTION', 'maxResults' => 10]
                    ]
                ]
            ]
        ]);

        if (!$response->successful()) {
            throw new Exception('Google Vision API request failed: ' . $response->body());
        }

        $result = $response->json();
        
        if (isset($result['responses'][0]['fullTextAnnotation']['text'])) {
            return $result['responses'][0]['fullTextAnnotation']['text'];
        }

        if (isset($result['responses'][0]['textAnnotations'][0]['description'])) {
            return $result['responses'][0]['textAnnotations'][0]['description'];
        }

        throw new Exception('No text detected in the document');
    }

    /**
     * AWS Textract OCR
     */
    private function awsTextractOCR($imagePath)
    {
        // Note: This requires AWS SDK for PHP
        // For now, providing the structure - actual implementation would need AWS SDK
        
        if (!$this->awsTextractConfig['key'] || !$this->awsTextractConfig['secret']) {
            throw new Exception('AWS Textract credentials not configured');
        }

        // Placeholder implementation
        // In production, you would use AWS SDK:
        /*
        $textract = new \Aws\Textract\TextractClient([
            'region' => $this->awsTextractConfig['region'],
            'version' => 'latest',
            'credentials' => [
                'key' => $this->awsTextractConfig['key'],
                'secret' => $this->awsTextractConfig['secret']
            ]
        ]);

        $result = $textract->detectDocumentText([
            'Document' => [
                'Bytes' => file_get_contents($imagePath)
            ]
        ]);
        */

        // For demo purposes, fall back to tesseract
        return $this->tesseractOCR($imagePath);
    }

    /**
     * Tesseract OCR (local installation required)
     */
    private function tesseractOCR($imagePath)
    {
        // Check if tesseract is available
        $tesseractPath = config('drivelink.ocr.tesseract_path', 'tesseract');
        
        $command = "{$tesseractPath} " . escapeshellarg($imagePath) . " stdout";
        $output = [];
        $returnCode = 0;

        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            throw new Exception('Tesseract OCR failed with return code: ' . $returnCode);
        }

        return implode("\n", $output);
    }

    /**
     * Fallback OCR method using basic image processing
     */
    private function fallbackOCR($imagePath)
    {
        // Simple fallback - in production you might want a more sophisticated approach
        // This could involve calling a different service or using a basic text extraction
        
        Log::warning('Using fallback OCR method - accuracy may be limited');
        
        // For demo purposes, return a message indicating manual review is needed
        throw new Exception('OCR processing unavailable - manual review required');
    }

    /**
     * Parse extracted text into structured data based on document type
     */
    private function parseDocumentData($text, $documentType)
    {
        switch ($documentType) {
            case 'nin':
                return $this->parseNINDocument($text);
            case 'license':
                return $this->parseLicenseDocument($text);
            case 'bvn':
                return $this->parseBVNDocument($text);
            case 'passport':
                return $this->parsePassportDocument($text);
            default:
                return $this->parseGenericDocument($text);
        }
    }

    /**
     * Parse Nigerian National ID (NIN) document
     */
    private function parseNINDocument($text)
    {
        $data = [];
        
        // Common NIN patterns
        $patterns = [
            'nin' => [
                '/(?:NIN|National Identification Number)[\s:]*(\d{11})/i',
                '/(\d{11})/i' // Generic 11-digit pattern
            ],
            'first_name' => [
                '/(?:FIRST NAME|SURNAME|NAME)[\s:]*([A-Z\s]+)/i',
                '/(?:Given Name|Firstname)[\s:]*([A-Z\s]+)/i'
            ],
            'surname' => [
                '/(?:SURNAME|LASTNAME|FAMILY NAME)[\s:]*([A-Z\s]+)/i',
                '/(?:Last Name)[\s:]*([A-Z\s]+)/i'
            ],
            'middle_name' => [
                '/(?:MIDDLE NAME|OTHER NAME)[\s:]*([A-Z\s]+)/i'
            ],
            'date_of_birth' => [
                '/(?:DATE OF BIRTH|DOB|BIRTH DATE)[\s:]*(\d{1,2}[\/\-]\d{1,2}[\/\-]\d{4})/i',
                '/(?:Born)[\s:]*(\d{1,2}[\/\-]\d{1,2}[\/\-]\d{4})/i',
                '/(\d{1,2}[\/\-]\d{1,2}[\/\-]\d{4})/'
            ],
            'gender' => [
                '/(?:GENDER|SEX)[\s:]*([MF]|MALE|FEMALE)/i'
            ],
            'state_of_origin' => [
                '/(?:STATE OF ORIGIN|STATE)[\s:]*([A-Z\s]+STATE)/i'
            ]
        ];

        foreach ($patterns as $field => $fieldPatterns) {
            foreach ($fieldPatterns as $pattern) {
                if (preg_match($pattern, $text, $matches)) {
                    $data[$field] = trim($matches[1]);
                    break; // Use first match found
                }
            }
        }

        // Clean and validate extracted data
        return $this->cleanExtractedData($data);
    }

    /**
     * Parse Driver's License document
     */
    private function parseLicenseDocument($text)
    {
        $data = [];
        
        $patterns = [
            'license_number' => [
                '/(?:LICENSE NO|LIC NO|LICENCE NUMBER|LICENSE NUMBER)[\s:]*([A-Z0-9]+)/i',
                '/([A-Z]{3}\d{8}[A-Z]{2})/' // Nigerian license format
            ],
            'first_name' => [
                '/(?:FIRST NAME|SURNAME|NAME)[\s:]*([A-Z\s]+)/i',
                '/(?:Given Name)[\s:]*([A-Z\s]+)/i'
            ],
            'surname' => [
                '/(?:SURNAME|LASTNAME|FAMILY NAME)[\s:]*([A-Z\s]+)/i'
            ],
            'date_of_birth' => [
                '/(?:DATE OF BIRTH|DOB|BIRTH DATE)[\s:]*(\d{1,2}[\/\-]\d{1,2}[\/\-]\d{4})/i',
                '/(\d{1,2}[\/\-]\d{1,2}[\/\-]\d{4})/'
            ],
            'license_class' => [
                '/(?:CLASS|CATEGORY)[\s:]*([A-E])/i'
            ],
            'expiry_date' => [
                '/(?:EXPIRY DATE|EXPIRES|EXP DATE)[\s:]*(\d{1,2}[\/\-]\d{1,2}[\/\-]\d{4})/i'
            ],
            'issue_date' => [
                '/(?:ISSUE DATE|ISSUED|DATE ISSUED)[\s:]*(\d{1,2}[\/\-]\d{1,2}[\/\-]\d{4})/i'
            ]
        ];

        foreach ($patterns as $field => $fieldPatterns) {
            foreach ($fieldPatterns as $pattern) {
                if (preg_match($pattern, $text, $matches)) {
                    $data[$field] = trim($matches[1]);
                    break;
                }
            }
        }

        return $this->cleanExtractedData($data);
    }

    /**
     * Parse BVN document
     */
    private function parseBVNDocument($text)
    {
        $data = [];
        
        $patterns = [
            'bvn' => [
                '/(?:BVN|Bank Verification Number)[\s:]*(\d{11})/i',
                '/(\d{11})/'
            ],
            'first_name' => [
                '/(?:FIRST NAME|SURNAME|NAME)[\s:]*([A-Z\s]+)/i'
            ],
            'surname' => [
                '/(?:SURNAME|LASTNAME|FAMILY NAME)[\s:]*([A-Z\s]+)/i'
            ],
            'date_of_birth' => [
                '/(?:DATE OF BIRTH|DOB)[\s:]*(\d{1,2}[\/\-]\d{1,2}[\/\-]\d{4})/i'
            ],
            'phone' => [
                '/(?:PHONE|MOBILE|TELEPHONE)[\s:]*(\+?\d{10,14})/i'
            ]
        ];

        foreach ($patterns as $field => $fieldPatterns) {
            foreach ($fieldPatterns as $pattern) {
                if (preg_match($pattern, $text, $matches)) {
                    $data[$field] = trim($matches[1]);
                    break;
                }
            }
        }

        return $this->cleanExtractedData($data);
    }

    /**
     * Parse passport document
     */
    private function parsePassportDocument($text)
    {
        $data = [];
        
        $patterns = [
            'passport_number' => [
                '/(?:PASSPORT NO|PASSPORT NUMBER)[\s:]*([A-Z]\d{8})/i',
                '/([A-Z]\d{8})/' // Nigerian passport format
            ],
            'first_name' => [
                '/(?:GIVEN NAMES|FIRST NAME)[\s:]*([A-Z\s]+)/i'
            ],
            'surname' => [
                '/(?:SURNAME)[\s:]*([A-Z\s]+)/i'
            ],
            'date_of_birth' => [
                '/(?:DATE OF BIRTH|DOB)[\s:]*(\d{1,2}[\/\-]\d{1,2}[\/\-]\d{4})/i'
            ],
            'nationality' => [
                '/(?:NATIONALITY|COUNTRY)[\s:]*([A-Z\s]+)/i'
            ],
            'place_of_birth' => [
                '/(?:PLACE OF BIRTH|BIRTH PLACE)[\s:]*([A-Z\s,]+)/i'
            ]
        ];

        foreach ($patterns as $field => $fieldPatterns) {
            foreach ($fieldPatterns as $pattern) {
                if (preg_match($pattern, $text, $matches)) {
                    $data[$field] = trim($matches[1]);
                    break;
                }
            }
        }

        return $this->cleanExtractedData($data);
    }

    /**
     * Parse generic document
     */
    private function parseGenericDocument($text)
    {
        return [
            'raw_text' => $text,
            'requires_manual_review' => true
        ];
    }

    /**
     * Clean and standardize extracted data
     */
    private function cleanExtractedData($data)
    {
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                // Remove extra spaces and normalize
                $data[$key] = preg_replace('/\s+/', ' ', trim($value));
                
                // Convert to proper case for names
                if (in_array($key, ['first_name', 'surname', 'middle_name'])) {
                    $data[$key] = ucwords(strtolower($data[$key]));
                }
                
                // Standardize date formats
                if (in_array($key, ['date_of_birth', 'expiry_date', 'issue_date'])) {
                    $data[$key] = $this->standardizeDateFormat($data[$key]);
                }
                
                // Clean phone numbers
                if ($key === 'phone') {
                    $data[$key] = $this->cleanPhoneNumber($data[$key]);
                }
            }
        }

        return $data;
    }

    /**
     * Standardize date format to YYYY-MM-DD
     */
    private function standardizeDateFormat($dateString)
    {
        try {
            $date = \DateTime::createFromFormat('d/m/Y', $dateString) ?: 
                   \DateTime::createFromFormat('m/d/Y', $dateString) ?: 
                   \DateTime::createFromFormat('d-m-Y', $dateString) ?: 
                   \DateTime::createFromFormat('Y-m-d', $dateString);
            
            return $date ? $date->format('Y-m-d') : $dateString;
        } catch (Exception $e) {
            return $dateString; // Return original if parsing fails
        }
    }

    /**
     * Clean and format phone number
     */
    private function cleanPhoneNumber($phone)
    {
        // Remove all non-digit characters
        $phone = preg_replace('/\D/', '', $phone);
        
        // Add Nigerian country code if not present
        if (strlen($phone) === 10 && substr($phone, 0, 1) === '0') {
            $phone = '234' . substr($phone, 1);
        } elseif (strlen($phone) === 11 && substr($phone, 0, 1) === '0') {
            $phone = '234' . substr($phone, 1);
        }
        
        return '+' . $phone;
    }

    /**
     * Get OCR confidence score (if supported by provider)
     */
    public function getConfidenceScore($ocrResult)
    {
        // This would depend on the OCR provider
        // Google Vision provides confidence scores
        // For now, return a default score
        return isset($ocrResult['confidence']) ? $ocrResult['confidence'] : 0.85;
    }

    /**
     * Validate extracted data quality
     */
    public function validateExtractedData($data, $documentType)
    {
        $validationRules = [
            'nin' => ['nin', 'first_name', 'surname', 'date_of_birth'],
            'license' => ['license_number', 'first_name', 'surname', 'date_of_birth'],
            'bvn' => ['bvn', 'first_name', 'surname', 'date_of_birth'],
            'passport' => ['passport_number', 'first_name', 'surname', 'date_of_birth']
        ];

        $requiredFields = $validationRules[$documentType] ?? [];
        $missingFields = [];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty(trim($data[$field]))) {
                $missingFields[] = $field;
            }
        }

        return [
            'is_valid' => empty($missingFields),
            'missing_fields' => $missingFields,
            'completeness_score' => $requiredFields ? (count($requiredFields) - count($missingFields)) / count($requiredFields) : 0
        ];
    }
}