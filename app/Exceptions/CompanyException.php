<?php

namespace App\Exceptions;

use Exception;

class CompanyException extends Exception
{
    /**
     * Company not found exception.
     *
     * @param string $identifier
     * @return static
     */
    public static function notFound(string $identifier): static
    {
        return new static("Company with identifier '{$identifier}' not found.");
    }

    /**
     * Company already verified exception.
     *
     * @param string $companyId
     * @return static
     */
    public static function alreadyVerified(string $companyId): static
    {
        return new static("Company '{$companyId}' is already verified.");
    }

    /**
     * Company already rejected exception.
     *
     * @param string $companyId
     * @return static
     */
    public static function alreadyRejected(string $companyId): static
    {
        return new static("Company '{$companyId}' has already been rejected.");
    }

    /**
     * Company suspended exception.
     *
     * @param string $companyId
     * @param string $reason
     * @return static
     */
    public static function suspended(string $companyId, string $reason = null): static
    {
        $message = "Company '{$companyId}' is suspended";
        if ($reason) {
            $message .= ": {$reason}";
        }
        return new static($message);
    }

    /**
     * Company inactive exception.
     *
     * @param string $companyId
     * @return static
     */
    public static function inactive(string $companyId): static
    {
        return new static("Company '{$companyId}' is inactive.");
    }

    /**
     * Company registration number exists exception.
     *
     * @param string $registrationNumber
     * @return static
     */
    public static function registrationNumberExists(string $registrationNumber): static
    {
        return new static("A company with registration number '{$registrationNumber}' already exists.");
    }

    /**
     * Company email exists exception.
     *
     * @param string $email
     * @return static
     */
    public static function emailExists(string $email): static
    {
        return new static("A company with email '{$email}' already exists.");
    }

    /**
     * Company phone exists exception.
     *
     * @param string $phone
     * @return static
     */
    public static function phoneExists(string $phone): static
    {
        return new static("A company with phone number '{$phone}' already exists.");
    }

    /**
     * Company document upload failed exception.
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
     * Company not eligible for verification exception.
     *
     * @param string $companyId
     * @param string $reason
     * @return static
     */
    public static function notEligibleForVerification(string $companyId, string $reason): static
    {
        return new static("Company '{$companyId}' is not eligible for verification: {$reason}");
    }

    /**
     * Company request limit exceeded exception.
     *
     * @param string $companyId
     * @param int $limit
     * @return static
     */
    public static function requestLimitExceeded(string $companyId, int $limit): static
    {
        return new static("Company '{$companyId}' has exceeded the maximum number of active requests ({$limit}).");
    }

    /**
     * Company request not found exception.
     *
     * @param string $requestId
     * @return static
     */
    public static function requestNotFound(string $requestId): static
    {
        return new static("Company request '{$requestId}' not found.");
    }

    /**
     * Company request already filled exception.
     *
     * @param string $requestId
     * @return static
     */
    public static function requestAlreadyFilled(string $requestId): static
    {
        return new static("Company request '{$requestId}' has already been filled.");
    }

    /**
     * Company request expired exception.
     *
     * @param string $requestId
     * @return static
     */
    public static function requestExpired(string $requestId): static
    {
        return new static("Company request '{$requestId}' has expired.");
    }

    /**
     * Company insufficient credits exception.
     *
     * @param string $companyId
     * @param int $required
     * @param int $available
     * @return static
     */
    public static function insufficientCredits(string $companyId, int $required, int $available): static
    {
        return new static("Company '{$companyId}' has insufficient credits. Required: {$required}, Available: {$available}");
    }

    /**
     * Company unauthorized action exception.
     *
     * @param string $companyId
     * @param string $action
     * @return static
     */
    public static function unauthorizedAction(string $companyId, string $action): static
    {
        return new static("Company '{$companyId}' is not authorized to perform action: {$action}");
    }
}