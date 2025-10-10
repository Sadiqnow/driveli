<?php

namespace App\Traits;

// use App\Constants\DrivelinkConstants;

trait HasVerification
{
    // public function markAsVerified($verifiedBy = null, $notes = null)
    // {
    //     $this->update([
    //         'verification_status' => DrivelinkConstants::VERIFICATION_VERIFIED,
    //         'verified_at' => now(),
    //         'verified_by' => $verifiedBy,
    //         'verification_notes' => $notes,
    //     ]);
    // }

    // public function markAsRejected($verifiedBy = null, $notes = null)
    // {
    //     $this->update([
    //         'verification_status' => DrivelinkConstants::VERIFICATION_REJECTED,
    //         'verified_at' => now(),
    //         'verified_by' => $verifiedBy,
    //         'verification_notes' => $notes,
    //     ]);
    // }

    // public function resetVerification()
    // {
    //     $this->update([
    //         'verification_status' => DrivelinkConstants::VERIFICATION_PENDING,
    //         'verified_at' => null,
    //         'verified_by' => null,
    //         'verification_notes' => null,
    //     ]);
    // }

    // public function scopePendingVerification($query)
    // {
    //     return $query->where('verification_status', DrivelinkConstants::VERIFICATION_PENDING);
    // }

    // public function scopeVerified($query)
    // {
    //     return $query->where('verification_status', DrivelinkConstants::VERIFICATION_VERIFIED);
    // }

    // public function scopeRejected($query)
    // {
    //     return $query->where('verification_status', DrivelinkConstants::VERIFICATION_REJECTED);
    // }
}