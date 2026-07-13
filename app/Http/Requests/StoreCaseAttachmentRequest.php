<?php

namespace App\Http\Requests;

use App\Models\StudentCase;
use Illuminate\Foundation\Http\FormRequest;

class StoreCaseAttachmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $case = $this->route('case');

        if (! $case instanceof StudentCase) {
            $caseId = $this->input('case_id');
            if (! $caseId) {
                return false;
            }

            $case = StudentCase::find($caseId);
        }

        if (! $case) {
            return false;
        }

        return $this->user()->can('update', $case);
    }

    public function rules(): array
    {
        $rules = [
            'file' => 'required|file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png',
            'label' => 'nullable|string|max:255',
        ];

        if ($this->route('case') === null) {
            $rules['case_id'] = 'required|exists:cases,id';
        }

        return $rules;
    }
}
