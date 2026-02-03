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
            'photo' => [
                'nullable',
                'base64image',
                'base64mimes:png,jpg,jpeg',
                'base64dimensions:width=454,height=454'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Chat name is required',
            'name.max' => 'Chat name must not exceed 250 characters',
            'photo.base64image' => 'The photo must be a valid base64 encoded image.',
            'photo.base64mimes' => 'The photo must be a file of type: png, jpg, jpeg.',
            'photo.base64dimensions' => 'The photo must have dimensions of 454x454 pixels.',
        ];
    }
}
