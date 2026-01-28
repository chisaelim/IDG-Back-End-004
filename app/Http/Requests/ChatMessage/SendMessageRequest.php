<?php

namespace App\Http\Requests\ChatMessage;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SendMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'content' => ['required'],
            'type' => ['required', 'string', Rule::in(['text'])],
        ];
    }

    public function messages(): array
    {
        return [
            'content.required' => 'Message content is required for chat messages',
            'type.in' => 'Message type must be text, image, video, or file',
        ];
    }
}
