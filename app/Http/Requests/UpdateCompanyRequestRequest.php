<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCompanyRequestRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('update', $this->route('companyRequest'));
    }

    public function rules()
    {
        return [
            'pickup_location' => 'sometimes|string|max:255',
            'pickup_state_id' => 'sometimes|exists:states,id',
            'pickup_lga_id' => 'sometimes|exists:lgas,id',
            'dropoff_location' => 'nullable|string|max:255',
            'dropoff_state_id' => 'nullable|exists:states,id',
            'dropoff_lga_id' => 'nullable|exists:lgas,id',
            'vehicle_type' => 'sometimes|string|max:100',
            'cargo_type' => 'nullable|string|max:100',
            'cargo_description' => 'nullable|string|max:500',
            'weight_kg' => 'nullable|numeric|min:0',
            'value_naira' => 'nullable|numeric|min:0',
            'pickup_date' => 'sometimes|date|after:now',
            'delivery_deadline' => 'nullable|date|after:pickup_date',
            'special_requirements' => 'nullable|string|max:1000',
            'budget_min' => 'nullable|numeric|min:0',
            'budget_max' => 'nullable|numeric|min:0|gte:budget_min',
            'experience_required' => 'nullable|integer|min:0|max:50',
            'urgency' => 'sometimes|in:low,medium,high,critical',
            'status' => 'sometimes|in:pending,active,completed,cancelled',
        ];
    }

    public function messages()
    {
        return [
            'pickup_state_id.exists' => 'Invalid pickup state selected',
            'pickup_lga_id.exists' => 'Invalid pickup LGA selected',
            'pickup_date.after' => 'Pickup date must be in the future',
            'delivery_deadline.after' => 'Delivery deadline must be after pickup date',
            'budget_max.gte' => 'Maximum budget must be greater than or equal to minimum budget',
            'urgency.in' => 'Invalid urgency level selected',
            'status.in' => 'Invalid status selected',
        ];
    }
}
