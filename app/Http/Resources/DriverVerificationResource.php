<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DriverVerificationResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'driver_id' => $this->driver_id,
            'status' => $this->status,
            'verified_at' => $this->verified_at,
            // TODO: Add more fields as needed
        ];
    }
}
