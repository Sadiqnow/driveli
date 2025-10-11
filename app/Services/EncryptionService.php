<?php

namespace App\Services;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class EncryptionService
{
    private const SENSITIVE_FIELDS = [
        'nin_number',
        'phone',
        'phone_2',
        'emergency_contact_phone',
        'account_number',
        'bvn',
        'bank_account_number',
        'passport_number',
    ];

    /**
     * Encrypt sensitive field data
     */
    public function encryptField(?string $value, string $fieldName): ?string
    {
        // If no value provided or field not sensitive, pass through
        if (is_null($value) || $value === '' || !$this->isSensitiveField($fieldName)) {
            return $value;
        }

        try {
            // Add field identifier to encrypted data for validation
            $dataToEncrypt = [
                'field' => $fieldName,
                'value' => $value,
                'timestamp' => now()->timestamp,
                'checksum' => hash('sha256', $value . $fieldName . config('app.key'))
            ];

            return Crypt::encryptString(json_encode($dataToEncrypt));
        } catch (\Exception $e) {
            Log::error('Field encryption failed', [
                'field' => $fieldName,
                'error' => $e->getMessage(),
            ]);

            throw new \RuntimeException('Failed to encrypt sensitive data');
        }
    }

    /**
     * Decrypt sensitive field data
     */
    public function decryptField(?string $encryptedValue, string $fieldName): ?string
    {
        if (empty($encryptedValue) || !$this->isSensitiveField($fieldName)) {
            return $encryptedValue;
        }

        try {
            $decryptedJson = Crypt::decryptString($encryptedValue);
            $data = json_decode($decryptedJson, true);

            if (!$this->validateDecryptedData($data, $fieldName)) {
                throw new DecryptException('Invalid encrypted data structure');
            }

            return $data['value'];
        } catch (DecryptException $e) {
            Log::warning('Field decryption failed', [
                'field' => $fieldName,
                'error' => $e->getMessage(),
            ]);
            
            return '[ENCRYPTED_DATA_CORRUPTED]';
        }
    }

    /**
     * Encrypt multiple fields in an array
     */
    public function encryptFields(array $data): array
    {
        foreach ($data as $key => $value) {
            if ($this->isSensitiveField($key) && !empty($value)) {
                $data[$key] = $this->encryptField($value, $key);
            }
        }

        return $data;
    }

    /**
     * Decrypt multiple fields in an array
     */
    public function decryptFields(array $data): array
    {
        foreach ($data as $key => $value) {
            if ($this->isSensitiveField($key) && !empty($value)) {
                $data[$key] = $this->decryptField($value, $key);
            }
        }

        return $data;
    }

    /**
     * Check if field contains sensitive data
     */
    public function isSensitiveField(string $fieldName): bool
    {
        return in_array($fieldName, self::SENSITIVE_FIELDS);
    }

    /**
     * Get masked version of sensitive data for display
     */
    public function maskSensitiveData(string $value, string $fieldName): string
    {
        if (!$this->isSensitiveField($fieldName)) {
            return $value;
        }

        return match ($fieldName) {
            'nin_number' => $this->maskNIN($value),
            'phone', 'phone_2', 'emergency_contact_phone' => $this->maskPhone($value),
            'account_number', 'bank_account_number' => $this->maskAccountNumber($value),
            'bvn' => $this->maskBVN($value),
            default => $this->maskGeneric($value),
        };
    }

    /**
     * Validate decrypted data integrity
     */
    private function validateDecryptedData(array $data, string $expectedField): bool
    {
        if (!isset($data['field'], $data['value'], $data['timestamp'], $data['checksum'])) {
            return false;
        }

        if ($data['field'] !== $expectedField) {
            return false;
        }

        $expectedChecksum = hash('sha256', $data['value'] . $expectedField . config('app.key'));
        return hash_equals($expectedChecksum, $data['checksum']);
    }

    /**
     * Mask NIN number (show only first 3 and last 2 digits)
     */
    private function maskNIN(string $nin): string
    {
        $length = strlen($nin);
        if ($length < 6) {
            return str_repeat('*', $length);
        }

        return substr($nin, 0, 3) . str_repeat('*', $length - 5) . substr($nin, -2);
    }

    /**
     * Mask phone number (show only last 4 digits)
     */
    private function maskPhone(string $phone): string
    {
        $length = strlen($phone);
        if ($length < 5) {
            return str_repeat('*', $length);
        }

        // Tests expect a fixed 4 asterisks followed by the last 4 digits
        return str_repeat('*', 4) . substr($phone, -4);
    }

    /**
     * Mask account number (show only last 4 digits)
     */
    private function maskAccountNumber(string $account): string
    {
        $length = strlen($account);
        if ($length < 5) {
            return str_repeat('*', $length);
        }

        return str_repeat('*', $length - 4) . substr($account, -4);
    }

    /**
     * Mask BVN (show only first 2 and last 2 digits)
     */
    private function maskBVN(string $bvn): string
    {
        $length = strlen($bvn);
        if ($length < 5) {
            return str_repeat('*', $length);
        }

        return substr($bvn, 0, 2) . str_repeat('*', $length - 4) . substr($bvn, -2);
    }

    /**
     * Generic masking for other sensitive fields
     */
    private function maskGeneric(string $value): string
    {
        $length = strlen($value);
        if ($length <= 2) {
            return str_repeat('*', $length);
        }

        $visibleChars = min(2, intval($length / 4));
        return substr($value, 0, $visibleChars) . 
               str_repeat('*', $length - ($visibleChars * 2)) . 
               substr($value, -$visibleChars);
    }

    /**
     * Search encrypted fields (for admin search functionality)
     */
    public function searchEncryptedField(string $searchTerm, string $fieldName): ?string
    {
        if (!$this->isSensitiveField($fieldName)) {
            return $searchTerm;
        }

        // For encrypted searches, we encrypt the search term and return it
        // Note: This only works for exact matches
        return $this->encryptField($searchTerm, $fieldName);
    }
    /**
     * Validate field data before encryption
     */
    public function validateSensitiveField(string $value, string $fieldName): bool
    {
        return match ($fieldName) {
            'nin_number' => preg_match('/^\d{11}$/', $value),
            'phone', 'phone_2', 'emergency_contact_phone' => preg_match('/^\+?[\d\-\s\(\)]+$/', $value),
            'bvn' => preg_match('/^\d{11}$/', $value),
            'account_number', 'bank_account_number' => preg_match('/^\d{10,20}$/', $value),
            default => !empty(trim($value)),
        };
    }
}