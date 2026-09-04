<?php

namespace App\Http\Requests;

use App\Support\DepartmentResolver;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('user'));
    }

    protected function prepareForValidation(): void
    {
        $this->merge(StoreUserRequest::normalizedDepartment($this->input('role'), $this->input('department')));
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($this->route('user')->id)],
            'phone' => ['nullable', 'string', 'regex:/^(09\d{9}|\+639\d{9})$/'],
            'role' => 'required|in:super_admin,admin,dean',
            'department' => [
                Rule::requiredIf(fn () => $this->input('role') === 'dean'),
                'nullable',
                Rule::in(DepartmentResolver::allShortcuts()),
            ],
            'password' => 'nullable|min:8|confirmed',
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => 'Enter a valid PH mobile number (e.g. 09171234567 or +639171234567).',
            'department.required' => 'Select a department for this dean.',
            'department.required_if' => 'Select a department for this dean.',
            'department.in' => 'Select a valid school department.',
        ];
    }
}
