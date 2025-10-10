<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class DriverEmploymentHistory extends Model
{
    use HasFactory;

    protected $table = 'driver_employment_history';

    protected $fillable = [
        'driver_id',
        'company_name',
        'rc_number',
        'start_date',
        'end_date',
        'vehicle_plate_number',
        'vehicle_cab_number',
        'reason_for_leaving',
        'employment_letter_path',
        'service_certificate_path',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function driver()
    {
        return $this->belongsTo(DriverNormalized::class, 'driver_id');
    }

    public function getDurationAttribute()
    {
        $endDate = $this->end_date ?: now();
        return $this->start_date->diffInMonths($endDate);
    }

    public function getDurationTextAttribute()
    {
        $months = $this->duration;
        if ($months < 12) {
            return $months . ' month(s)';
        }
        
        $years = floor($months / 12);
        $remainingMonths = $months % 12;
        
        $text = $years . ' year(s)';
        if ($remainingMonths > 0) {
            $text .= ' ' . $remainingMonths . ' month(s)';
        }
        
        return $text;
    }

    public function getEmploymentLetterUrlAttribute()
    {
        return $this->employment_letter_path ? Storage::url($this->employment_letter_path) : null;
    }

    public function getServiceCertificateUrlAttribute()
    {
        return $this->service_certificate_path ? Storage::url($this->service_certificate_path) : null;
    }

    public function getIsCurrentAttribute()
    {
        return is_null($this->end_date);
    }

    public function scopeCurrent($query)
    {
        return $query->whereNull('end_date');
    }

    public function scopePrevious($query)
    {
        return $query->whereNotNull('end_date');
    }
}