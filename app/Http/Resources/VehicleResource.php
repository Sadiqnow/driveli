<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class VehicleResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'registration_number' => $this->registration_number,
            'make' => $this->make,
            'model' => $this->model,
            'year' => $this->year,
            'color' => $this->color,
            'vin' => $this->vin,
            'engine_number' => $this->engine_number,
            'chassis_number' => $this->chassis_number,
            'vehicle_type' => $this->vehicle_type,
            'seating_capacity' => $this->seating_capacity,
            'purchase_price' => $this->purchase_price,
            'purchase_date' => $this->purchase_date,
            'current_value' => $this->current_value,
            'insurance_expiry' => $this->insurance_expiry,
            'insurance_provider' => $this->insurance_provider,
            'road_worthiness_expiry' => $this->road_worthiness_expiry,
            'mileage' => $this->mileage,
            'status' => $this->status,
            'notes' => $this->notes,
            'features' => $this->features,
            'fleet' => FleetResource::make($this->whenLoaded('fleet')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
