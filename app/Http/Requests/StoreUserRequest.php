<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\User::class);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => ['nullable', 'string', 'regex:/^(09\d{9}|\+639\d{9})$/'],
            'password' => 'required|min:8|confirmed',
            'role' => 'required|in:super_admin,admin,dean',
            'department' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => 'Enter a valid PH mobile number (e.g. 09171234567 or +639171234567).',
        ];
    }
}
