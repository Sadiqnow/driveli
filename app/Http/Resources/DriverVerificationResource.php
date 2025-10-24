<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DriverVerificationResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'success' => $this->resource['success'] ?? true,
            'message' => $this->resource['message'] ?? null,
            'driver_id' => $this->resource['driver_id'] ?? null,
            'status' => $this->resource['status'] ?? null,
            'report' => $this->resource['report'] ?? null,
            'timestamp' => now()->toISOString(),
        ];
    }
}
