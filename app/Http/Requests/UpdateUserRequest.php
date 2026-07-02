<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('user'));
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($this->route('user')->id)],
            'role' => 'required|in:super_admin,admin,dean',
            'department' => 'nullable|string|max:255',
            'password' => 'nullable|min:8|confirmed',
        ];
    }
}
