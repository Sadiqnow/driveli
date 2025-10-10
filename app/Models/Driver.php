<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class Driver extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        // Basic Personal Information - these should exist in any drivers table
        'first_name', 'surname', 'email', 'phone', 'date_of_birth', 'password'
    ];

    protected $hidden = [
        'password', 'remember_token'
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'email_verified_at' => 'datetime',
    ];

    // ===========================
    // ACCESSORS & MUTATORS
    // ===========================

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->surname);
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->first_name;
    }

    public function getAgeAttribute(): ?int
    {
        return $this->date_of_birth?->age;
    }

    public function setPasswordAttribute($value)
    {
        if (!empty($value)) {
            $this->attributes['password'] = bcrypt($value);
        }
    }

    // ===========================
    // HELPER METHODS
    // ===========================

    public function isActive(): bool
    {
        return true; // Default to active for now
    }
}