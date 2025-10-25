<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DriverResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'driver_id' => $this->driver_id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'date_of_birth' => $this->date_of_birth,
            'license_number' => $this->license_number,
            'license_class' => $this->license_class,
            'license_expiry_date' => $this->license_expiry_date,
            'experience_years' => $this->experience_years,
            'status' => $this->status,
            'verification_status' => $this->verification_status,
            'completion_percentage' => $this->completion_percentage,
            'profile' => $this->whenLoaded('profile'),
            'locations' => $this->whenLoaded('locations'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
