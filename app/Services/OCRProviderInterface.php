<?php

namespace App\Services;

interface OCRProviderInterface
{
    /**
     * Extract text and data from document image
     *
     * @param string $imagePath Path to the document image
     * @param string $documentType Type of document (nin, license, utility, photo)
     * @return array Raw OCR result with text and confidence
     */
    public function extractText(string $imagePath, string $documentType): array;

    /**
     * Get the provider name
     *
     * @return string
     */
    public function getProviderName(): string;

    /**
     * Check if the provider is available and configured
     *
     * @return bool
     */
    public function isAvailable(): bool;

    /**
     * Get the confidence score from raw result
     *
     * @param array $rawResult
     * @return float
     */
    public function getConfidenceScore(array $rawResult): float;
}
