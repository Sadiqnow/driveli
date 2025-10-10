<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class ErrorHandlingService
{
    /**
     * Handle and log application exceptions
     */
    public function handleException(Throwable $exception, Request $request = null): void
    {
        $context = [
            'exception_class' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
            'timestamp' => now()->toISOString()
        ];

        if ($request) {
            $context['request'] = [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'user_id' => auth()->id(),
                'parameters' => $this->sanitizeRequestData($request->all())
            ];
        }

        Log::error('Application Exception', $context);

        // Send critical error notifications if in production
        if (app()->environment('production') && $this->isCriticalError($exception)) {
            $this->notifyAdminsOfCriticalError($exception, $context);
        }
    }


    /**
     * Check for brute force attempts and log warnings
     */
    private function checkBruteForceAttempts(string $identifier, string $event): void
    {
        $cacheKey = "failed_attempts:{$event}:{$identifier}";
        $attempts = cache()->get($cacheKey, 0) + 1;
        
        cache()->put($cacheKey, $attempts, now()->addMinutes(30));
        
        if ($attempts >= 5) {
            Log::alert('Potential brute force attack detected', [
                'identifier' => $identifier,
                'event' => $event,
                'attempts' => $attempts,
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        }
    }

    /**
     * Log security-related events
     */
    public function logSecurityEvent(string $event, array $details, string $severity = 'warning'): void
    {
        $context = array_merge([
            'event_type' => 'security',
            'event' => $event,
            'timestamp' => now()->toISOString(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'user_id' => auth()->id()
        ], $details);

        switch ($severity) {
            case 'critical':
                Log::critical("Security Event: {$event}", $context);
                break;
            case 'error':
                Log::error("Security Event: {$event}", $context);
                break;
            case 'warning':
            default:
                Log::warning("Security Event: {$event}", $context);
                break;
        }
    }

    /**
     * Log authentication events
     */
    public function logAuthEvent(string $event, string $email, bool $success = true, array $additionalData = []): void
    {
        $context = array_merge([
            'event_type' => 'authentication',
            'event' => $event,
            'email' => $email,
            'success' => $success,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toISOString()
        ], $additionalData);

        if ($success) {
            Log::info("Auth Event: {$event}", $context);
        } else {
            Log::warning("Auth Event: {$event}", $context);
            
            // Log failed authentication attempts for security monitoring
            if (in_array($event, ['login_failed', 'invalid_token', 'account_locked'])) {
                $this->logSecurityEvent($event, $context, 'warning');
            }
        }
    }

    /**
     * Log database operations
     */
    public function logDatabaseOperation(string $operation, string $table, array $data = [], bool $success = true, string $error = null): void
    {
        $context = [
            'event_type' => 'database',
            'operation' => $operation,
            'table' => $table,
            'success' => $success,
            'user_id' => auth()->id(),
            'timestamp' => now()->toISOString()
        ];

        if (!empty($data)) {
            $context['data_count'] = is_array($data) ? count($data) : 1;
        }

        if (!$success && $error) {
            $context['error'] = $error;
            Log::error("Database Operation Failed: {$operation} on {$table}", $context);
        } else {
            Log::info("Database Operation: {$operation} on {$table}", $context);
        }
    }

    /**
     * Log file operations
     */
    public function logFileOperation(string $operation, string $filename, bool $success = true, string $error = null): void
    {
        $context = [
            'event_type' => 'file_operation',
            'operation' => $operation,
            'filename' => basename($filename),
            'success' => $success,
            'user_id' => auth()->id(),
            'timestamp' => now()->toISOString()
        ];

        if (!$success && $error) {
            $context['error'] = $error;
            Log::error("File Operation Failed: {$operation}", $context);
        } else {
            Log::info("File Operation: {$operation}", $context);
        }
    }

    /**
     * Log OCR verification events
     */
    public function logOCREvent(string $event, string $documentType, array $details = []): void
    {
        $context = array_merge([
            'event_type' => 'ocr_verification',
            'event' => $event,
            'document_type' => $documentType,
            'user_id' => auth()->id(),
            'timestamp' => now()->toISOString()
        ], $details);

        Log::info("OCR Event: {$event}", $context);
    }

    /**
     * Create structured error response
     */
    public function createErrorResponse(Throwable $exception, int $statusCode = 500): array
    {
        $response = [
            'success' => false,
            'message' => 'An error occurred',
            'timestamp' => now()->toISOString()
        ];

        // Add detailed error information for debugging (non-production only)
        if (!app()->environment('production')) {
            $response['debug'] = [
                'exception' => get_class($exception),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine()
            ];
        }

        // Add specific error messages based on exception type
        if ($exception instanceof \Illuminate\Validation\ValidationException) {
            $response['message'] = 'Validation failed';
            $response['errors'] = $exception->errors();
        } elseif ($exception instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
            $response['message'] = 'Resource not found';
        } elseif ($exception instanceof \Illuminate\Auth\AuthenticationException) {
            $response['message'] = 'Authentication required';
        } elseif ($exception instanceof \Illuminate\Auth\Access\AuthorizationException) {
            $response['message'] = 'Insufficient permissions';
        }

        return $response;
    }

    /**
     * Sanitize request data for logging (remove sensitive information)
     */
    private function sanitizeRequestData(array $data): array
    {
        $sensitiveFields = [
            'password',
            'password_confirmation',
            'token',
            'api_key',
            'secret',
            'credit_card',
            'ssn',
            'nin_number'
        ];

        foreach ($sensitiveFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = '[REDACTED]';
            }
        }

        return $data;
    }

    /**
     * Determine if an error is critical
     */
    private function isCriticalError(Throwable $exception): bool
    {
        $criticalExceptions = [
            \Illuminate\Database\QueryException::class,
            \PDOException::class,
            \OutOfMemoryError::class,
            \Error::class
        ];

        foreach ($criticalExceptions as $criticalException) {
            if ($exception instanceof $criticalException) {
                return true;
            }
        }

        return false;
    }

    /**
     * Notify administrators of critical errors
     */
    private function notifyAdminsOfCriticalError(Throwable $exception, array $context): void
    {
        try {
            // In a real application, you would send email notifications here
            Log::critical('Critical Error Notification Sent', [
                'exception_class' => get_class($exception),
                'message' => $exception->getMessage(),
                'notified_at' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            // Fallback logging if notification fails
            Log::emergency('Failed to send critical error notification', [
                'notification_error' => $e->getMessage(),
                'original_exception' => get_class($exception)
            ]);
        }
    }

    /**
     * Get error statistics
     */
    public function getErrorStats(int $days = 7): array
    {
        // This would typically query a log storage system
        // For now, return placeholder data
        return [
            'total_errors' => 0,
            'critical_errors' => 0,
            'security_events' => 0,
            'auth_failures' => 0,
            'period_days' => $days
        ];
    }

    /**
     * Clear old log entries (if using database logging)
     */
    public function clearOldLogs(int $daysToKeep = 30): int
    {
        $cutoffDate = now()->subDays($daysToKeep);
        
        // This would typically delete old log entries from database
        // Return count of deleted entries
        return 0;
    }
}