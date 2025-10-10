<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Str;

/**
 * Base secure request class with comprehensive input validation and sanitization
 */
abstract class SecureRequest extends FormRequest
{
    /**
     * Common security rules for input validation
     */
    protected array $securityRules = [
        'no_script_tags' => 'regex:/^(?!.*<script).*$/i',
        'no_sql_injection' => 'regex:/^(?!.*(union|select|insert|update|delete|drop|create|alter|exec|script|javascript|vbscript|onload|onerror|onclick)).*$/i',
        'no_xss_patterns' => 'regex:/^(?!.*(javascript:|data:|vbscript:|onload|onerror|onclick)).*$/i',
    ];

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Override in specific request classes
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return []; // Override in specific request classes
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->sanitizeInput();
        $this->validateRequestSize();
        $this->validateContentType();
    }

    /**
     * Sanitize all input data
     */
    protected function sanitizeInput(): void
    {
        $sanitized = [];
        
        foreach ($this->all() as $key => $value) {
            $sanitized[$key] = $this->sanitizeValue($value);
        }
        
        $this->replace($sanitized);
    }

    /**
     * Sanitize individual values
     */
    protected function sanitizeValue($value)
    {
        if (is_array($value)) {
            return array_map([$this, 'sanitizeValue'], $value);
        }
        
        if (!is_string($value)) {
            return $value;
        }
        
        // Remove null bytes
        $value = str_replace("\0", '', $value);
        
        // Trim whitespace
        $value = trim($value);
        
        // Remove control characters except tab, newline, carriage return
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $value);
        
        // Escape HTML special characters for display
        if (!$this->isAllowedHtmlField($this->getFieldNameForValue($value))) {
            $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        }
        
        return $value;
    }

    /**
     * Check if field is allowed to contain HTML
     */
    protected function isAllowedHtmlField(string $fieldName): bool
    {
        $allowedHtmlFields = $this->getAllowedHtmlFields();
        return in_array($fieldName, $allowedHtmlFields);
    }

    /**
     * Get fields allowed to contain HTML (override in specific requests)
     */
    protected function getAllowedHtmlFields(): array
    {
        return []; // By default, no fields allow HTML
    }

    /**
     * Get field name for a value (simple implementation)
     */
    protected function getFieldNameForValue($value): string
    {
        foreach ($this->all() as $key => $val) {
            if ($val === $value) {
                return $key;
            }
        }
        return '';
    }

    /**
     * Validate request size
     */
    protected function validateRequestSize(): void
    {
        $maxSize = config('drivelink.security.max_request_size', 10 * 1024 * 1024); // 10MB default
        $contentLength = $this->header('Content-Length', 0);
        
        if ($contentLength > $maxSize) {
            throw new HttpResponseException(
                response()->json([
                    'success' => false,
                    'message' => 'Request size too large',
                    'error' => 'REQUEST_TOO_LARGE'
                ], 413)
            );
        }
    }

    /**
     * Validate content type for specific endpoints
     */
    protected function validateContentType(): void
    {
        if ($this->isMethod('POST') || $this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $contentType = $this->header('Content-Type', '');
            $allowedTypes = $this->getAllowedContentTypes();
            
            if (!empty($allowedTypes)) {
                $isValid = false;
                foreach ($allowedTypes as $type) {
                    if (str_contains($contentType, $type)) {
                        $isValid = true;
                        break;
                    }
                }
                
                if (!$isValid) {
                    throw new HttpResponseException(
                        response()->json([
                            'success' => false,
                            'message' => 'Invalid content type',
                            'error' => 'INVALID_CONTENT_TYPE'
                        ], 415)
                    );
                }
            }
        }
    }

    /**
     * Get allowed content types (override in specific requests)
     */
    protected function getAllowedContentTypes(): array
    {
        return [
            'application/json',
            'application/x-www-form-urlencoded',
            'multipart/form-data'
        ];
    }

    /**
     * Get common validation rules
     */
    protected function getCommonRules(): array
    {
        return [
            'name' => 'required|string|max:255|regex:/^[a-zA-Z\s\-\'.]+$/',
            'email' => 'required|email:filter|max:255',
            'phone' => 'required|string|max:20|regex:/^[\+]?[0-9\-\(\)\s]+$/',
            'password' => 'required|string|min:8|confirmed|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/',
        ];
    }

    /**
     * Get security-focused validation rules
     */
    protected function getSecurityRules(): array
    {
        return $this->securityRules;
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
                'error' => 'VALIDATION_FAILED'
            ], 422)
        );
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'first_name' => 'first name',
            'last_name' => 'last name',
            'email' => 'email address',
            'phone' => 'phone number',
            'password' => 'password',
            'password_confirmation' => 'password confirmation',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'required' => 'The :attribute field is required.',
            'email' => 'Please enter a valid email address.',
            'min' => 'The :attribute must be at least :min characters.',
            'max' => 'The :attribute must not exceed :max characters.',
            'confirmed' => 'The :attribute confirmation does not match.',
            'regex' => 'The :attribute format is invalid.',
            'unique' => 'The :attribute has already been taken.',
            'exists' => 'The selected :attribute is invalid.',
        ];
    }
}