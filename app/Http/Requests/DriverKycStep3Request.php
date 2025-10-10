<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DriverKycStep3Request extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'driver_license_scan' => [
                'required',
                'file',
                'max:2048', // 2MB max
                'mimes:jpg,jpeg,png,pdf',
                'dimensions:min_width=300,min_height=300', // Ensure readable quality
            ],
            'national_id' => [
                'required',
                'file',
                'max:2048', // 2MB max
                'mimes:jpg,jpeg,png,pdf',
                'dimensions:min_width=300,min_height=300',
            ],
            'passport_photo' => [
                'required',
                'file',
                'max:2048', // 2MB max
                'mimes:jpg,jpeg,png',
                'dimensions:min_width=200,min_height=200,max_width=2000,max_height=2000',
            ],
            'utility_bill' => [
                'nullable',
                'file',
                'max:2048',
                'mimes:jpg,jpeg,png,pdf',
            ],
            'bank_statement' => [
                'nullable',
                'file',
                'max:2048',
                'mimes:jpg,jpeg,png,pdf',
            ],
            'employment_letter' => [
                'nullable',
                'file',
                'max:2048',
                'mimes:jpg,jpeg,png,pdf',
            ],
            'terms_accepted' => [
                'required',
                'accepted',
            ],
            'data_consent' => [
                'required',
                'accepted',
            ],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'driver_license_scan.required' => 'Driver license scan is required.',
            'driver_license_scan.file' => 'Driver license scan must be a valid file.',
            'driver_license_scan.max' => 'Driver license scan file size must not exceed 2MB.',
            'driver_license_scan.mimes' => 'Driver license scan must be a JPG, JPEG, PNG, or PDF file.',
            'driver_license_scan.dimensions' => 'Driver license scan must be at least 300x300 pixels for clarity.',
            
            'national_id.required' => 'National ID scan is required.',
            'national_id.file' => 'National ID scan must be a valid file.',
            'national_id.max' => 'National ID scan file size must not exceed 2MB.',
            'national_id.mimes' => 'National ID scan must be a JPG, JPEG, PNG, or PDF file.',
            'national_id.dimensions' => 'National ID scan must be at least 300x300 pixels for clarity.',
            
            'passport_photo.required' => 'Passport photo is required.',
            'passport_photo.file' => 'Passport photo must be a valid file.',
            'passport_photo.max' => 'Passport photo file size must not exceed 2MB.',
            'passport_photo.mimes' => 'Passport photo must be a JPG, JPEG, or PNG file.',
            'passport_photo.dimensions' => 'Passport photo must be between 200x200 and 2000x2000 pixels.',
            
            'utility_bill.max' => 'Utility bill file size must not exceed 2MB.',
            'utility_bill.mimes' => 'Utility bill must be a JPG, JPEG, PNG, or PDF file.',
            
            'bank_statement.max' => 'Bank statement file size must not exceed 2MB.',
            'bank_statement.mimes' => 'Bank statement must be a JPG, JPEG, PNG, or PDF file.',
            
            'employment_letter.max' => 'Employment letter file size must not exceed 2MB.',
            'employment_letter.mimes' => 'Employment letter must be a JPG, JPEG, PNG, or PDF file.',
            
            'terms_accepted.required' => 'You must accept the terms and conditions.',
            'terms_accepted.accepted' => 'You must accept the terms and conditions.',
            
            'data_consent.required' => 'You must consent to data processing.',
            'data_consent.accepted' => 'You must consent to data processing.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'driver_license_scan' => 'driver license scan',
            'national_id' => 'national ID scan',
            'passport_photo' => 'passport photo',
            'utility_bill' => 'utility bill',
            'bank_statement' => 'bank statement',
            'employment_letter' => 'employment letter',
            'terms_accepted' => 'terms and conditions',
            'data_consent' => 'data consent',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Custom validation logic
            $this->validateFileIntegrity($validator);
            $this->validateDocumentQuality($validator);
        });
    }

    /**
     * Validate file integrity and security.
     */
    private function validateFileIntegrity($validator): void
    {
        $files = ['driver_license_scan', 'national_id', 'passport_photo', 'utility_bill', 'bank_statement', 'employment_letter'];
        
        foreach ($files as $field) {
            if ($this->hasFile($field)) {
                $file = $this->file($field);
                
                // Check if file is corrupted
                if (!$file->isValid()) {
                    $validator->errors()->add($field, "The {$field} file appears to be corrupted or invalid.");
                    continue;
                }
                
                // Additional security checks
                $mimeType = $file->getMimeType();
                $extension = strtolower($file->getClientOriginalExtension());
                
                // Check for mime type spoofing
                $allowedMimes = [
                    'jpg' => ['image/jpeg'],
                    'jpeg' => ['image/jpeg'],
                    'png' => ['image/png'],
                    'pdf' => ['application/pdf'],
                ];
                
                if (isset($allowedMimes[$extension]) && !in_array($mimeType, $allowedMimes[$extension])) {
                    $validator->errors()->add($field, "The {$field} file type does not match its extension.");
                }
            }
        }
    }

    /**
     * Validate document quality for OCR processing.
     */
    private function validateDocumentQuality($validator): void
    {
        $imageFields = ['driver_license_scan', 'national_id', 'passport_photo'];
        
        foreach ($imageFields as $field) {
            if ($this->hasFile($field) && $this->file($field)->isValid()) {
                $file = $this->file($field);
                
                // Only check image files
                if (str_starts_with($file->getMimeType(), 'image/')) {
                    try {
                        $imageInfo = getimagesize($file->getPathname());
                        if ($imageInfo === false) {
                            $validator->errors()->add($field, "The {$field} image appears to be corrupted.");
                            continue;
                        }
                        
                        // Check for extremely small file sizes (likely corrupt or low quality)
                        if ($file->getSize() < 50000) { // 50KB minimum
                            $validator->errors()->add($field, "The {$field} image quality is too low. Please upload a clearer image.");
                        }
                        
                    } catch (\Exception $e) {
                        $validator->errors()->add($field, "Unable to process the {$field} image. Please try uploading a different file.");
                    }
                }
            }
        }
    }
}