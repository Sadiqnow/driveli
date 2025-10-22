<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifyDriverRequest extends FormRequest
{
    public function authorize()
    {
        return true; // TODO: Implement authorization logic
    }

    public function rules()
    {
        return [
            // TODO: Define validation rules for driver verification
            'driver_id' => 'required|integer',
            'documents' => 'required|array',
        ];
    }
}
