<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FleetResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'manager_name' => $this->manager_name,
            'manager_phone' => $this->manager_phone,
            'manager_email' => $this->manager_email,
            'operating_regions' => $this->operating_regions,
            'base_location' => $this->base_location,
            'status' => $this->status,
            'company' => CompanyResource::make($this->whenLoaded('company')),
            'vehicles' => VehicleResource::collection($this->whenLoaded('vehicles')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
