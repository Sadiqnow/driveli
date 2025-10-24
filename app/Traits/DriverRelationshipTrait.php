<?php

namespace App\Traits;

trait DriverRelationshipTrait
{
    // ========================================================================================
    // LOCATION RELATIONSHIPS
    // ========================================================================================

    /**
     * Origin location relationship
     */
    public function originLocation()
    {
        return $this->hasOne(DriverLocation::class, 'driver_id', 'id')
            ->select(['id', 'driver_id', 'location_type', 'state_id', 'lga_id', 'address', 'is_primary'])
            ->where('location_type', 'origin')
            ->where('is_primary', true)
            ->with(['state:id,name', 'lga:id,name']);
    }

    /**
     * Residence location relationship
     */
    public function residenceLocation()
    {
        return $this->hasOne(DriverLocation::class, 'driver_id', 'id')
            ->select(['id', 'driver_id', 'location_type', 'state_id', 'lga_id', 'address', 'is_primary'])
            ->where('location_type', 'residence')
            ->where('is_primary', true)
            ->with(['state:id,name', 'lga:id,name']);
    }

    /**
     * Birth location relationship
     */
    public function birthLocation()
    {
        return $this->hasOne(DriverLocation::class, 'driver_id', 'id')
            ->select(['id', 'driver_id', 'state_id', 'lga_id', 'address'])
            ->where('location_type', 'birth')
            ->where('is_primary', true)
            ->with(['state:id,name', 'lga:id,name']);
    }

    // ========================================================================================
    // SPECIALIZED RELATIONSHIPS
    // ========================================================================================

    /**
     * Primary next of kin relationship
     */
    public function primaryNextOfKin()
    {
        return $this->hasOne(DriverNextOfKin::class, 'driver_id', 'id')
            ->select(['id', 'driver_id', 'name', 'relationship', 'phone', 'address'])
            ->where('is_primary', true);
    }

    /**
     * Primary banking detail relationship
     */
    public function primaryBankingDetail()
    {
        return $this->hasOne(DriverBankingDetail::class, 'driver_id', 'id')
            ->select(['id', 'driver_id', 'bank_id', 'account_number', 'account_name', 'is_verified'])
            ->where('is_primary', true)
            ->with(['bank:id,name,code']);
    }

    // ========================================================================================
    // DOCUMENT RELATIONSHIPS
    // ========================================================================================

    /**
     * NIN document relationship
     */
    public function ninDocument()
    {
        return $this->hasOne(DriverDocument::class, 'driver_id', 'id')
            ->where('document_type', 'nin');
    }

    /**
     * License front document relationship
     */
    public function licenseFrontDocument()
    {
        return $this->hasOne(DriverDocument::class, 'driver_id', 'id')
            ->where('document_type', 'license_front');
    }

    /**
     * License back document relationship
     */
    public function licenseBackDocument()
    {
        return $this->hasOne(DriverDocument::class, 'driver_id', 'id')
            ->where('document_type', 'license_back');
    }

    // ========================================================================================
    // EMPLOYMENT RELATIONSHIPS
    // ========================================================================================

    /**
     * Current employment relationship
     */
    public function currentEmployment()
    {
        return $this->hasOne(DriverEmploymentHistory::class, 'driver_id', 'id')
            ->select(['id', 'driver_id', 'company_name', 'job_title', 'start_date'])
            ->whereNull('end_date')
            ->where('is_current', true);
    }
}
