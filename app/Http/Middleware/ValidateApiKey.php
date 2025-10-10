<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ValidateApiKey
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-API-Key') ?? $request->get('api_key');
        
        if (!$apiKey) {
            return response()->json([
                'success' => false,
                'message' => 'API key is required',
                'error' => 'MISSING_API_KEY'
            ], 401);
        }
        
        // Validate API key format
        if (!$this->isValidApiKeyFormat($apiKey)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid API key format',
                'error' => 'INVALID_API_KEY_FORMAT'
            ], 401);
        }
        
        // Additional validation logic would go here
        // For example, checking against a database of valid API keys
        
        return $next($request);
    }
    
    /**
     * Validate API key format
     */
    private function isValidApiKeyFormat(string $apiKey): bool
    {
        // API key should be at least 32 characters and alphanumeric with some special chars
        return preg_match('/^[a-zA-Z0-9_\-\.]{32,}$/', $apiKey);
    }
}