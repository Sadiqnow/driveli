<?php

namespace App\Exceptions;

use Exception;

class DriverException extends Exception
{
    /**
     * Driver not found exception.
     *
     * @param string $identifier
     * @return static
     */
    public static function notFound(string $identifier): static
    {
        return new static("Driver with identifier '{$identifier}' not found.");
    }

    /**
     * Driver already verified exception.
     *
     * @param string $driverId
     * @return static
     */
    public static function alreadyVerified(string $driverId): static
    {
        return new static("Driver '{$driverId}' is already verified.");
    }

    /**
     * Driver already rejected exception.
     *
     * @param string $driverId
     * @return static
     */
    public static function alreadyRejected(string $driverId): static
    {
        return new static("Driver '{$driverId}' has already been rejected.");
    }

    /**
     * Driver not eligible for verification exception.
     *
     * @param string $driverId
     * @param string $reason
     * @return static
     */
    public static function notEligibleForVerification(string $driverId, string $reason): static
    {
        return new static("Driver '{$driverId}' is not eligible for verification: {$reason}");
    }

    /**
     * Driver document upload failed exception.
     *
     * @param string $documentType
     * @param string $reason
     * @return static
     */
    public static function documentUploadFailed(string $documentType, string $reason): static
    {
        return new static("Failed to upload {$documentType} document: {$reason}");
    }

    /**
     * Driver profile incomplete exception.
     *
     * @param string $driverId
     * @param array $missingFields
     * @return static
     */
    public static function profileIncomplete(string $driverId, array $missingFields): static
    {
        $fields = implode(', ', $missingFields);
        return new static("Driver '{$driverId}' profile is incomplete. Missing: {$fields}");
    }

    /**
     * Driver account suspended exception.
     *
     * @param string $driverId
     * @param string $reason
     * @return static
     */
    public static function accountSuspended(string $driverId, string $reason = null): static
    {
        $message = "Driver account '{$driverId}' is suspended";
        if ($reason) {
            $message .= ": {$reason}";
        }
        return new static($message);
    }

    /**
     * Driver account inactive exception.
     *
     * @param string $driverId
     * @return static
     */
    public static function accountInactive(string $driverId): static
    {
        return new static("Driver account '{$driverId}' is inactive.");
    }

    /**
     * Driver duplicate information exception.
     *
     * @param string $field
     * @param string $value
     * @return static
     */
    public static function duplicateInformation(string $field, string $value): static
    {
        return new static("A driver with {$field} '{$value}' already exists.");
    }

    /**
     * Driver age restriction exception.
     *
     * @param int $age
     * @param int $minAge
     * @param int $maxAge
     * @return static
     */
    public static function ageRestriction(int $age, int $minAge = 18, int $maxAge = 70): static
    {
        return new static("Driver age ({$age}) must be between {$minAge} and {$maxAge} years.");
    }

    /**
     * Driver invalid license exception.
     *
     * @param string $licenseNumber
     * @param string $reason
     * @return static
     */
    public static function invalidLicense(string $licenseNumber, string $reason): static
    {
        return new static("Driver license '{$licenseNumber}' is invalid: {$reason}");
    }

    /**
     * Driver OCR verification failed exception.
     *
     * @param string $driverId
     * @param string $documentType
     * @param string $reason
     * @return static
     */
    public static function ocrVerificationFailed(string $driverId, string $documentType, string $reason): static
    {
        return new static("OCR verification failed for driver '{$driverId}' document '{$documentType}': {$reason}");
    }
}