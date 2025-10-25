<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CompanyRequestResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'request_id' => $this->request_id,
            'pickup_location' => $this->pickup_location,
            'pickup_state' => $this->whenLoaded('pickupState'),
            'pickup_lga' => $this->whenLoaded('pickupLga'),
            'dropoff_location' => $this->dropoff_location,
            'dropoff_state' => $this->whenLoaded('dropoffState'),
            'dropoff_lga' => $this->whenLoaded('dropoffLga'),
            'vehicle_type' => $this->vehicle_type,
            'cargo_type' => $this->cargo_type,
            'cargo_description' => $this->cargo_description,
            'weight_kg' => $this->weight_kg,
            'value_naira' => $this->value_naira,
            'pickup_date' => $this->pickup_date,
            'delivery_deadline' => $this->delivery_deadline,
            'special_requirements' => $this->special_requirements,
            'budget_min' => $this->budget_min,
            'budget_max' => $this->budget_max,
            'experience_required' => $this->experience_required,
            'urgency' => $this->urgency,
            'status' => $this->status,
            'matches' => CompanyMatchResource::collection($this->whenLoaded('matches')),
            'company' => CompanyResource::make($this->whenLoaded('company')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
