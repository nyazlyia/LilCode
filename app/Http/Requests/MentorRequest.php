<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MentorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'level' => ['required', 'string', 'regex:/^[1-3]-[1-5]$/'],
            'program' => ['present', 'array', 'max:40'],
            'status' => ['nullable', 'in:crash,incomplete,too_long,success,too_many_blocks'],
            'tier' => ['required', 'integer', 'between:1,3'],
            'question' => ['nullable', 'string', 'max:120'],
        ];
    }
}
