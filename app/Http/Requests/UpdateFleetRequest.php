<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFleetRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('update', $this->route('fleet'));
    }

    public function rules()
    {
        return [
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:1000',
            'manager_name' => 'nullable|string|max:255',
            'manager_phone' => 'nullable|string|max:20',
            'manager_email' => 'nullable|email|max:255',
            'operating_regions' => 'nullable|array',
            'base_location' => 'nullable|string|max:255',
            'status' => 'sometimes|in:active,inactive',
        ];
    }

    public function messages()
    {
        return [
            'manager_email.email' => 'Please provide a valid manager email address',
            'operating_regions.array' => 'Operating regions must be an array',
            'status.in' => 'Invalid status selected',
        ];
    }
}
