<?php

namespace App\Traits;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use App\Services\EncryptionService;
use Illuminate\Support\Facades\Log;

trait HasEncryptedFields
{
    /**
     * Get the encrypted fields for this model.
     *
     * @return array
     */
    public function getEncryptedFields(): array
    {
        // Support either $encrypted (legacy) or $encryptedFields on the consuming model
        if (property_exists($this, 'encrypted') && is_array($this->encrypted)) {
            return $this->encrypted;
        }

        if (property_exists($this, 'encryptedFields') && is_array($this->encryptedFields)) {
            return $this->encryptedFields;
        }

        return [];
    }

    /**
     * Determine if the attribute should be encrypted.
     *
     * @param string $attribute
     * @return bool
     */
    protected function shouldEncrypt(string $attribute): bool
    {
        return in_array($attribute, $this->getEncryptedFields());
    }

    /**
     * Encrypt an attribute value.
     *
     * @param string $attribute
     * @param mixed $value
     * @return mixed
     */
    protected function encryptAttribute(string $attribute, $value)
    {
        if (is_null($value) || empty($value)) {
            return $value;
        }

        // Don't double-encrypt
        if ($this->isEncrypted($value)) {
            return $value;
        }

        try {
            // Delegate to central EncryptionService for consistent format
            $service = app(EncryptionService::class);
            $encrypted = $service->encryptField($value, $attribute);

            // encryptField returns original value on non-sensitive fields or throws on failure
            return $encrypted ?? $value;
        } catch (\Exception $e) {
            Log::error('Encryption failed for attribute: ' . $attribute, [
                'error' => $e->getMessage(),
                'model' => get_class($this)
            ]);

            // Fall back to original value to avoid breaking writes
            return $value;
        }
    }

    /**
     * Decrypt an attribute value.
     *
     * @param string $attribute
     * @param mixed $value
     * @return mixed
     */
    protected function decryptAttribute(string $attribute, $value)
    {
        if (is_null($value) || empty($value)) {
            return $value;
        }

        // If not encrypted, return as is
        if (!$this->isEncrypted($value)) {
            return $value;
        }

        try {
            // Use EncryptionService to decrypt and validate payload
            $service = app(EncryptionService::class);
            $decrypted = $service->decryptField($value, $attribute);

            // decryptField returns a marker string on corruption; prefer original value in that case
            if ($decrypted === '[ENCRYPTED_DATA_CORRUPTED]') {
                Log::warning('Decryption returned corrupted marker for attribute: ' . $attribute, [
                    'model' => get_class($this),
                    'model_id' => $this->id ?? 'unknown'
                ]);

                return $value;
            }

            return $decrypted;
        } catch (DecryptException $e) {
            Log::error('Decryption failed for attribute: ' . $attribute, [
                'error' => $e->getMessage(),
                'model' => get_class($this),
                'model_id' => $this->id ?? 'unknown'
            ]);

            return $value;
        }
    }

    /**
     * Check if a value appears to be encrypted.
     *
     * @param mixed $value
     * @return bool
     */
    protected function isEncrypted($value): bool
    {
        if (!is_string($value)) {
            return false;
        }

        // Laravel's encrypted values start with 'eyJ' (base64 encoded JSON)
        return strpos($value, 'eyJ') === 0;
    }

    /**
     * Override the getAttribute method to decrypt encrypted fields.
     *
     * @param string $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);

        if ($this->shouldEncrypt($key)) {
            return $this->decryptAttribute($key, $value);
        }

        return $value;
    }

    /**
     * Override the setAttribute method to encrypt fields.
     *
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    public function setAttribute($key, $value)
    {
        if ($this->shouldEncrypt($key)) {
            $value = $this->encryptAttribute($key, $value);
        }

        return parent::setAttribute($key, $value);
    }

    /**
     * Override the attributesToArray method to handle encrypted fields.
     *
     * @return array
     */
    public function attributesToArray()
    {
        $attributes = parent::attributesToArray();

        foreach ($this->getEncryptedFields() as $field) {
            if (isset($attributes[$field])) {
                $attributes[$field] = $this->decryptAttribute($field, $this->attributes[$field] ?? null);
            }
        }

        return $attributes;
    }

    /**
     * Override the toArray method to handle encrypted fields.
     *
     * @return array
     */
    public function toArray()
    {
        $array = parent::toArray();

        foreach ($this->getEncryptedFields() as $field) {
            if (isset($array[$field])) {
                $array[$field] = $this->decryptAttribute($field, $this->attributes[$field] ?? null);
            }
        }

        return $array;
    }

    /**
     * Get encrypted field for database queries (returns encrypted value).
     *
     * @param string $field
     * @return string|null
     */
    public function getEncryptedValue(string $field): ?string
    {
        if (!$this->shouldEncrypt($field)) {
            return $this->attributes[$field] ?? null;
        }

        return $this->attributes[$field] ?? null;
    }

    /**
     * Search by encrypted field (for admin queries).
     * Note: This requires the plain text value to encrypt and search.
     *
     * @param string $field
     * @param string $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereEncrypted($query, string $field, string $value)
    {
        if (!$this->shouldEncrypt($field)) {
            return $query->where($field, $value);
        }

        $encryptedValue = $this->encryptAttribute($field, $value);
        return $query->where($field, $encryptedValue);
    }

    /**
     * Bulk encrypt existing data for a field.
     * Use this method carefully, preferably in migrations.
     *
     * @param string $field
     * @return int Number of records updated
     */
    public static function encryptExistingField(string $field): int
    {
        $model = new static();
        
        if (!$model->shouldEncrypt($field)) {
            throw new \InvalidArgumentException("Field '{$field}' is not marked for encryption");
        }

        $updated = 0;
        static::whereNotNull($field)
            ->where($field, '!=', '')
            ->chunk(100, function ($records) use ($field, &$updated, $model) {
                foreach ($records as $record) {
                    $value = $record->attributes[$field] ?? null;
                    
                    // Skip if already encrypted
                    if ($model->isEncrypted($value)) {
                        continue;
                    }

                    $encryptedValue = $model->encryptAttribute($field, $value);
                    
                    // Update directly in database to avoid model events
                    $record->getConnection()
                        ->table($record->getTable())
                        ->where($record->getKeyName(), $record->getKey())
                        ->update([$field => $encryptedValue]);
                    
                    $updated++;
                }
            });

        return $updated;
    }
}