<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CompanyMatchResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'match_score' => $this->match_score,
            'status' => $this->status,
            'agreed_rate' => $this->agreed_rate,
            'notes' => $this->notes,
            'driver' => DriverResource::make($this->whenLoaded('driver')),
            'company_request' => CompanyRequestResource::make($this->whenLoaded('companyRequest')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
