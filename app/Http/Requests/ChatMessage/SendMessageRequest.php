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
        $rules = [
            'type' => ['required', 'string', Rule::in(['text', 'image', 'video', 'audio', 'file'])],
        ];

        if ($this->type === 'text') {
            $rules['content'] = ['required', 'string'];
        } else {
            $rules['content'] = ['required', 'file', 'max:20480']; // 20MB max

            if ($this->type === 'image') {
                $rules['content'][] = 'mimes:jpg,jpeg,png,gif,webp';
            } elseif ($this->type === 'video') {
                $rules['content'][] = 'mimes:mp4,mov,avi,webm';
            } elseif ($this->type === 'audio') {
                $rules['content'][] = 'mimes:mp3,wav,aac,ogg,webm';
            }
            // 'file' type accepts any file extension
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'content.required' => 'Message content is required',
            'content.string' => 'Text message content must be a string',
            'content.file' => 'Content must be a valid file',
            'content.max' => 'File size must not exceed 20MB',
            'content.mimes' => 'Invalid file format for the selected type',
            'type.in' => 'Message type must be text, image, video, audio, or file',
        ];
    }
}
