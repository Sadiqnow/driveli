<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverNextOfKin extends Model
{
    use HasFactory;

    protected $table = 'driver_next_of_kin';

    protected $fillable = [
        'driver_id',
        'name',
        'phone',
        'address',
        'relationship',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function driver()
    {
        return $this->belongsTo(Driver::class, 'driver_id');
    }

    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }
}