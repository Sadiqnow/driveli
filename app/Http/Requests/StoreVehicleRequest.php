<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVehicleRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('update', $this->route('fleet'));
    }

    public function rules()
    {
        return [
            'registration_number' => 'required|string|max:20|unique:vehicles',
            'make' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'color' => 'nullable|string|max:50',
            'vin' => 'nullable|string|max:50',
            'engine_number' => 'nullable|string|max:50',
            'chassis_number' => 'nullable|string|max:50',
            'vehicle_type' => 'required|string|max:50',
            'seating_capacity' => 'required|integer|min:1|max:100',
            'purchase_price' => 'nullable|numeric|min:0',
            'purchase_date' => 'nullable|date',
            'current_value' => 'nullable|numeric|min:0',
            'insurance_expiry' => 'nullable|date',
            'insurance_provider' => 'nullable|string|max:100',
            'road_worthiness_expiry' => 'nullable|date',
            'mileage' => 'nullable|integer|min:0',
            'notes' => 'nullable|string|max:1000',
            'features' => 'nullable|array',
        ];
    }

    public function messages()
    {
        return [
            'registration_number.required' => 'Registration number is required',
            'registration_number.unique' => 'This registration number is already registered',
            'make.required' => 'Vehicle make is required',
            'model.required' => 'Vehicle model is required',
            'year.required' => 'Manufacturing year is required',
            'year.min' => 'Invalid manufacturing year',
            'year.max' => 'Manufacturing year cannot be in the future',
            'vehicle_type.required' => 'Vehicle type is required',
            'seating_capacity.required' => 'Seating capacity is required',
            'seating_capacity.min' => 'Seating capacity must be at least 1',
            'seating_capacity.max' => 'Seating capacity cannot exceed 100',
            'purchase_price.min' => 'Purchase price cannot be negative',
            'current_value.min' => 'Current value cannot be negative',
            'mileage.min' => 'Mileage cannot be negative',
            'features.array' => 'Features must be an array',
        ];
    }
}
