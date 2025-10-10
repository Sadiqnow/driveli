<?php

namespace App\Traits;

trait SecureSearch
{
    /**
     * Apply secure search filters to a query
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $search
     * @param array $searchFields
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function applySecureSearch($query, string $search, array $searchFields)
    {
        $search = trim($search);
        
        if (empty($search) || strlen($search) > 255) {
            return $query;
        }

        // Sanitize search input
        $sanitizedSearch = filter_var($search, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
        
        if (empty($sanitizedSearch)) {
            return $query;
        }

        return $query->where(function($q) use ($sanitizedSearch, $searchFields) {
            foreach ($searchFields as $index => $field) {
                if ($index === 0) {
                    $q->where($field, 'LIKE', '%' . addslashes($sanitizedSearch) . '%');
                } else {
                    $q->orWhere($field, 'LIKE', '%' . addslashes($sanitizedSearch) . '%');
                }
            }
        });
    }

    /**
     * Validate and sanitize search input
     *
     * @param string $search
     * @return string|null
     */
    protected function sanitizeSearchInput(string $search): ?string
    {
        $search = trim($search);
        
        if (empty($search) || strlen($search) > 255) {
            return null;
        }

        // Remove potentially dangerous characters
        $sanitized = filter_var($search, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
        
        // Additional security: remove SQL injection patterns
        $patterns = [
            '/\bUNION\b/i',
            '/\bSELECT\b/i',
            '/\bINSERT\b/i',
            '/\bUPDATE\b/i',
            '/\bDELETE\b/i',
            '/\bDROP\b/i',
            '/\bALTER\b/i',
            '/\bCREATE\b/i',
            '/--/',
            '/\/\*/',
            '/\*\//',
            '/;/',
        ];
        
        $sanitized = preg_replace($patterns, '', $sanitized);
        
        return empty($sanitized) ? null : $sanitized;
    }
}