<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GlobalVehicleType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'sub_category',
        'specifications',
        'license_requirements',
        'requires_special_training',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'specifications' => 'array',
        'license_requirements' => 'array',
        'requires_special_training' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeBySubCategory($query, $subCategory)
    {
        return $query->where('sub_category', $subCategory);
    }

    public function scopeRequiresTraining($query)
    {
        return $query->where('requires_special_training', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    // Methods
    public function getSpecification($key)
    {
        return $this->specifications[$key] ?? null;
    }

    public function hasLicenseRequirement($requirement)
    {
        return in_array($requirement, $this->license_requirements ?? []);
    }

    public static function getByCategory($category)
    {
        return self::byCategory($category)->active()->ordered()->limit(50)->get();
    }

    public static function getCommercialTruckTypes()
    {
        return self::byCategory('commercial_truck')->active()->ordered()->limit(20)->get();
    }

    public static function getProfessionalVehicleTypes()
    {
        return self::byCategory('professional')->active()->ordered()->limit(20)->get();
    }

    public static function getPublicVehicleTypes()
    {
        return self::byCategory('public')->active()->ordered()->limit(20)->get();
    }

    public static function getExecutiveVehicleTypes()
    {
        return self::byCategory('executive')->active()->ordered()->limit(20)->get();
    }
}