<?php

namespace App\Http\Requests\Chat;

use Illuminate\Foundation\Http\FormRequest;

class UpdateChatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:250'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Chat name is required',
            'name.max' => 'Chat name must not exceed 250 characters',
        ];
    }
}
