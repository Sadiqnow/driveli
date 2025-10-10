<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ValidationException extends Exception
{
    protected array $errors;

    /**
     * Create a new validation exception instance.
     *
     * @param string $message
     * @param array $errors
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(string $message = 'Validation failed', array $errors = [], int $code = 422, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->errors = $errors;
    }

    /**
     * Create validation exception with field errors.
     *
     * @param array $errors
     * @param string $message
     * @return static
     */
    public static function withErrors(array $errors, string $message = 'The given data was invalid.'): static
    {
        return new static($message, $errors);
    }

    /**
     * Create validation exception for required field.
     *
     * @param string $field
     * @return static
     */
    public static function requiredField(string $field): static
    {
        return new static(
            "The {$field} field is required.",
            [$field => ["The {$field} field is required."]]
        );
    }

    /**
     * Create validation exception for invalid field format.
     *
     * @param string $field
     * @param string $format
     * @return static
     */
    public static function invalidFormat(string $field, string $format): static
    {
        return new static(
            "The {$field} field must be a valid {$format}.",
            [$field => ["The {$field} field must be a valid {$format}."]]
        );
    }

    /**
     * Create validation exception for field too long.
     *
     * @param string $field
     * @param int $maxLength
     * @return static
     */
    public static function fieldTooLong(string $field, int $maxLength): static
    {
        return new static(
            "The {$field} field must not exceed {$maxLength} characters.",
            [$field => ["The {$field} field must not exceed {$maxLength} characters."]]
        );
    }

    /**
     * Create validation exception for field too short.
     *
     * @param string $field
     * @param int $minLength
     * @return static
     */
    public static function fieldTooShort(string $field, int $minLength): static
    {
        return new static(
            "The {$field} field must be at least {$minLength} characters.",
            [$field => ["The {$field} field must be at least {$minLength} characters."]]
        );
    }

    /**
     * Create validation exception for unique field constraint.
     *
     * @param string $field
     * @param string $value
     * @return static
     */
    public static function fieldNotUnique(string $field, string $value): static
    {
        return new static(
            "The {$field} '{$value}' has already been taken.",
            [$field => ["The {$field} has already been taken."]]
        );
    }

    /**
     * Create validation exception for file upload.
     *
     * @param string $field
     * @param string $reason
     * @return static
     */
    public static function fileUploadError(string $field, string $reason): static
    {
        return new static(
            "File upload failed for {$field}: {$reason}",
            [$field => ["File upload failed: {$reason}"]]
        );
    }

    /**
     * Create validation exception for file size.
     *
     * @param string $field
     * @param int $maxSizeKb
     * @return static
     */
    public static function fileTooLarge(string $field, int $maxSizeKb): static
    {
        $maxSizeMb = round($maxSizeKb / 1024, 1);
        return new static(
            "The {$field} file must not exceed {$maxSizeMb}MB.",
            [$field => ["The {$field} file must not exceed {$maxSizeMb}MB."]]
        );
    }

    /**
     * Create validation exception for invalid file type.
     *
     * @param string $field
     * @param array $allowedTypes
     * @return static
     */
    public static function invalidFileType(string $field, array $allowedTypes): static
    {
        $typesText = implode(', ', $allowedTypes);
        return new static(
            "The {$field} file must be one of: {$typesText}.",
            [$field => ["The {$field} file must be one of: {$typesText}."]]
        );
    }

    /**
     * Create validation exception for date range.
     *
     * @param string $field
     * @param string $minDate
     * @param string $maxDate
     * @return static
     */
    public static function invalidDateRange(string $field, string $minDate = null, string $maxDate = null): static
    {
        if ($minDate && $maxDate) {
            $message = "The {$field} must be between {$minDate} and {$maxDate}.";
        } elseif ($minDate) {
            $message = "The {$field} must be after {$minDate}.";
        } elseif ($maxDate) {
            $message = "The {$field} must be before {$maxDate}.";
        } else {
            $message = "The {$field} is invalid.";
        }

        return new static($message, [$field => [$message]]);
    }

    /**
     * Create validation exception for numeric range.
     *
     * @param string $field
     * @param float $min
     * @param float $max
     * @return static
     */
    public static function numericOutOfRange(string $field, float $min = null, float $max = null): static
    {
        if ($min !== null && $max !== null) {
            $message = "The {$field} must be between {$min} and {$max}.";
        } elseif ($min !== null) {
            $message = "The {$field} must be at least {$min}.";
        } elseif ($max !== null) {
            $message = "The {$field} must not exceed {$max}.";
        } else {
            $message = "The {$field} is invalid.";
        }

        return new static($message, [$field => [$message]]);
    }

    /**
     * Create validation exception for array validation.
     *
     * @param string $field
     * @param int $minItems
     * @param int $maxItems
     * @return static
     */
    public static function invalidArraySize(string $field, int $minItems = null, int $maxItems = null): static
    {
        if ($minItems !== null && $maxItems !== null) {
            $message = "The {$field} must contain between {$minItems} and {$maxItems} items.";
        } elseif ($minItems !== null) {
            $message = "The {$field} must contain at least {$minItems} items.";
        } elseif ($maxItems !== null) {
            $message = "The {$field} must not contain more than {$maxItems} items.";
        } else {
            $message = "The {$field} array is invalid.";
        }

        return new static($message, [$field => [$message]]);
    }

    /**
     * Get validation errors.
     *
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Render the exception as an HTTP response.
     *
     * @param Request $request
     * @return JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function render(Request $request)
    {
        if ($request->expectsJson()) {
            return new JsonResponse([
                'success' => false,
                'message' => $this->getMessage(),
                'errors' => $this->errors,
            ], $this->getCode());
        }

        return redirect()->back()
            ->withErrors($this->errors)
            ->withInput($request->except('password', 'password_confirmation'));
    }
}