<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
// use App\Constants\DrivelinkConstants;

class Company extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable; // SoftDeletes temporarily disabled until column is added

    protected $fillable = [
        'name',
        'company_id',
        'registration_number',
        'tax_id',
        'email',
        'phone',
        'website',
        'address',
        'state',
        'lga',
        'postal_code',
        'industry',
        'company_size',
        'description',
        'contact_person_name',
        'contact_person_title',
        'contact_person_phone',
        'contact_person_email',
        'default_commission_rate',
        'payment_terms',
        'preferred_regions',
        'vehicle_types_needed',
        'status',
        'verification_status',
        'verified_at',
        'verified_by',
        'logo',
        'registration_certificate',
        'tax_certificate',
        'additional_documents',
        'total_requests',
        'fulfilled_requests',
        'total_amount_paid',
        'average_rating',
        'password',
        'email_verified_at',
        'remember_token',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'email_verified_at' => 'datetime',
        'preferred_regions' => 'array',
        'vehicle_types_needed' => 'array',
        'additional_documents' => 'array',
        'default_commission_rate' => 'decimal:2',
        'total_amount_paid' => 'decimal:2',
        'average_rating' => 'decimal:2',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Mutators
    public function setPasswordAttribute($value)
    {
        if (!empty($value)) {
            $this->attributes['password'] = \Illuminate\Support\Facades\Hash::make($value);
        }
    }

    // Relationships
    public function verifiedBy()
    {
        return $this->belongsTo(AdminUser::class, 'verified_by');
    }

    public function requests()
    {
        return $this->hasMany(CompanyRequest::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }

    public function scopeVerified($query)
    {
        return $query->where('verification_status', 'Verified');
    }

    public function scopeByIndustry($query, $industry)
    {
        return $query->where('industry', $industry);
    }

    // Accessors
    public function getFormattedPhoneAttribute()
    {
        return \App\Helpers\DrivelinkHelper::formatNigerianPhone($this->phone);
    }

    public function getFormattedContactPhoneAttribute()
    {
        return \App\Helpers\DrivelinkHelper::formatNigerianPhone($this->contact_person_phone);
    }

    public function getFulfillmentRateAttribute()
    {
        if ($this->total_requests === 0) return 0;
        return round(($this->fulfilled_requests / $this->total_requests) * 100, 1);
    }

    public function getVerificationBadgeAttribute()
    {
        switch ($this->verification_status) {
            case 'Verified':
                return ['text' => 'Verified', 'class' => 'drivelink-status-verified'];
            case 'Rejected':
                return ['text' => 'Rejected', 'class' => 'drivelink-status-rejected'];
            default:
                return ['text' => 'Pending', 'class' => 'drivelink-status-pending'];
        }
    }

    // Methods
    public function isActive()
    {
        return $this->status === 'Active';
    }

    public function isVerified()
    {
        return $this->verification_status === 'Verified';
    }

    public static function generateCompanyId()
    {
        $prefix = 'COMP';
        $lastCompany = self::orderBy('id', 'desc')->first();
        $nextNumber = $lastCompany ? (int)substr($lastCompany->company_id, 4) + 1 : 1;
        
        return $prefix . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }
}