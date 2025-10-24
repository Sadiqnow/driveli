<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifyDriverRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Admin authorization handled by middleware
    }

    public function rules()
    {
        return [
            'driver_id' => 'sometimes|integer|exists:drivers,id',
        ];
    }
}
