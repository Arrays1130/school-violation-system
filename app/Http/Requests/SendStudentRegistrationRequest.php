<?php

namespace App\Http\Requests;

use App\Http\Concerns\ValidatesRecaptcha;
use Illuminate\Foundation\Http\FormRequest;

class SendStudentRegistrationRequest extends FormRequest
{
    use ValidatesRecaptcha;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return array_merge([
            'full_name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'unique:students,email',
                function ($attribute, $value, $fail) {
                    if (! str_ends_with(strtolower($value), '@ilinkcst.edu.ph')) {
                        $fail('The email must be an institutional @ilinkcst.edu.ph address.');
                    }
                },
            ],
            'section' => 'required|string|max:255',
            'year_level' => 'required|string|max:255',
            'academic_year' => 'nullable|string|max:255',
            'department' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'guardian_name' => 'nullable|string|max:255',
            'guardian_email' => 'nullable|email',
            'guardian_phone' => 'nullable|string|max:20',
        ], $this->recaptchaRules());
    }

    public function messages(): array
    {
        return $this->recaptchaMessages();
    }
}
