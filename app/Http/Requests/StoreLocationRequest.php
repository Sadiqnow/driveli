<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLocationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Only authenticated drivers can submit location updates
        return auth('api')->check() && auth('api')->user()->user_type === 'driver';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy' => 'nullable|numeric|min:0|max:100',
            'device_info' => 'nullable|string|max:255',
            'metadata' => 'nullable|array',
            'metadata.speed' => 'nullable|numeric|min:0',
            'metadata.heading' => 'nullable|numeric|between:0,359',
            'metadata.altitude' => 'nullable|numeric',
            'metadata.battery_level' => 'nullable|numeric|between:0,100',
            'metadata.network_type' => 'nullable|string|in:wifi,4g,5g,3g,2g',
            'recorded_at' => 'nullable|date|before_or_equal:now',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'latitude.required' => 'Latitude is required.',
            'latitude.numeric' => 'Latitude must be a valid number.',
            'latitude.between' => 'Latitude must be between -90 and 90 degrees.',
            'longitude.required' => 'Longitude is required.',
            'longitude.numeric' => 'Longitude must be a valid number.',
            'longitude.between' => 'Longitude must be between -180 and 180 degrees.',
            'accuracy.numeric' => 'Accuracy must be a valid number.',
            'accuracy.min' => 'Accuracy must be at least 0 meters.',
            'accuracy.max' => 'Accuracy cannot exceed 100 meters.',
            'device_info.string' => 'Device info must be a valid string.',
            'device_info.max' => 'Device info cannot exceed 255 characters.',
            'metadata.array' => 'Metadata must be a valid array.',
            'metadata.speed.numeric' => 'Speed must be a valid number.',
            'metadata.speed.min' => 'Speed must be at least 0.',
            'metadata.heading.numeric' => 'Heading must be a valid number.',
            'metadata.heading.between' => 'Heading must be between 0 and 359 degrees.',
            'metadata.altitude.numeric' => 'Altitude must be a valid number.',
            'metadata.battery_level.numeric' => 'Battery level must be a valid number.',
            'metadata.battery_level.between' => 'Battery level must be between 0 and 100.',
            'metadata.network_type.in' => 'Network type must be one of: wifi, 4g, 5g, 3g, 2g.',
            'recorded_at.date' => 'Recorded at must be a valid date.',
            'recorded_at.before_or_equal' => 'Recorded at cannot be in the future.',
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        // Set default recorded_at if not provided
        if (!$this->has('recorded_at')) {
            $this->merge(['recorded_at' => now()->toISOString()]);
        }
    }
}
