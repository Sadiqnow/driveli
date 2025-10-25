<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVehicleRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('update', $this->route('fleet'));
    }

    public function rules()
    {
        $vehicle = $this->route('vehicle');

        return [
            'registration_number' => 'sometimes|string|max:20|unique:vehicles,registration_number,' . $vehicle->id,
            'make' => 'sometimes|string|max:100',
            'model' => 'sometimes|string|max:100',
            'year' => 'sometimes|integer|min:1900|max:' . (date('Y') + 1),
            'color' => 'nullable|string|max:50',
            'vin' => 'nullable|string|max:50',
            'engine_number' => 'nullable|string|max:50',
            'chassis_number' => 'nullable|string|max:50',
            'vehicle_type' => 'sometimes|string|max:50',
            'seating_capacity' => 'sometimes|integer|min:1|max:100',
            'purchase_price' => 'nullable|numeric|min:0',
            'purchase_date' => 'nullable|date',
            'current_value' => 'nullable|numeric|min:0',
            'insurance_expiry' => 'nullable|date',
            'insurance_provider' => 'nullable|string|max:100',
            'road_worthiness_expiry' => 'nullable|date',
            'mileage' => 'nullable|integer|min:0',
            'status' => 'sometimes|in:active,maintenance,sold',
            'notes' => 'nullable|string|max:1000',
            'features' => 'nullable|array',
        ];
    }

    public function messages()
    {
        return [
            'registration_number.unique' => 'This registration number is already registered',
            'year.min' => 'Invalid manufacturing year',
            'year.max' => 'Manufacturing year cannot be in the future',
            'seating_capacity.min' => 'Seating capacity must be at least 1',
            'seating_capacity.max' => 'Seating capacity cannot exceed 100',
            'purchase_price.min' => 'Purchase price cannot be negative',
            'current_value.min' => 'Current value cannot be negative',
            'mileage.min' => 'Mileage cannot be negative',
            'status.in' => 'Invalid status selected',
            'features.array' => 'Features must be an array',
        ];
    }
}
