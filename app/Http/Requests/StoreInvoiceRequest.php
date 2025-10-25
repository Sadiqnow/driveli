<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'company_request_id' => 'required|exists:company_requests,id',
            'amount' => 'required|numeric|min:0',
            'description' => 'required|string|max:500',
            'due_date' => 'required|date|after:today',
            'items' => 'nullable|array',
            'items.*.description' => 'required_with:items|string|max:255',
            'items.*.quantity' => 'required_with:items|integer|min:1',
            'items.*.unit_price' => 'required_with:items|numeric|min:0',
        ];
    }

    public function messages()
    {
        return [
            'company_request_id.required' => 'Company request is required',
            'company_request_id.exists' => 'Invalid company request selected',
            'amount.required' => 'Invoice amount is required',
            'amount.min' => 'Invoice amount cannot be negative',
            'description.required' => 'Invoice description is required',
            'due_date.required' => 'Due date is required',
            'due_date.after' => 'Due date must be in the future',
            'items.array' => 'Invoice items must be an array',
            'items.*.description.required_with' => 'Item description is required',
            'items.*.quantity.required_with' => 'Item quantity is required',
            'items.*.quantity.min' => 'Item quantity must be at least 1',
            'items.*.unit_price.required_with' => 'Item unit price is required',
            'items.*.unit_price.min' => 'Item unit price cannot be negative',
        ];
    }
}
