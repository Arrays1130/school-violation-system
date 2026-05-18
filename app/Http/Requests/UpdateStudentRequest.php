<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentRequest extends FormRequest
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
        // $this->route('student') returns the student model because of route model binding
        $studentId = $this->route('student')->id;

        return [
            'full_name' => 'required|string|max:255',
            'section' => 'required|string|max:100',
            'department' => 'required|string|max:100',
            'email' => 'required|email|unique:students,email,' . $studentId,
            'guardian_name' => 'nullable|string|max:255',
            'guardian_phone' => 'nullable|string|max:50',
        ];
    }
}
