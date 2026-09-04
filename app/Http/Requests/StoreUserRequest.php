<?php

namespace App\Http\Requests;

use App\Support\DepartmentResolver;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\User::class);
    }

    protected function prepareForValidation(): void
    {
        $this->merge(self::normalizedDepartment($this->input('role'), $this->input('department')));
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => ['nullable', 'string', 'regex:/^(09\d{9}|\+639\d{9})$/'],
            'password' => 'required|min:8|confirmed',
            'role' => 'required|in:super_admin,admin,dean',
            'department' => [
                Rule::requiredIf(fn () => $this->input('role') === 'dean'),
                'nullable',
                Rule::in(DepartmentResolver::allShortcuts()),
            ],
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

    /**
     * @return array{department: ?string}
     */
    public static function normalizedDepartment(mixed $role, mixed $department): array
    {
        if ($role !== 'dean') {
            return ['department' => null];
        }

        $value = is_string($department) ? $department : null;

        return ['department' => DepartmentResolver::toShortcut($value) ?? $value];
    }
}
