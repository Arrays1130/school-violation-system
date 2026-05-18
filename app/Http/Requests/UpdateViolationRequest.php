<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateViolationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->role === 'super_admin';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:50', 'unique:violations,code,' . $this->route('violation')->id],
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'in:Appearance,Attendance,Conduct,Academic,Other'],
            'severity' => ['required', 'string', 'in:Minor,Major'],
            'default_description' => ['nullable', 'string'],
        ];
    }
}
