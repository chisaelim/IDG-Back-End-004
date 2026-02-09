<?php

namespace App\Http\Requests\Chat;

use Illuminate\Contracts\Validation\Validator;
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
            'photo' => [
                'nullable',
                'base64image',
                'base64mimes:png,jpg,jpeg',
                'base64dimensions:width=454,height=454'
            ],
            'type' => ['required', 'string', Rule::in(['personal', 'group'])],
            'user_ids' => ['required', 'array', 'min:1', Rule::when($this->input('type') === 'personal', ['max:1'])],
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
            'user_ids.*.distinct' => 'Duplicate user IDs are not allowed',
            'photo.base64image' => 'The photo must be a valid base64 encoded image.',
            'photo.base64mimes' => 'The photo must be a file of type: png, jpg, jpeg.',
            'photo.base64dimensions' => 'The photo must have dimensions of 454x454 pixels.',
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            foreach ($this->input('user_ids', []) as $userId) {
                if ($userId == $this->user()->id) {
                    $validator->errors()->add('user_ids', 'You cannot add yourself to the chat.');
                    break;
                }
            }
        });
    }
}
