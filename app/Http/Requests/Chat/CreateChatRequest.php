<?php

namespace App\Http\Requests\Chat;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateChatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'nullable',
                'string',
                'max:250',
                Rule::requiredIf(fn() => $this->input('type') === 'group')
            ],
            'type' => ['required', 'string', Rule::in(['personal', 'group'])],
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['required', 'integer', 'exists:users,id', 'distinct'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Group chat name is required',
            'type.in' => 'Chat type must be either personal or group',
            'user_ids.required' => 'At least one member is required',
            'user_ids.*.exists' => 'One or more selected users do not exist',
        ];
    }

    protected function prepareForValidation(): void
    {
        // For personal chat, ensure only one member is provided
        if ($this->input('type') === 'personal' && is_array($this->input('user_ids'))) {
            $this->merge([
                'user_ids' => array_slice($this->input('user_ids'), 0, 1)
            ]);
        }
    }
}
