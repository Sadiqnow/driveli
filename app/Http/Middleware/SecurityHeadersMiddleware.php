<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Security Headers Middleware
 * 
 * Adds essential security headers to all HTTP responses to protect
 * against common web vulnerabilities.
 * 
 * @package App\Http\Middleware
 */
class SecurityHeadersMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Skip security headers for local development to avoid CSRF issues
        if (app()->environment('local', 'development')) {
            return $response;
        }

        // Add security headers
        $response->headers->add([
            // Prevent MIME type sniffing
            'X-Content-Type-Options' => 'nosniff',
            
            // Enable XSS protection
            'X-XSS-Protection' => '1; mode=block',
            
            // Control iframe embedding
            'X-Frame-Options' => 'DENY',
            
            // Prevent information disclosure
            'X-Powered-By' => 'DriveLink',
            
            // Referrer policy
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            
            // Feature policy / permissions policy
            'Permissions-Policy' => 'geolocation=(self), microphone=(), camera=()',
            
            // Content Security Policy for enhanced XSS protection
            'Content-Security-Policy' => $this->getContentSecurityPolicy($request),
            
            // Strict Transport Security (HTTPS only)
            'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains; preload',
            
            // Cross-Origin policies
            'Cross-Origin-Embedder-Policy' => 'require-corp',
            'Cross-Origin-Opener-Policy' => 'same-origin',
            'Cross-Origin-Resource-Policy' => 'same-origin',
        ]);

        // Remove potentially revealing headers
        $response->headers->remove('Server');
        $response->headers->remove('X-Powered-By');

        return $response;
    }

    /**
     * Generate Content Security Policy based on environment and request type.
     *
     * @param Request $request
     * @return string
     */
    private function getContentSecurityPolicy(Request $request): string
    {
        $isApi = $request->is('api/*');
        $isDev = app()->environment('local', 'development');

        $policies = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdnjs.cloudflare.com https://cdn.jsdelivr.net" . ($isDev ? " 'unsafe-eval'" : ""),
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdnjs.cloudflare.com",
            "font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com",
            "img-src 'self' data: https: https://via.placeholder.com",
            "connect-src 'self' https://cdnjs.cloudflare.com https://fonts.googleapis.com",
            "frame-ancestors 'none'",
            "base-uri 'self'",
            "form-action 'self'",
        ];

        // For API endpoints, be more restrictive
        if ($isApi) {
            $policies = [
                "default-src 'none'",
                "script-src 'none'",
                "style-src 'none'",
                "img-src 'none'",
                "font-src 'none'",
                "connect-src 'self'",
                "frame-ancestors 'none'",
                "base-uri 'none'",
                "form-action 'none'",
            ];
        }

        return implode('; ', $policies);
    }
}