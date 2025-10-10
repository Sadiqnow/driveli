<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// use App\Constants\DrivelinkConstants;

class Guarantor extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'first_name',
        'last_name',
        'relationship',
        'phone',
        'email',
        'address',
        'state',
        'lga',
        'nin',
        'occupation',
        'employer',
        'how_long_known',
        'id_document',
        'passport_photograph',
        'attestation_letter',
        'verification_status',
        'verified_at',
        'verified_by',
        'verification_notes',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    // Relationships
    public function driver()
    {
        return $this->belongsTo(DriverNormalized::class, 'driver_id');
    }

    public function verifiedBy()
    {
        return $this->belongsTo(AdminUser::class, 'verified_by');
    }

    // Accessors
    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    // Alias for 'name' - returns full name
    public function getNameAttribute()
    {
        return $this->getFullNameAttribute();
    }

    // public function getFormattedPhoneAttribute()
    // {
    //     return \App\Helpers\DrivelinkHelper::formatNigerianPhone($this->phone);
    // }

    // public function getVerificationBadgeAttribute()
    // {
    //     switch ($this->verification_status) {
    //         case DrivelinkConstants::VERIFICATION_VERIFIED:
    //             return ['text' => 'Verified', 'class' => 'drivelink-status-verified'];
    //         case DrivelinkConstants::VERIFICATION_REJECTED:
    //             return ['text' => 'Rejected', 'class' => 'drivelink-status-rejected'];
    //         default:
    //             return ['text' => 'Pending', 'class' => 'drivelink-status-pending'];
    //     }
    // }

    // Methods
    // public function isVerified()
    // {
    //     return $this->verification_status === DrivelinkConstants::VERIFICATION_VERIFIED;
    // }
}