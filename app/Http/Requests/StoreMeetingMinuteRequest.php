<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMeetingMinuteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\StudentCase::class);
    }

    public function rules(): array
    {
        return [
            'case_id' => 'nullable|exists:cases,id',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'meeting_date' => 'required|date',
            'venue' => 'required|string|max:255',
        ];
    }
}
