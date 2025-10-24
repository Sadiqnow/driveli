<?php

namespace App\Services\Providers;

use App\Services\OCRProviderInterface;
use Illuminate\Support\Facades\Log;
use thiagoalessio\TesseractOCR\TesseractOCR;

class TesseractProvider implements OCRProviderInterface
{
    private $tesseractPath;
    private $language;

    public function __construct()
    {
        $this->tesseractPath = config('drivelink.ocr.tesseract.path', 'C:\Program Files\Tesseract-OCR\tesseract.exe');
        $this->language = config('drivelink.ocr.tesseract.language', 'eng');
    }

    /**
     * Extract text from document image using Tesseract OCR
     */
    public function extractText(string $imagePath, string $documentType): array
    {
        try {
            Log::info('Tesseract OCR extraction started', [
                'image_path' => $imagePath,
                'document_type' => $documentType
            ]);

            $tesseract = new TesseractOCR($imagePath);
            $tesseract->executable($this->tesseractPath);
            $tesseract->lang($this->language);

            // Configure based on document type
            switch ($documentType) {
                case 'nin':
                case 'license':
                    $tesseract->psm(6); // Uniform block of text
                    break;
                case 'utility':
                    $tesseract->psm(3); // Fully automatic page segmentation
                    break;
                case 'photo':
                    $tesseract->psm(8); // Single word
                    break;
                default:
                    $tesseract->psm(3);
            }

            $extractedText = $tesseract->run();

            return [
                'success' => true,
                'text' => $extractedText,
                'confidence' => 0.75, // Tesseract doesn't provide confidence scores easily
                'provider' => 'tesseract',
                'timestamp' => now()
            ];

        } catch (\Exception $e) {
            Log::error('Tesseract OCR extraction failed', [
                'image_path' => $imagePath,
                'document_type' => $documentType,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'text' => '',
                'confidence' => 0.0,
                'error' => $e->getMessage(),
                'provider' => 'tesseract',
                'timestamp' => now()
            ];
        }
    }

    /**
     * Get provider name
     */
    public function getProviderName(): string
    {
        return 'tesseract';
    }

    /**
     * Check if Tesseract is available
     */
    public function isAvailable(): bool
    {
        return file_exists($this->tesseractPath) && is_executable($this->tesseractPath);
    }

    /**
     * Get confidence score from raw result
     */
    public function getConfidenceScore(array $rawResult): float
    {
        return $rawResult['confidence'] ?? 0.0;
    }
}
