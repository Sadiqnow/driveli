<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class DriverDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'document_type',
        'document_path',
        'file_content',
        'document_number',
        'issue_date',
        'expiry_date',
        'verification_status',
        'verified_at',
        'verification_cost',
        'ocr_data',
        'ocr_match_score',
        'verification_notes',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
        'verified_at' => 'datetime',
        'verification_cost' => 'decimal:2',
        'ocr_data' => 'array',
        'ocr_match_score' => 'decimal:2',
    ];

    public function driver()
    {
        return $this->belongsTo(Drivers::class, 'driver_id', 'id');
    }

    public function verifiedBy()
    {
        return $this->belongsTo(AdminUser::class, 'verified_by');
    }

    public function getDocumentUrlAttribute()
    {
        return Storage::url($this->document_path);
    }

    public function getIsExpiredAttribute()
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    public function getIsVerifiedAttribute()
    {
        return $this->verification_status === 'approved';
    }

    public function getDocumentTypeNameAttribute()
    {
        return match($this->document_type) {
            'nin' => 'NIN Document',
            'license_front' => 'Driver License (Front)',
            'license_back' => 'Driver License (Back)',
            'profile_picture' => 'Profile Picture',
            'passport_photo' => 'Passport Photograph',
            'employment_letter' => 'Employment Letter',
            'service_certificate' => 'Service Certificate',
            default => ucfirst(str_replace('_', ' ', $this->document_type))
        };
    }

    public function scopeVerified($query)
    {
        return $query->where('verification_status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('verification_status', 'pending');
    }

    public function scopeRejected($query)
    {
        return $query->where('verification_status', 'rejected');
    }

    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<', now());
    }

    public function scopeByType($query, $type)
    {
        return $query->where('document_type', $type);
    }
}