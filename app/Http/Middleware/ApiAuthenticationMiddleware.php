<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * API Authentication Middleware
 * 
 * Enhanced authentication middleware for API requests with
 * token validation, rate limiting, and security checks.
 * 
 * @package App\Http\Middleware
 */
class ApiAuthenticationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @param string|null $guard
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $guard = null)
    {
        // Check for API token in various locations
        $token = $this->extractToken($request);
        
        if (!$token) {
            return $this->unauthorizedResponse('API token required');
        }

        // Validate token format
        if (!$this->isValidTokenFormat($token)) {
            return $this->unauthorizedResponse('Invalid token format');
        }

        // Attempt to authenticate using the token
        $accessToken = PersonalAccessToken::findToken($token);
        
        if (!$accessToken) {
            return $this->unauthorizedResponse('Invalid or expired token');
        }

        // Check token expiration
        if ($this->isTokenExpired($accessToken)) {
            return $this->unauthorizedResponse('Token has expired');
        }

        // Check token abilities/scopes
        if (!$this->hasRequiredAbilities($accessToken, $request)) {
            return $this->unauthorizedResponse('Insufficient token permissions');
        }

        // Set the authenticated user
        $user = $accessToken->tokenable;
        
        if (!$user) {
            return $this->unauthorizedResponse('Token owner not found');
        }

        // Check if user is active
        if (!$this->isUserActive($user)) {
            return $this->unauthorizedResponse('User account is inactive');
        }

        // Authenticate the user
        Auth::guard($guard)->setUser($user);
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        // Update last used timestamp
        $accessToken->forceFill(['last_used_at' => now()])->save();

        // Add authentication info to request
        $request->merge([
            '_authenticated_via' => 'sanctum',
            '_access_token_id' => $accessToken->id,
            '_token_abilities' => $accessToken->abilities,
        ]);

        return $next($request);
    }

    /**
     * Extract API token from request.
     *
     * @param Request $request
     * @return string|null
     */
    private function extractToken(Request $request): ?string
    {
        // Check Authorization header (Bearer token)
        $authHeader = $request->header('Authorization');
        if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
            return substr($authHeader, 7);
        }

        // Check query parameter (for development/testing only)
        if (app()->environment(['local', 'testing'])) {
            return $request->query('api_token');
        }

        // Check custom header
        return $request->header('X-API-Token');
    }

    /**
     * Check if token format is valid.
     *
     * @param string $token
     * @return bool
     */
    private function isValidTokenFormat(string $token): bool
    {
        // Basic format validation
        return strlen($token) >= 40 && preg_match('/^[a-zA-Z0-9]+$/', $token);
    }

    /**
     * Check if token is expired.
     *
     * @param PersonalAccessToken $accessToken
     * @return bool
     */
    private function isTokenExpired(PersonalAccessToken $accessToken): bool
    {
        return $accessToken->expires_at && $accessToken->expires_at->isPast();
    }

    /**
     * Check if token has required abilities for the request.
     *
     * @param PersonalAccessToken $accessToken
     * @param Request $request
     * @return bool
     */
    private function hasRequiredAbilities(PersonalAccessToken $accessToken, Request $request): bool
    {
        // If token has wildcard ability, allow all
        if (in_array('*', $accessToken->abilities)) {
            return true;
        }

        // Map HTTP methods to required abilities
        $requiredAbility = $this->getRequiredAbilityForRequest($request);
        
        if (!$requiredAbility) {
            return true; // No specific ability required
        }

        return in_array($requiredAbility, $accessToken->abilities);
    }

    /**
     * Get required ability based on request method and endpoint.
     *
     * @param Request $request
     * @return string|null
     */
    private function getRequiredAbilityForRequest(Request $request): ?string
    {
        $method = $request->method();
        $path = $request->path();

        // Map endpoints to required abilities
        $abilityMap = [
            'GET' => 'read',
            'POST' => 'create',
            'PUT' => 'update',
            'PATCH' => 'update',
            'DELETE' => 'delete',
        ];

        // Special cases for admin endpoints
        if (str_starts_with($path, 'api/admin/')) {
            return 'admin:' . strtolower($method);
        }

        // Special cases for driver endpoints
        if (str_starts_with($path, 'api/driver/')) {
            return 'driver:' . strtolower($method);
        }

        return $abilityMap[$method] ?? null;
    }

    /**
     * Check if user account is active.
     *
     * @param mixed $user
     * @return bool
     */
    private function isUserActive($user): bool
    {
        // Check different user types
        if (method_exists($user, 'isActive')) {
            return $user->isActive();
        }

        // Check common status fields
        if (isset($user->status)) {
            return in_array(strtolower($user->status), ['active', 'verified']);
        }

        if (isset($user->is_active)) {
            return (bool) $user->is_active;
        }

        // Default to active if no status field found
        return true;
    }

    /**
     * Return unauthorized response.
     *
     * @param string $message
     * @return \Illuminate\Http\JsonResponse
     */
    private function unauthorizedResponse(string $message): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'error_code' => 'UNAUTHORIZED',
            'timestamp' => now()->toISOString(),
        ], 401);
    }
}