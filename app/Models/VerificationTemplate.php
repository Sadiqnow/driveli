<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VerificationTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'description',
        'template_data',
        'is_active',
        'priority'
    ];

    protected $casts = [
        'template_data' => 'array',
        'is_active' => 'boolean',
        'priority' => 'integer'
    ];

    /**
     * Scope for active templates
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for templates by type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for ordering by priority
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('priority', 'asc')->orderBy('name', 'asc');
    }

    /**
     * Get required fields from template data
     */
    public function getRequiredFields()
    {
        return $this->template_data['required_fields'] ?? [];
    }

    /**
     * Get validation rules from template data
     */
    public function getValidationRules()
    {
        return $this->template_data['validation_rules'] ?? [];
    }

    /**
     * Get API endpoints from template data
     */
    public function getApiEndpoints()
    {
        return $this->template_data['api_endpoints'] ?? [];
    }
}
