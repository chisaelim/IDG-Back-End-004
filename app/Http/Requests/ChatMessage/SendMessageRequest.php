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
            'content' => [
                Rule::requiredIf(fn() => $this->input('type') === 'text'),
                'string',
            ],
            'type' => ['required', 'string', Rule::in(['text'])],
            'file' => [
                Rule::requiredIf(fn() => in_array($this->input('type'), ['image', 'video', 'file'])),
                'file',
                'max:51200', // 50MB max
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'content.required' => 'Message content is required for text messages',
            'type.in' => 'Message type must be text, image, video, or file',
            'file.required' => 'File is required for non-text messages',
            'file.max' => 'File size must not exceed 50MB',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Add specific file validation based on type
        $rules = $this->rules();
        
        if ($this->input('type') === 'image') {
            $rules['file'][] = 'mimes:jpeg,jpg,png,gif,webp';
        } elseif ($this->input('type') === 'video') {
            $rules['file'][] = 'mimes:mp4,mov,avi,wmv,flv,webm';
        }
    }
}
