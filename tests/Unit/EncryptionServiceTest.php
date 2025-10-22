<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\EncryptionService;
use Illuminate\Support\Facades\Crypt;

class EncryptionServiceTest extends TestCase
{
    private EncryptionService $encryptionService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->encryptionService = new EncryptionService();
    }

    public function test_can_identify_sensitive_fields()
    {
        $this->assertTrue($this->encryptionService->isSensitiveField('nin_number'));
        $this->assertTrue($this->encryptionService->isSensitiveField('phone'));
        $this->assertTrue($this->encryptionService->isSensitiveField('bvn'));
        $this->assertFalse($this->encryptionService->isSensitiveField('first_name'));
        $this->assertFalse($this->encryptionService->isSensitiveField('email'));
    }

    public function test_can_encrypt_sensitive_field()
    {
        $originalValue = '08012345678';
        $fieldName = 'phone';

        $encrypted = $this->encryptionService->encryptField($originalValue, $fieldName);

        $this->assertNotEmpty($encrypted);
        $this->assertNotEquals($originalValue, $encrypted);
    }

    public function test_can_decrypt_sensitive_field()
    {
        $originalValue = '08012345678';
        $fieldName = 'phone';

        $encrypted = $this->encryptionService->encryptField($originalValue, $fieldName);
        $decrypted = $this->encryptionService->decryptField($encrypted, $fieldName);

        $this->assertEquals($originalValue, $decrypted);
    }

    public function test_non_sensitive_fields_pass_through_unchanged()
    {
        $originalValue = 'John Doe';
        $fieldName = 'first_name';

        $encrypted = $this->encryptionService->encryptField($originalValue, $fieldName);
        $this->assertEquals($originalValue, $encrypted);

        $decrypted = $this->encryptionService->decryptField($originalValue, $fieldName);
        $this->assertEquals($originalValue, $decrypted);
    }

    public function test_can_encrypt_multiple_fields()
    {
        $data = [
            'first_name' => 'John',
            'phone' => '08012345678',
            'nin_number' => '12345678901',
            'email' => 'john@test.com',
        ];

        $encrypted = $this->encryptionService->encryptFields($data);

        $this->assertEquals('John', $encrypted['first_name']);
        $this->assertEquals('john@test.com', $encrypted['email']);
        $this->assertNotEquals('08012345678', $encrypted['phone']);
        $this->assertNotEquals('12345678901', $encrypted['nin_number']);
    }

    public function test_can_decrypt_multiple_fields()
    {
        $originalData = [
            'first_name' => 'John',
            'phone' => '08012345678',
            'nin_number' => '12345678901',
            'email' => 'john@test.com',
        ];

        $encrypted = $this->encryptionService->encryptFields($originalData);
        $decrypted = $this->encryptionService->decryptFields($encrypted);

        $this->assertEquals($originalData, $decrypted);
    }

    public function test_can_mask_sensitive_data()
    {
        $testCases = [
            ['phone', '08012345678', '*******5678'],
            ['nin_number', '12345678901', '123******01'],
            ['bvn', '12345678901', '12*******01'],
            ['account_number', '1234567890', '******7890'],
        ];

        foreach ($testCases as [$field, $value, $expectedMask]) {
            $masked = $this->encryptionService->maskSensitiveData($value, $field);
            $this->assertEquals($expectedMask, $masked);
        }
    }

    public function test_validates_sensitive_field_data()
    {
        $this->assertTrue($this->encryptionService->validateSensitiveField('12345678901', 'nin_number'));
        $this->assertFalse($this->encryptionService->validateSensitiveField('123', 'nin_number'));
        
        $this->assertTrue($this->encryptionService->validateSensitiveField('08012345678', 'phone'));
        $this->assertTrue($this->encryptionService->validateSensitiveField('+234 801 234 5678', 'phone'));
        
        $this->assertTrue($this->encryptionService->validateSensitiveField('1234567890', 'account_number'));
        $this->assertFalse($this->encryptionService->validateSensitiveField('123', 'account_number'));
    }

    public function test_handles_empty_values()
    {
        $encrypted = $this->encryptionService->encryptField('', 'phone');
        $this->assertEquals('', $encrypted);

        $decrypted = $this->encryptionService->decryptField('', 'phone');
        $this->assertEquals('', $decrypted);

        $encrypted = $this->encryptionService->encryptField(null, 'phone');
        $this->assertNull($encrypted);
    }

    public function test_handles_corrupted_encrypted_data()
    {
        $corrupted = 'corrupted-encrypted-data';
        $decrypted = $this->encryptionService->decryptField($corrupted, 'phone');
        
        $this->assertEquals('[ENCRYPTED_DATA_CORRUPTED]', $decrypted);
    }

    public function test_encrypted_data_includes_integrity_check()
    {
        $originalValue = '08012345678';
        $fieldName = 'phone';

        $encrypted = $this->encryptionService->encryptField($originalValue, $fieldName);
        $decryptedJson = Crypt::decryptString($encrypted);
        $data = json_decode($decryptedJson, true);

        $this->assertArrayHasKey('field', $data);
        $this->assertArrayHasKey('value', $data);
        $this->assertArrayHasKey('timestamp', $data);
        $this->assertArrayHasKey('checksum', $data);
        
        $this->assertEquals($fieldName, $data['field']);
        $this->assertEquals($originalValue, $data['value']);
    }

    public function test_detects_tampered_encrypted_data()
    {
        $originalValue = '08012345678';
        $fieldName = 'phone';

        $encrypted = $this->encryptionService->encryptField($originalValue, $fieldName);
        
        // Tamper with the encrypted data
        $decryptedJson = Crypt::decryptString($encrypted);
        $data = json_decode($decryptedJson, true);
        $data['value'] = '08087654321'; // Change the value
        $tamperedJson = json_encode($data);
        $tamperedEncrypted = Crypt::encryptString($tamperedJson);

        $decrypted = $this->encryptionService->decryptField($tamperedEncrypted, $fieldName);
        
        // Should detect tampering and return corrupted data marker
        $this->assertEquals('[ENCRYPTED_DATA_CORRUPTED]', $decrypted);
    }

    public function test_search_encrypted_field_returns_encrypted_search_term()
    {
        $searchTerm = '08012345678';
        $fieldName = 'phone';

        $encryptedSearch = $this->encryptionService->searchEncryptedField($searchTerm, $fieldName);
        
        $this->assertNotEquals($searchTerm, $encryptedSearch);
        
        // Should be able to decrypt back to original
        $decrypted = $this->encryptionService->decryptField($encryptedSearch, $fieldName);
        $this->assertEquals($searchTerm, $decrypted);
    }

    public function test_masking_handles_short_values()
    {
        $short = '123';
        $masked = $this->encryptionService->maskSensitiveData($short, 'phone');
        $this->assertEquals('***', $masked);

        $veryShort = '12';
        $masked = $this->encryptionService->maskSensitiveData($veryShort, 'nin_number');
        $this->assertEquals('**', $masked);
    }
}