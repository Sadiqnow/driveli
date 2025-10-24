<?php

namespace App\Services\DriverVerification;

use thiagoalessio\TesseractOCR\TesseractOCR;
use Illuminate\Support\Facades\Log;

class OCRService
{
    /**
     * Extract text from image using Tesseract OCR
     *
     * @param string $imagePath
     * @return string
     */
    public function extractText($imagePath)
    {
        try {
            if (!file_exists($imagePath)) {
                Log::error('OCR image file not found: ' . $imagePath);
                return '';
            }

            // Check if Tesseract is available
            if (!class_exists('thiagoalessio\TesseractOCR\TesseractOCR')) {
                Log::warning('Tesseract OCR not available, using fallback');
                return $this->fallbackTextExtraction($imagePath);
            }

            $tesseract = new TesseractOCR($imagePath);

            // Configure Tesseract for better accuracy
            $tesseract->lang('eng'); // English language
            $tesseract->psm(6); // Uniform block of text
            $tesseract->oem(3); // Default OCR Engine mode

            $text = $tesseract->run();

            Log::info('OCR text extraction completed', [
                'image_path' => $imagePath,
                'text_length' => strlen($text)
            ]);

            return trim($text);

        } catch (\Exception $e) {
            Log::error('OCR extraction error: ' . $e->getMessage(), [
                'image_path' => $imagePath
            ]);
            return $this->fallbackTextExtraction($imagePath);
        }
    }

    /**
     * Extract text from license/ID documents
     *
     * @param string $imagePath
     * @return array
     */
    public function extractDocumentData($imagePath)
    {
        $text = $this->extractText($imagePath);

        // For testing, if text is the fallback, use the test text
        if ($text === 'Sample extracted text - OCR service not configured') {
            return $this->parseDocumentText($text);
        }

        return $this->parseDocumentText($text);
    }

    /**
     * Parse extracted text to find relevant document information
     *
     * @param string $text
     * @return array
     */
    public function parseDocumentText($text)
    {
        $data = [
            'name' => '',
            'license_number' => '',
            'expiry_date' => '',
            'date_of_birth' => '',
            'address' => '',
        ];

        // Simple regex patterns for common document fields
        $patterns = [
            'name' => '/(?:NAME|FULL NAME|DRIVER NAME)[\s:]*([A-Z\s]+)/i',
            'license_number' => '/(?:LICENSE|DL|DRIVER.?S LICENSE)[\s#:]*([A-Z0-9\s]+)/i',
            'expiry_date' => '/(?:EXP|EXPIRY|EXPIRES)[\s:]*(\d{1,2}[-\/]\d{1,2}[-\/]\d{2,4})/i',
            'date_of_birth' => '/(?:DOB|BIRTH|DATE OF BIRTH)[\s:]*(\d{1,2}[-\/]\d{1,2}[-\/]\d{2,4})/i',
        ];

        // Alternative patterns for different document formats
        $altPatterns = [
            'name' => '/NAME[\s:]*([A-Z\s]+)/i',
            'license_number' => '/DL[\s#:]*([A-Z0-9\s]+)/i',
        ];

        foreach ($patterns as $field => $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                $data[$field] = trim($matches[1]);
            }
        }

        // Also try alternative patterns
        foreach ($altPatterns as $field => $pattern) {
            if (empty($data[$field]) && preg_match($pattern, $text, $matches)) {
                $data[$field] = trim($matches[1]);
            }
        }

        // Debug: Log the text and extracted data
        Log::debug('OCR parsing', [
            'input_text' => $text,
            'extracted_data' => $data
        ]);

        return $data;
    }

    /**
     * Fallback text extraction for when Tesseract is not available
     *
     * @param string $imagePath
     * @return string
     */
    private function fallbackTextExtraction($imagePath)
    {
        // For now, return a placeholder. In production, you might use a different OCR service
        return 'Sample extracted text - OCR service not configured';
    }
}
