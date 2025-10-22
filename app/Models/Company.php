<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Company extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

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
        'email_verified_at',
        'password',
    ];

    protected $hidden = [
        'password',
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
        'average_rating' => 'decimal:1',
    ];

    // Relationships
    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'verified_by');
    }

    public function companyRequests(): HasMany
    {
        return $this->hasMany(CompanyRequest::class);
    }

    public function companyVerifications(): HasMany
    {
        return $this->hasMany(CompanyVerification::class);
    }

    public function requestTemplates(): HasMany
    {
        return $this->hasMany(CompanyRequestTemplate::class);
    }

    public function driverMatches(): HasMany
    {
        return $this->hasMany(DriverMatch::class, 'company_request_id');
    }

    // Helper methods
    public function isVerified(): bool
    {
        return $this->verification_status === 'Verified';
    }

    public function isActive(): bool
    {
        return $this->status === 'Active';
    }

    public function getPendingRequestsCount(): int
    {
        return $this->companyRequests()->where('status', 'pending')->count();
    }

    public function getActiveRequestsCount(): int
    {
        return $this->companyRequests()->where('status', 'active')->count();
    }

    public function getFulfillmentRate(): float
    {
        if ($this->total_requests === 0) {
            return 0.0;
        }
        return round(($this->fulfilled_requests / $this->total_requests) * 100, 2);
    }

    public function updateStatistics(): void
    {
        $this->update([
            'total_requests' => $this->companyRequests()->count(),
            'fulfilled_requests' => $this->companyRequests()->where('status', 'completed')->count(),
        ]);
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

    public function scopePendingVerification($query)
    {
        return $query->where('verification_status', 'Pending');
    }

    public function scopeByIndustry($query, $industry)
    {
        return $query->where('industry', $industry);
    }

    public function scopeByState($query, $state)
    {
        return $query->where('state', $state);
    }
}
