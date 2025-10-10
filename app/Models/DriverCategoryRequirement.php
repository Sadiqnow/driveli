<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverCategoryRequirement extends Model
{
    use HasFactory;

    protected $fillable = [
        'category',
        'country_id',
        'required_licenses',
        'required_certifications',
        'required_documents',
        'background_check_requirements',
        'minimum_experience_years',
        'vehicle_requirements',
        'is_active',
    ];

    protected $casts = [
        'required_licenses' => 'array',
        'required_certifications' => 'array',
        'required_documents' => 'array',
        'background_check_requirements' => 'array',
        'vehicle_requirements' => 'array',
        'minimum_experience_years' => 'integer',
        'is_active' => 'boolean',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByCountry($query, $countryId)
    {
        return $query->where('country_id', $countryId);
    }

    // Methods
    public function getRequirementsByType($type)
    {
        $fieldMap = [
            'licenses' => 'required_licenses',
            'certifications' => 'required_certifications',
            'documents' => 'required_documents',
            'background_checks' => 'background_check_requirements',
            'vehicles' => 'vehicle_requirements',
        ];

        return $this->{$fieldMap[$type] ?? $type} ?? [];
    }

    public static function getRequirementsForCategory($category, $countryId = null)
    {
        $query = self::byCategory($category);
        
        if ($countryId) {
            $query->byCountry($countryId);
        } else {
            // Default to Nigeria if no country specified
            $nigeriaId = Country::where('iso_code_2', 'NG')->first()?->id ?? 1;
            $query->byCountry($nigeriaId);
        }

        return $query->active()->first();
    }
}