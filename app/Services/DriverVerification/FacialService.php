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
     * Placeholder for facial verification logic
     */
    public function verifyFace($imagePath, $referenceImagePath)
    {
        // TODO: Implement actual facial verification using OpenCV, AWS Rekognition, etc.
        return true; // Placeholder return
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
