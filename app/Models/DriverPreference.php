<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'vehicle_types',
        'experience_level',
        'years_of_experience',
        'preferred_routes',
        'working_hours',
        'special_skills',
    ];

    protected $casts = [
        'vehicle_types' => 'array',
        'preferred_routes' => 'array',
        'working_hours' => 'array',
        'special_skills' => 'array',
        'years_of_experience' => 'integer',
    ];

    public function driver()
    {
        return $this->belongsTo(DriverNormalized::class, 'driver_id');
    }

    public function getVehicleTypesTextAttribute()
    {
        return is_array($this->vehicle_types) ? implode(', ', $this->vehicle_types) : '';
    }

    public function getPreferredRoutesTextAttribute()
    {
        return is_array($this->preferred_routes) ? implode(', ', $this->preferred_routes) : '';
    }

    public function getSpecialSkillsTextAttribute()
    {
        return is_array($this->special_skills) ? implode(', ', $this->special_skills) : '';
    }

    public function getWorkingHoursTextAttribute()
    {
        if (!is_array($this->working_hours)) return '';
        
        $startTime = $this->working_hours['start'] ?? '09:00';
        $endTime = $this->working_hours['end'] ?? '17:00';
        
        return $startTime . ' - ' . $endTime;
    }
}