<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCaseAttachmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
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
