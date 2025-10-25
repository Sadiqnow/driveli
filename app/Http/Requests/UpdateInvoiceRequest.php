<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInvoiceRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('update', $this->route('invoice'));
    }

    public function rules()
    {
        return [
            'amount' => 'sometimes|numeric|min:0',
            'description' => 'sometimes|string|max:500',
            'due_date' => 'sometimes|date|after:today',
            'status' => 'sometimes|in:pending,paid,overdue,cancelled',
            'items' => 'nullable|array',
            'items.*.description' => 'required_with:items|string|max:255',
            'items.*.quantity' => 'required_with:items|integer|min:1',
            'items.*.unit_price' => 'required_with:items|numeric|min:0',
        ];
    }

    public function messages()
    {
        return [
            'amount.min' => 'Invoice amount cannot be negative',
            'due_date.after' => 'Due date must be in the future',
            'status.in' => 'Invalid status selected',
            'items.array' => 'Invoice items must be an array',
            'items.*.description.required_with' => 'Item description is required',
            'items.*.quantity.required_with' => 'Item quantity is required',
            'items.*.quantity.min' => 'Item quantity must be at least 1',
            'items.*.unit_price.required_with' => 'Item unit price is required',
            'items.*.unit_price.min' => 'Item unit price cannot be negative',
        ];
    }
}
