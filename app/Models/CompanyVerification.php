<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'verification_type',
        'status',
        'submitted_documents',
        'verified_at',
        'verified_by',
        'rejection_reason',
        'notes'
    ];

    protected $casts = [
        'submitted_documents' => 'array',
        'verified_at' => 'datetime'
    ];

    /**
     * Get the company that owns the verification
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the admin user who verified this
     */
    public function verifiedBy()
    {
        return $this->belongsTo(AdminUser::class, 'verified_by');
    }

    /**
     * Scope for pending verifications
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for approved verifications
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope for rejected verifications
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope for under review verifications
     */
    public function scopeUnderReview($query)
    {
        return $query->where('status', 'under_review');
    }

    /**
     * Scope for verifications by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('verification_type', $type);
    }

    /**
     * Check if verification is completed
     */
    public function isCompleted(): bool
    {
        return in_array($this->status, ['approved', 'rejected']);
    }

    /**
     * Check if verification is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if verification is under review
     */
    public function isUnderReview(): bool
    {
        return $this->status === 'under_review';
    }
}
