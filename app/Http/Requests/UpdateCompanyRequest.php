<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCompanyRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('update', $this->route('company'));
    }

    public function rules()
    {
        $company = $this->route('company');

        return [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:companies,email,' . $company->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'website' => 'nullable|url|max:255',
            'industry' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:1000',
        ];
    }

    public function messages()
    {
        return [
            'email.email' => 'Please provide a valid email address',
            'email.unique' => 'This email address is already registered',
            'website.url' => 'Please provide a valid website URL',
        ];
    }
}
