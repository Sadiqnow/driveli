<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Sanctum\PersonalAccessToken;

class SecureApiMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$guards): HttpResponse
    {
        // Check API key or token authentication
        if (!$this->authenticateRequest($request, $guards)) {
            return $this->unauthorizedResponse();
        }

        // Validate request signature for API calls
        if (!$this->validateRequestSignature($request)) {
            return $this->forbiddenResponse('Invalid request signature');
        }

        // Check for suspicious patterns
        if ($this->detectSuspiciousActivity($request)) {
            return $this->forbiddenResponse('Suspicious activity detected');
        }

        // Add security headers to response
        $response = $next($request);
        return $this->addSecurityHeaders($response);
    }

    /**
     * Authenticate API request
     */
    private function authenticateRequest(Request $request, array $guards): bool
    {
        // Check for API key in headers
        if ($apiKey = $request->header('X-API-Key')) {
            return $this->validateApiKey($apiKey);
        }

        // Check for Bearer token
        if ($token = $request->bearerToken()) {
            return $this->validateBearerToken($token);
        }

        // Fallback to guard authentication
        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Validate API key
     */
    private function validateApiKey(string $apiKey): bool
    {
        $hashedKey = config('app.api_keys.' . substr($apiKey, 0, 8));
        
        if (!$hashedKey) {
            return false;
        }

        return Hash::check($apiKey, $hashedKey);
    }

    /**
     * Validate Bearer token
     */
    private function validateBearerToken(string $token): bool
    {
        $accessToken = PersonalAccessToken::findToken($token);
        
        if (!$accessToken || !$accessToken->can('api-access')) {
            return false;
        }

        // Check token expiration
        if ($accessToken->expires_at && $accessToken->expires_at->isPast()) {
            return false;
        }

        // Set authenticated user
        Auth::setUser($accessToken->tokenable);

        // Set current access token on the user for ability checks
        $accessToken->tokenable->accessToken = $accessToken;

        return true;
    }

    /**
     * Validate request signature for sensitive operations
     */
    private function validateRequestSignature(Request $request): bool
    {
        // Skip signature validation for GET requests
        if ($request->isMethod('GET')) {
            return true;
        }

        $signature = $request->header('X-Request-Signature');
        if (!$signature) {
            return false;
        }

        $payload = json_encode($request->all());
        $timestamp = $request->header('X-Timestamp');
        
        // Validate timestamp (must be within 5 minutes)
        if (!$timestamp || abs(time() - $timestamp) > 300) {
            return false;
        }

        $expectedSignature = hash_hmac('sha256', $payload . $timestamp, config('app.api_secret'));
        
        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Detect suspicious activity patterns
     */
    private function detectSuspiciousActivity(Request $request): bool
    {
        $ip = $request->ip();
        $userAgent = $request->userAgent();
        
        // Check for common attack patterns
        $suspiciousPatterns = [
            '/\b(DROP|DELETE|INSERT|UPDATE|UNION|SELECT)\b/i',
            '/<script[^>]*>.*?<\/script>/i',
            '/javascript:/i',
            '/vbscript:/i',
            '/onload\s*=/i',
            '/onerror\s*=/i',
        ];

        $requestData = json_encode($request->all());
        
        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $requestData)) {
                $this->logSuspiciousActivity($request, 'Malicious payload detected');
                return true;
            }
        }

        // Check for rapid requests from same IP
        $key = "suspicious_activity:{$ip}";
        if (RateLimiter::tooManyAttempts($key, 100, 60)) {
            $this->logSuspiciousActivity($request, 'Rate limit exceeded');
            return true;
        }

        RateLimiter::hit($key, 60);

        return false;
    }

    /**
     * Log suspicious activity
     */
    private function logSuspiciousActivity(Request $request, string $reason): void
    {
        \Log::warning('Suspicious API activity detected', [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'reason' => $reason,
            'payload_size' => strlen(json_encode($request->all())),
            'headers' => $request->headers->all(),
        ]);
    }

    /**
     * Add security headers to response
     */
    private function addSecurityHeaders(HttpResponse $response): HttpResponse
    {
        $headers = [
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'DENY',
            'X-XSS-Protection' => '1; mode=block',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Content-Security-Policy' => "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline';",
            'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
        ];

        foreach ($headers as $key => $value) {
            $response->headers->set($key, $value);
        }

        return $response;
    }

    /**
     * Return unauthorized response
     */
    private function unauthorizedResponse(): HttpResponse
    {
        return response()->json([
            'error' => 'Unauthorized',
            'message' => 'Invalid or missing authentication credentials',
        ], 401);
    }

    /**
     * Return forbidden response
     */
    private function forbiddenResponse(string $message = 'Forbidden'): HttpResponse
    {
        return response()->json([
            'error' => 'Forbidden',
            'message' => $message,
        ], 403);
    }
}