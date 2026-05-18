<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCaseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'description' => 'required|string',
            'witness' => 'nullable|string|max:255',
            'occurred_at' => 'required|date',
            'status' => 'required|in:Pending,Under OSA Review,Endorsed to Grievance,Hearing Scheduled,Approved,Closed,Dismissed',
        ];
    }
}
