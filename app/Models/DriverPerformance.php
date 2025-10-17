<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverPerformance extends Model
{
    use HasFactory;

    protected $table = 'driver_performance';

    protected $fillable = [
        'driver_id',
        'current_location_lat',
        'current_location_lng',
        'current_city',
        'total_jobs_completed',
        'average_rating',
        'total_ratings',
        'total_earnings',
        'last_job_completed_at',
    ];

    protected $casts = [
        'current_location_lat' => 'decimal:8',
        'current_location_lng' => 'decimal:8',
        'total_jobs_completed' => 'integer',
        'average_rating' => 'decimal:2',
        'total_ratings' => 'integer',
        'total_earnings' => 'decimal:2',
        'last_job_completed_at' => 'datetime',
    ];

    public function driver()
    {
        return $this->belongsTo(Driver::class, 'driver_id');
    }

    public function getFormattedEarningsAttribute()
    {
        return '₦' . number_format($this->total_earnings, 2);
    }

    public function getSuccessRateAttribute()
    {
        if ($this->total_jobs_completed === 0) return 0;
        return round(($this->total_jobs_completed / max($this->total_jobs_completed, 1)) * 100, 1);
    }

    public function getCompletionRateAttribute()
    {
        if ($this->total_jobs_completed === 0) return 0.00;
        return min(100.00, ($this->total_jobs_completed * 10));
    }

    public function getRatingStarsAttribute()
    {
        return str_repeat('★', floor($this->average_rating)) . 
               str_repeat('☆', 5 - floor($this->average_rating));
    }
}