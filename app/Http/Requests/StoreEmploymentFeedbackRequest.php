<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmploymentFeedbackRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true; // Public form, no authorization needed
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'employment_start_date' => 'nullable|date|before_or_equal:today',
            'employment_end_date' => 'nullable|date|after_or_equal:employment_start_date',
            'performance_rating' => 'required|in:excellent,good,average,poor,very_poor',
            'reason_for_leaving' => 'nullable|string|max:1000',
            'feedback_notes' => 'nullable|string|max:2000',
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
            'performance_rating.required' => 'Please select a performance rating.',
            'performance_rating.in' => 'Please select a valid performance rating.',
            'employment_start_date.date' => 'Please enter a valid start date.',
            'employment_start_date.before_or_equal' => 'Start date cannot be in the future.',
            'employment_end_date.date' => 'Please enter a valid end date.',
            'employment_end_date.after_or_equal' => 'End date must be after or equal to start date.',
            'reason_for_leaving.max' => 'Reason for leaving cannot exceed 1000 characters.',
            'feedback_notes.max' => 'Additional comments cannot exceed 2000 characters.',
        ];
    }
}
