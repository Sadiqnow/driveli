<?php

namespace App\Services\DriverVerification;

use App\Models\Drivers;
use Illuminate\Support\Facades\Log;

class FacialService
{
    /**
     * Perform facial matching for driver verification
     *
     * @param Drivers $driver
     * @return float Match score between 0.0 and 1.0
     */
    public function match(Drivers $driver): float
    {
        try {
            // Get driver's profile picture
            $profileImagePath = $this->getProfileImagePath($driver);

            // Get reference image from documents (e.g., license photo)
            $referenceImagePath = $this->getReferenceImagePath($driver);

            if (!$profileImagePath || !$referenceImagePath) {
                Log::warning('Facial match failed: Missing images', [
                    'driver_id' => $driver->id,
                    'profile_image' => $profileImagePath,
                    'reference_image' => $referenceImagePath
                ]);
                return 0.0;
            }

            // TODO: Implement actual facial recognition logic
            // For now, return a placeholder score
            $matchScore = $this->verifyFace($profileImagePath, $referenceImagePath) ? 0.8 : 0.2;

            Log::info('Facial match completed', [
                'driver_id' => $driver->id,
                'match_score' => $matchScore
            ]);

            return $matchScore;

        } catch (\Exception $e) {
            Log::error('Facial match error', [
                'driver_id' => $driver->id,
                'error' => $e->getMessage()
            ]);
            return 0.0;
        }
    }

    /**
     * Perform facial verification using AWS Rekognition
     */
    public function verifyFace($imagePath, $referenceImagePath)
    {
        try {
            // Check if AWS SDK is available
            if (!class_exists('Aws\Rekognition\RekognitionClient')) {
                Log::warning('AWS Rekognition not available, using fallback');
                return $this->fallbackFaceVerification($imagePath, $referenceImagePath);
            }

            $rekognition = new \Aws\Rekognition\RekognitionClient([
                'version' => 'latest',
                'region' => config('services.aws.region', 'us-east-1'),
                'credentials' => [
                    'key' => config('services.aws.key'),
                    'secret' => config('services.aws.secret'),
                ],
            ]);

            // Read image files
            $sourceImage = file_get_contents($imagePath);
            $targetImage = file_get_contents($referenceImagePath);

            if (!$sourceImage || !$targetImage) {
                return false;
            }

            $result = $rekognition->compareFaces([
                'SourceImage' => [
                    'Bytes' => $sourceImage,
                ],
                'TargetImage' => [
                    'Bytes' => $targetImage,
                ],
                'SimilarityThreshold' => 80.0,
            ]);

            $faceMatches = $result->get('FaceMatches');
            return !empty($faceMatches) && $faceMatches[0]['Similarity'] >= 80.0;

        } catch (\Exception $e) {
            Log::error('AWS Rekognition error: ' . $e->getMessage());
            return $this->fallbackFaceVerification($imagePath, $referenceImagePath);
        }
    }

    /**
     * Fallback facial verification using basic image comparison
     */
    private function fallbackFaceVerification($imagePath, $referenceImagePath)
    {
        try {
            // Use Intervention Image for basic comparison
            $image1 = \Intervention\Image\ImageManagerStatic::make($imagePath);
            $image2 = \Intervention\Image\ImageManagerStatic::make($referenceImagePath);

            // Basic size comparison
            if (abs($image1->width() - $image2->width()) > 50 || abs($image1->height() - $image2->height()) > 50) {
                return false;
            }

            // For now, return true if images exist and are similar in size
            // In production, implement more sophisticated comparison
            return true;

        } catch (\Exception $e) {
            Log::error('Fallback face verification error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get driver's profile image path
     */
    private function getProfileImagePath(Drivers $driver): ?string
    {
        if ($driver->profile_picture) {
            return storage_path('app/public/' . $driver->profile_picture);
        }
        return null;
    }

    /**
     * Get reference image path from driver's documents
     */
    private function getReferenceImagePath(Drivers $driver): ?string
    {
        // Look for license or photo document
        $document = $driver->documents()->whereIn('document_type', ['license', 'photo'])->first();

        if ($document && $document->document_path) {
            return storage_path('app/public/' . $document->document_path);
        }

        return null;
    }
}
