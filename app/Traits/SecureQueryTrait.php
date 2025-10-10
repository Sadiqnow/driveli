<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

trait SecureQueryTrait
{
    /**
     * Execute a secure select query with parameter binding
     */
    protected function secureSelect(string $query, array $bindings = []): array
    {
        // Validate query doesn't contain dangerous operations
        if (preg_match('/\b(DROP|DELETE|INSERT|UPDATE|ALTER|CREATE|TRUNCATE)\b/i', $query)) {
            throw new \InvalidArgumentException('Only SELECT queries are allowed');
        }

        return DB::select($query, $bindings);
    }

    /**
     * Apply secure search functionality to query builder
     */
    protected function applySecureSearch(Builder $query, string $search, array $searchFields = []): Builder
    {
        // Sanitize search term
        $search = trim($search);
        if (empty($search) || strlen($search) > 255) {
            return $query;
        }

        // Use parameter binding for safe search
        return $query->where(function ($q) use ($search, $searchFields) {
            foreach ($searchFields as $field) {
                // Validate field name to prevent SQL injection
                if ($this->isValidFieldName($field)) {
                    $q->orWhere($field, 'LIKE', '%' . $search . '%');
                }
            }
        });
    }

    /**
     * Validate field name to prevent SQL injection
     */
    private function isValidFieldName(string $fieldName): bool
    {
        // Only allow alphanumeric characters, underscores, and dots for table.column notation
        return preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*(\.[a-zA-Z_][a-zA-Z0-9_]*)?$/', $fieldName);
    }

    /**
     * Execute secure paginated query
     */
    protected function securePaginate(Builder $query, int $perPage = 15, array $columns = ['*'])
    {
        // Validate per page limit
        $perPage = min(max($perPage, 1), 100);
        
        return $query->paginate($perPage, $columns);
    }

    /**
     * Bulk update with security checks
     */
    protected function secureBulkUpdate(string $table, array $updates, array $conditions = []): int
    {
        // Validate table name
        if (!$this->isValidTableName($table)) {
            throw new \InvalidArgumentException('Invalid table name');
        }

        $query = DB::table($table);
        
        // Apply conditions safely
        foreach ($conditions as $field => $value) {
            if ($this->isValidFieldName($field)) {
                $query->where($field, $value);
            }
        }

        return $query->update($updates);
    }

    /**
     * Validate table name
     */
    private function isValidTableName(string $tableName): bool
    {
        return preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $tableName);
    }

    /**
     * Get table schema information securely
     */
    protected function getTableSchema(string $tableName): array
    {
        if (!$this->isValidTableName($tableName)) {
            throw new \InvalidArgumentException('Invalid table name');
        }

        return $this->secureSelect("DESCRIBE {$tableName}");
    }

    /**
     * Check if table exists securely
     */
    protected function tableExists(string $tableName): bool
    {
        if (!$this->isValidTableName($tableName)) {
            return false;
        }

        $result = $this->secureSelect("SHOW TABLES LIKE ?", [$tableName]);
        return count($result) > 0;
    }
}