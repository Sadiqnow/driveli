<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'invoice_number' => $this->invoice_number,
            'amount' => $this->amount,
            'description' => $this->description,
            'status' => $this->status,
            'due_date' => $this->due_date,
            'paid_at' => $this->paid_at,
            'items' => $this->items,
            'company' => CompanyResource::make($this->whenLoaded('company')),
            'company_request' => CompanyRequestResource::make($this->whenLoaded('companyRequest')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
