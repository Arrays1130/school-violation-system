<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCaseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\StudentCase::class);
    }

    protected function prepareForValidation(): void
    {
        $studentIds = $this->input('student_ids');

        if (! is_array($studentIds) || count($studentIds) === 0) {
            if ($this->filled('student_id')) {
                $studentIds = [(int) $this->input('student_id')];
            } else {
                $studentIds = [];
            }
        }

        $studentIds = array_values(array_unique(array_map('intval', $studentIds)));

        $this->merge([
            'student_ids' => $studentIds,
            'student_id' => $studentIds[0] ?? $this->input('student_id'),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'required|integer|exists:students,id',
            'student_id' => 'nullable|exists:students,id',
            'violation_id' => 'required|exists:violations,id',
            'description' => 'required|string',
            'witness' => 'nullable|string|max:255',
            'occurred_at' => 'required|date',
        ];
    }

    public function messages(): array
    {
        return [
            'student_ids.required' => 'Select at least one student.',
            'student_ids.min' => 'Select at least one student.',
        ];
    }
}
